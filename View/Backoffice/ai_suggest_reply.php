<?php
/**
 * ASCLEPIA - Suggestions de réponses IA (Groq uniquement)
 * Pas de fallback local
 */

header('Content-Type: application/json; charset=utf-8');

$postContent = $_POST['post_content'] ?? '';
$context = $_POST['context'] ?? 'medical';

if (empty($postContent)) {
    echo json_encode(['success' => false, 'error' => 'Contenu du post vide']);
    exit;
}

// Tronquer le texte si trop long
if (strlen($postContent) > 800) {
    $postContent = substr($postContent, 0, 797) . '...';
}

$apiKey = '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';

$prompt = "Tu es un assistant médical bienveillant. Propose 3 réponses possibles à ce post de forum médical.

RÈGLES :
- Réponses courtes (max 120 caractères chacune)
- Ton professionnel et empathique
- Une réponse par ligne, précédée d'un tiret (-)
- Ne pas répéter la même idée

POST: " . $postContent;

$payload = json_encode([
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Tu es un assistant médical. Propose des réponses utiles et bienveillantes. Réponds uniquement avec les 3 réponses, une par ligne, précédées d\'un tiret.'
        ],
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ],
    'temperature' => 0.7,
    'max_tokens' => 300
]);

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['success' => false, 'error' => 'Erreur réseau: ' . $curlError]);
    exit;
}

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $suggestions = $data['choices'][0]['message']['content'] ?? '';
    
    // Extraire les suggestions (formattées avec des tirets)
    preg_match_all('/[-•]\s*(.+)/', $suggestions, $matches);
    $suggestionsList = !empty($matches[1]) ? $matches[1] : [];
    
    // Nettoyer et limiter à 3 suggestions
    $suggestionsList = array_slice(array_filter(array_map('trim', $suggestionsList)), 0, 3);
    
    if (empty($suggestionsList)) {
        // Si le format n'est pas respecté, on essaie de séparer par lignes
        $lines = explode("\n", trim($suggestions));
        $suggestionsList = array_slice(array_filter(array_map('trim', $lines)), 0, 3);
    }
    
    echo json_encode([
        'success' => true,
        'suggestions' => $suggestionsList,
        'raw' => $suggestions // pour debug (optionnel)
    ]);
} else {
    $errorMsg = json_decode($response, true);
    $error = $errorMsg['error']['message'] ?? 'Erreur API HTTP ' . $httpCode;
    echo json_encode(['success' => false, 'error' => $error]);
}
?>