<?php
include '../../Controller/ReponseController.php';
require_once __DIR__ . '/../../Model/Reponse.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupérer les données
    $texteRep = $_POST['texte_rep'] ?? '';
    $gifUrl = $_POST['gif_url'] ?? '';
    
    // Si un texte amélioré est envoyé, l'utiliser
    if (isset($_POST['improved_text']) && !empty($_POST['improved_text'])) {
        $texteRep = $_POST['improved_text'];
    }
    
    // Si un GIF est sélectionné, l'ajouter au texte avec un tag
    if (!empty($gifUrl)) {
        $texteRep = $texteRep . "\n\n[GIF:" . $gifUrl . "]";
    }
    
    if (!empty($texteRep) && isset($_POST['id_post'])) {
        
        $reponseC = new ReponseController();
        
        $reponse = new Reponse(
            null,
            htmlspecialchars($texteRep),
            date('Y-m-d H:i:s'),
            1,
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