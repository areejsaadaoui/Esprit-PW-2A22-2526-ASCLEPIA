<?php
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ../Frontoffice/postlist.php');
    exit;
}

$id_post = (int)$_GET['id'];
$postC = new PostController();

$post = $postC->getPostById($id_post);

if (!$post) {
    header('Location: ../Frontoffice/postlist.php?error=notfound');
    exit;
}


$imagePath = $post->getImage();
if (!empty($imagePath)) {
    $fullImagePath = __DIR__ . '/' . $imagePath;
    if (file_exists($fullImagePath)) {
        unlink($fullImagePath);
    }
}

$postC->deletePost($id_post);
header('Location: ../Frontoffice/postlist.php');
exit;
?>