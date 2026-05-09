<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Controller/PostController.php';

$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($postId <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID invalide']);
    exit;
}

$postC = new PostController();
$post = $postC->getPostById($postId);

if ($post) {
    echo json_encode([
        'success' => true,
        'content' => $post->getContenu()
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Post non trouvé']);
}
?>