<?php
include '../../Controller/PostController.php';
$postC = new PostController();
$posts = $postC->listPosts();
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

        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
                <div class="grid-item">
                    <div class="card post-card">
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
                        
                        <div class="post-actions">

    <a href="../Backoffice/modifpost.php?id=<?php echo $post->getIdPost(); ?>" 
       class="btn btn-primary btn-sm">
        <i class="fa-solid fa-pen"></i> Modifier
    </a>
</div>
                    </div>
                </div>
            <?php endforeach; ?>
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

<script src="list.js"></script>

</body>
</html>