<?php
header('Content-Type: application/json');

// REMPLACEZ CETTE CLÉ PAR VOTRE CLÉ GROQ (Gratuite sur console.groq.com)
$apiKey = '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'; 

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['error' => 'Message vide']);
    exit;
}

$data = [
    'model' => 'llama-3.1-8b-instant', // Modèle stable et ultra-rapide
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Tu es l\'assistant médical d\'ASCLEPIA. Réponds de manière concise et professionnelle en français.'
        ],
        [
            'role' => 'user',
            'content' => $userMessage
        ]
    ],
    'temperature' => 0.7
];

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);

// Désactiver la vérification SSL si vous êtes sur XAMPP et que ça bloque
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'Erreur de connexion : ' . curl_error($ch)]);
} else {
    echo $response;
}

curl_close($ch);
?>
