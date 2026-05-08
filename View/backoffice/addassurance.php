<?php
include '../../Controller/AssuranceController.php';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Assurance - ASCLEPIA Admin</title>
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
                    <div class="page-title">Nouvelle Assurance</div>
                    <div class="breadcrumb">
                        <a href="assuranceList.php">Assurances</a>
                        <span>/</span>
                        <span>Ajouter</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
            <?php endif; ?>

            <div class="card" style="max-width: 700px; margin: 0 auto;">
                <div class="card-header">
                    <div class="card-title"><i class="fa-solid fa-shield-halved" style="color:var(--primary)"></i> Ajouter une assurance</div>
                </div>

                <form action="" method="POST" id="formAdd" onsubmit="return validerFormulaire()">

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Nom de l'assurance</label>
                                <input type="text" name="nom_assurance" id="nom_assurance" class="form-control" placeholder="Ex: Assurance Santé Plus">
                                <span class="form-error" id="err_nom">Minimum 3 caractères requis.</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">Type</label>
                                <select name="TYPE" id="TYPE" class="form-control">
                                    <option value="">-- Choisir --</option>
                                    <option value="Santé">Santé</option>
                                    <option value="Dentaire">Dentaire</option>
                                    <option value="Vision">Vision</option>
                                    <option value="Maternité">Maternité</option>
                                    <option value="Complète">Complète</option>
                                </select>
                                <span class="form-error" id="err_type">Veuillez choisir un type.</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" placeholder="Décrivez cette assurance..."></textarea>
                        <span class="form-error" id="err_description">Minimum 10 caractères requis.</span>
                    </div>

                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">Prix (DT / mois)</label>
                                <input type="number" name="prix" id="prix" class="form-control" placeholder="0.00">
                                <span class="form-error" id="err_prix">Doit être supérieur à 0.</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">Durée (mois)</label>
                                <input type="number" name="duree" id="duree" class="form-control" placeholder="12">
                                <span class="form-error" id="err_duree">Doit être supérieure à 0.</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">Taux remboursement (%)</label>
                                <input type="number" name="taux_remboursement" id="taux_remboursement" class="form-control" placeholder="80">
                                <span class="form-error" id="err_taux">Doit être entre 0 et 100.</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Ajouter
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