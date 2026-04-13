<?php
require_once '../../config/db.php';
require_once '../../models/Consultation.php';

$model = new Consultation($pdo);
$consultations = $model->getAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des consultations</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #2c3e50; color: white; padding: 10px; }
        td { padding: 10px; border: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; color: white; }
        .btn-add { background-color: #27ae60; margin-bottom: 15px; display: inline-block; }
        .btn-edit { background-color: #f39c12; }
        .btn-delete { background-color: #e74c3c; }
    </style>
</head>
<body>

<h1>Liste des consultations</h1>
<a href="add_consultation.php" class="btn btn-add">+ Ajouter une consultation</a>

<table>
    <tr>
        <th>ID</th>
        <th>Date</th>
        <th>Diagnostique</th>
        <th>Notes</th>
        <th>Actions</th>
    </tr>
    <?php if (empty($consultations)): ?>
    <tr>
        <td colspan="5" style="text-align:center">Aucune consultation trouvée</td>
    </tr>
    <?php else: ?>
    <?php foreach ($consultations as $c): ?>
    <tr>
        <td><?= $c['id_consultation'] ?></td>
        <td><?= $c['date_consultation'] ?></td>
        <td><?= htmlspecialchars($c['diagnostique']) ?></td>
        <td><?= htmlspecialchars($c['notes']) ?></td>
        <td>
            <a href="edit_consultation.php?id=<?= $c['id_consultation'] ?>" class="btn btn-edit">Modifier</a>
            <a href="delete_consultation.php?id=<?= $c['id_consultation'] ?>" class="btn btn-delete">Supprimer</a>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</table>

</body>
</html>