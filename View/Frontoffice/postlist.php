<?php
include '../../Controller/PostController.php';
$postC = new PostController();
$posts = $postC->listPosts();
// ========== GESTION DU TRI ==========
$orderBy = $_GET['order'] ?? 'date_desc';

switch ($orderBy) {
    case 'date_asc':
        usort($posts, function($a, $b) {
            return strtotime($a->getDatePost()) - strtotime($b->getDatePost());
        });
        break;
    case 'length_desc':
        usort($posts, function($a, $b) {
            return strlen($b->getContenu()) - strlen($a->getContenu());
        });
        break;
    case 'length_asc':
        usort($posts, function($a, $b) {
            return strlen($a->getContenu()) - strlen($b->getContenu());
        });
        break;
    default: // date_desc
        usort($posts, function($a, $b) {
            return strtotime($b->getDatePost()) - strtotime($a->getDatePost());
        });
}
// ========== PAGINATION ==========
$postsParPage = 9;
$totalPosts = count($posts);
$nombreDePages = ceil($totalPosts / $postsParPage);

$pageActuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($pageActuelle < 1) $pageActuelle = 1;
if ($pageActuelle > $nombreDePages && $nombreDePages > 0) $pageActuelle = $nombreDePages;

$premierIndex = ($pageActuelle - 1) * $postsParPage;
$postsAPaginer = array_slice($posts, $premierIndex, $postsParPage);


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>ASCLEPIA — communauté</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/frontoffice.css">
    <style>
        .post-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: var(--radius);
            margin-bottom: 12px;
            cursor: pointer;
        }
        
        .post-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        
        .post-content {
            flex: 1;
        }
        
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        
        .post-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            justify-content: flex-end;
        }
        
        @media (max-width: 768px) {
            .posts-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
        }
        
        @media (max-width: 480px) {
            .posts-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }
        
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
        }
        
        .hamburger span {
            width: 25px;
            height: 3px;
            background: white;
            border-radius: 3px;
            transition: 0.3s;
        }
        
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }
            .nav-links {
                display: flex;
            }
        }
        .sort-select {
    cursor: pointer;
    transition: all 0.2s;
}
.sort-select:hover {
    border-color: var(--primary);
}

/* ===== DARK MODE – VERSION POSTLIST ===== */

body.dark-mode {
    background: #1a1a2e !important;
}

/* Fond des sections principales */
body.dark-mode .section-padding,
body.dark-mode .container {
    background: transparent !important;
}

/* Cartes (posts) */
body.dark-mode .card,
body.dark-mode .post-card,
body.dark-mode .quick-card {
    background: #16213e !important;
    border-color: #2d2d44 !important;
}

/* Navbar */
body.dark-mode .navbar {
    background: #0f0f1a !important;
    border-bottom: 1px solid #2d2d44 !important;
}

body.dark-mode .navbar .navbar-name,
body.dark-mode .navbar .nav-link {
    color: #e0e0e0 !important;
}

body.dark-mode .navbar .nav-link:hover,
body.dark-mode .navbar .nav-link.active {
    color: #0ea5e9 !important;
}

/* Footer */
body.dark-mode .footer {
    background: #0f0f1a !important;
    border-top: 1px solid #2d2d44 !important;
}

body.dark-mode .footer p,
body.dark-mode .footer .footer-section h4,
body.dark-mode .footer .footer-links a {
    color: #c0c0d0 !important;
}

/* Barre de tri */
body.dark-mode .sort-select {
    background: #16213e !important;
    color: white !important;
    border-color: #2d2d44 !important;
}

body.dark-mode .sort-select option {
    background: #16213e !important;
    color: white !important;
}

/* Indicateur du nombre de posts */
body.dark-mode .sort-bar div:last-child,
body.dark-mode .sort-bar div:last-child i,
body.dark-mode .sort-bar .fa-chart-line {
    color: white !important;
}

/* Texte "Filtre" et icône */
body.dark-mode .sort-bar span,
body.dark-mode .sort-bar i.fa-sort {
    color: white !important;
}

/* Métadonnées du post */
body.dark-mode .post-meta .post-author,
body.dark-mode .post-meta .post-date,
body.dark-mode .post-date i,
body.dark-mode .post-date {
    color: #c0c0d0 !important;
}

body.dark-mode .post-content p {
    color: #e0e0e0 !important;
}

/* Boutons dans les cartes */
body.dark-mode .btn-outline {
    border-color: #475569 !important;
    color: #cbd5e1 !important;
}

body.dark-mode .btn-outline:hover {
    background: #334155 !important;
    color: white !important;
}

body.dark-mode .btn-primary {
    background: #0ea5e9 !important;
    color: white !important;
}

body.dark-mode .btn-danger {
    background: #dc2626 !important;
    color: white !important;
}

