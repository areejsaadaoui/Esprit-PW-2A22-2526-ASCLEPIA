<?php
session_start();
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../frontoffice/login.html');
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ../Frontoffice/postlist.php');
    exit;
}

$id_post = (int)$_GET['id'];
$postC = new PostController();

// Récupérer le post pour vérifier le propriétaire
$post = $postC->getPostById($id_post);

if (!$post) {
    header('Location: ../Frontoffice/postlist.php?error=notfound');
    exit;
}

// Vérifier les droits : admin OU propriétaire du post
$isOwner = ($post->getIdUtilisateur() == $userId);
$isAdmin = ($userRole === 'admin');

if (!$isOwner && !$isAdmin) {
    // Pas autorisé à supprimer
    header('Location: ../Frontoffice/postlist.php?error=unauthorized');
    exit;
}

// Supprimer l'image associée si elle existe
$imagePath = $post->getImage();
if (!empty($imagePath)) {
    // Vérifier si c'est un chemin local (pas une URL)
    if (!filter_var($imagePath, FILTER_VALIDATE_URL)) {
        $fullImagePath = __DIR__ . '/' . $imagePath;
        if (file_exists($fullImagePath)) {
            unlink($fullImagePath);
        }
    }
}

// Supprimer le post
$postC->deletePost($id_post);

// Redirection avec message de succès
header('Location: ../Frontoffice/postlist.php?deleted=1');
exit;
?>