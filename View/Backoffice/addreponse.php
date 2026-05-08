<?php
session_start();

include '../../Controller/ReponseController.php';
require_once __DIR__ . '/../../Model/Reponse.php';

// Récupérer l'ID de l'utilisateur connecté
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header('Location: ../frontoffice/login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $texteRep = $_POST['texte_rep'] ?? '';
    $gifUrl = $_POST['gif_url'] ?? '';
    
    if (isset($_POST['improved_text']) && !empty($_POST['improved_text'])) {
        $texteRep = $_POST['improved_text'];
    }
    
    if (!empty($gifUrl)) {
        $texteRep = $texteRep . "\n\n[GIF:" . $gifUrl . "]";
    }
    
    if (!empty($texteRep) && isset($_POST['id_post'])) {
        
        $reponseC = new ReponseController();
        
        $reponse = new Reponse(
            null,
            htmlspecialchars($texteRep),
            date('Y-m-d H:i:s'),
            $userId,  // ← UTILISATION DE L'ID DE SESSION
            (int)$_POST['id_post']
        );
        
        $result = $reponseC->addReponse($reponse);
        
        if ($result) {
            $id_post = (int)$_POST['id_post'];
            header('Location: showpost.php?id=' . $id_post . '&success=reponse_ajoutee');
            exit;
        } else {
            header('Location: showpost.php?id=' . (int)$_POST['id_post'] . '&error=erreur_ajout');
            exit;
        }
    }
}

header('Location: showpost.php?error=missing_data');
exit;
?>