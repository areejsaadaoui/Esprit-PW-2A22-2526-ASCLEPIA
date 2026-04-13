<?php
require_once '../../config/db.php';
require_once '../../models/Consultation.php';

$model = new Consultation($pdo);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$consultation = $model->getById($id);

if (!$consultation) {
    die("Consultation introuvable.");
}

if (isset($_GET['confirm']) && $_GET['confirm'] === 'oui') {
    $model->delete($id);
    header('Location: list_consultation.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer une consultation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #e74c3c; }
        .box { background: #fff0f0; border: 1px solid #e74c3c; border-radius: 8px; padding: 20px; max-width: 500px; margin-top: 20px; }
        .info { margin: 8px 0; font-size: 15px; }
        .info span { font-weight: bold; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; color: white; text-decoration: none; display: inline-block; margin-top: 15px; margin-right: 10px; font-size: 15px; }
        .btn-delete { background-color: #e74c3c; }
        .btn-cancel { background-color: #2c3e50; }
    </style>
</head>
<body>

<h1>Supprimer la consultation</h1>

<div class="box">
    <p>Es-tu sûr de vouloir supprimer cette consultation ?</p>
    <div class="info">Date : <span><?= $consultation['date_consultation'] ?></span></div>
    <div class="info">Diagnostique : <span><?= htmlspecialchars($consultation['diagnostique']) ?></span></div>
    <div class="info">Notes : <span><?= htmlspecialchars($consultation['notes']) ?></span></div>

    <a href="delete_consultation.php?id=<?= $id ?>&confirm=oui" class="btn btn-delete">Oui, supprimer</a>
    <a href="list_consultation.php" class="btn btn-cancel">Annuler</a>
</div>

</body>
</html>