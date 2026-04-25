<?php
include '../../Controller/ContratController.php';
require_once __DIR__ . '/../../Model/Contrat.php';

$contratC       = new ContratController();
$assurances     = $contratC->listAssurances();
$assurancesList = [];
foreach ($assurances as $a) { $assurancesList[] = $a; }

// Récupérer détails assurances pour JS
$allAssurances    = config::getConnexion()->query("SELECT * FROM assurance")->fetchAll();
$assuranceDetails = [];
foreach ($allAssurances as $a) { $assuranceDetails[$a['id_assurance']] = $a; }

$success = false;
$error   = '';

if (isset($_POST['id_assurance'], $_POST['date_d'], $_POST['montant'])) {
    if (!empty($_POST['id_assurance']) && !empty($_POST['date_d']) && !empty($_POST['montant'])) {
        $contrat = new Contrat(
            null,
            $_POST['date_d'],
            !empty($_POST['date_f']) ? $_POST['date_f'] : null,
            (int)$_POST['id_assurance'],
            (float)$_POST['montant'],
            'Actif'
        );
        $contratC->addContrat($contrat);
        $success = true;
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

// ID passé depuis assurancefront.php
$preselect = isset($_GET['id_assurance']) ? (int)$_GET['id_assurance'] : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Souscrire - ASCLEPIA</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/frontoffice.css">
    <link rel="stylesheet" href="../../assets/css/assurance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .souscription-wrapper { max-width:680px; margin:0 auto; padding:40px 24px 80px; }
        .assurance-preview { background:rgba(14,165,233,0.06); border:1px solid rgba(14,165,233,0.2); border-radius:var(--radius-lg); padding:20px 24px; margin-bottom:24px; display:none; }
        .assurance-preview.visible { display:block; }
        .preview-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid rgba(14,165,233,0.1); font-size:0.9rem; }
        .preview-row:last-child { border-bottom:none; }
        .preview-label { color:var(--text-muted); font-weight:500; }
        .preview-value { font-weight:700; color:var(--dark); }
        .preview-value.price { color:var(--primary); font-size:1.1rem; }
        .success-box { text-align:center; padding:60px 24px; }
        .success-icon { width:80px; height:80px; background:linear-gradient(135deg,#10b981,#059669); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; margin:0 auto 24px; box-shadow:0 8px 24px rgba(16,185,129,0.3); }
    </style>
</head>
<body>

    <nav class="navbar" id="navbar">
        <a href="#" class="navbar-brand">
            <div class="navbar-logo">🏥</div>
            <div class="navbar-name">ASCL<span>EPIA</span></div>
        </a>
        <div class="nav-links" id="navLinks">
            <a href="#" class="nav-link">Accueil</a>
            <a href="assurancefront.php" class="nav-link">Assurances</a>
            <a href="#" class="nav-link active">Souscrire</a>
            <a href="#" class="nav-link">Contact</a>
        </div>
        <div class="nav-actions">
            <a href="#" class="btn btn-outline-white btn-sm">Connexion</a>
            <a href="#" class="btn btn-primary btn-sm">S'inscrire</a>
        </div>
        <div class="hamburger" onclick="document.getElementById('navLinks').classList.toggle('open')">
            <span></span><span></span><span></span>
        </div>
    </nav>

    <section class="hero" style="min-height:35vh; padding:100px 0 50px;">
        <div class="hero-glow hero-glow-1"></div>
        <div class="hero-glow hero-glow-2"></div>
        <div class="container">
            <div class="hero-content" style="max-width:100%; text-align:center;">
                <div class="hero-badge">📄 Contrat</div>
                <h1 class="hero-title">Souscrire à une <span class="highlight">assurance</span></h1>
                <p class="hero-subtitle" style="margin:0 auto;">Remplissez le formulaire ci-dessous pour souscrire à votre assurance.</p>
            </div>
        </div>
    </section>

    <section style="background:var(--bg); padding:60px 0;">
        <div class="container">
            <div class="souscription-wrapper">

                <?php if ($success): ?>
                <div class="card">
                    <div class="success-box">
                        <div class="success-icon">✅</div>
                        <h2 style="margin-bottom:12px; color:var(--dark);">Souscription réussie !</h2>
                        <p style="color:var(--text-muted); margin-bottom:32px;">
                            Votre contrat a été créé avec succès. Il est maintenant <strong style="color:var(--accent)">Actif</strong>.
                        </p>
                        <div class="d-flex gap-2" style="justify-content:center;">
                            <a href="souscrireContrat.php" class="btn btn-primary">
                                <i class="fa-solid fa-plus"></i> Nouveau contrat
                            </a>
                            <a href="assurancefront.php" class="btn btn-outline">Voir les assurances</a>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" style="margin-bottom:24px;">
                        <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fa-solid fa-file-contract" style="color:var(--primary)"></i>
                            Formulaire de souscription
                        </div>
                    </div>

                    <form action="" method="POST" id="formSouscrire" onsubmit="return validerFormulaire()">

                        <div class="form-group">
                            <label class="form-label">Choisir une assurance <span style="color:var(--danger)">*</span></label>
                            <select name="id_assurance" id="id_assurance" class="form-control" onchange="majPreview()">
                                <option value="">-- Sélectionner une assurance --</option>
                                <?php foreach ($assurancesList as $a): ?>
                                    <option value="<?= $a['id_assurance'] ?>"
                                        <?= $preselect == $a['id_assurance'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($a['nom_assurance']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="form-error" id="err_assurance">Veuillez choisir une assurance.</span>
                        </div>

                        <div class="assurance-preview" id="assurancePreview">
                            <p style="font-weight:700; color:var(--dark); margin-bottom:12px;">
                                <i class="fa-solid fa-circle-info" style="color:var(--primary)"></i>
                                Détails de l'assurance sélectionnée
                            </p>
                            <div class="preview-row">
                                <span class="preview-label">Type</span>
                                <span class="preview-value" id="prev_type">—</span>
                            </div>
                            <div class="preview-row">
                                <span class="preview-label">Durée</span>
                                <span class="preview-value" id="prev_duree">—</span>
                            </div>
                            <div class="preview-row">
                                <span class="preview-label">Remboursement</span>
                                <span class="preview-value" id="prev_taux">—</span>
                            </div>
                            <div class="preview-row">
                                <span class="preview-label">Prix mensuel</span>
                                <span class="preview-value price" id="prev_prix">—</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Date de début <span style="color:var(--danger)">*</span></label>
                                    <input type="date" name="date_d" id="date_d" class="form-control" onchange="calcMontant()">
                                    <span class="form-error" id="err_date_d">La date de début est requise.</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Date de fin <span style="color:var(--text-muted); font-size:0.78rem;">(optionnel)</span></label>
                                    <input type="date" name="date_f" id="date_f" class="form-control" onchange="calcMontant()">
                                    <span class="form-error" id="err_date_f">La date de fin doit être après la date de début.</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Montant total (DT) <span style="color:var(--danger)">*</span></label>
                            <input type="number" name="montant" id="montant" class="form-control" placeholder="Calculé automatiquement">
                            <div class="form-hint">Calculé automatiquement selon l'assurance et la durée.</div>
                            <span class="form-error" id="err_montant">Le montant doit être supérieur à 0.</span>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-lg" style="flex:1; justify-content:center;">
                                <i class="fa-solid fa-check"></i> Confirmer la souscription
                            </button>
                            <a href="assurancefront.php" class="btn btn-outline">Annuler</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>© 2025 ASCLEPIA. Tous droits réservés.</p>
                <p>Fait avec ❤️ par l'équipe ASCLEPIA</p>
            </div>
        </div>
    </footer>

<script>
    var assurances = <?= json_encode($assuranceDetails) ?>;

    window.addEventListener('scroll', function() {
        document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
    });

    // Pré-sélectionner si id passé en URL
    window.addEventListener('load', function() {
        if (document.getElementById('id_assurance').value !== '') {
            majPreview();
        }
    });

    function majPreview() {
        var id      = document.getElementById('id_assurance').value;
        var preview = document.getElementById('assurancePreview');
        if (id && assurances[id]) {
            var a = assurances[id];
            document.getElementById('prev_type').textContent  = a.TYPE;
            document.getElementById('prev_duree').textContent = a.duree + ' mois';
            document.getElementById('prev_taux').textContent  = a.taux_remboursement + '%';
            document.getElementById('prev_prix').textContent  = parseFloat(a.prix).toFixed(2) + ' DT / mois';
            preview.classList.add('visible');
            calcMontant();
        } else {
            preview.classList.remove('visible');
            document.getElementById('montant').value = '';
        }
    }

    function calcMontant() {
        var id    = document.getElementById('id_assurance').value;
        var dateD = document.getElementById('date_d').value;
        var dateF = document.getElementById('date_f').value;
        if (!id || !assurances[id] || !dateD) return;
        var prix        = parseFloat(assurances[id].prix);
        var dureeDefaut = parseInt(assurances[id].duree);
        if (dateF && dateF > dateD) {
            var d1   = new Date(dateD);
            var d2   = new Date(dateF);
            var mois = (d2.getFullYear() - d1.getFullYear()) * 12 + (d2.getMonth() - d1.getMonth());
            document.getElementById('montant').value = (prix * mois).toFixed(2);
        } else {
            document.getElementById('montant').value = (prix * dureeDefaut).toFixed(2);
        }
    }

    function validerChamp(id, condition) {
        var input  = document.getElementById(id);
        var valide = condition(input.value);
        input.classList.toggle('is-invalid', !valide);
        return valide;
    }

    function validerFormulaire() {
        var ok = true;
        if (!validerChamp('id_assurance', function(v) { return v !== ''; })) ok = false;
        if (!validerChamp('date_d',       function(v) { return v !== ''; })) ok = false;
        if (!validerChamp('montant',      function(v) { return v !== '' && parseFloat(v) > 0; })) ok = false;
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
    document.getElementById('date_d').addEventListener('change', function() { validerChamp('date_d', function(v) { return v !== ''; }); });
    document.getElementById('montant').addEventListener('input', function() { validerChamp('montant', function(v) { return v !== '' && parseFloat(v) > 0; }); });
</script>
</body>
</html>