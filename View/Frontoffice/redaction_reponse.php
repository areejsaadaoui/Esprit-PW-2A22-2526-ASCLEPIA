<?php
/**
 * ASCLEPIA — Réactions emoji sur réponses (AJAX)
 * POST: id_rep, emoji, action (add|remove)
 */
require_once __DIR__ . '/../../Controller/ReponseController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode invalide']);
    exit;
}

$id_rep = isset($_POST['id_rep']) ? (int)$_POST['id_rep'] : 0;
$emoji  = $_POST['emoji'] ?? '';
$action = $_POST['action'] ?? 'add';

$EMOJIS_AUTORISES = ['❤️', '😂', '🔥', '👍', '😮', '😢', '👏'];

if (!$id_rep || !in_array($emoji, $EMOJIS_AUTORISES)) {
    echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
    exit;
}

$reponseC = new ReponseController();
$ok = $reponseC->toggleReaction($id_rep, $emoji, $action);

// Récupérer les réactions à jour
$row = $reponseC->getReponseById($id_rep);
$reactions = [];
if (!empty($row['reactions'])) {
    $reactions = json_decode($row['reactions'], true) ?? [];
}

echo json_encode([
    'success'   => $ok,
    'reactions' => $reactions
]);
exit;
