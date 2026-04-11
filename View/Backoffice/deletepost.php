<?php
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../frontoffice/login.html');
    exit;
}

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: postList.php');
    exit;
}

$id_post = (int)$_GET['id'];
$postC = new PostController();

// Récupérer le post pour vérifier les droits et supprimer l'image
$post = $postC->getPostById($id_post);

// Vérifier si le post existe
if (!$post) {
    header('Location: postList.php?error=notfound');
    exit;
}

// Vérifier les droits (admin ou propriétaire)
$admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$own = $post->getIdUtilisateur() == $_SESSION['user_id'];

if (!$admin && !$own) {
    header('Location: postList.php?error=unauthorized');
    exit;
}

// Supprimer l'image associée si elle existe
$imagePath = $post->getImage();
if (!empty($imagePath)) {
    $fullImagePath = __DIR__ . '/' . $imagePath;
    if (file_exists($fullImagePath)) {
        unlink($fullImagePath);
    }
}

// Supprimer le post de la base de données
$postC->deletePost($id_post);
  header('Location: ../Frontoffice/postList.php');


exit;
?>