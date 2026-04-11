
<?php

require_once '../../controller/PharmacieC.php';

if (isset($_GET['id_pharmacie'])) {
    $pharmacieC = new pharmacieC();
    $pharmacieC->supprimerPharmacie($_GET['id_pharmacie']);
    header('Location: listepharmacie.php');
    exit();
} else {
    echo "ID de pharmacie non spécifié.";
}