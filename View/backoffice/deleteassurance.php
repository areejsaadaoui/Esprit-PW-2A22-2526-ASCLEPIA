<?php
include '../../controller/AssuranceController.php';
$assuranceC = new AssuranceController();
$assuranceC->deleteAssurance($_GET['id']);
header('Location: assuranceList.php');
?>