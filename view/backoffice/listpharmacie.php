<?php

include '../../Controller/PharmacieC.php';

$pc=new pharmacieC();
$liste=$pc->listepharmacie ();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Exemple2A</title>
</head>
<body>
    <h1>Liste des pharmacies</h1>
    <ul>
        <?php
        foreach($liste as $p){
        ?>
            <li>
                <?= $p['id_pharmacie'] ?>
                <?= $p['nom'] ?>
                <?php echo ($p['adresse']); ?>
                <?= $p['telephone'] ?>
                <?= $p['email'] ?>
                <a href="deletepharmacie.php?id_pharmacie=<?= $p['id_pharmacie'] ?>">Supprimer</a>
                <a href="editpharmacie.php?id_pharmacie=<?= $p['id_pharmacie'] ?>&nom=<?= $p['nom'] ?>&adresse=<?= $p['adresse'] ?>&telephone=<?= $p['telephone'] ?>&email=<?= $p['email'] ?>">Modifier</a>
            </li>
        <?php
        }
        ?>
    </ul>
</body>
</html>