<?php
require_once '../../config/db.php';
require_once '../../controllers/ConsultationController.php';

$controller = new ConsultationController($pdo);
$consultations = $controller->getAllConsultations();
?>"
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Consultations - ASCLEPIA</title>
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
            <a href="#" class="nav-link active">Mes Consultations</a>
            <a href="#" class="nav-link">Pharmacies</a>
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
                <div class="hero-badge">📋 Espace patient</div>
                <h1 class="hero-title">Mes <span class="highlight">Consultations</span></h1>
                <p class="hero-subtitle" style="margin:0 auto;">
                    Consultez l'historique de vos consultations médicales.
                </p>
            </div>
        </div>
    </section>

    <!-- CONSULTATIONS -->
    <section class="section-padding" style="background:var(--bg);">
        <div class="container">

            <?php if (empty($consultations)): ?>
            <div class="card" style="text-align:center; padding:60px;">
                <div style="font-size:3rem; margin-bottom:16px;">📋</div>
                <h3>Aucune consultation trouvée</h3>
                <p class="text-muted" style="margin-top:8px;">Vous n'avez pas encore de consultation enregistrée.</p>
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($consultations as $c): ?>
                <div class="col-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fa-solid fa-calendar-check" style="color:var(--primary)"></i>
                                Consultation #<?= $c->getIdConsultation() ?>
                            </div>
                            <span class="badge badge-primary">
                                <i class="fa-regular fa-clock"></i>
                                <?= date('d/m/Y', strtotime($c->getDateConsultation())) ?>
                            </span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fa-solid fa-stethoscope"></i> Diagnostique
                            </label>
                            <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.6;">
                                <?= htmlspecialchars($c->getDiagnostique()) ?>
                            </p>
                        </div>

                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">
                                <i class="fa-solid fa-notes-medical"></i> Notes
                            </label>
                            <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.6;">
                                <?= htmlspecialchars($c->getNotes()) ?>
                            </p>
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