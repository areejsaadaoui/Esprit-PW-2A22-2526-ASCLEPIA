<?php

require_once '../../Controller/ReponseController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_rep = (int)$_POST['id_rep'];
    $texte = htmlspecialchars($_POST['texte_rep']);
    $gifUrl = $_POST['gif_url'] ?? '';
    $id_post = (int)$_POST['id_post'];
    
    // Si un GIF est fourni, l'ajouter au texte avec le tag
    if (!empty($gifUrl)) {
        $texte = $texte . "\n\n[GIF:" . htmlspecialchars($gifUrl) . "]";
    }
    
    $reponseC = new ReponseController();
    $reponseC->modifreponse($id_rep, $texte);
    
    header("Location: showpost.php?id=$id_post");
    exit;
}
?>