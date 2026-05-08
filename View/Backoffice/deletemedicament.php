<?php
require_once '../../Controller/MedicamentC.php';

if (isset($_GET['id_medicament'])) {
    $mc = new medicamentC();
    $mc->supprimerMedicament($_GET['id_medicament']);
}

header('Location: listemedicament.php');
exit();
?>
