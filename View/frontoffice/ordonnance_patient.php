<?php
require_once '../../config/db.php';
require_once '../../models/Ordonnance.php';

$model = new Ordonnance($pdo);
$ordonnances = $model->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Ordonnances - ASCLEPIA</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/frontoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <a href="#" class="navbar-brand">
            <div class="navbar-logo">🏥</div>
            <div class="navbar-name">ASCL<span>EPIA</span></div>
        </a>
        <div class="nav-links" id="navLinks">
            <a href="#" class="nav-link">Accueil</a>
            <a href="consultation_patient.php" class="nav-link">Mes Consultations</a>
            <a href="ordonnance_patient.php" class="nav-link active">Mes Ordonnances</a>
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

    <!-- HERO -->
    <section class="hero" style="min-height:40vh; padding:120px 0 60px;">
        <div class="container">
            <div class="hero-content" style="max-width:100%; text-align:center;">
                <div class="hero-badge">💊 Espace patient</div>
                <h1 class="hero-title">Mes <span class="highlight">Ordonnances</span></h1>
                <p class="hero-subtitle" style="margin:0 auto;">
                    Consultez vos ordonnances et traitements prescrits.
                </p>
            </div>
        </div>
    </section>

    <!-- ORDONNANCES -->
    <section class="section-padding" style="background:var(--bg);">
        <div class="container">

            <?php if (empty($ordonnances)): ?>
            <div class="card" style="text-align:center; padding:60px;">
                <div style="font-size:3rem; margin-bottom:16px;">💊</div>
                <h3>Aucune ordonnance trouvée</h3>
                <p class="text-muted" style="margin-top:8px;">Vous n'avez pas encore d'ordonnance enregistrée.</p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($ordonnances as $o): ?>
                <div class="col-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fa-solid fa-file-prescription" style="color:var(--primary)"></i>
                                Ordonnance #<?= $o['id_ordonnance'] ?>
                            </div>
                            <span class="badge badge-success">
                                <i class="fa-regular fa-clock"></i>
                                <?= date('d/m/Y', strtotime($o['date_creation'])) ?>
                            </span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fa-solid fa-calendar-check"></i> Consultation
                            </label>
                            <p style="color:var(--text-muted); font-size:0.9rem;">
                                #<?= $o['id_consultation'] ?> — <?= date('d/m/Y', strtotime($o['date_consultation'])) ?>
                            </p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fa-solid fa-pills"></i> Médicaments
                            </label>
                            <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.6;">
                                <?= htmlspecialchars($o['medicaments']) ?>
                            </p>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fa-solid fa-notes-medical"></i> Instructions
                            </label>
                            <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.6;">
                                <?= htmlspecialchars($o['instructions']) ?>
                            </p>
                        </div>

                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">
                                <i class="fa-regular fa-calendar"></i> Durée du traitement
                            </label>
                            <span class="badge badge-primary">
                                <?= $o['duree_traitement'] ?> jours
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>© 2025 ASCLEPIA. Tous droits réservés.</p>
                <p>Fait avec ❤️ par l'équipe ASCLEPIA</p>
            </div>
        </div>
    </footer>

<script>
    window.addEventListener('scroll', function() {
        document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
    });
</script>
</body>
</html>