<?php
include '../../controller/AssuranceController.php';
$assuranceC = new AssuranceController();
$list       = $assuranceC->listAssurances();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Assurances</title>
</head>
<body>
    <h1>Liste des Assurances</h1>
    <a href="addAssurance.php">+ Ajouter une assurance</a>
    <br><br>
    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Prix (DT)</th>
            <th>Type</th>
            <th>Durée (mois)</th>
            <th>Taux de remboursement (%)</th>
            <th colspan="2">Actions</th>
        </tr>
        <?php foreach ($list as $a): ?>
        <tr>
            <td><?= $a['id_assurance'] ?></td>
            <td><?= $a['nom_assurance'] ?></td>
            <td><?= $a['description'] ?></td>
            <td><?= $a['prix'] ?></td>
            <td><?= $a['TYPE'] ?></td>
            <td><?= $a['duree'] ?></td>
            <td><?= $a['taux_remboursement'] ?></td>
            <td>
                <form method="POST" action="updateAssurance.php">
                    <input type="hidden" name="id" value="<?= $a['id_assurance'] ?>">
                    <input type="submit" value="Modifier">
                </form>
            </td>
            <td>
                <a href="deleteAssurance.php?id=<?= $a['id_assurance'] ?>"
                   onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>