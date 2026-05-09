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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: showpost.php?error=missing_id');
    exit;
}

$id_rep = (int)$_GET['id'];
$reponseC = new ReponseController();
$reponse = $reponseC->getReponseById($id_rep);

if (!$reponse) {
    header('Location: showpost.php?error=reponse_not_found');
    exit;
}

$id_post = $reponse['id_post'];
$reponseOwnerId = $reponse['id_utilisateur'] ?? null;

// ===== VÉRIFICATION DES DROITS =====
// Admin OU propriétaire de la réponse peut la supprimer
$isOwner = ($reponseOwnerId == $userId);
$isAdmin = ($userRole === 'admin');

if (!$isOwner && !$isAdmin) {
    // Pas autorisé à supprimer
    header("Location: showpost.php?id=$id_post&error=unauthorized");
    exit;
}

// Supprimer la réponse
$reponseC->deleteReponse($id_rep);
header("Location: showpost.php?id=$id_post&success=reponse_deleted");
exit;
?>