<?php


require_once '../../Controller/ReponseController.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: showpost.php?error=missing_id');
    exit;
}

$id_rep = (int)$_GET['id'];
$reponseC = new ReponseController();
$reponse = $reponseC->getReponseById($id_rep);
if ($reponse) {
    $id_post = $reponse['id_post'];
    $reponseC->deleteReponse($id_rep);
    header("Location: showpost.php?id=$id_post&success=reponse_deleted");
} else {
    header('Location: showpost.php?error=reponse_not_found');
}
exit;