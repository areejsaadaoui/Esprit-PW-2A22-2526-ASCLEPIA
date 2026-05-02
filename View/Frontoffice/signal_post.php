<?php
/**
 * ASCLEPIA — Signalement de post (AJAX)
 * POST: id_post, action (signal|unsignal)
 * Retourne JSON
 */
require_once __DIR__ . '/../../Controller/PostController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode invalide']);
    exit;
}

$id_post = isset($_POST['id_post']) ? (int)$_POST['id_post'] : 0;
$action  = $_POST['action'] ?? 'signal';

if (!$id_post) {
    echo json_encode(['success' => false, 'error' => 'ID invalide']);
    exit;
}

$postC = new PostController();

if ($action === 'signal') {
    $ok = $postC->signalerPost($id_post);
} else {
    $ok = $postC->retirerSignalement($id_post);
}

// Récupérer le post mis à jour pour retourner le vrai compte
$post = $postC->getPostById($id_post);

echo json_encode([
    'success'      => $ok,
    'signalements' => $post ? ($post->getSignalements() ?? 0) : 0,
    'action'       => $action
]);
exit;
