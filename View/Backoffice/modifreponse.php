<?php
session_start();

require_once '../../Controller/ReponseController.php';

// ===== VÉRIFICATION DE LA SESSION =====
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['user_role'] ?? '';
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

if (!$isLoggedIn || !$userId) {
    header('Location: ../frontoffice/login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_rep = (int)$_POST['id_rep'];
    $texte = htmlspecialchars($_POST['texte_rep']);
    $gifUrl = $_POST['gif_url'] ?? '';
    $id_post = (int)$_POST['id_post'];
    
    // Vérifier si l'utilisateur est le propriétaire de la réponse
    $reponseC = new ReponseController();
    $reponse = $reponseC->getReponseById($id_rep);
    
    if (!$reponse) {
        header("Location: showpost.php?id=$id_post&error=reponse_not_found");
        exit;
    }
    
    $reponseOwnerId = $reponse['id_utilisateur'] ?? null;
    $isOwner = ($reponseOwnerId == $userId);
    
    // Seul le propriétaire peut modifier sa réponse
    if (!$isOwner) {
        header("Location: showpost.php?id=$id_post&error=unauthorized");
        exit;
    }
    
    // Si un GIF est fourni, l'ajouter au texte avec le tag
    if (!empty($gifUrl)) {
        $texte = $texte . "\n\n[GIF:" . htmlspecialchars($gifUrl) . "]";
    }
    
    $reponseC->modifreponse($id_rep, $texte);
    
    header("Location: showpost.php?id=$id_post&success=reponse_modified");
    exit;
}

header('Location: showpost.php?error=invalid_request');
exit;
?>