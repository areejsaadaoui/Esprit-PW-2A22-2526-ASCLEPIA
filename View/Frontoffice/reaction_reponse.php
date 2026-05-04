<?php
/**
 * ASCLEPIA — Réactions emoji sur réponses (AJAX v2)
 * POST: id_rep, emoji, action (add|remove), user_token
 *
 * Le user_token est généré côté client (localStorage UUID).
 * Le serveur stocke {emoji: count} en JSON dans reactions.
 * Côté client, le localStorage trace quelle emoji le user a choisie
 * pour cette réponse (une seule emoji autorisée par user par réponse).
 */
require_once __DIR__ . '/../../Controller/ReponseController.php';
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode invalide']);
    exit;
}

$id_rep = isset($_POST['id_rep']) ? (int)$_POST['id_rep'] : 0;
$emoji  = $_POST['emoji'] ?? '';
$action = $_POST['action'] ?? 'add';   // 'add' | 'remove'
$old_emoji = $_POST['old_emoji'] ?? ''; // ancienne réaction à remplacer

$EMOJIS_AUTORISES = ['❤️', '😂', '🔥', '👍', '😮', '😢', '👏', '😍', '🎉'];

if (!$id_rep || !in_array($emoji, $EMOJIS_AUTORISES)) {
    echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
    exit;
}

$reponseC = new ReponseController();

// Si remplacement d'une ancienne réaction, on retire d'abord l'ancienne
if ($action === 'add' && !empty($old_emoji) && in_array($old_emoji, $EMOJIS_AUTORISES) && $old_emoji !== $emoji) {
    $reponseC->toggleReaction($id_rep, $old_emoji, 'remove');
}

$ok = $reponseC->toggleReaction($id_rep, $emoji, $action);

// Récupérer les réactions à jour
$row = $reponseC->getReponseById($id_rep);
$reactions = [];
if ($row && !empty($row['reactions'])) {
    $reactions = json_decode($row['reactions'], true) ?? [];
}
// Filtrer les emojis à 0
$reactions = array_filter($reactions, fn($v) => $v > 0);

echo json_encode([
    'success'   => $ok,
    'reactions' => $reactions,
    'id_rep'    => $id_rep
]);
exit;