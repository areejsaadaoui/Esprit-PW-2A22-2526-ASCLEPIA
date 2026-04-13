<?php
require_once '../../config/db.php';
require_once '../../models/Consultation.php';

$model = new Consultation($pdo);
$success = '';
$errors = [];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$consultation = $model->getById($id);

if (!$consultation) {
    die("Consultation introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = trim($_POST['date_consultation']);
    $diagnostique = trim($_POST['diagnostique']);
    $notes = trim($_POST['notes']);

    if (empty($date)) {
        $errors[] = "La date est obligatoire.";
    } elseif (strtotime($date) > time()) {
        $errors[] = "La date ne peut pas être dans le futur.";
    }

    if (empty($diagnostique)) {
        $errors[] = "Le diagnostique est obligatoire.";
    } elseif (strlen($diagnostique) < 10) {
        $errors[] = "Le diagnostique doit contenir au moins 10 caractères.";
    }

    if (empty($notes)) {
        $errors[] = "Les notes sont obligatoires.";
    } elseif (strlen($notes) < 5) {
        $errors[] = "Les notes doivent contenir au moins 5 caractères.";
    }

    if (empty($errors)) {
        $data = [
            'date_consultation' => $date,
            'diagnostique'      => $diagnostique,
            'notes'             => $notes
        ];
        if ($model->update($id, $data)) {
            $success = "Consultation modifiée avec succès !";
            $consultation = $model->getById($id);
        } else {
            $errors[] = "Erreur lors de la modification.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une consultation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #2c3e50; }
        form { max-width: 500px; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        input.error-field, textarea.error-field { border: 2px solid red; }
        textarea { height: 100px; resize: vertical; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; color: white; margin-top: 15px; }
        .btn-save { background-color: #f39c12; font-size: 15px; }
        .btn-back { background-color: #2c3e50; text-decoration: none; padding: 10px 20px; border-radius: 4px; color: white; display: inline-block; margin-bottom: 15px; }
        .success { color: green; background: #eaffea; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error-box { color: red; background: #fff0f0; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error-box ul { margin: 5px 0; padding-left: 20px; }
        .field-error { color: red; font-size: 12px; margin-top: 3px; display: none; }
        .counter { font-size: 11px; color: #888; text-align: right; }
    </style>
</head>
<body>

<h1>Modifier la consultation #<?= $id ?></h1>
<a href="list_consultation.php" class="btn-back">← Retour à la liste</a>

<?php if ($success): ?>
    <div class="success"><?= $success ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="error-box">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= $e ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form id="editForm" method="POST" onsubmit="return validerFormulaire()">

    <label>Date de consultation *</label>
    <input type="datetime-local" name="date_consultation" id="date_consultation"
        value="<?= date('Y-m-d\TH:i', strtotime($consultation['date_consultation'])) ?>">
    <span class="field-error" id="err_date">La date est obligatoire et ne peut pas être dans le futur.</span>

    <label>Diagnostique * <span style="font-weight:normal;font-size:12px">(min. 10 caractères)</span></label>
    <textarea name="diagnostique" id="diagnostique"
        oninput="compter('diagnostique', 'count_diag', 10)"><?= htmlspecialchars($consultation['diagnostique']) ?></textarea>
    <div class="counter"><span id="count_diag"><?= strlen($consultation['diagnostique']) ?></span> caractères</div>
    <span class="field-error" id="err_diag">Le diagnostique est obligatoire (minimum 10 caractères).</span>

    <label>Notes * <span style="font-weight:normal;font-size:12px">(min. 5 caractères)</span></label>
    <textarea name="notes" id="notes"
        oninput="compter('notes', 'count_notes', 5)"><?= htmlspecialchars($consultation['notes']) ?></textarea>
    <div class="counter"><span id="count_notes"><?= strlen($consultation['notes']) ?></span> caractères</div>
    <span class="field-error" id="err_notes">Les notes sont obligatoires (minimum 5 caractères).</span>

    <button type="submit" class="btn btn-save">Modifier</button>
</form>

<script>
    function compter(champId, compteurId, minimum) {
        const champ = document.getElementById(champId);
        const compteur = document.getElementById(compteurId);
        const nb = champ.value.length;
        compteur.textContent = nb;
        compteur.style.color = nb >= minimum ? 'green' : 'red';
    }

    function validerFormulaire() {
        let valide = true;
        document.querySelectorAll('.field-error').forEach(e => e.style.display = 'none');
        document.querySelectorAll('.error-field').forEach(e => e.classList.remove('error-field'));

        const date = document.getElementById('date_consultation').value;
        if (!date) {
            afficherErreur('date_consultation', 'err_date');
            valide = false;
        } else {
            const dateChoisie = new Date(date);
            const maintenant = new Date();
            if (dateChoisie > maintenant) {
                afficherErreur('date_consultation', 'err_date');
                valide = false;
            }
        }

        const diag = document.getElementById('diagnostique').value.trim();
        if (diag.length < 10) {
            afficherErreur('diagnostique', 'err_diag');
            valide = false;
        }

        const notes = document.getElementById('notes').value.trim();
        if (notes.length < 5) {
            afficherErreur('notes', 'err_notes');
            valide = false;
        }

        return valide;
    }

    function afficherErreur(champId, erreurId) {
        document.getElementById(champId).classList.add('error-field');
        document.getElementById(erreurId).style.display = 'block';
    }

    const maintenant = new Date();
    const offset = maintenant.getTimezoneOffset() * 60000;
    const dateLocale = new Date(maintenant - offset).toISOString().slice(0, 16);
    document.getElementById('date_consultation').max = dateLocale;
</script>

</body>
</html>