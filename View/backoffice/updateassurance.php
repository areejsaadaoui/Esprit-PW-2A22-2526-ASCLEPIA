<?php
include '../../Controller/AssuranceController.php';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Assurance - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">

    <aside class="sidebar">
        <a href="#" class="sidebar-brand">
            <div class="sidebar-logo">🏥</div>
            <div class="sidebar-title">ASCL<span>EPIA</span></div>
        </a>
        <div class="sidebar-user">
            <div class="user-avatar">A</div>
            <div class="user-info">
                <div class="name">Administrateur</div>
                <div class="role">Super Admin</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Gestion</div>
            <div class="nav-item">
                <a href="assuranceList.php" class="active">
                    <span class="nav-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    Assurances
                </a>
            </div>
            <div class="nav-item">
                <a href="#">
                    <span class="nav-icon"><i class="fa-solid fa-file-contract"></i></span>
                    Contrats
                </a>
            </div>
        </nav>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <div class="page-title">Modifier Assurance</div>
                    <div class="breadcrumb">
                        <a href="assuranceList.php">Assurances</a>
                        <span>/</span>
                        <span>Modifier</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <div class="card" style="max-width: 700px; margin: 0 auto;">
                <div class="card-header">
                    <div class="card-title"><i class="fa-solid fa-pen" style="color:var(--primary)"></i> Modifier l'assurance</div>
                </div>

                <form action="" method="POST" id="formUpdate" onsubmit="return validerFormulaire()">
                    <input type="hidden" name="id" value="<?= $_POST['id'] ?? ($assurance['id_assurance'] ?? '') ?>">

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Nom de l'assurance</label>
                                <input type="text" name="nom_assurance" id="nom_assurance" class="form-control" value="<?= htmlspecialchars($assurance['nom_assurance'] ?? '') ?>">
                                <span class="form-error" id="err_nom">Minimum 3 caractères requis.</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Type</label>
                                <select name="TYPE" id="TYPE" class="form-control">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach (['Santé', 'Dentaire', 'Vision', 'Maternité', 'Complète'] as $opt): ?>
                                        <option value="<?= $opt ?>" <?= (isset($assurance['TYPE']) && $assurance['TYPE'] === $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="form-error" id="err_type">Veuillez choisir un type.</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control"><?= htmlspecialchars($assurance['description'] ?? '') ?></textarea>
                        <span class="form-error" id="err_description">Minimum 10 caractères requis.</span>
                    </div>

                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">Prix (DT / mois)</label>
                                <input type="number" name="prix" id="prix" class="form-control" value="<?= $assurance['prix'] ?? '' ?>">
                                <span class="form-error" id="err_prix">Doit être supérieur à 0.</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">Durée (mois)</label>
                                <input type="number" name="duree" id="duree" class="form-control" value="<?= $assurance['duree'] ?? '' ?>">
                                <span class="form-error" id="err_duree">Doit être supérieure à 0.</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">Taux remboursement (%)</label>
                                <input type="number" name="taux_remboursement" id="taux_remboursement" class="form-control" value="<?= $assurance['taux_remboursement'] ?? '' ?>">
                                <span class="form-error" id="err_taux">Doit être entre 0 et 100.</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Mettre à jour
                        </button>
                        <a href="assuranceList.php" class="btn btn-outline">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });

    function validerChamp(id, condition) {
        var input = document.getElementById(id);
        var valide = condition(input.value);
        input.classList.toggle('is-invalid', !valide);
        return valide;
    }

    function validerFormulaire() {
        var ok = true;
        if (!validerChamp('nom_assurance', function(v) { return v.trim().length >= 3; })) ok = false;
        if (!validerChamp('description', function(v) { return v.trim().length >= 10; })) ok = false;
        if (!validerChamp('prix', function(v) { return v.trim() !== '' && parseFloat(v) > 0; })) ok = false;
        if (!validerChamp('TYPE', function(v) { return v !== ''; })) ok = false;
        if (!validerChamp('duree', function(v) { return v.trim() !== '' && parseInt(v) > 0; })) ok = false;
        if (!validerChamp('taux_remboursement', function(v) { return v.trim() !== '' && parseFloat(v) >= 0 && parseFloat(v) <= 100; })) ok = false;
        return ok;
    }

    ['nom_assurance', 'description', 'prix', 'duree', 'taux_remboursement'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() { validerChamp(id, function(v) {
            if (id === 'nom_assurance') return v.trim().length >= 3;
            if (id === 'description') return v.trim().length >= 10;
            if (id === 'prix') return v.trim() !== '' && parseFloat(v) > 0;
            if (id === 'duree') return v.trim() !== '' && parseInt(v) > 0;
            if (id === 'taux_remboursement') return v.trim() !== '' && parseFloat(v) >= 0 && parseFloat(v) <= 100;
        }); });
    });
    document.getElementById('TYPE').addEventListener('change', function() {
        validerChamp('TYPE', function(v) { return v !== ''; });
    });
</script>
</body>
</html>