<?php
include '../../Controller/PostController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

$id_post = isset($_POST['id_post']) ? (int)$_POST['id_post'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($id_post <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID post invalide']);
    exit;
}

$postC = new PostController();

// Récupérer le nombre actuel de likes
$post = $postC->getPostById($id_post);
if (!$post) {
    echo json_encode(['success' => false, 'error' => 'Post non trouvé']);
    exit;
}

$currentLikes = $post->getLikes();
$newLikes = $currentLikes;

// Appliquer l'action
if ($action === 'like') {
    $postC->addLike($id_post);
    $newLikes = $currentLikes + 1;
} elseif ($action === 'unlike') {
    $postC->removeLike($id_post);
    $newLikes = max(0, $currentLikes - 1);
} else {
    echo json_encode(['success' => false, 'error' => 'Action invalide']);
    exit;
}

// Retourner la réponse
echo json_encode([
    'success' => true,
    'newCount' => $newLikes
]);
?>