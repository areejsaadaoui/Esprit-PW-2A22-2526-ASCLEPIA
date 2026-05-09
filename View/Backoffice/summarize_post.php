<?php

header('Content-Type: application/json; charset=utf-8');

$contenu = trim($_POST['contenu'] ?? '');
$postId  = (int)($_POST['post_id'] ?? 0);

if (strlen($contenu) < 30) {
    echo json_encode(['success' => false, 'error' => 'Texte trop court pour un résumé (minimum 30 caractères)']);
    exit;
}

// Votre clé API Groq
$apiKey = 'api_key_here'; // Remplacez par votre clé API Groq

$payload = json_encode([
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        [
            'role' => 'system',
            'content' => "Tu es un assistant médical. Résume ce post de forum médical en 2-3 phrases.
            RÈGLES :
            - Résumé entre 200 et 300 caractères, même si le post est long, reste concis et focalisé sur les infos médicales clés
            - Inclus : symptômes principaux, durée, médicaments essayés, demande du patient si c'est un témoignage ou une question si c'est un post de question sans réponse si tu as des réponses n'hésite pas à les inclure après dire qu'ils sont différent du résumé
            - Reste précis et médical
            - Réponds UNIQUEMENT avec le résumé, sans guillemets, sans explication"

        ],
        [
            'role' => 'user',
            'content' => "Résume ce post médical de façon détaillée : " . $contenu
        ]
    ],
    'temperature' => 0.3,
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
$curlErr = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['success' => false, 'error' => 'Erreur réseau : ' . $curlErr]);
    exit;
}

if ($httpCode === 200) {
    $result = json_decode($response, true);
    $summary = trim($result['choices'][0]['message']['content'] ?? '');
    $summary = trim($summary, '"\'');
    
    if (strlen($summary) > 350) {
        $summary = mb_substr($summary, 0, 347) . '...';
    }

    echo json_encode([
        'success' => true,
        'summary' => $summary,
        'char_count' => strlen($summary),
        'original_length' => strlen($contenu)
    ]);
} else {
    $errorMsg = json_decode($response, true);
    $error = $errorMsg['error']['message'] ?? ('Erreur API HTTP ' . $httpCode);
    echo json_encode(['success' => false, 'error' => $error]);
}
?>