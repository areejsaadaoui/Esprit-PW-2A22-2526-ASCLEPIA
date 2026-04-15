
<?php

require_once '../../controller/PharmacieC.php';

if (isset($_GET['id_pharmacie'])) {// Vérifier si l'ID de la pharmacie est passé en paramètre et null ou non
    $pharmacieC = new pharmacieC();
    $pharmacieC->supprimerPharmacie($_GET['id_pharmacie']);
    header('Location: listepharmacie.php');
    exit();
} else {
    echo "ID de pharmacie non spécifié.";
}