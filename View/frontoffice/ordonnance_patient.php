<?php
session_start();
require_once '../../config.php';
require_once '../../Controller/OrdonnanceController.php';
include '../../Controller/UserController.php';

// === SESSION ===
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userId     = $_SESSION['user_id']    ?? null;
$userNom    = $_SESSION['user_nom']   ?? '';
$userEmail  = $_SESSION['user_email'] ?? '';
$userRole   = $_SESSION['user_role']  ?? '';
$isAdmin    = ($userRole === 'admin');

// Récupérer l'avatar via le controller
$userC      = new UserController();
$userAvatar = ($isLoggedIn && $userId) ? $userC->getAvatarByUserId($userId) : 'default';

// Rediriger si non connecté
if (!$isLoggedIn || !$userId) {
    header('Location: ../front/indexp.php');
    exit;
}

$controller  = new OrdonnanceController(config::getConnexion());
$ordonnances = $controller->getOrdonnancesByPatient($userId);
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
    <link rel="stylesheet" href="../assets/css/avatar.css">
    <link rel="stylesheet" href="../../assets/css/assurance.css">
    <style>
        .nav-user-info { display:flex; align-items:center; gap:8px; color:white; font-size:0.9rem; }
        .nav-user-info .user-avatar { width:32px; height:32px; border-radius:50%; object-fit:cover; border:2px solid rgba(255,255,255,0.4); }
    </style>
</head>
<body id="body">

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <a href="#" class="navbar-brand">
            <div class="navbar-logo">🏥</div>
            <div class="navbar-name">ASCL<span>EPIA</span></div>
        </a>
        <div class="nav-links" id="navLinks">
            <a href="../front/indexp.php" class="nav-link">Accueil</a>
            <a href="consultation_patient.php" class="nav-link">Mes Consultations</a>
            <a href="ordonnance_patient.php" class="nav-link active">Mes Ordonnances</a>
            <a href="#" class="nav-link">Contact</a>
        </div>
        <div class="nav-actions">

            <?php if ($isLoggedIn): ?>
                <div class="nav-user-info">
                    <div class="avatar-css small avatar-<?= htmlspecialchars($userAvatar) ?>"></div>
                    <span><?= htmlspecialchars($userNom) ?></span>
                </div>
                <?php if ($isAdmin): ?>
                    <a href="../back/dashboard.php" class="btn btn-outline-white btn-sm">
                        <i class="fa-solid fa-gauge"></i> Admin
                    </a>
                <?php endif; ?>
                <a href="../back/logout.php" class="btn btn-outline-white btn-sm">
                    <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
                </a>
            <?php else: ?>
                <a href="login.html" class="btn btn-outline-white btn-sm">Connexion</a>
                <a href="loginuser.html" class="btn btn-primary btn-sm">S'inscrire</a>
            <?php endif; ?>

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
                <?php if ($isLoggedIn): ?>
                    <p style="margin-top:12px; color:rgba(255,255,255,0.8); font-size:0.95rem;">
                        👋 Bienvenue, <strong><?= htmlspecialchars($userNom) ?></strong>
                    </p>
                <?php endif; ?>
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

    <!-- BOUTON DARK MODE -->
    <button class="dark-toggle" id="darkToggle" onclick="toggleDark()" title="Mode sombre">🌙</button>

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

    // ---- MODE SOMBRE ----
    var darkMode = localStorage.getItem('darkMode') === 'true';

    function appliquerDark() {
        if (darkMode) {
            document.getElementById('body').classList.add('dark-mode');
            document.getElementById('darkToggle').textContent = '☀️';
        } else {
            document.getElementById('body').classList.remove('dark-mode');
            document.getElementById('darkToggle').textContent = '🌙';
        }
    }

    function toggleDark() {
        darkMode = !darkMode;
        localStorage.setItem('darkMode', darkMode);
        appliquerDark();
    }

    appliquerDark();
</script>
</body>
</html>