<?php
header('Content-Type: application/json');
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';

$postC = new PostController();
$posts = $postC->listPosts();

// Formater les données pour JSON
$data = [];
foreach ($posts as $post) {
    $data[] = [
        'id_post' => $post->getIdPost(),
        'contenu' => $post->getContenu(),
        'date_post' => $post->getDatePost(),
        'image' => $post->getImage(),
        'id_utilisateur' => $post->getIdUtilisateur()
    ];
}

echo json_encode($data);
?>