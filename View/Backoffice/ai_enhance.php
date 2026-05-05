<?php
header('Content-Type: application/json; charset=utf-8');

$contenu = trim($_POST['contenu'] ?? '');

if (strlen($contenu) < 5) {
    echo json_encode(['success' => false, 'error' => 'Texte trop court (minimum 5 caractères)']);
    exit;
}

//  clé API Groq
$apiKey = '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';

$payload = json_encode([
    'model' => 'llama-3.3-70b-versatile',  // Modèle puissant et gratuit
    'messages' => [
        [
            'role' => 'system', 
            'content' => 'Tu es un assistant qui corrige les fautes d\'orthographe et de grammaire en français ou en anglais selon le besoin. Tu ne fais que corriger le texte, sans ajouter d\'explications, sans changer le sens, sans ajouter de contenu. Tu réponds UNIQUEMENT avec le texte corrigé.'
        ],
        [
            'role' => 'user', 
            'content' => "Corrige ce texte :\n\n" . $contenu
        ]
    ],
    'temperature' => 0.1,  
    'max_tokens' => 500
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
$curlErr = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['success' => false, 'error' => 'Erreur réseau : ' . $curlErr]);
    exit;
}

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $corrected = trim($data['choices'][0]['message']['content'] ?? '');
    
    // Nettoyer les guillemets si présents
    $corrected = trim($corrected, '"\'');
    
    echo json_encode([
        'success' => true,
        'newContent' => $corrected,
        'original' => $contenu,
        'message' => '✅ Texte amélioré par IA Groq'
    ]);
} else {
    $errorMsg = json_decode($response, true);
    $error = $errorMsg['error']['message'] ?? 'Erreur API HTTP ' . $httpCode;
    echo json_encode(['success' => false, 'error' => $error]);
}