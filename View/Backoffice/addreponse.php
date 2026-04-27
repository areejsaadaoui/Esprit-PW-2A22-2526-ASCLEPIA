<?php
include '../../Controller/ReponseController.php';
require_once __DIR__ . '/../../Model/Reponse.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['texte_rep']) && !empty($_POST['texte_rep']) && isset($_POST['id_post'])) {
        
        $reponseC = new ReponseController();
        
        $reponse = new Reponse(
            null,
            htmlspecialchars($_POST['texte_rep']),
            date('Y-m-d H:i:s'),
            1, // id_utilisateur fixe
            (int)$_POST['id_post']
        );
        
        $reponseC->addReponse($reponse);
    }
}

$id_post = (int)$_POST['id_post'];
header('Location: showpost.php?id=' . $id_post);
exit;
?>