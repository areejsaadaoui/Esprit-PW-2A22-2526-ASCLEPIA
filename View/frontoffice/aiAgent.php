<?php
require_once __DIR__ . '/../../Controller/AssuranceController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$input    = json_decode(file_get_contents('php://input'), true);
$message  = trim($input['message'] ?? '');
$lang     = $input['lang'] ?? 'en';
$messages = $input['messages'] ?? [];   // full conversation history from frontend
$langFull = $lang === 'fr' ? 'French' : 'English';

if (empty($message)) {
    echo json_encode(['error' => 'No message provided']);
    exit;
}

// Fetch all insurances live from DB
$assuranceC = new AssuranceController();
$result     = $assuranceC->listAssurances();
$assurances = $result->fetchAll(PDO::FETCH_ASSOC);

// Build insurance context string
$insuranceContext = "AVAILABLE INSURANCE PLANS:\n\n";
foreach ($assurances as $a) {
    $insuranceContext .= "- Name: {$a['nom_assurance']} | Type: {$a['TYPE']} | Price: {$a['prix']} DT/year | Reimbursement: {$a['taux_remboursement']}% | Duration: {$a['duree']} months | {$a['description']}\n";
}

$systemPrompt = "You are Dr. ASCLEPIA, a warm and caring AI health insurance advisor — like a trusted family doctor who also happens to know everything about insurance.

Respond ONLY in {$langFull}.

YOUR PERSONALITY:
- Warm, gentle and reassuring. You make people feel heard and understood.
- You speak simply and clearly, never with cold corporate language.
- You occasionally use light expressions like 'Great question!', 'No worries!', 'Let me help you with that!' to feel human.
- You remember everything the user told you earlier in the conversation and refer back to it naturally (e.g. 'Since you mentioned you have a family...' or 'Given your budget of X...').
- Keep it short and sweet — max 2-3 sentences per reply.

CONVERSATION BEHAVIOR:
- Always read the full conversation history before responding. Never ask for information the user already gave you.
- If the user seems unsure or vague, ask ONE clarifying question or give a helpful recommendation. Never ask multiple questions at once.
- Before recommending plans, if you don't know their budget yet, ask ONE simple question about it.
- Always mention the user's situation when recommending (e.g. 'For someone in your situation, DentaPlus is a great fit because...').
- NEVER recommend plans that exceed the user's stated budget. If a plan is too expensive, warn them kindly and skip it.
- If a plan clearly does NOT fit the user (wrong type, too expensive, wrong age group), warn them kindly.
- At the end of a recommendation, always suggest ONE best pick with a short reason.

STRICT FORMAT RULES:
- Keep answers SHORT. Max 2-3 sentences. Never write long paragraphs or walls of text.
- Never use markdown (no **, no ##, no bullet dashes, no numbered lists). Plain conversational text only.
- When mentioning plans, always use their EXACT names from the database so the system can display them as cards.
- End with a short warm follow-up question only when it adds value.

{$insuranceContext}";

// ── BUILD CONVERSATION PROMPT WITH FULL HISTORY ──
$conversationText = '';
if (!empty($messages)) {
    foreach ($messages as $msg) {
        $role = strtoupper($msg['role']); // USER / ASSISTANT
        $conversationText .= "{$role}: {$msg['content']}\n";
    }
} else {
    // fallback if no history sent
    $conversationText = "USER: {$message}\n";
}
$conversationText .= "ASSISTANT:";

$payload = [
    'model' => 'llama3.2:3b',
    'prompt' => $conversationText,
    'system' => $systemPrompt,
    'stream' => false,
];

$ch = curl_init('http://localhost:11434/api/generate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 120); // increased to 3 minutes for 3b model

$response = curl_exec($ch);
$error    = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(['error' => 'Could not connect to Ollama: ' . $error]);
    exit;
}

$data  = json_decode($response, true);
$reply = $data['response'] ?? '';

if (empty($reply)) {
    echo json_encode(['error' => 'No response from model. It may still be loading, please try again.']);
    exit;
}

// Clean up any accidental markdown
$reply = preg_replace('/\*\*(.*?)\*\*/', '$1', $reply);
$reply = preg_replace('/\*(.*?)\*/', '$1', $reply);
$reply = preg_replace('/#{1,6}\s/', '', $reply);
$reply = preg_replace('/^\s*[-*]\s/m', '', $reply);
$reply = trim($reply);

// ── EXTRACT USER BUDGET FROM CONVERSATION ──
$userBudget = null;
foreach ($messages as $msg) {
    if ($msg['role'] === 'user') {
        if (preg_match('/(\d+)\s*(dt|dinar|tnd)/i', $msg['content'], $budgetMatch)) {
            $userBudget = (float)$budgetMatch[1];
        }
    }
}
// also check current message
if ($userBudget === null && preg_match('/(\d+)\s*(dt|dinar|tnd)/i', $message, $budgetMatch)) {
    $userBudget = (float)$budgetMatch[1];
}

// ── AUTO CARD DETECTION ──
$mentionedPlans = [];
$replyLower = strtolower($reply);

foreach ($assurances as $a) {
    $planNameLower = strtolower($a['nom_assurance']);
    if (strpos($replyLower, $planNameLower) !== false) {

        // Skip plans that exceed user budget (yearly price vs monthly budget * 12)
        if ($userBudget !== null && (float)$a['prix'] > ($userBudget * 12)) {
            continue;
        }

        $mentionedPlans[] = [
            'name'          => $a['nom_assurance'],
            'type'          => $a['TYPE'],
            'price'         => $a['prix'],
            'reimbursement' => $a['taux_remboursement'],
            'duration'      => $a['duree'],
            'description'   => $a['description'],
        ];
    }
}

// Detect if this looks like a comparison request
$isComparison = preg_match('/compar|versus|vs\b|différence|difference|which is better/i', $message);

echo json_encode([
    'response'      => $reply,
    'plans'         => $mentionedPlans,
    'is_comparison' => (bool)$isComparison,
]);