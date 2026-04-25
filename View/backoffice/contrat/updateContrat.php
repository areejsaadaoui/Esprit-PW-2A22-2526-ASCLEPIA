<?php
include '../../../Controller/ContratController.php';
require_once __DIR__ . '/../../../Model/Contrat.php';

$error    = '';
$contrat  = null;
$contratC = new ContratController();
$assurances = $contratC->listAssurances();
$assurancesList = [];
foreach ($assurances as $a) { $assurancesList[] = $a; }

if (isset($_POST['id'], $_POST['date_d'], $_POST['id_assurance'], $_POST['montant'], $_POST['statut']) && !empty($_POST['date_d'])) {
    $contrat = new Contrat(
        (int)$_POST['id'],
        $_POST['date_d'],
        !empty($_POST['date_f']) ? $_POST['date_f'] : null,
        (int)$_POST['id_assurance'],
        (float)$_POST['montant'],
        $_POST['statut']
    );
    $contratC->updateContrat($contrat, $_POST['id']);
    header('Location: contratList.php');
    exit;
}

if (isset($_POST['id'])) {
    $contrat = $contratC->showContrat($_POST['id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Contrat - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <link rel="stylesheet" href="../../../assets/css/backoffice.css">
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
                <a href="../assurancelist.php">
                    <span class="nav-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    Assurances
                </a>
            </div>
            <div class="nav-item">
                <a href="contratList.php" class="active">
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
                    <div class="page-title">Modifier Contrat</div>
                    <div class="breadcrumb">
                        <a href="contratList.php">Contrats</a><span>/</span><span>Modifier</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <div class="card" style="max-width:700px; margin:0 auto;">
                <div class="card-header">
                    <div class="card-title"><i class="fa-solid fa-pen" style="color:var(--primary)"></i> Modifier le contrat</div>
                </div>

                <form action="" method="POST" id="formUpdate" onsubmit="return validerFormulaire()">
                    <input type="hidden" name="id" value="<?= $_POST['id'] ?? ($contrat['id_contrat'] ?? '') ?>">

                    <div class="form-group">
                        <label class="form-label">Assurance <span style="color:var(--danger)">*</span></label>
                        <select name="id_assurance" id="id_assurance" class="form-control">
                            <option value="">-- Choisir une assurance --</option>
                            <?php foreach ($assurancesList as $a): ?>
                                <option value="<?= $a['id_assurance'] ?>"
                                    <?= (isset($contrat['id_assurance']) && $contrat['id_assurance'] == $a['id_assurance']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['nom_assurance']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-error" id="err_assurance">Veuillez choisir une assurance.</span>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Date début <span style="color:var(--danger)">*</span></label>
                                <input type="date" name="date_d" id="date_d" class="form-control" value="<?= $contrat['date_d'] ?? '' ?>">
                                <span class="form-error" id="err_date_d">La date de début est requise.</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Date fin <span style="color:var(--text-muted); font-size:0.78rem;">(optionnel)</span></label>
                                <input type="date" name="date_f" id="date_f" class="form-control" value="<?= $contrat['date_f'] ?? '' ?>">
                                <span class="form-error" id="err_date_f">La date de fin doit être après la date de début.</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Montant (DT) <span style="color:var(--danger)">*</span></label>
                                <input type="number" name="montant" id="montant" class="form-control" value="<?= $contrat['montant'] ?? '' ?>">
                                <span class="form-error" id="err_montant">Le montant doit être supérieur à 0.</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Statut <span style="color:var(--danger)">*</span></label>
                                <select name="statut" id="statut" class="form-control">
                                    <option value="">-- Choisir --</option>
                                    <?php foreach (['Actif', 'Expiré', 'Annulé'] as $s): ?>
                                        <option value="<?= $s ?>" <?= (isset($contrat['statut']) && $contrat['statut'] === $s) ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="form-error" id="err_statut">Veuillez choisir un statut.</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Mettre à jour
                        </button>
                        <a href="contratList.php" class="btn btn-outline">Annuler</a>
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
        if (!validerChamp('id_assurance', function(v) { return v !== ''; })) ok = false;
        if (!validerChamp('date_d',       function(v) { return v !== ''; })) ok = false;
        if (!validerChamp('montant',      function(v) { return v.trim() !== '' && parseFloat(v) > 0; })) ok = false;
        if (!validerChamp('statut',       function(v) { return v !== ''; })) ok = false;
        var dateD = document.getElementById('date_d').value;
        var dateF = document.getElementById('date_f').value;
        if (dateF !== '' && dateF <= dateD) {
            document.getElementById('date_f').classList.add('is-invalid');
            ok = false;
        } else {
            document.getElementById('date_f').classList.remove('is-invalid');
        }
        return ok;
    }

    document.getElementById('id_assurance').addEventListener('change', function() { validerChamp('id_assurance', function(v) { return v !== ''; }); });
    document.getElementById('statut').addEventListener('change', function() { validerChamp('statut', function(v) { return v !== ''; }); });
    document.getElementById('date_d').addEventListener('change', function() { validerChamp('date_d', function(v) { return v !== ''; }); });
    document.getElementById('montant').addEventListener('input', function() { validerChamp('montant', function(v) { return v.trim() !== '' && parseFloat(v) > 0; }); });
</script>
</body>
</html>