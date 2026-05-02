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

function embedYouTube($content) {
    $patterns = [
        '/(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
        '/(?:https?:\/\/)?(?:www\.)?youtu\.be\/([a-zA-Z0-9_-]+)/',
        '/https?:\/\/www\.youtube\.com\/embed\/([a-zA-Z0-9_-]+)/'
    ];
    
    $result = $content;
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content, $matches)) {
            $videoId = $matches[1];
            $iframe = '<div class="youtube-embed" style="margin: 20px 0;">'
                    . '<iframe width="100%" height="315" src="https://www.youtube.com/embed/' . $videoId . '" '
                    . 'frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" '
                    . 'allowfullscreen></iframe>'
                    . '</div>';
            // Remplace le lien par le texte + l'iframe
            $result = str_replace($matches[0], $iframe, $content);
            break;
        }
    }
    
    return $result;
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
/* ===== DARK MODE – VERSION SHOWPOST ===== */

body.dark-mode {
    background: #1a1a2e !important;
}

/* Section principale */
body.dark-mode .section-padding {
    background: #1a1a2e !important;
}

/* Carte principale du post */
body.dark-mode .card {
    background: #16213e !important;
    border-color: #2d2d44 !important;
}

/* Textes principaux */
body.dark-mode .post-author,
body.dark-mode .post-date,
body.dark-mode .post-content-full {
    color: #e0e0e0 !important;
}

body.dark-mode .post-date i,
body.dark-mode .post-date {
    color: #a0a0c0 !important;
}

