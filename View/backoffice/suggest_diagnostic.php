<?php
require_once '../../config.php';
$pdo = config::getConnexion();
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT DISTINCT diagnostique FROM consultation 
         WHERE diagnostique LIKE ? 
         AND diagnostique != ''
         ORDER BY date_consultation DESC 
         LIMIT 5"
    );
    $stmt->execute(['%' . $q . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Extraire aussi les mots clés fréquents
    $stmtMots = $pdo->prepare(
        "SELECT diagnostique FROM consultation 
         WHERE diagnostique != '' 
         ORDER BY date_consultation DESC"
    );
    $stmtMots->execute();
    $tousLesdiag = $stmtMots->fetchAll(PDO::FETCH_COLUMN);
    
    $suggestions = [];
    foreach ($tousLesdiag as $diag) {
        $mots = preg_split('/\s+/', strtolower($diag));
        foreach ($mots as $mot) {
            $mot = trim($mot, '.,;:!?');
            if (strlen($mot) > 3 && stripos($mot, $q) !== false) {
                $suggestions[] = $mot;
            }
        }
    }
    
    $suggestions = array_unique($suggestions);
    $suggestions = array_slice($suggestions, 0, 5);
    
    $final = array_merge($results, $suggestions);
    $final = array_unique($final);
    $final = array_slice($final, 0, 8);
    
    echo json_encode(array_values($final));
} catch (Exception $e) {
    echo json_encode([]);
}
?>