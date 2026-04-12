<?php
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontoffice/login.html');
    exit;
}
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: postList.php');
    exit;
}

$id_post = (int)$_GET['id'];
$postC = new PostController();

$post = $postC->getPostById($id_post);

if (!$post) {
    header('Location: postList.php?error=notfound');
    exit;
}

$admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$own = $post->getIdUtilisateur() == $_SESSION['user_id'];

if (!$admin && !$own) {
    header('Location: postList.php?error=unauthorized');
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
  header('Location: ../Frontoffice/postList.php');


exit;
?>