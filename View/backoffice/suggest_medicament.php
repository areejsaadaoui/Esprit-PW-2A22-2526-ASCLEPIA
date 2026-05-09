<?php
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];

/* =====================
   1. SUGGESTION DB
===================== */
try {
   require_once '../../config.php'; $pdo = config::getConnexion();

    $stmt = $pdo->prepare("SELECT nom FROM medicaments WHERE nom LIKE ? LIMIT 5");
    $stmt->execute(["%$q%"]);

    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Pas de suggestion DB si la table ou la base n'existe pas.
}

/* =====================
   2. SUGGESTION IA
===================== */
$apiKey = "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"; // 🔴 ta clé ici

if (!empty($apiKey)) {
    try {
        $prompt = "Donne 5 médicaments français proches de '$q'. Répond uniquement par une liste séparée par des virgules.";

        $data = [
            "model" => "gpt-4.1-mini",
            "messages" => [
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "temperature" => 0.2,
            "max_tokens" => 100
        ];

        $ch = curl_init("https://api.openai.com/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response !== false && $httpStatus >= 200 && $httpStatus < 300) {
            $json = json_decode($response, true);
            if (isset($json['error'])) {
                error_log('OpenAI error: ' . $json['error']['message']);
            } else {
                $text = $json['choices'][0]['message']['content'] ?? '';
                $ai = preg_split('/[\r\n,•\-*]+/', strtolower($text));
                $ai = array_filter(array_map('trim', $ai));
                $results = array_merge($results, $ai);
            }
        } else {
            if ($response !== false) {
                $json = json_decode($response, true);
                if (isset($json['error'])) {
                    error_log('OpenAI error: ' . $json['error']['message']);
                }
            }
        }
    } catch (Exception $e) {
        error_log('OpenAI request exception: ' . $e->getMessage());
    }
}

/* =====================
   CLEAN FINAL
===================== */
$results = array_filter($results, fn($s) => strlen($s) > 2);
$results = array_unique($results);

$extra = [];
if (stripos('fervex', $q) === 0 || stripos($q, 'fe') === 0 || stripos($q, 'fer') === 0) {
    $extra[] = 'Fervex';
}
if (stripos('vitamine c', $q) === 0 || stripos($q, 'vit') === 0) {
    $extra[] = 'Vitamine C';
}

if (empty($results)) {
    $fallback = [
        'paracétamol',
        'paracétamol codéiné',
        'ibuprofène',
        'aspirine',
        'amoxicilline',
        'nurofène',
        'fervex',
        'vitamine c'
    ];

    $results = array_values(array_filter($fallback, fn($s) => stripos($s, $q) !== false));

    if (empty($results) && strlen($q) >= 2) {
        $results = array_slice($fallback, 0, 3);
    }
}

$results = array_unique(array_merge($results, $extra));
$results = array_slice($results, 0, 8);

echo json_encode(array_values($results));