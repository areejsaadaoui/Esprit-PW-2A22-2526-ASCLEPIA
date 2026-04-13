<?php
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';
session_start();

$postC = new PostController();
$posts = $postC->listPosts();

// Trier par date décroissante
usort($posts, function($a, $b) {
    return strtotime($b->getDatePost()) - strtotime($a->getDatePost());
});

// Message de succès ou d'erreur
$message = '';
if (isset($_GET['success'])) {
    $message = '<div class="alert alert-success" style="display: flex; margin-bottom: 20px;">
                    <i class="fa-solid fa-circle-check" style="margin-right: 10px;"></i>
                    Post supprimé avec succès !
                </div>';
}
if (isset($_GET['error'])) {
    $message = '<div class="alert alert-danger" style="display: flex; margin-bottom: 20px;">
                    <i class="fa-solid fa-circle-exclamation" style="margin-right: 10px;"></i>
                    Erreur lors de la suppression.
                </div>';
}
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
    <link rel="stylesheet" href="assets/css/animations.css"/>
<link rel="stylesheet" href="assets/css/frontoffice-animations.css"/>
    <style>
        /* ===== STYLES RESPONSIVES ===== */
        
        /* Structure responsive */
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
        
        .post-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        
        /* Grille responsive - 3 colonnes par défaut */
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin: 0 -12px;
            padding: 0 12px;
        }
        
        .grid-item {
            width: 100%;
        }
        
        /* ===== TABLETTES (768px - 1024px) ===== */
        @media (max-width: 1024px) and (min-width: 769px) {
            .posts-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
            
            .container {
                padding: 0 20px;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
        
        /* ===== MOBILES (moins de 768px) ===== */
        @media (max-width: 768px) {
            .posts-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
            
            .container {
                padding: 0 16px;
            }
            
            .navbar {
                padding: 0 16px;
            }
            
            .navbar-name {
                font-size: 1.1rem;
            }
            
            .nav-links {
                position: fixed;
                top: 72px;
                left: -100%;
                width: 80%;
                height: calc(100vh - 72px);
                background: var(--dark);
                flex-direction: column;
                padding: 20px;
                transition: left 0.3s ease;
                z-index: 999;
            }
            
            .nav-links.open {
                left: 0;
            }
            
            .nav-link {
                width: 100%;
                padding: 12px 16px;
            }
            
            .nav-actions {
                gap: 8px;
            }
            
            .btn-sm {
                padding: 6px 12px;
                font-size: 0.75rem;
            }
            
            .section-padding {
                padding: 40px 0;
            }
            
            .section-title {
                font-size: 1.6rem;
            }
            
            .section-desc {
                font-size: 0.9rem;
            }
            
            .hero {
                padding: 80px 0 40px;
                text-align: center;
            }
            
            .hero-title {
                font-size: 1.8rem;
            }
            
            .hero-subtitle {
                font-size: 0.95rem;
            }
            
            .hero-actions {
                justify-content: center;
            }
            
            .hero-stats {
                justify-content: center;
                gap: 20px;
            }
            
            .hero-stat .number {
                font-size: 1.4rem;
            }
            
            .card {
                padding: 20px;
            }
            
            .post-card {
                padding: 16px;
            }
            
            .post-meta {
                flex-wrap: wrap;
            }
            
            .post-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.75rem;
            }
            
            .post-author {
                font-size: 0.85rem;
            }
            
            .post-date {
                font-size: 0.7rem;
            }
            
            .post-footer {
                flex-wrap: wrap;
                gap: 8px;
            }
            
            .post-stat {
                font-size: 0.75rem;
            }
            
            .btn-sm {
                padding: 6px 12px;
                font-size: 0.75rem;
            }
            
            .footer {
                padding: 40px 0 0;
            }
            
            .footer .row {
                flex-direction: column;
                gap: 32px;
            }
            
            .footer-bottom {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }
            
            /* Bouton nouvelle discussion */
            div[style*="text-align: right"] {
                text-align: center !important;
                margin-bottom: 20px !important;
            }
            
            /* Actions sur les posts */
            .post-actions {
                justify-content: center;
            }
            
            .post-actions .btn-sm {
                padding: 6px 10px;
                font-size: 0.7rem;
            }
        }
        
        /* ===== TRÈS PETITS MOBILES (moins de 480px) ===== */
        @media (max-width: 480px) {
            .posts-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .container {
                padding: 0 12px;
            }
            
            .section-title {
                font-size: 1.4rem;
            }
            
            .card {
                padding: 16px;
            }
            
            .post-image {
                height: 140px;
            }
            
            .post-content p {
                font-size: 0.8rem;
            }
            
            .btn {
                padding: 8px 16px;
                font-size: 0.8rem;
            }
            
            .btn-lg {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .hero-stats {
                gap: 12px;
            }
            
            .hero-stat .number {
                font-size: 1.2rem;
            }
            
            .hero-stat .label {
                font-size: 0.7rem;
            }
        }
        
        /* ===== AJUSTEMENTS SUPPLÉMENTAIRES ===== */
        
        /* Navigation mobile - hamburger visible */
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
        
        /* Scroll fluide */
        html {
            scroll-behavior: smooth;
        }
        
        /* Amélioration du touch sur mobile */
        @media (max-width: 768px) {
            .btn, 
            .nav-link,
            .post-actions a,
            .post-footer a {
                cursor: pointer;
                -webkit-tap-highlight-color: transparent;
            }
            
            .btn:active,
            .nav-link:active {
                transform: scale(0.98);
            }
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
        <a href="postList.php" class="nav-link active">Communauté</a>
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

        <?php echo $message; ?>

        <div class="posts-grid">
            <?php if (empty($posts)): ?>
                <div class="grid-item" style="grid-column: 1 / -1;">
                    <div class="card" style="text-align: center; padding: 60px;">
                        <i class="fa-solid fa-comments" style="font-size: 3rem; color: var(--gray-light); margin-bottom: 16px;"></i>
                        <h3 style="margin-bottom: 8px;">Aucun post pour le moment</h3>
                        <p style="color: var(--text-muted); margin-bottom: 24px;">Soyez le premier à partager votre expérience !</p>
                        <a href="addpost.php" class="btn btn-primary">Créer le premier post</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="grid-item">
                        <div class="card post-card">
                            <!-- Afficher l'image si elle existe -->
                            <?php 
                            $imagePath = $post->getImage();
                            if (!empty($imagePath) && file_exists(__DIR__ . '/../Backoffice/' . $imagePath)):
                            ?>
                                <img src="../Backoffice/<?php echo $imagePath; ?>" 
                                     alt="Image du post" 
                                     class="post-image"
                                     onclick="window.location.href='../Backoffice/showpost.php?id=<?php echo $post->getIdPost(); ?>'">
                            <?php endif; ?>
                            
                            <div class="post-meta">
                                <div class="post-avatar" style="background: var(--gradient-primary);">
                                    <?php echo strtoupper(substr($post->getIdUtilisateur() ?? 'U', 0, 2)); ?>
                                </div>
                                <div>
                                    <div class="post-author">Utilisateur #<?php echo $post->getIdUtilisateur(); ?></div>
                                    <div class="post-date">
                                        <i class="fa-regular fa-calendar"></i>
                                        <?php 
                                            $date = new DateTime($post->getDatePost());
                                            echo $date->format('d/m/Y à H:i');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="post-content">
                                <p style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 16px;">
                                    <?php echo nl2br(htmlspecialchars(substr($post->getContenu(), 0, 150))); ?>
                                    <?php if (strlen($post->getContenu()) > 150): ?>...<?php endif; ?>
                                </p>
                            </div>
                            
                            <div class="post-footer">
                                <div class="post-stat">
                                    <i class="fa-regular fa-heart"></i> 
                                    <?php echo rand(0, 50); ?> J'aime
                                </div>
                                <div class="post-stat">
                                    <i class="fa-regular fa-comment"></i> 
                                    <?php echo rand(0, 20); ?> Réponses
                                </div>
                                <a href="../Backoffice/showpost.php?id=<?php echo $post->getIdPost(); ?>" class="btn btn-outline btn-sm">
                                    Lire la suite
                                </a> 
                            </div>
                            
                            <!-- Boutons Modifier/Supprimer (visibles uniquement pour l'auteur ou l'admin) -->
                            <?php if (isset($_SESSION['user_id']) && ($post->getIdUtilisateur() == $_SESSION['user_id'] || ($_SESSION['role'] ?? '') === 'admin')): ?>
                            <div class="post-actions">
                                <a href="../Backoffice/updatepost.php?id=<?php echo $post->getIdPost(); ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-pen"></i> Modifier
                                </a>
                                <a href="../Backoffice/deletepost.php?id=<?php echo $post->getIdPost(); ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?')">
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
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

<script src="addpost.js"></script>
<script src="assets/js/animations.js" defer></script>

</body>
</html>