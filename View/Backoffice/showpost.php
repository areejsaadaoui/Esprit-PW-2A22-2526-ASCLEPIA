<?php
include '../../Controller/PostController.php';
include '../../Controller/ReponseController.php'; 
require_once __DIR__ . '/../../Model/Post.php';
require_once __DIR__ . '/../../Model/Reponse.php';


$postC    = new PostController();
$reponseC = new ReponseController(); 

$post = null;
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_post  = (int)$_GET['id'];
    $post     = $postC->getPostById($id_post);
    $reponses = $reponseC->getReponsesByPost($id_post); 
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
    <style>
        .detail-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: var(--radius-lg);
            margin: 20px 0;
            box-shadow: var(--shadow);
        }
        .post-content-full {
            font-size: 1.05rem;
            line-height: 1.8;
            color: var(--text);
            margin: 24px 0;
        }
        .comment-section {
            margin-top: 48px;
            padding-top: 32px;
            border-top: 1px solid var(--border);
        }
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
        }
    </style>
</head>
<body>

<nav class="navbar" id="navbar">
    <a href="../frontoffice/index.html" class="navbar-brand">
        <div class="navbar-logo"></div>
        <div class="navbar-name">ASC<span>LEPIA</span></div>
    </a>
    <div class="nav-links" id="navLinks">
        <a href="../frontoffice/index.html#accueil" class="nav-link">Accueil</a>
        <a href="../frontoffice/index.html#services" class="nav-link">Services</a>
        <a href="../frontoffice/index.html#pharmacies" class="nav-link">Pharmacies</a>
        <a href="../frontoffice/index.html#assurances" class="nav-link">Assurances</a>
        <a href="../Frontoffice/postlist.php" class="nav-link active">Communauté</a>
        <a href="../frontoffice/index.html#avis" class="nav-link">Avis</a>
    </div>
    <div class="nav-actions">
        <a href="../frontoffice/login.html" class="btn btn-outline-white btn-sm">Se connecter</a>
        <a href="../frontoffice/login.html" class="btn btn-primary btn-sm">S'inscrire</a>
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
                    if (!empty($imagePath) && file_exists(__DIR__ . '/' . $imagePath)):
                    ?>
                        <div style="text-align: center;">
                            <img src="<?php echo $imagePath; ?>" 
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
                        <button class="btn btn-outline" onclick="alert('Fonctionnalité à venir')">
                            <i class="fa-regular fa-heart"></i> J'aime
                        </button>
                        <button class="btn btn-outline" onclick="alert('Fonctionnalité à venir')">
                            <i class="fa-regular fa-comment"></i> Commenter
                        </button>
                        <button class="btn btn-outline" onclick="alert('Fonctionnalité à venir')">
                            <i class="fa-regular fa-share-from-square"></i> Partager
                        </button>
                        <a href="../Backoffice/deletepost.php?id=<?php echo $post->getIdPost(); ?>" 
                                   class="btn btn-danger btn-sm" >
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </a>
                    </div>
                  
                    <!-- Formulaire pour ajouter une réponse -->
<form method="POST" action="../Backoffice/addreponse.php">
    <input type="hidden" name="id_post" value="<?php echo $post->getIdPost(); ?>">
    <textarea class="form-control" name="texte_rep" rows="3" 
              placeholder="Écrire une réponse..." 
              style="margin-bottom: 12px;" required></textarea>
    <div style="text-align: right;">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa-regular fa-paper-plane"></i> Publier
        </button>
    </div>
</form>

<!-- Afficher les réponses existantes (résultat de la jointure) -->
<?php if (!empty($reponses)): ?>
    <?php foreach ($reponses as $rep): ?>
        <div class="card" style="padding: 16px; margin-top: 16px; background: var(--bg);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <strong>Utilisateur #<?php echo $rep->getIdUtilisateur(); ?></strong>
                <span style="color: var(--text-muted); font-size: 0.8rem;">
                    <?php 
                        $d = new DateTime($rep->getDateRep());
                        echo $d->format('d/m/Y à H:i');
                    ?>
                </span>
            </div>
            <p style="margin-top: 8px;"><?php echo nl2br(htmlspecialchars($rep->getTexteRep())); ?></p>
        </div>
          <!-- Bouton supprimer -->
            <a href="deleteReponse.php?id=<?= $rep->getIdRep() ?>" 
               class="btn btn-danger btn-sm" 
               onclick="return confirm('Supprimer cette réponse ?')">
                <i class="fas fa-trash"></i>
            </a>
    <?php endforeach; ?>

<?php else: ?>
    <p style="text-align: center; color: var(--text-muted); padding: 40px;">
        Soyez le premier à répondre !
    </p>
<?php endif; ?>
                    
                    <!-- Bouton retour -->
                    <div style="margin-top: 32px; text-align: center;">
                        <a href="../Frontoffice/postlist.php" class="btn btn-outline">
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
                        <li><a href="../frontoffice/index.html">Accueil</a></li>
                        <li><a href="../frontoffice/login.html">S'inscrire</a></li>
                        <li><a href="../frontoffice/login.html">Se connecter</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 ASCLEPIA. Tous droits réservés.</p>
        </div>
    </div>
</footer>

</body>
</html>