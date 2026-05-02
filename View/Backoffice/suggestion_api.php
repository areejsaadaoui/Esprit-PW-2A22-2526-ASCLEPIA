<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$inputText = $_GET['text'] ?? '';
$isInitial = isset($_GET['initial']) && $_GET['initial'] === 'true';
$inputText = trim($inputText);

// Clé API Claude
$apiKey = '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'; // Remplacer par la vraie clé

// ============================================
// Mode initial : 3 suggestions sans contexte
// ============================================
if ($isInitial) {
    $prompt = "Tu es un assistant pour un forum médical en français (ASCLEPIA). 
Génère exactement 3 courtes suggestions de réponse (max 8 mots chacune) que quelqu'un pourrait écrire dans un forum de santé.
Ces suggestions doivent être variées, naturelles, et utiles dans un contexte médical.
Réponds UNIQUEMENT avec un JSON valide dans ce format exact:
{\"suggestions\": [\"suggestion1\", \"suggestion2\", \"suggestion3\"]}
Aucun autre texte.";

    $suggestions = callClaudeAPI($prompt, $apiKey);
    echo json_encode(['suggestions' => $suggestions, 'error' => null, 'mode' => 'initial']);
    exit;
}

// ============================================
// Mode contextuel : suggestions basées sur le texte tapé
// ============================================
if (strlen($inputText) < 1) {
    echo json_encode(['suggestions' => [], 'error' => null]);
    exit;
}

$inputText = htmlspecialchars($inputText, ENT_QUOTES, 'UTF-8');

$prompt = "Tu es un assistant pour un forum médical en français (ASCLEPIA).
L'utilisateur est en train d'écrire ce début de message : \"$inputText\"

Génère exactement 3 courtes suggestions contextuelles (max 8 mots chacune) pour compléter ou continuer ce message.
Les suggestions doivent être naturelles, médicalement pertinentes et en français.
Réponds UNIQUEMENT avec un JSON valide dans ce format exact:
{\"suggestions\": [\"suggestion1\", \"suggestion2\", \"suggestion3\"]}
Aucun autre texte.";

$suggestions = callClaudeAPI($prompt, $apiKey);

if (empty($suggestions)) {
    $suggestions = getFallbackSuggestions($inputText);
}

echo json_encode(['suggestions' => $suggestions, 'error' => null, 'mode' => 'contextual']);

// ============================================
// Appel API Claude
// ============================================
function callClaudeAPI($prompt, $apiKey) {
    $postData = json_encode([
        'model' => 'claude-haiku-4-5-20251001',
        'max_tokens' => 150,
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
        ]
    ]);

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError || $httpCode !== 200) {
        error_log("Claude API Error (HTTP $httpCode): $curlError | Response: $response");
        return [];
    }

    $result = json_decode($response, true);
    $text = $result['content'][0]['text'] ?? '';

    // Extraire le JSON de la réponse
    if (preg_match('/\{.*\}/s', $text, $matches)) {
        $parsed = json_decode($matches[0], true);
        if (isset($parsed['suggestions']) && is_array($parsed['suggestions'])) {
            return array_slice($parsed['suggestions'], 0, 3);
        }
    }

    return [];
}

// ============================================
// Fallback statique si API échoue
// ============================================
function getFallbackSuggestions($text) {
    $textLower = mb_strtolower($text, 'UTF-8');
    
    $completions = [
        'dou' => ['douleur thoracique', 'douleur persistante', 'douleur intense'],
        'mal' => ['mal de tête sévère', 'maladie chronique', 'mal au ventre'],
        'fiè' => ['fièvre élevée', 'fièvre persistante', 'fièvre depuis 3 jours'],
        'fati' => ['fatigue chronique', 'fatigue intense', 'fatigue inexpliquée'],
        'naus' => ['nausées le matin', 'nausées persistantes', 'nausées et vomissements'],
        'vert' => ['vertiges fréquents', 'vertiges en se levant', 'vertige positionnel'],
        'hyp' => ['hypertension artérielle', 'hypoglycémie', 'hypotension'],
        'can' => ['cancer du sein', 'cancer colorectal', 'cancérologie'],
        'infe' => ['infection urinaire', 'infection respiratoire', 'infection bactérienne'],
        'all' => ['allergie alimentaire', 'allergie au pollen', 'allergie cutanée'],
        'str' => ['stress chronique', 'stress au travail', 'stress et anxiété'],
        'sym' => ['symptômes persistants', 'symptômes inhabituels', 'symptômes depuis'],
        'trai' => ['traitement médical', 'traitement naturel', 'traitement adapté'],
        'méd' => ['médicament prescrit', 'médicament en vente libre', 'médecin généraliste'],
        'cons' => ['consultation urgente', 'consultation médicale', 'consulter un spécialiste'],
    ];
    
    foreach ($completions as $prefix => $list) {
        if (strpos($textLower, $prefix) === 0) {
            return $list;
        }
    }
    
    return ['consulter un médecin', 'symptômes préoccupants', 'traitement naturel'];
}
?>