/* Avatar */
body.dark-mode .post-avatar {
    background: linear-gradient(135deg, #0ea5e9, #3b82f6) !important;
}

/* Image */
body.dark-mode .detail-image {
    filter: brightness(0.9);
}

/* Boutons d'action */
body.dark-mode .action-buttons .btn-outline {
    border-color: #475569 !important;
    color: #cbd5e1 !important;
}

body.dark-mode .action-buttons .btn-outline:hover {
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

/* Formulaire de réponse */
body.dark-mode .form-control {
    background: #0f0f1a !important;
    border-color: #2d2d44 !important;
    color: white !important;
}

body.dark-mode .form-control:focus {
    border-color: #0ea5e9 !important;
    box-shadow: 0 0 0 3px rgba(14,165,233,0.2) !important;
}

body.dark-mode .form-control::placeholder {
    color: #a0a0c0 !important;
}

/* Cartes de réponses */
body.dark-mode .card[style*="background: var(--bg)"] {
    background: #16213e !important;
    border-color: #2d2d44 !important;
}

body.dark-mode .card[style*="background: var(--bg)"] strong {
    color: white !important;
}

body.dark-mode .card[style*="background: var(--bg)"] p {
    color: #e0e0e0 !important;
}

body.dark-mode .card[style*="background: var(--bg)"] small {
    color: #a0a0c0 !important;
}

body.dark-mode .card[style*="background: var(--bg)"]:hover {
    background: #1a2a4a !important;
}

/* Boutons dans les réponses */
body.dark-mode .btn-outline.btn-sm {
    border-color: #475569 !important;
    color: #cbd5e1 !important;
}

body.dark-mode .btn-outline.btn-sm:hover {
    background: #334155 !important;
    color: white !important;
}

/* Bouton retour */
body.dark-mode .btn-outline {
    border-color: #475569 !important;
    color: #cbd5e1 !important;
}

body.dark-mode .btn-outline:hover {
    background: #334155 !important;
    color: white !important;
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

/* Message "Soyez le premier" */
body.dark-mode p[style*="text-align: center"] {
    background: #16213e !important;
    color: #a0a0c0 !important;
    border: 1px solid #2d2d44 !important;
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


.suggestion-bubble {
    transition: all 0.2s ease;
    font-weight: 500;
}

.suggestion-bubble:active {
    transform: scale(0.96);
}

body.dark-mode .suggestion-bubble {
    background: #334155 !important;
    color: #e2e8f0 !important;
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
                    
                    <!-- Image ou GIF -->
<!-- Image ou GIF -->
<?php 
$mediaPath = $post->getImage();
if (!empty($mediaPath)):
    $isGif = (strpos($mediaPath, '.gif') !== false || strpos($mediaPath, 'giphy.com') !== false);
    
    if (!$isGif && !filter_var($mediaPath, FILTER_VALIDATE_URL)) {
        $mediaPath = '../Backoffice/' . $mediaPath;
    }
?>
    <div style="text-align: center;">
        <img src="<?= $mediaPath ?>" 
             alt="Post media" 
             class="detail-image"
             style="max-width: 100%; border-radius: 20px; <?= $isGif ? 'max-height: 400px; object-fit: contain;' : '' ?>">
    </div>
<?php endif; ?>
                    
                    <!-- Contenu complet -->
                    <div class="post-content-full">
<?php 
$contenu = nl2br(htmlspecialchars($post->getContenu()));
echo embedYouTube($contenu);
?>                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="action-buttons">
                        <button class="btn btn-outline" onclick="copierLienPost(<?= $post->getIdPost() ?>)">
                            <i class="fa-regular fa-share-from-square"></i> Partager
                        </button>
                        <!-- Badge sentiment -->
                        <?= $post->getSentimentBadge() ?>
                        <!-- Bouton signalement AJAX -->
                        <button class="btn btn-outline btn-sm" id="btnSignal"
                                style="color:#f59e0b;border-color:#f59e0b;"
                                onclick="toggleSignal(<?= $post->getIdPost() ?>)">
                            <i class="fas fa-flag"></i> 
                            <span id="signalCount">0</span> Signaler
                        </button>
                        <a href="../Backoffice/deletepost.php?id=<?php echo $post->getIdPost(); ?>" 
                                   class="btn btn-danger btn-sm" >
                                    <i class="fa-solid fa-trash"></i> Supprimer
                                </a>
                                 <a href="../Backoffice/modifpost.php?id=<?php echo $post->getIdPost(); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-pen"></i> Modifier
    </a>
 <button class="btn btn-outline btn-sm" 
        data-post-id="<?= $post->getIdPost() ?>"
        data-content="<?= htmlspecialchars(json_encode($post->getContenu()), ENT_QUOTES, 'UTF-8') ?>"
        onclick="generateSummary(this)"
        id="summary-btn-<?= $post->getIdPost() ?>">
    <i class="fas fa-brain"></i> 🤖 Résumé IA
</button>
                    </div>
                    
                   

<!-- Conteneur pour le résumé -->
<div id="summary-<?= $post->getIdPost() ?>" style="display: none; margin-top: 10px;"></div>
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
                
                <!-- RÉACTIONS EMOJI -->
                <div class="reactions-bar" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px;" 
                     data-rep-id="<?= $rep->getIdRep() ?>">
                    <?php
                    $emojis = ['❤️','😂','🔥','👍','😮','😢','👏'];
                    foreach ($emojis as $emoji):
                    ?>
                    <button class="reaction-btn"
                            onclick="toggleReaction(this, <?= $rep->getIdRep() ?>, '<?= $emoji ?>')"
                            style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:20px;
                                   padding:3px 10px;font-size:0.85rem;cursor:pointer;transition:0.2s;
                                   display:inline-flex;align-items:center;gap:4px;"
                            onmouseover="this.style.transform='scale(1.1)'"
                            onmouseout="this.style.transform='scale(1)'">
                        <?= $emoji ?> <span class="reaction-count">0</span>
                    </button>
                    <?php endforeach; ?>
                </div>
                
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

<script>
// ============ SIGNALEMENT DE POST ============
let postSignale = JSON.parse(localStorage.getItem('signaled_posts') || '[]');
const postId = <?= $post->getIdPost() ?>;
const btnSignal = document.getElementById('btnSignal');

function updateSignalBtn(signale) {
    if (btnSignal) {
        btnSignal.style.color    = signale ? '#ef4444' : '#f59e0b';
        btnSignal.style.borderColor = signale ? '#ef4444' : '#f59e0b';
        btnSignal.querySelector('i').style.animation = signale ? 'likePop 0.3s ease' : '';
    }
}

updateSignalBtn(postSignale.includes(postId));

function toggleSignal(id_post) {
    const estSignale = postSignale.includes(id_post);
    const action = estSignale ? 'unsignal' : 'signal';

    fetch('../Frontoffice/signal_post.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id_post=' + id_post + '&action=' + action
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (action === 'signal') {
                postSignale.push(id_post);
            } else {
                postSignale = postSignale.filter(id => id !== id_post);
            }
            localStorage.setItem('signaled_posts', JSON.stringify(postSignale));
            document.getElementById('signalCount').textContent = data.signalements;
            updateSignalBtn(action === 'signal');
            showToast(action === 'signal' ? '⚠️ Post signalé' : '✓ Signalement retiré', action === 'signal' ? '#f59e0b' : '#10b981');
        }
    })
    .catch(err => console.error('Erreur signalement:', err));
}

// ============ RÉACTIONS EMOJI ============
function toggleReaction(btn, id_rep, emoji) {
    const countSpan = btn.querySelector('.reaction-count');
    const current = parseInt(countSpan.textContent) || 0;
    const isActive = btn.classList.contains('active-reaction');
    const action = isActive ? 'remove' : 'add';

    // Optimistic UI
    countSpan.textContent = isActive ? Math.max(0, current - 1) : current + 1;
    btn.classList.toggle('active-reaction');
    btn.style.background = isActive ? '#f8fafc' : '#dbeafe';
    btn.style.borderColor = isActive ? '#e2e8f0' : '#3b82f6';

    fetch('../Frontoffice/reaction_reponse.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_rep=${id_rep}&emoji=${encodeURIComponent(emoji)}&action=${action}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.reactions) {
            // Mettre à jour tous les compteurs de cette réponse
            const bar = btn.closest('.reactions-bar');
            bar.querySelectorAll('.reaction-btn').forEach(b => {
                const ej = b.textContent.trim().split(' ')[0];
                const cnt = b.querySelector('.reaction-count');
                if (cnt) cnt.textContent = data.reactions[ej] || 0;
            });
        }
    })
    .catch(err => console.error('Erreur réaction:', err));
}

// ============ COPIER LIEN ============
function copierLienPost(postId) {
    const lien = window.location.origin + window.location.pathname + '?id=' + postId;
    navigator.clipboard.writeText(lien).then(() => {
        showToast('✓ Lien copié !', '#10b981');
    }).catch(() => alert('Lien : ' + lien));
}

// ============ TOAST NOTIFICATION ============
function showToast(msg, color = '#0f172a') {
    const t = document.createElement('div');
    t.innerHTML = msg;
    Object.assign(t.style, {
        position: 'fixed', bottom: '25px', left: '50%',
        transform: 'translateX(-50%)',
        background: color, color: 'white',
        padding: '10px 24px', borderRadius: '30px',
        zIndex: '99999', fontSize: '0.9rem', fontWeight: '600',
        boxShadow: '0 4px 20px rgba(0,0,0,0.15)',
        animation: 'fadeInScale 0.3s ease'
    });
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2500);
}
</script>




