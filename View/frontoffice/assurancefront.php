<?php
include '../../controller/AssuranceController.php';
$assuranceC = new AssuranceController();
$list       = $assuranceC->listAssurances();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Assurances - ASCLEPIA</title>
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
            <a href="#" class="nav-link active">Assurances</a>
            <a href="#" class="nav-link">Médecins</a>
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
    <section class="hero" style="min-height: 40vh; padding: 120px 0 60px;">
        <div class="hero-glow hero-glow-1"></div>
        <div class="hero-glow hero-glow-2"></div>
        <div class="container">
            <div class="hero-content" style="max-width:100%; text-align:center;">
                <div class="hero-badge">🛡️ Nos offres</div>
                <h1 class="hero-title">Choisissez votre <span class="highlight">assurance santé</span></h1>
                <p class="hero-subtitle" style="margin: 0 auto;">Des formules adaptées à chaque besoin, pour vous et votre famille.</p>
            </div>
        </div>
    </section>

    <!-- ASSURANCES SECTION -->
    <section class="section-padding" style="background: var(--bg);">
        <div class="container">
            <div class="row" style="justify-content: center;">
                <?php foreach ($list as $a): ?>
                <div class="col-4">
                    <div class="card assurance-card" style="text-align:center;">
                        <div class="icon-box icon-box-lg" style="margin: 0 auto 16px;">🛡️</div>

                        <span class="badge badge-primary" style="margin: 0 auto 12px;"><?= htmlspecialchars($a['TYPE']) ?></span>

                        <h3 style="font-size:1.2rem; margin-bottom: 10px;"><?= htmlspecialchars($a['nom_assurance']) ?></h3>

                        <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:16px; line-height:1.6;">
                            <?= htmlspecialchars($a['description']) ?>
                        </p>

                        <div class="rate"><?= number_format($a['prix'], 2) ?> DT <span>/ mois</span></div>

                        <div class="progress-bar-wrap" style="margin: 12px 0 6px;">
                            <div class="progress-bar" style="width: <?= $a['taux_remboursement'] ?>%"></div>
                        </div>
                        <p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:16px;">
                            Remboursement : <strong style="color:var(--primary)"><?= $a['taux_remboursement'] ?>%</strong>
                        </p>

                        <p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:20px;">
                            <i class="fa-regular fa-clock"></i> Durée : <?= $a['duree'] ?> mois
                        </p>

                        <a href="#" class="btn btn-primary" style="width:100%; justify-content:center;">
                            Souscrire <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
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