/* Pagination */
body.dark-mode .pagination a,
body.dark-mode .pagination span {
    background: #16213e !important;
    color: #c0c0d0 !important;
    border-color: #2d2d44 !important;
}

body.dark-mode .pagination .active {
    background: #0ea5e9 !important;
    color: white !important;
}

body.dark-mode .pagination a:hover {
    background: #1a2a4a !important;
    color: white !important;
}

/* Section header */
body.dark-mode .section-header .section-title,
body.dark-mode .section-header .section-desc,
body.dark-mode .section-tag {
    color: white !important;
}

body.dark-mode .section-tag i {
    color: #0ea5e9 !important;
}

/* Avatar dans les posts */
body.dark-mode .post-avatar {
    background: linear-gradient(135deg, #0ea5e9, #3b82f6) !important;
}

/* Bouton Nouvelle discussion */
body.dark-mode .btn-primary {
    background: #0ea5e9 !important;
    border: none !important;
}

/* Mot "Filtre" en blanc */
body.dark-mode .fa-sort span {
    color: white !important;
}
/* Bouton toggle flottant */
.theme-toggle {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #0ea5e9;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 1.3rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: all 0.3s;
    z-index: 9999;
}

.theme-toggle:hover {
    transform: scale(1.1);
}
    </style>
</head>
<body>

<nav class="navbar" id="navbar">
    <a href="index.html" class="navbar-brand">
        <div class="navbar-logo"></div>
        <div class="navbar-name">ASC<span>LEPIA</span></div>
    </a>
    <div class="nav-links" id="navLinks">
        <a href="index.html#accueil" class="nav-link">Accueil</a>
        <a href="index.html#services" class="nav-link">Services</a>
        <a href="index.html#pharmacies" class="nav-link">Pharmacies</a>
        <a href="index.html#assurances" class="nav-link">Assurances</a>
        <a href="postlist.php" class="nav-link active">Communauté</a>
        <a href="index.html#avis" class="nav-link">Plus</a>
    </div>
    <div class="nav-actions">
        <a href="login.html" class="btn btn-outline-white btn-sm">Se connecter</a>
        <a href="login.html" class="btn btn-primary btn-sm">S'inscrire</a>
        <div class="hamburger" id="hamburger" onclick="toggleMenu()">
            <span></span><span></span><span></span>
        </div>
    </div>
</nav>

<section class="section-padding" style="background: var(--bg); min-height: 80vh;">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">
                <i class="fa-solid fa-comments"></i>
                Communauté
            </div>
            <h2 class="section-title">Forum Santé</h2>
            <p class="section-desc">
                Échangez avec la communauté. Posez vos questions et partagez votre expérience.
            </p>
        </div>

        <div style="text-align: right; margin-bottom: 30px;">
            <a href="../Backoffice/addpost.php" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i>
                Nouvelle discussion
            </a>
        </div>
        <!-- Barre de tri -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-sort"></i>
        <span style="font-weight: 500;">Filtre </span>
        
        <form method="GET" action="" id="orderForm" style="display: inline;">
            <select name="order" class="sort-select" onchange="this.form.submit()" style="padding: 8px 15px; border-radius: 30px; border: 1px solid var(--border); background: white;">
                <option value="date_desc" <?= ($orderBy == 'date_desc') ? 'selected' : '' ?>>📅 Date décroissante</option>
                <option value="date_asc" <?= ($orderBy == 'date_asc') ? 'selected' : '' ?>>📅 Date croissante</option>
                <option value="length_desc" <?= ($orderBy == 'length_desc') ? 'selected' : '' ?>>📄 Plus long </option>
                <option value="length_asc" <?= ($orderBy == 'length_asc') ? 'selected' : '' ?>>📄 Plus court </option>
                
            </select>
        </form>
    </div>
    
    <!-- Indicateur du nombre de posts -->
    <div style="color: var(--text-muted); font-size: 0.85rem;">
        <i class="fas fa-chart-line"></i> <?= count($posts) ?> discussions au total
    </div>
</div>

       <div class="posts-grid">
    <?php foreach ($postsAPaginer as $post): ?>
        <div class="grid-item">
            <div class="card post-card">
                

                <!-- Image ou GIF -->
<?php 
$mediaPath = $post->getImage();
if (!empty($mediaPath)):
    $isGif = (strpos($mediaPath, '.gif') !== false || strpos($mediaPath, 'giphy.com') !== false);
    
    // Ajoute ../Backoffice/ uniquement pour les images uploadées (pas pour les URL GIPHY)
    if (!$isGif && !filter_var($mediaPath, FILTER_VALIDATE_URL)) {
        $mediaPath = '../Backoffice/' . $mediaPath;
    }
    
    $imgStyle = $isGif ? 'object-fit: contain; max-height: 180px;' : 'object-fit: cover; height: 180px;';
?>
    <img src="<?= $mediaPath ?>" 
         alt="Post media" 
         class="post-image"
         style="width: 100%; <?= $imgStyle ?> border-radius: 12px; margin-bottom: 12px; cursor: pointer;"
         onclick="window.location.href='../Backoffice/showpost.php?id=<?= $post->getIdPost() ?>'">
<?php endif; ?>
                
                <!-- Post meta -->
                <div class="post-meta">
                    <div class="post-avatar" style="background: var(--gradient-primary);">
                        <?= strtoupper(substr($post->getIdUtilisateur() ?? 'U', 0, 2)) ?>
                    </div>
                    <div>
                        <div class="post-author">Utilisateur #<?= $post->getIdUtilisateur() ?></div>
                        <div class="post-date">
                            <i class="fa-regular fa-calendar"></i>
                            <?= (new DateTime($post->getDatePost()))->format('d/m/Y à H:i') ?>
                        </div>
                    </div>
                </div>
                
                <!-- Post contenu -->
                <div class="post-content">
                    <p style="font-size: 0.88rem; line-height: 1.6; margin-bottom: 16px; color: var(--text-muted);">
                        <?= nl2br(htmlspecialchars(substr($post->getContenu(), 0, 150))) ?>
                        <?php if (strlen($post->getContenu()) > 150): ?>...<?php endif; ?>
                    </p>
                </div>
                
                <!-- Post footer -->
                <div class="post-footer">
                    <a href="../Backoffice/showpost.php?id=<?= $post->getIdPost() ?>" class="btn btn-outline btn-sm">
                        Lire la suite
                    </a>
                    <div class="post-actions">
                        <a href="../Backoffice/modifpost.php?id=<?= $post->getIdPost() ?>" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <a href="../Backoffice/deletepost.php?id=<?= $post->getIdPost() ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Supprimer ce post ?')">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
    <?php endforeach; ?>
</div>
   <!-- PAGINATION -->
<?php if ($nombreDePages > 1): ?>
    <div style="display: flex; justify-content: center; gap: 8px; margin-top: 40px; flex-wrap: wrap;">
        <!-- Précédent -->
        <?php if ($pageActuelle > 1): ?>
            <a href="?order=<?= $orderBy ?>&page=<?= $pageActuelle - 1 ?>" class="btn btn-outline btn-sm">
                ◀ Précédent
            </a>
        <?php else: ?>
            <span class="btn btn-outline btn-sm disabled" style="opacity:0.5;">◀ Précédent</span>
        <?php endif; ?>

        <!-- Numéros de pages -->
        <?php for ($i = 1; $i <= $nombreDePages; $i++): ?>
            <?php if ($i == $pageActuelle): ?>
                <span class="btn btn-primary btn-sm"><?= $i ?></span>
            <?php else: ?>
                <a href="?order=<?= $orderBy ?>&page=<?= $i ?>" class="btn btn-outline btn-sm"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <!-- Suivant -->
        <?php if ($pageActuelle < $nombreDePages): ?>
            <a href="?order=<?= $orderBy ?>&page=<?= $pageActuelle + 1 ?>" class="btn btn-outline btn-sm">
                Suivant ▶
            </a>
        <?php else: ?>
            <span class="btn btn-outline btn-sm disabled" style="opacity:0.5;">Suivant ▶</span>
        <?php endif; ?>
    </div>
<?php endif; ?>

</section>

<footer class="footer">
    <div class="container">
        <div class="row" style="gap: 48px;">
            <div style="flex: 0 0 260px;">
                <div class="footer-brand">
                    <div class="navbar-brand" style="margin-bottom: 16px;">
                        <div class="navbar-logo">⚕️</div>
                        <div class="navbar-name" style="font-size: 1.2rem;">ASC<span class="text-primary">LEPIA</span></div>
                    </div>
                    <p>Votre plateforme médicale complète.</p>
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
                        <li><a href="consultation.php">Consultations</a></li>
                        <li><a href="addpharmacie.php">Pharmacies</a></li>
                        <li><a href="assurance.php">Assurances</a></li>
                        <li><a href="forum.php">Forum santé</a></li>
                    </ul>
                </div>
            </div>
            <div class="col">
                <div class="footer-section">
                    <h4>Liens utiles</h4>
                    <ul class="footer-links">
                        <li><a href="index.html">Accueil</a></li>
                        <li><a href="login.html">S'inscrire</a></li>
                        <li><a href="login.html">Se connecter</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 ASCLEPIA. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<script src="list.js"></script>
<button class="theme-toggle" id="themeToggle">
    <i class="fas fa-moon"></i>
</button>

<script>
// Dark Mode - seul l'arrière-plan change
(function() {
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;
    
    const body = document.body;
    
    // Charger le thème sauvegardé
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    } else {
        body.classList.remove('dark-mode');
        themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    }
    
    // Basculement
    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        
        if (body.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            localStorage.setItem('theme', 'light');
            themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        }
    });
})();
</script>
</body>
</html>