<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

// Only logged-in users can search
if (empty($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$pdo = config::getConnexion();

// ── Lookup by ID (used by edit page to pre-select current patient) ──
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id <= 0) { echo json_encode([]); exit(); }

    $stmt = $pdo->prepare(
        "SELECT id_user, nom FROM utilisateur WHERE role = 'patient' AND id_user = ? LIMIT 1"
    );
    $stmt->execute([$id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit();
}

// ── Search by name ──
$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit();
}

try {
    $stmt = $pdo->prepare(
        "SELECT id_user, nom FROM utilisateur
         WHERE role = 'patient' AND nom LIKE :q
         ORDER BY nom ASC
         LIMIT 20"
    );
    $stmt->execute([':q' => '%' . $q . '%']);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo json_encode([]);
}
?>