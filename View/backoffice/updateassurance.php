<?php
include '../../controller/AssuranceController.php';
require_once __DIR__ . '/../../Model/Assurance.php';

$error      = '';
$assurance  = null;
$assuranceC = new AssuranceController();

if (isset($_POST['id'], $_POST['nom_assurance'], $_POST['description'], $_POST['prix'], $_POST['TYPE'], $_POST['duree'], $_POST['taux_remboursement']) && !empty($_POST['nom_assurance'])) {
    $assurance = new Assurance(
        (int)$_POST['id'],
        $_POST['nom_assurance'],
        $_POST['description'],
        (float)$_POST['prix'],
        $_POST['TYPE'],
        (int)$_POST['duree'],
        (float)$_POST['taux_remboursement']
    );
    $assuranceC->updateAssurance($assurance, $_POST['id']);
    header('Location: assuranceList.php');
    exit;
}

if (isset($_POST['id'])) {
    $assurance = $assuranceC->showAssurance($_POST['id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une Assurance</title>
    <style>
        .error-msg {
            color: red;
            font-size: 0.85rem;
            margin-top: 4px;
            display: none;
        }
        input.invalid, textarea.invalid, select.invalid {
            border: 2px solid red;
        }
        input.valid, textarea.valid, select.valid {
            border: 2px solid green;
        }
    </style>
</head>
<body>
    <h1>Modifier une Assurance</h1>

    <form action="" method="POST" id="formUpdate" onsubmit="return validerFormulaire()">
        <input type="hidden" name="id" value="<?= $_POST['id'] ?? ($assurance['id_assurance'] ?? '') ?>">

        <label>Nom :</label><br>
        <input type="text" name="nom_assurance" id="nom_assurance" value="<?= $assurance['nom_assurance'] ?? '' ?>"><br>
        <span class="error-msg" id="err_nom">Le nom doit contenir au moins 3 caractères.</span><br>

        <label>Description :</label><br>
        <textarea name="description" id="description" rows="3"><?= $assurance['description'] ?? '' ?></textarea><br>
        <span class="error-msg" id="err_description">La description doit contenir au moins 10 caractères.</span><br>

        <label>Prix (DT) :</label><br>
        <input type="number" step="0.01" name="prix" id="prix" value="<?= $assurance['prix'] ?? '' ?>"><br>
        <span class="error-msg" id="err_prix">Le prix doit être supérieur à 0.</span><br>

        <label>Type :</label><br>
        <select name="TYPE" id="TYPE">
            <option value="">-- Choisir --</option>
            <?php foreach (['Santé', 'Dentaire', 'Vision', 'Maternité', 'Complète'] as $opt): ?>
                <option value="<?= $opt ?>" <?= (isset($assurance['TYPE']) && $assurance['TYPE'] === $opt) ? 'selected' : '' ?>>
                    <?= $opt ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <span class="error-msg" id="err_type">Veuillez choisir un type.</span><br>

        <label>Durée (mois) :</label><br>
        <input type="number" name="duree" id="duree" value="<?= $assurance['duree'] ?? '' ?>"><br>
        <span class="error-msg" id="err_duree">La durée doit être supérieure à 0.</span><br>

        <label>Taux de remboursement (%) :</label><br>
        <input type="number" step="0.01" name="taux_remboursement" id="taux_remboursement" value="<?= $assurance['taux_remboursement'] ?? '' ?>"><br>
        <span class="error-msg" id="err_taux">Le taux doit être entre 0 et 100.</span><br><br>

        <button type="submit">Mettre à jour</button>
        <a href="assuranceList.php">Annuler</a>
    </form>

    <script>
        function afficherErreur(id, show) {
            var span = document.getElementById(id);
            span.style.display = show ? 'block' : 'none';
        }

        function validerChamp(id, condition, errId) {
            var input = document.getElementById(id);
            var valide = condition(input.value);
            input.className = valide ? 'valid' : 'invalid';
            afficherErreur(errId, !valide);
            return valide;
        }

        function validerFormulaire() {
            var ok = true;

            // Nom : minimum 3 caractères
            if (!validerChamp('nom_assurance', function(v) { return v.trim().length >= 3; }, 'err_nom')) ok = false;

            // Description : minimum 10 caractères
            if (!validerChamp('description', function(v) { return v.trim().length >= 10; }, 'err_description')) ok = false;

            // Prix : supérieur à 0
            if (!validerChamp('prix', function(v) { return v !== '' && parseFloat(v) > 0; }, 'err_prix')) ok = false;

            // Type : non vide
            if (!validerChamp('TYPE', function(v) { return v !== ''; }, 'err_type')) ok = false;

            // Durée : supérieure à 0
            if (!validerChamp('duree', function(v) { return v !== '' && parseInt(v) > 0; }, 'err_duree')) ok = false;

            // Taux : entre 0 et 100
            if (!validerChamp('taux_remboursement', function(v) { return v !== '' && parseFloat(v) >= 0 && parseFloat(v) <= 100; }, 'err_taux')) ok = false;

            return ok;
        }

        // Validation en temps réel
        document.getElementById('nom_assurance').addEventListener('input', function() {
            validerChamp('nom_assurance', function(v) { return v.trim().length >= 3; }, 'err_nom');
        });
        document.getElementById('description').addEventListener('input', function() {
            validerChamp('description', function(v) { return v.trim().length >= 10; }, 'err_description');
        });
        document.getElementById('prix').addEventListener('input', function() {
            validerChamp('prix', function(v) { return v !== '' && parseFloat(v) > 0; }, 'err_prix');
        });
        document.getElementById('TYPE').addEventListener('change', function() {
            validerChamp('TYPE', function(v) { return v !== ''; }, 'err_type');
        });
        document.getElementById('duree').addEventListener('input', function() {
            validerChamp('duree', function(v) { return v !== '' && parseInt(v) > 0; }, 'err_duree');
        });
        document.getElementById('taux_remboursement').addEventListener('input', function() {
            validerChamp('taux_remboursement', function(v) { return v !== '' && parseFloat(v) >= 0 && parseFloat(v) <= 100; }, 'err_taux');
        });
    </script>
</body>
</html>