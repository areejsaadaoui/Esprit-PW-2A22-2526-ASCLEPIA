<?php
include '../../Controller/AssuranceController.php';
$assuranceC = new AssuranceController();
$assuranceC->deleteAssurance($_GET['id']);
header('Location: assuranceList.php');
?>