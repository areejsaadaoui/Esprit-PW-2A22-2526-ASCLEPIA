<?php
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';
session_start();

$postC = new PostController();
$post = null;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_post = (int)$_GET['id'];
    $post = $postC->getPostById($id_post);
}

// Si le post n'existe pas
if (!$post) {
    header('Location: postList.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASCLEPIA — Détail du post</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/frontoffice.css">
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
        <a href="../Frontoffice/postList.php" class="nav-link active">Communauté</a>
        <a href="../Frontoffice/index.html#avis" class="nav-link">Avis</a>
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
        <div class="row" style="justify-content: center;">
            <div class="col-8">
                <div class="card" style="padding: 40px;">
                    
                    <!-- En-tête du post -->
                    <div class="post-meta" style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
                        <div class="post-avatar" style="background: var(--gradient-primary); width: 50px; height: 50px; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">
                            <?php echo strtoupper(substr($post->getIdUtilisateur() ?? 'U', 0, 2)); ?>
                        </div>
                        <div>
                            <div class="post-author" style="font-size: 1.1rem; font-weight: 700;">Utilisateur #<?php echo $post->getIdUtilisateur(); ?></div>
                            <div class="post-date" style="color: var(--text-muted);">
                                <i class="fa-regular fa-calendar"></i>
                                <?php 
                                    $date = new DateTime($post->getDatePost());
                                    echo $date->format('d/m/Y à H:i');
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image si elle existe -->
                    <?php 
                    $imagePath = $post->getImage();
                    if (!empty($imagePath) && file_exists(__DIR__ . '/../Backoffice/' . $imagePath)):
                    ?>
                        <div style="text-align: center;">
                            <img src="../Backoffice/<?php echo $imagePath; ?>" 
                                 alt="Image du post" 
                                 class="detail-image">
                        </div>
                    <?php endif; ?>
                    
                    <!-- Contenu complet -->
                    <div class="post-content-full">
                        <?php echo nl2br(htmlspecialchars($post->getContenu())); ?>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="action-buttons">
                        <button class="btn btn-outline" >
                            <i class="fa-regular fa-heart"></i> J'aime
                        </button>
                        <button class="btn btn-outline" >
                            <i class="fa-regular fa-comment"></i> Commenter
                        </button>
                        <button class="btn btn-outline" >
                            <i class="fa-regular fa-share-from-square"></i> Partager
                        </button>
                        
                        <!-- Bouton Supprimer (pour admin ou propriétaire) -->
                        <?php if (isset($_SESSION['user_id']) && ($post->getIdUtilisateur() == $_SESSION['user_id'] || ($_SESSION['role'] ?? '') === 'admin')): ?>
                            <a href="../Backoffice/deletepost.php?id=<?php echo $post->getIdPost(); ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?')">
                                <i class="fa-solid fa-trash"></i> Supprimer
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Section commentaires -->
                    <div class="comment-section">
                        <h3 style="margin-bottom: 24px;">
                            <i class="fa-regular fa-comments"></i> Commentaires
                        </h3>
                        
                        <div class="card" style="padding: 20px; margin-bottom: 24px; background: var(--bg);">
                            <textarea class="form-control"  style="margin-bottom: 12px;"></textarea>
                            <div style="text-align: right;">
                                <button class="btn btn-primary btn-sm" >
                                    <i class="fa-regular fa-paper-plane"></i> Publier
                                </button>
                            </div>
                        </div>
                        
                        <p style="text-align: center; color: var(--text-muted); padding: 20px;">
                            <i class="fa-regular fa-comment-dots" style="font-size: 2rem; margin-bottom: 12px; display: block;"></i>
                            Laissez un commentaire !
                        </p>
                    </div>
                    
                    <!-- Bouton retour -->
                    <div style="margin-top: 32px; text-align: center;">
                        <a href="../Frontoffice/postList.php" class="btn btn-outline">
                            <i class="fa-solid fa-arrow-left"></i> Retour au forum
                        </a>
                    </div>
                </div>
            </div>
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

<script src="../Frontoffice/addpost.js"></script>

</body>
</html>