<button class="theme-toggle" id="themeToggle">
    <i class="fas fa-moon"></i>
</button>
<script>
// Dark Mode - showpost.php
(function() {
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;
    
    const body = document.body;
    
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    } else {
        body.classList.remove('dark-mode');
        themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    }
    
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

<script>
    console.log("🔍 Test : textarea trouvé ?", document.querySelector('textarea[name="texte_rep"]'));
</script>

<script>
// ========== RÉSUMÉ IA SIMPLIFIÉ ==========
// ========== RÉSUMÉ IA SIMPLIFIÉ ==========
// ========== RÉSUMÉ IA ==========
async function generateSummary(button) {
    const postId = button.getAttribute('data-post-id');
    let content = button.getAttribute('data-content');
    
    // Décoder le JSON
    try {
        content = JSON.parse(content);
    } catch(e) {
        console.error("Erreur de parsing:", e);
    }
    
    console.log("Génération du résumé pour le post " + postId);
    
    const container = document.getElementById(`summary-${postId}`);
    const btn = document.getElementById(`summary-btn-${postId}`);
    
    if (!container) {
        console.error("Container non trouvé");
        return;
    }
    
    container.style.display = 'block';
    container.innerHTML = '<div style="padding: 12px; background: #f0fdf4; border-radius: 12px;">⏳ Génération du résumé...</div>';
    
    if (btn) btn.style.display = 'none';
    
    try {
        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('contenu', content);
        
        const response = await fetch('summarize_post.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log("Réponse:", data);
        
        if (data.success) {
            container.innerHTML = `
                <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 12px; border-radius: 8px; margin-top: 10px;">
                    <strong>🤖 Résumé IA :</strong>
                    <p style="margin: 8px 0 0 0; font-size: 0.9rem;">${escapeHtml(data.summary)}</p>
                    <button onclick="hideSummary(${postId})" 
                            style="margin-top: 8px; background: none; border: none; color: #64748b; cursor: pointer;">
                        ✖ Fermer
                    </button>
                </div>
            `;
        } else {
            container.innerHTML = `<div style="background: #fee2e2; padding: 12px; border-radius: 8px;">❌ ${escapeHtml(data.error || 'Erreur')}</div>`;
        }
    } catch(error) {
        console.error("Erreur:", error);
        container.innerHTML = `<div style="background: #fee2e2; padding: 12px; border-radius: 8px;">❌ Erreur de connexion: ${escapeHtml(error.message)}</div>`;
    }
    
    if (btn) btn.style.display = 'flex';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function hideSummary(postId) {
    const container = document.getElementById(`summary-${postId}`);
    const btn = document.getElementById(`summary-btn-${postId}`);
    if (container) container.style.display = 'none';
    if (btn) btn.style.display = 'flex';
}
</script>
</body>
</html>