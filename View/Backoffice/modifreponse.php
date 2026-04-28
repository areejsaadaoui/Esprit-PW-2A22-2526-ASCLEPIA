<?php

require_once '../../Controller/ReponseController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_rep = (int)$_POST['id_rep'];
    $texte = htmlspecialchars($_POST['texte_rep']);
    $id_post = (int)$_POST['id_post'];
    
    $reponseC = new ReponseController();
    $reponseC->modifreponse($id_rep, $texte);
    
    header("Location: showpost.php?id=$id_post");
    exit;
}
?>