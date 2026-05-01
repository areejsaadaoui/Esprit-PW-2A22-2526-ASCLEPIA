<?php
// choose_avatar.php - Page de sélection d'avatar
session_start();

require_once '../../config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.html');
    exit();
}

$pdo = config::getConnexion();
$user_id = $_SESSION['user_id'] ?? null;
$success = '';
$error = '';

if (!$user_id) {
    header('Location: login.html');
    exit();
}

// Récupérer l'avatar actuel
$stmt = $pdo->prepare("SELECT avatar_style, nom FROM utilisateur WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$current_style = $user['avatar_style'] ?? 'default';
$user_nom = $user['nom'] ?? 'Utilisateur';

// Sauvegarder le choix
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['avatar_style'])) {
    $selected = $_POST['avatar_style'];
    $stmt = $pdo->prepare("UPDATE utilisateur SET avatar_style = ? WHERE id_user = ?");
    
    if ($stmt->execute([$selected, $user_id])) {
        $success = true;
        $current_style = $selected;
    } else {
        $error = true;
    }
}

// Liste des avatars disponibles
$avatars = [
    // Hommes
    ['style' => 'homme_cheveux_noirs', 'name' => 'Homme cheveux noirs', 'category' => 'Homme'],
    ['style' => 'homme_cheveux_bruns', 'name' => 'Homme cheveux bruns', 'category' => 'Homme'],
    ['style' => 'homme_cheveux_blancs', 'name' => 'Homme cheveux blancs', 'category' => 'Homme'],
    ['style' => 'homme_barbu', 'name' => 'Homme barbu', 'category' => 'Homme'],
    ['style' => 'homme_jeune', 'name' => 'Homme jeune', 'category' => 'Homme'],
    ['style' => 'homme_age', 'name' => 'Homme âgé', 'category' => 'Homme'],
    
    // Femmes
    ['style' => 'femme_cheveux_noirs', 'name' => 'Femme cheveux noirs', 'category' => 'Femme'],
    ['style' => 'femme_cheveux_blonds', 'name' => 'Femme cheveux blonds', 'category' => 'Femme'],
    ['style' => 'femme_cheveux_bruns', 'name' => 'Femme cheveux bruns', 'category' => 'Femme'],
    ['style' => 'femme_voilee', 'name' => 'Femme voilée', 'category' => 'Femme'],
    ['style' => 'femme_agee', 'name' => 'Femme âgée', 'category' => 'Femme'],
    
    // Professionnels
    ['style' => 'medecin', 'name' => 'Médecin', 'category' => 'Profession'],
    ['style' => 'infirmiere', 'name' => 'Infirmière', 'category' => 'Profession'],
    ['style' => 'pharmacien', 'name' => 'Pharmacien', 'category' => 'Profession'],
    
    // Autres
    ['style' => 'default', 'name' => 'Avatar par défaut', 'category' => 'Défaut'],
    ['style' => 'patient', 'name' => 'Patient', 'category' => 'Autre'],
    ['style' => 'enfant', 'name' => 'Enfant', 'category' => 'Autre'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Choisissez votre avatar - ASCLEPIA">
    <title>Choisir mon avatar — ASCLEPIA</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/frontoffice.css">
    <link rel="stylesheet" href="../assets/css/avatar.css">

    <style>
        /* Styles spécifiques pour la page avatar */
        .avatar-section-header {
            text-align: center;
            margin-bottom: 48px;
        }
        
        .avatar-section-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(99,102,241,0.1);
            padding: 6px 16px;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .current-avatar-card {
            background: white;
            border-radius: 24px;
            padding: 24px 32px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid var(--border);
        }
        
        .current-avatar-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .current-avatar-preview {
            width: 80px;
            height: 80px;
        }
        
        .avatar-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }
        
        .avatar-card-item {
            background: white;
            border-radius: 20px;
            padding: 24px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid var(--border);
            position: relative;
        }
        
        .avatar-card-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }
        
        .avatar-card-item.selected {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(99,102,241,0.05), rgba(139,92,246,0.05));
        }
        
        .avatar-card-item.selected::after {
            content: '✓';
            position: absolute;
            top: 12px;
            right: 12px;
            background: var(--primary);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        .avatar-preview-img {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
        }
        
        .avatar-card-name {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text);
            margin-bottom: 5px;
        }
        
        .avatar-card-category {
            font-size: 0.7rem;
            color: var(--text-muted);
            display: inline-block;
            padding: 2px 10px;
            background: var(--bg);
            border-radius: 20px;
        }
        
        .validation-bar {
            background: var(--bg);
            border-radius: 20px;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
            border: 1px solid var(--border);
        }
        
        .alert-custom {
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-custom.success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #22c55e;
        }
        
        .alert-custom.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        @media (max-width: 768px) {
            .avatar-gallery {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
                gap: 15px;
            }
            .current-avatar-card {
                flex-direction: column;
                text-align: center;
            }
            .current-avatar-info {
                flex-direction: column;
            }
            .validation-bar {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<!-- ================================================
     NAVBAR (identique à indexp.php)
     ================================================ -->
<nav class="navbar" id="navbar">
    <a href="indexp.php" class="navbar-brand">
        <div class="navbar-logo"></div>
        <div class="navbar-name">ASC<span>LEPIA</span></div>
    </a>

    <div class="nav-links" id="navLinks">
        <a href="indexp.php#accueil" class="nav-link">Accueil</a>
        <a href="indexp.php#services" class="nav-link">Services</a>
        <a href="indexp.php#pharmacies" class="nav-link">Pharmacies</a>
        <a href="indexp.php#assurances" class="nav-link">Assurances</a>
        <a href="indexp.php#forum" class="nav-link">Post&Reponse</a>
        <a href="indexp.php#avis" class="nav-link">Avis</a>
    </div>

    <div class="nav-actions">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div class="avatar-css avatar-<?php echo $current_style; ?> small"></div>
            <span style="color: white; font-weight: 500;"><?php echo htmlspecialchars($user_nom); ?></span>
            <a href="profile.php" class="btn btn-outline-white btn-sm">
                <i class="fa-solid fa-user"></i> Mon profil
            </a>
            <a href="../back/logout.php" class="btn btn-outline-white btn-sm">
                <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
        <div class="hamburger" id="hamburger" onclick="toggleMenu()">
            <span></span><span></span><span></span>
        </div>
    </div>
</nav>

<!-- ================================================
     PAGE CONTENT
     ================================================ -->
<section class="section-padding" style="background: var(--bg); min-height: calc(100vh - 70px);">
    <div class="container">
        
        <!-- Header -->
        <div class="avatar-section-header">
            <div class="avatar-section-tag">
                <i class="fa-solid fa-face-smile"></i>
                Personnalisation
            </div>
            <h2 class="section-title">Choisissez votre avatar</h2>
            <p class="section-desc">Sélectionnez l'avatar qui vous correspond le mieux</p>
        </div>

        <!-- Message de succès/erreur -->
        <?php if ($success): ?>
            <div class="alert-custom success">
                <i class="fa-solid fa-check-circle" style="font-size: 1.2rem;"></i>
                ✓ Avatar mis à jour avec succès !
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-custom error">
                <i class="fa-solid fa-exclamation-triangle" style="font-size: 1.2rem;"></i>
                ✗ Erreur lors de la mise à jour.
            </div>
        <?php endif; ?>

        <!-- Avatar actuel -->
        <div class="current-avatar-card">
            <div class="current-avatar-info">
                <div class="current-avatar-preview">
                    <div class="avatar-css avatar-<?php echo $current_style; ?> large"></div>
                </div>
                <div>
                    <h3 style="margin-bottom: 4px;">Avatar actuel</h3>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">
                        <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($user_nom); ?>
                    </p>
                </div>
            </div>
            <a href="profile.php" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Retour au profil
            </a>
        </div>

        <!-- Formulaire -->
        <form method="POST" id="avatarForm">
            <div class="avatar-gallery">
                <?php foreach ($avatars as $avatar): ?>
                <div class="avatar-card-item <?php echo $current_style == $avatar['style'] ? 'selected' : ''; ?>" 
                     data-style="<?php echo $avatar['style']; ?>"
                     onclick="selectAvatar('<?php echo $avatar['style']; ?>', this)">
                    <div class="avatar-preview-img">
                        <div class="avatar-css avatar-<?php echo $avatar['style']; ?> medium"></div>
                    </div>
                    <div class="avatar-card-name"><?php echo $avatar['name']; ?></div>
                    <div class="avatar-card-category"><?php echo $avatar['category']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="validation-bar">
                <div style="color: var(--text-muted); font-size: 0.85rem;">
                    <i class="fa-solid fa-info-circle"></i> Cliquez sur un avatar pour le sélectionner
                </div>
                <div>
                    <input type="hidden" name="avatar_style" id="selectedAvatar" value="<?php echo $current_style; ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check-double"></i> Valider mon choix
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- ================================================
     FOOTER (identique)
     ================================================ -->
<footer class="footer">
    <div class="container">
        <div class="row" style="gap: 48px;">
            <div style="flex: 0 0 260px;">
                <div class="footer-brand">
                    <div class="navbar-brand" style="margin-bottom: 16px;">
                        <div class="navbar-logo">⚕️</div>
                        <div class="navbar-name" style="font-size: 1.2rem;">ASC<span class="text-primary">LEPIA</span></div>
                    </div>
                    <p>Votre plateforme médicale complète. Consultations, ordonnances, pharmacies, assurances et communauté santé.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul class="footer-links">
                        <li><a href="consultation.php"><i class="fa-solid fa-stethoscope"></i> Consultations</a></li>
                        <li><a href="consultation.php"><i class="fa-solid fa-file-prescription"></i> Ordonnances</a></li>
                        <li><a href="addpharmacie.php"><i class="fa-solid fa-pills"></i> Pharmacies</a></li>
                        <li><a href="assurance.php"><i class="fa-solid fa-shield-halved"></i> Assurances</a></li>
                        <li><a href="forum.php"><i class="fa-solid fa-comments"></i> Forum santé</a></li>
                    </ul>
                </div>
            </div>
            <div class="col">
                <div class="footer-section">
                    <h4>Liens utiles</h4>
                    <ul class="footer-links">
                        <li><a href="indexp.php"><i class="fa-solid fa-home"></i> Accueil</a></li>
                        <li><a href="profile.php"><i class="fa-solid fa-user"></i> Mon profil</a></li>
                        <li><a href="choose_avatar.php"><i class="fa-solid fa-face-smile"></i> Changer avatar</a></li>
                        <li><a href="../back/logout.php"><i class="fa-solid fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            <div class="col">
                <div class="footer-section">
                    <h4>Contact</h4>
                    <div class="footer-contact-item">
                        <i class="fa-solid fa-location-dot icon"></i>
                        <span>Rue de l'Innovation, Tunis 1002, Tunisie</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fa-solid fa-phone icon"></i>
                        <span>+216 71 000 000</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fa-solid fa-envelope icon"></i>
                        <span>contact@asclepia.tn</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fa-solid fa-clock icon"></i>
                        <span>24h/7j — Service disponible en permanence</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 <a href="indexp.php">ASCLEPIA</a>. Tous droits réservés.</p>
            <p>Conçu avec ❤️ pour une meilleure santé</p>
        </div>
    </div>
</footer>

<script>
    // Mobile menu
    function toggleMenu() {
        document.getElementById('navLinks').classList.toggle('open');
    }

    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 30);
    });

    // Sélection d'avatar
    function selectAvatar(style, element) {
        document.getElementById('selectedAvatar').value = style;
        
        // Mettre à jour l'aperçu
        const previewDiv = document.querySelector('.current-avatar-preview');
        previewDiv.innerHTML = `<div class="avatar-css avatar-${style} large"></div>`;
        
        // Mettre à jour la navbar
        const navAvatar = document.querySelector('.nav-actions .avatar-css');
        if (navAvatar) {
            navAvatar.className = `avatar-css avatar-${style} small`;
        }
        
        // Mettre à jour la classe selected
        document.querySelectorAll('.avatar-card-item').forEach(item => {
            item.classList.remove('selected');
        });
        element.classList.add('selected');
    }

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
</script>

</body>
</html>