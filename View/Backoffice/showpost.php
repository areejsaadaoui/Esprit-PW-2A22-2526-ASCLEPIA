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
        /* ===== ANIMATIONS OBLIGATOIRES ===== */
@keyframes fadeInScale {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
@keyframes floatSoft {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-6px); }
    100% { transform: translateY(0px); }
}
@keyframes shineBorder {
    0% { border-left-color: #0ea5e9; }
    50% { border-left-color: #10b981; }
    100% { border-left-color: #0ea5e9; }
}

/* Carte principale du post */
.card {
    animation: fadeInScale 0.5s ease-out;
    transition: all 0.3s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 35px -12px rgba(0,0,0,0.2);
}

/* Image */
.detail-image {
    transition: transform 0.3s;
}
.detail-image:hover {
    transform: scale(1.02);
}

/* Avatar */
.post-avatar {
    animation: floatSoft 3s infinite;
    transition: 0.2s;
}
.post-avatar:hover {
    transform: scale(1.1);
}

/* Boutons d'action */
.action-buttons .btn {
    transition: all 0.2s;
}
.action-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 12px rgba(0,0,0,0.1);
}

/* Formulaire de réponse */
textarea.form-control {
    transition: 0.2s;
    border-radius: 28px;
}
textarea.form-control:focus {
    transform: scale(1.01);
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14,165,233,0.2);
}

/* Cartes de réponse (chaque réponse) */
.card[style*="background: var(--bg)"] {
    transition: all 0.25s;
    border-left: 4px solid #0ea5e9;
    animation: fadeInScale 0.3s backwards;
    animation-delay: calc(0.05s * var(--order, 1));
}
.card[style*="background: var(--bg)"]:hover {
    transform: translateX(6px) translateY(-2px);
    background: white !important;
    border-left-color: #10b981;
    animation: shineBorder 1s infinite;
}

/* Bouton Publier */
.btn-primary.btn-sm:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(14,165,233,0.4);
}

/* Bouton Retour */
.btn-outline {
    transition: 0.2s;
}
.btn-outline:hover {
    transform: translateX(-5px);
    background: #0ea5e9;
    color: white;
}

/* Message "Soyez le premier" */
p[style*="text-align: center"] {
    animation: floatSoft 2s infinite;
    background: #f1f5f9;
    border-radius: 60px;
    padding: 20px;
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
                        <button class="btn btn-outline">
                            <i class="fa-regular fa-heart"></i> J'aime
                        </button>
                        
                        <button class="btn btn-outline" >
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
              style="margin-bottom: 12px;" ></textarea>
    <div style="text-align: right;">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa-regular fa-paper-plane"></i> Publier
        </button>
    </div>
</form>

<!-- Afficher les réponses existantes -->
<?php if (!empty($reponses)): ?>
    <?php foreach ($reponses as $rep): ?>
        <div class="card" style="padding: 16px; margin-top: 16px; background: var(--bg); border-radius: 16px;" id="reponse-<?= $rep->getIdRep() ?>">
            
            <!-- Si on est en mode édition (paramètre edit) -->
            <?php if (isset($_GET['edit_reponse']) && $_GET['edit_reponse'] == $rep->getIdRep()): ?>
                
                <!-- ✅ FORMULAIRE DE MODIFICATION (updateReponse.php) -->
                <form method="POST" action="modifreponse.php">
                    <div style="margin-bottom: 10px;">
                        <strong><?= htmlspecialchars($rep->getAuteur()) ?></strong>
                        <small><?= date('d/m/Y H:i', strtotime($rep->getDateRep())) ?></small>
                    </div>
                    <textarea name="texte_rep" class="form-control" rows="4" style="margin: 10px 0;"><?= htmlspecialchars($rep->getTexteRep()) ?></textarea>
                    <input type="hidden" name="id_rep" value="<?= $rep->getIdRep() ?>">
                    <input type="hidden" name="id_post" value="<?= $post->getIdPost() ?>">
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <a href="showpost.php?id=<?= $post->getIdPost() ?>" class="btn btn-outline btn-sm">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </form>
                
            <?php else: ?>
                
                <!-- Mode affichage normal -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <strong><?= htmlspecialchars($rep->getAuteur()) ?></strong>
                    <small><?= date('d/m/Y H:i', strtotime($rep->getDateRep())) ?></small>
                </div>
                <p style="margin: 10px 0;"><?= nl2br(htmlspecialchars($rep->getTexteRep())) ?></p>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <a href="showpost.php?id=<?= $post->getIdPost() ?>&edit_reponse=<?= $rep->getIdRep() ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-pen"></i> Modifier
                    </a>
                    <a href="deleteReponse.php?id=<?= $rep->getIdRep() ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette réponse ?')">
                        <i class="fas fa-trash"></i> Supprimer
                    </a>
                </div>
                
            <?php endif; ?>
            
        </div>
    <?php endforeach; ?>
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

<script src="../Frontoffice/rep.js"></script>
</body>
</html>