<?php
require_once '../../config/db.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT DISTINCT medicaments FROM ordonnance 
         WHERE medicaments LIKE ? 
         AND medicaments != ''
         ORDER BY date_creation DESC 
         LIMIT 5"
    );
    $stmt->execute(['%' . $q . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $suggestions = [];
    foreach ($results as $med) {
        $mots = preg_split('/[,\n]+/', strtolower($med));
        foreach ($mots as $mot) {
            $mot = trim($mot);
            if (strlen($mot) > 2 && stripos($mot, $q) !== false) {
                $suggestions[] = $mot;
            }
        }
    }

    $suggestions = array_unique($suggestions);
    $suggestions = array_slice($suggestions, 0, 8);

    echo json_encode(array_values($suggestions));
} catch (Exception $e) {
    echo json_encode([]);
}
?>