<?php
require_once '../../config.php';
header('Content-Type: application/json');

// récupérer la recherche
$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

// 🔴 MET TA NOUVELLE CLÉ ICI (ne la partage jamais)
$apiKey = API_KEY;

try {

    // préparation de la requête IA
    $data = [
        "model" => "gpt-4.1-mini",
        "messages" => [
            [
                "role" => "user",
                "content" => "Donne 5 noms de médicaments proches de: $q. Répond uniquement avec des mots séparés par des virgules."
            ]
        ]
    ];

    // initialiser CURL
    $ch = curl_init("https://api.openai.com/v1/chat/completions");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $apiKey,
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    // vérifier erreur CURL
    if ($response === false) {
        echo json_encode([]);
        exit;
    }

    curl_close($ch);

    // décoder réponse
    $result = json_decode($response, true);

    $text = $result['choices'][0]['message']['content'] ?? '';

    // transformer texte en tableau
    $suggestions = array_map('trim', explode(',', $text));

    // suggestions locales supplémentaires
    $extra = [];
    if (stripos('fervex', $q) === 0 || stripos($q, 'fe') === 0) {
        $extra[] = 'Fervex';
    }
    if (stripos('vitamine c', $q) === 0 || stripos($q, 'vit') === 0) {
        $extra[] = 'Vitamine C';
    }

    $suggestions = array_filter($suggestions, fn($s) => strlen($s) > 2);
    $suggestions = array_values(array_unique(array_merge($suggestions, $extra)));
    $suggestions = array_slice($suggestions, 0, 8);

    echo json_encode($suggestions);

} catch (Exception $e) {
    echo json_encode([]);
}
?>