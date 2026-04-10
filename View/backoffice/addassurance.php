<?php
include '../../controller/AssuranceController.php';
require_once __DIR__ . '/../../Model/Assurance.php';

$error      = '';
$assuranceC = new AssuranceController();

if (isset($_POST['nom_assurance'], $_POST['description'], $_POST['prix'], $_POST['TYPE'], $_POST['duree'], $_POST['taux_remboursement'])) {
    if (!empty($_POST['nom_assurance']) && !empty($_POST['description']) && !empty($_POST['prix']) && !empty($_POST['TYPE']) && !empty($_POST['duree']) && !empty($_POST['taux_remboursement'])) {
        $assurance = new Assurance(
            null,
            $_POST['nom_assurance'],
            $_POST['description'],
            (float)$_POST['prix'],
            $_POST['TYPE'],
            (int)$_POST['duree'],
            (float)$_POST['taux_remboursement']
        );
        $assuranceC->addAssurance($assurance);
        header('Location: assuranceList.php');
        exit;
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Assurance</title>
    <style>
        .error-msg {
            color: red;
            font-size: 0.85rem;
            margin-top: 4px;
            display: none;
        }
        input.invalid, textarea.invalid {
            border: 2px solid red;
        }
        input.valid, textarea.valid {
            border: 2px solid green;
        }
    </style>
</head>
<body>
    <h1>Ajouter une Assurance</h1>
    <?php if (!empty($error)): ?>
        <div style="color:red;"><?= $error ?></div>
    <?php endif; ?>

    <form action="" method="POST" id="formAdd" onsubmit="return validerFormulaire()">

        <label>Nom :</label><br>
        <input type="text" name="nom_assurance" id="nom_assurance"><br>
        <span class="error-msg" id="err_nom">Le nom doit contenir au moins 3 caractères.</span><br>

        <label>Description :</label><br>
        <textarea name="description" id="description" rows="3"></textarea><br>
        <span class="error-msg" id="err_description">La description doit contenir au moins 10 caractères.</span><br>

        <label>Prix (DT) :</label><br>
        <input type="number" step="0.01" name="prix" id="prix" min="0"><br>
        <span class="error-msg" id="err_prix">Le prix doit être supérieur à 0.</span><br>

        <label>Type :</label><br>
        <select name="TYPE" id="TYPE">
            <option value="">-- Choisir --</option>
            <option value="Santé">Santé</option>
            <option value="Dentaire">Dentaire</option>
            <option value="Vision">Vision</option>
            <option value="Maternité">Maternité</option>
            <option value="Complète">Complète</option>
        </select><br>
        <span class="error-msg" id="err_type">Veuillez choisir un type.</span><br>

        <label>Durée (mois) :</label><br>
        <input type="number" name="duree" id="duree" min="1"><br>
        <span class="error-msg" id="err_duree">La durée doit être supérieure à 0.</span><br>

        <label>Taux de remboursement (%) :</label><br>
        <input type="number" step="0.01" name="taux_remboursement" id="taux_remboursement" min="0" max="100"><br>
        <span class="error-msg" id="err_taux">Le taux doit être entre 0 et 100.</span><br><br>

        <button type="submit">Ajouter</button>
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