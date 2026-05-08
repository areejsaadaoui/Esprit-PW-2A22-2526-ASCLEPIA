<?php
include '../../../Controller/ContratController.php';
$contratC     = new ContratController();
$id_assurance = isset($_GET['id_assurance']) ? (int)$_GET['id_assurance'] : 0;
$contratC->deleteContrat($_GET['id']);
if ($id_assurance) {
    header('Location: contratsParAssurance.php?id=' . $id_assurance);
} else {
    header('Location: contratList.php');
}
exit;
?>