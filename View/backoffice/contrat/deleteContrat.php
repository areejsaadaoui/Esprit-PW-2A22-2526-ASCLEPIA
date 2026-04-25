<?php
include '../../../Controller/ContratController.php';
$contratC = new ContratController();
$contratC->deleteContrat($_GET['id']);
header('Location: contratList.php');
?>