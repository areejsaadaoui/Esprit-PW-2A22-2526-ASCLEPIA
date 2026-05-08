<?php
session_start(); 

include '../../Controller/PostController.php';
include '../../Controller/ReponseController.php'; 
require_once __DIR__ . '/../../Model/Post.php';
require_once __DIR__ . '/../../Model/Reponse.php';

// ===== RÉCUPÉRATION DE LA SESSION =====
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['user_role'] ?? '';
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Fonction pour afficher le texte et le GIF extrait
function afficherReponseAvecGif($texte) {
    // Chercher le tag GIF dans le texte
    preg_match('/\[GIF:(.*?)\]/', $texte, $matches);
    
    if (!empty($matches)) {
        $gifUrl = $matches[1];
        // Enlever le tag GIF du texte
        $cleanTexte = preg_replace('/\n?\n?\[GIF:.*?\]\n?\n?/', '', $texte);
        $cleanTexte = trim($cleanTexte);
        
        // Construction HTML
        $html = '';
        if (!empty($cleanTexte)) {
            $html .= '<p style="margin: 0 0 10px 0;">' . nl2br(htmlspecialchars($cleanTexte)) . '</p>';
        }
        $html .= '<div style="margin-top: 10px;">
                    <img src="' . htmlspecialchars($gifUrl) . '" 
                         alt="GIF" 
                         style="max-width: 200px; max-height: 150px; border-radius: 12px; object-fit: contain; border: 1px solid #e2e8f0; padding: 4px;">
                  </div>';
        return $html;
    }
    
    // Pas de GIF, afficher juste le texte
    return '<p style="margin: 0;">' . nl2br(htmlspecialchars($texte)) . '</p>';
}

$postC    = new PostController();
$reponseC = new ReponseController(); 

$post = null;
$reponses = [];
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_post  = (int)$_GET['id'];
    $post     = $postC->getPostById($id_post);
    $reponses = $reponseC->getReponsesByPost($id_post);
}

if (!$post) {
    header('Location: ../Frontoffice/postlist.php');
    exit;
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
    <link rel="stylesheet" href="../assets/css/avatar.css">
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
/* Réduire la largeur des réponses */
/* Centrer les réponses avec largeur réduite */
.card[id^="reponse-"] {
    margin-left: auto !important;
    margin-right: auto !important;
    max-width: 80% !important;
    width: 100% !important;
}

.card[id^="reponse-"] p {
    font-size: 1rem;
    margin-bottom: 6px;
    line-height: 1.6;
}
.card[id^="reponse-"] strong {
    font-size: 1rem;
}

.card[id^="reponse-"] small {
    font-size: 0.85rem;
}
.card[id^="reponse-"] .btn-sm {
    padding: 2px 6px !important;
    font-size: 0.65rem !important;
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
    border-color: #0ee999;
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

body.dark-mode .section-padding {
    background: #1a1a2e !important;
}

body.dark-mode .card {
    background: #16213e !important;
    border-color: #2d2d44 !important;
}

body.dark-mode .post-author,
body.dark-mode .post-date,
body.dark-mode .post-content-full {
    color: #e0e0e0 !important;
}

body.dark-mode .post-date i,
body.dark-mode .post-date {
    color: #a0a0c0 !important;
}

body.dark-mode .post-avatar {
    background: linear-gradient(135deg, #0ea5e9, #3b82f6) !important;
}

body.dark-mode .detail-image {
    filter: brightness(0.9);
}

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

body.dark-mode .form-control {
    background: #0f0f1a !important;
    border-color: #2d2d44 !important;
    color: white !important;
}

body.dark-mode .form-control:focus {
    border-color: #0ee9a7 !important;
    box-shadow: 0 0 0 3px rgba(14,165,233,0.2) !important;
}

body.dark-mode .form-control::placeholder {
    color: #a0a0c0 !important;
}

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

body.dark-mode .btn-outline.btn-sm {
    border-color: #475569 !important;
    color: #cbd5e1 !important;
}

body.dark-mode .btn-outline.btn-sm:hover {
    background: #334155 !important;
    color: white !important;
}

body.dark-mode .btn-outline {
    border-color: #475569 !important;
    color: #cbd5e1 !important;
}

body.dark-mode .btn-outline:hover {
    background: #334155 !important;
    color: white !important;
}

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

body.dark-mode .footer {
    background: #0f0f1a !important;
    border-top: 1px solid #2d2d44 !important;
}

body.dark-mode .footer p,
body.dark-mode .footer .footer-section h4,
body.dark-mode .footer .footer-links a {
    color: #c0c0d0 !important;
}

body.dark-mode p[style*="text-align: center"] {
    background: #16213e !important;
    color: #a0a0c0 !important;
    border: 1px solid #2d2d44 !important;
}

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

/* ===== EMOJI PICKER FACEBOOK-STYLE ===== */
.reactions-area {
    position: relative;
    display: inline-block;
}

.react-main-btn {
    background: none;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 0.85rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #64748b;
    transition: all 0.2s;
    font-family: inherit;
}
.react-main-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0ea5e9;
}

.emoji-picker-popup {
    display: none;
    position: absolute;
    bottom: calc(100% + 8px);
    left: 0;
    background: white;
    border-radius: 40px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.18);
    padding: 8px 14px;
    gap: 6px;
    z-index: 999;
    flex-direction: row;
    white-space: nowrap;
    border: 1px solid #e2e8f0;
}
.emoji-picker-popup.open {
    display: flex;
    animation: pickerPop 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes pickerPop {
    from { opacity: 0; transform: scale(0.6) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}

.emoji-choice-btn {
    font-size: 1.5rem;
    border: none;
    background: none;
    cursor: pointer;
    border-radius: 50%;
    padding: 4px;
    transition: transform 0.15s;
    line-height: 1;
}
.emoji-choice-btn:hover {
    transform: scale(1.4) translateY(-4px);
}

.reactions-strip {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
    margin-top: 6px;
}
.react-pill {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 2px 9px;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all 0.15s;
}
.react-pill.mine {
    background: #dbeafe;
    border-color: #3b82f6;
}
.react-pill:hover {
    transform: scale(1.08);
}

body.dark-mode .react-main-btn {
    border-color: #475569;
    color: #94a3b8;
}
body.dark-mode .react-main-btn:hover {
    background: #334155;
    color: #0ea5e9;
}
body.dark-mode .emoji-picker-popup {
    background: #16213e;
    border-color: #2d2d44;
    box-shadow: 0 8px 30px rgba(0,0,0,0.4);
}
body.dark-mode .react-pill {
    background: #1e293b;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .react-pill.mine {
    background: #1e3a5f;
    border-color: #3b82f6;
}
/* Effet verre (glassmorphism) */
.post-card {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(0px);
    transition: all 0.35s ease;
}

.post-card:hover {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(4px);
}

body.dark-mode .post-card {
    background: rgba(22, 33, 62, 0.9);
}

body.dark-mode .post-card:hover {
    background: rgba(22, 33, 62, 0.98);
}
/* ===== ANIMATIONS POUR SHOWPOST.PHP ===== */

/* Animation de la carte principale */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 40px -15px rgba(0,0,0,0.2);
}

/* Animation des boutons d'action */
.action-buttons .btn {
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.action-buttons .btn:hover {
    transform: translateY(-3px);
}

.action-buttons .btn-outline:hover {
    background: #0ea5e9;
    color: white;
    border-color: #0ea5e9;
}

/* Bouton Supprimer */
.btn-danger:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 14px rgba(239, 68, 68, 0.4);
}

/* Bouton Modifier */
.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 14px rgba(14, 165, 233, 0.4);
}

/* Bouton Résumé IA */
.summary-btn {
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.summary-btn:hover {
    transform: translateY(-3px);
    background: #7c3aed;
    box-shadow: 0 6px 14px rgba(124, 58, 237, 0.4);
}

/* Bouton Partager */
.btn-outline[onclick*="copierLienPost"]:hover {
    background: #3b82f6;
    border-color: #3b82f6;
    transform: translateY(-3px);
}
/* Bouton signalement */
#btnSignal {
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

#btnSignal:hover {
    transform: translateY(-2px);
}

/* Image du post */
.detail-image {
    transition: transform 0.4s ease;
}

.detail-image:hover {
    transform: scale(1.02);
}

/* Avatar */
.post-avatar {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.post-avatar:hover {
    transform: scale(1.1);
}

/* Formulaire de réponse */
.form-control {
    transition: all 0.25s ease;
}

.form-control:focus {
    transform: scale(1.01);
}

/* Bouton Publier réponse */
.btn-primary.btn-sm {
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.btn-primary.btn-sm:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(14, 165, 233, 0.4);
}

/* Cartes des réponses */
.card[style*="background: var(--bg)"] {
    transition: all 0.25s ease;
}

.card[style*="background: var(--bg)"]:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 25px -10px rgba(0,0,0,0.15);
}

/* Boutons Modifier/Supprimer dans les réponses */
.card .btn-sm {
    transition: all 0.2s ease;
}

.card .btn-sm:hover {
    transform: translateY(-2px);
}

/* Bouton Retour */
.btn-outline[href*="postlist"] {
    transition: all 0.25s ease;
}

.btn-outline[href*="postlist"]:hover {
    transform: translateX(-5px);
    background: #0ea5e9;
    color: white;
}

/* Animation de pulsation pour le bouton IA */
@keyframes gentlePulse {
    0% { box-shadow: 0 0 0 0 rgba(124, 58, 237, 0.4); }
    70% { box-shadow: 0 0 0 8px rgba(124, 58, 237, 0); }
    100% { box-shadow: 0 0 0 0 rgba(124, 58, 237, 0); }
}

.summary-btn {
    animation: gentlePulse 2s infinite;
}

.summary-btn:hover {
    animation: none;
}

/* Version Dark Mode */
body.dark-mode .card:hover {
    box-shadow: 0 25px 40px -15px rgba(0,0,0,0.5);
}

body.dark-mode .summary-btn {
    background: linear-gradient(135deg, #7c3aed, #4f46e5);
}

body.dark-mode .action-buttons .btn-outline:hover {
    background: #0ea5e9;
    color: white;
}


    </style>
</head>
<body>

<nav class="navbar" id="navbar">
    <a href="../frontoffice/indexp.php" class="navbar-brand">
        <div class="navbar-logo"></div>
        <div class="navbar-name">ASC<span>LEPIA</span></div>
    </a>
    <div class="nav-links" id="navLinks">
        <a href="../front/indexp.php#accueil" class="nav-link">Accueil</a>
        <a href="../front/indexp.php#services" class="nav-link">Services</a>
        <a href="../front/indexp.php#pharmacies" class="nav-link">Pharmacies</a>
        <a href="../front/indexp.php#assurances" class="nav-link">Assurances</a>
        <a href="../Frontoffice/postlist.php" class="nav-link active">Communauté</a>
        <a href="../front/indexp.php#avis" class="nav-link">Avis</a>
    </div>
    <div class="nav-actions">
        <?php if ($isLoggedIn): ?>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="avatar-css avatar-<?= htmlspecialchars($_SESSION['user_avatar'] ?? 'default') ?> small"
                     style="width: 36px; height: 36px; border-radius: 50%;"></div>
                <span style="color: white;">Bonjour, <?= htmlspecialchars($_SESSION['user_nom'] ?? '') ?></span>
                <a href="../back/logout.php" class="btn btn-outline-white btn-sm">
                    <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        <?php else: ?>
            <a href="../frontoffice/login.html" class="btn btn-outline-white btn-sm">Se connecter</a>
            <a href="../frontoffice/loginuser.html" class="btn btn-primary btn-sm">S'inscrire</a>
        <?php endif; ?>
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
                    <!-- En-tête du post -->
<div class="post-meta" style="margin-bottom: 24px; display: flex; align-items: center; gap: 16px;">
    
    <!-- Avatar avec la classe CSS -->
    <div class="avatar-css avatar-<?= htmlspecialchars($post->getUserAvatar()) ?>"
         style="width: 50px; height: 50px; border-radius: 50%; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center;">
    </div>
    
    <div>
        <div class="post-author" style="font-size: 1.1rem; font-weight: 700;">
            <?= htmlspecialchars($post->getUserFullName()) ?>
        </div>
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

                       

                        <!-- Bouton signalement AJAX -->
                        <?php
                        $signaledPosts = isset($_COOKIE['signaled_posts']) ? explode(',', $_COOKIE['signaled_posts']) : [];
                        $isSignaled    = in_array($post->getIdPost(), $signaledPosts);
                        ?>
                        <button class="btn btn-outline btn-sm" id="btnSignal"
                                style="color:<?= $isSignaled ? '#ef4444' : '#f59e0b' ?>;border-color:<?= $isSignaled ? '#ef4444' : '#f59e0b' ?>;"
                                onclick="toggleSignal(<?= $post->getIdPost() ?>)">
                            <i class="fas fa-flag"></i> 
                            <span id="signalCount"><?= $post->getSignalements() ?? 0 ?></span> Signaler
                        </button>

                         <?php 
    $isOwner = ($post->getIdUtilisateur() == $userId);
    $isAdmin = ($userRole === 'admin');
    if ($isOwner || $isAdmin): ?>
        <a href="../Backoffice/deletepost.php?id=<?php echo $post->getIdPost(); ?>" 
           class="btn btn-danger btn-sm" 
           onclick="return confirm('Supprimer ce post ?')">
            <i class="fa-solid fa-trash"></i> Supprimer
        </a>
        <a href="../Backoffice/modifpost.php?id=<?php echo $post->getIdPost(); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-pen"></i> Modifier
        </a>
    <?php endif; ?>
</div>
<button class="btn btn-outline btn-sm summary-btn" 
        id="summary-btn-<?= $post->getIdPost() ?>"
        data-post-id="<?= $post->getIdPost() ?>"
        data-content='<?= htmlspecialchars(json_encode($post->getContenu()), ENT_QUOTES, 'UTF-8') ?>'
        onclick="generateSummary(<?= $post->getIdPost() ?>)">
    <i class="fas fa-brain"></i> 🤖 Résumé IA
</button>
                    </div>
                    

<!-- Conteneur pour le résumé -->
<div id="summary-<?= $post->getIdPost() ?>" style="display: none; margin-top: 10px;"></div>
                    </div>
                  
                  

<!-- Formulaire pour ajouter une réponse -->
<div style="max-width: 700px; width: 100%; margin: 0 auto; float: none;">
    <form method="POST" action="../Backoffice/addreponse.php" id="reponseForm" style="width: 100%; margin: 0; padding: 0; float: none;">
        <input type="hidden" name="id_post" value="<?php echo $post->getIdPost(); ?>">
        <label style="display: block; text-align: left; font-weight: 600; margin-bottom: 8px;">
            <i class="fa-regular fa-comment"></i> Ajouter une réponse :
        </label>
        
        <div style="position: relative; width: 100%;">
            <textarea class="form-control" name="texte_rep" id="reponseContent" rows="3" 
                      placeholder="Écrire une réponse..." 
                      style="padding-right: 90px; width: 100%; text-align: left;"></textarea>
            
            <div style="position: absolute; bottom: 8px; right: 8px; display: flex; gap: 5px;">
                <button type="button" id="btnGifReponse" style="background: none; border: none; cursor: pointer; font-size: 1rem; color: #ec4899;">🎬</button>
                <button type="button" id="btnEnhanceReponse" style="background: none; border: none; cursor: pointer; font-size: 1rem; color: #8b5cf6;">✨</button>
       
                <button type="button" id="suggestReplyBtn" onclick="getAISuggestions(<?= $post->getIdPost() ?>)" style="background: none; border: none; cursor: pointer; font-size: 1rem; color: #10b981;">💡</button>
            </div>
        </div>
                <!-- Preview amélioration IA (AJOUTER MANQUANT) -->
        <div id="aiPreviewReponse" style="display: none; margin-top: 10px; padding: 12px; background: #f0fdf4; border-radius: 12px; border-left: 4px solid #10b981;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <strong style="font-size: 0.85rem;">🤖 Version améliorée :</strong>
                <span id="aiLoadingReponse" style="display: none; font-size: 0.7rem; color: #10b981;">⏳ Amélioration...</span>
            </div>
            <p id="aiPreviewTextReponse" style="margin: 0 0 10px 0; font-size: 0.9rem; line-height: 1.5;"></p>
            <div style="display: flex; gap: 8px;">
                <button type="button" id="acceptAIReponse" class="btn btn-success btn-sm" style="padding: 4px 12px; font-size: 0.75rem;">
                    <i class="fas fa-check"></i> Accepter
                </button>
                <button type="button" id="rejectAIReponse" class="btn btn-outline btn-sm" style="padding: 4px 12px; font-size: 0.75rem;">
                    <i class="fas fa-times"></i> Annuler
                </button>
            </div>
        </div>
        <input type="hidden" name="gif_url" id="gifUrlInput" value="">
        
        <div id="gifPreviewContainer" style="display: none; margin-top: 10px;"></div>
        <div id="suggestionsContainer" style="display: none; margin-top: 15px;"></div>
        <div id="aiPreviewReponse" style="display: none; margin-top: 10px;"></div>
        
        <div style="text-align: right; margin-top: 10px;">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-regular fa-paper-plane"></i> Publier
            </button>
        </div>
    </form>
</div>


        <!-- Input caché pour le GIF sélectionné -->
        <input type="hidden" name="gif_url" id="gifUrlInput" value="">
        
        <!-- Aperçu du GIF sélectionné -->
        <div id="gifPreviewContainer" style="display: none; margin-top: 10px; padding: 12px; background: #fce7f3; border-radius: 12px; border-left: 4px solid #ec4899;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <strong style="font-size: 0.85rem;">🎬 GIF sélectionné :</strong>
            </div>
            <img id="gifPreviewImg" src="" alt="GIF preview" style="max-width: 100%; max-height: 200px; border-radius: 8px; margin-bottom: 8px;">
            <button type="button" id="btnRemoveGif" class="btn btn-outline btn-sm" style="padding: 4px 12px; font-size: 0.75rem;">
                <i class="fas fa-times"></i> Supprimer le GIF
            </button>
        </div>
        
    <div id="suggestionsContainer" style="display: none; margin-top: 15px;"></div>
    <div id="suggestionsList" style="display: flex; flex-direction: column; gap: 12px;"></div>
</div>


<!-- ===== MODALE GIPHY ===== -->
<div id="giphyModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 20px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; font-size: 1.5rem;">🎬 Rechercher un GIF</h2>
            <button type="button" id="closeGiphyModal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">✕</button>
        </div>
        
        <div style="margin-bottom: 15px;">
            <input type="text" id="giphySearchInput" placeholder="Chercher des GIFs... (ex: chat, sourire, dance)" 
                   style="width: 100%; padding: 10px 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem;">
        </div>
        
        <div id="giphyResults" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; min-height: 200px;">
            <div style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px 20px;">
                Tapez pour chercher des GIFs...
            </div>
        </div>
        
        <div style="margin-top: 15px; text-align: center;">
            <small style="color: #94a3b8;">Powered by <strong>GIPHY</strong></small>
        </div>
    </div>
</div>

<!-- Afficher les réponses existantes -->
<?php if (!empty($reponses)): ?>
    <?php foreach ($reponses as $repIdx => $rep):
        $repId = $rep->getIdRep();
        $rawReact  = $reponseC->getReponseById($repId)['reactions'] ?? null;
        $reactData = $rawReact ? (json_decode($rawReact, true) ?? []) : [];
        $reactData = array_filter($reactData, fn($v) => $v > 0);
       
    ?>
<div class="card" style="padding: 4px 8px; margin-top: 6px; margin-bottom: 6px; background: var(--bg); border-radius: 6px;" id="reponse-<?= $repId ?>">
            
            <!-- Si on est en mode édition (paramètre edit) -->
            <?php if (isset($_GET['edit_reponse']) && $_GET['edit_reponse'] == $repId): ?>
                
                <!-- FORMULAIRE DE MODIFICATION -->
                <form method="POST" action="modifreponse.php">
                    <div style="max-width: 700px; width: 100%; margin: 0 auto; float: none;">
                        <strong><?= htmlspecialchars($rep->getAuteurNom()) ?></strong>
                        <small><?= date('d/m/Y H:i', strtotime($rep->getDateRep())) ?></small>
                    </div>
                    
                    <?php
                        // Extraire le GIF du texte s'il existe
                        $texteReponse = $rep->getTexteRep();
                        $gifUrl = '';
                        $texteSansGif = $texteReponse;
                        
                        if (preg_match('/\[GIF:(.*?)\]/', $texteReponse, $matches)) {
                            $gifUrl = $matches[1];
                            $texteSansGif = preg_replace('/\n?\n?\[GIF:.*?\]\n?\n?/', '', $texteReponse);
                            $texteSansGif = trim($texteSansGif);
                        }
                    ?>
                    
                    <div style="position: relative;">
                        <textarea name="texte_rep" class="form-control edit-textarea" rows="4" style="margin: 10px 0; padding-right: 45px;"><?= htmlspecialchars($texteSansGif) ?></textarea>
                        
                        <!-- Bouton GIF pour modification (intégré dans le textarea) -->
                        <button type="button" class="btn-edit-gif" style="position: absolute; bottom: 10px; right: 10px; background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #ec4899; transition: all 0.2s; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;" title="🎬 Changer le GIF">
                            <i class="fas fa-film"></i>
                        </button>
                    </div>
                    
                    <!-- Aperçu du GIF actuel (si existe) -->
                    <?php if (!empty($gifUrl)): ?>
                        <div class="edit-gif-preview" style="margin: 10px 0; padding: 12px; background: #fce7f3; border-radius: 12px; border-left: 4px solid #ec4899;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <strong style="font-size: 0.85rem;">🎬 GIF actuel :</strong>
                            </div>
                            <img src="<?= htmlspecialchars($gifUrl) ?>" alt="GIF" style="max-width: 100%; max-height: 200px; border-radius: 8px; margin-bottom: 8px;">
                            <button type="button" class="btn-remove-edit-gif btn btn-outline btn-sm" style="padding: 4px 12px; font-size: 0.75rem;">
                                <i class="fas fa-times"></i> Supprimer
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Input caché pour le GIF modifié -->
                    <input type="hidden" class="edit-gif-url" name="gif_url" value="<?= htmlspecialchars($gifUrl) ?>">
                    <input type="hidden" name="id_rep" value="<?= $repId ?>">
                    <input type="hidden" name="id_post" value="<?= $post->getIdPost() ?>">
                    
                    <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 10px;">
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
    <strong><?= htmlspecialchars($rep->getAuteurNom()) ?></strong>
    <small><?= date('d/m/Y H:i', strtotime($rep->getDateRep())) ?></small>
</div>

<?= afficherReponseAvecGif($rep->getTexteRep()) ?>
                
                <!-- ===== RÉACTIONS EMOJI — Facebook style ===== -->
                <div style="margin-bottom: 10px;">
                    <div class="reactions-area" id="rarea-<?= $repId ?>">
                        <!-- Bouton principal -->
                        <button class="react-main-btn" id="rbtn-<?= $repId ?>"
                                onclick="togglePicker(<?= $repId ?>)"
                                onmouseenter="schedulePicker(<?= $repId ?>, true)"
                                onmouseleave="schedulePicker(<?= $repId ?>, false)">
                            <span id="rbtn-icon-<?= $repId ?>">❤️</span>
                            j'aime
                        </button>

                        <!-- Picker popup -->
                        <div class="emoji-picker-popup" id="picker-<?= $repId ?>"
                             onmouseenter="clearPickerTimer(<?= $repId ?>)"
                             onmouseleave="schedulePicker(<?= $repId ?>, false)">
                            <?php foreach (['❤️','😂','🔥','👍','😮','😢','👏','😍','🎉'] as $em): ?>
                            <button class="emoji-choice-btn" title="<?= $em ?>"
                                    onclick="chooseEmoji(<?= $repId ?>, '<?= $em ?>')">
                                <?= $em ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Strip des réactions existantes -->
                    <div class="reactions-strip" id="rstrip-<?= $repId ?>">
                        <?php foreach ($reactData as $em => $cnt): ?>
                        <span class="react-pill" data-emoji="<?= htmlspecialchars($em) ?>"
                              onclick="chooseEmoji(<?= $repId ?>, '<?= $em ?>')">
                            <?= $em ?> <span><?= (int)$cnt ?></span>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <?php 
// Vérifier si l'utilisateur connecté est le propriétaire de la réponse
$isReponseOwner = ($rep->getIdUtilisateur() == $userId);
$isAdmin = ($userRole === 'admin');
if ($isReponseOwner): ?>
    <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 10px;">
        <a href="showpost.php?id=<?= $post->getIdPost() ?>&edit_reponse=<?= $rep->getIdRep() ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-pen"></i> Modifier
        </a>
        <?php elseif ($isAdmin): ?>
            <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 10px;">
        <a href="deleteReponse.php?id=<?= $rep->getIdRep() ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette réponse ?')">
            <i class="fas fa-trash"></i> Supprimer
        </a>
    </div>
<?php endif; ?>
                
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
                        <li><a href="../front/indexp.php#consultations">Consultations</a></li>
                        <li><a href="../front/indexp.php#pharmacies">Pharmacies</a></li>
                        <li><a href="../front/indexp.php#assurances">Assurances</a></li>
                        <li><a href="../front/indexp.php#forum">Forum santé</a></li>
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
        btnSignal.style.color       = signale ? '#ef4444' : '#f59e0b';
        btnSignal.style.borderColor = signale ? '#ef4444' : '#f59e0b';
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
            showToast(action === 'signal' ? '⚠️ Post signalé aux modérateurs' : '✓ Signalement retiré', action === 'signal' ? '#f59e0b' : '#10b981');
        }
    })
    .catch(err => console.error('Erreur signalement:', err));
}


// ============ EMOJI PICKER FACEBOOK-STYLE ============
const userReactions = JSON.parse(localStorage.getItem('asc_reactions') || '{}');
let pickerTimers = {};

// Au chargement : marquer les réactions de l'utilisateur
document.addEventListener('DOMContentLoaded', () => {
    Object.entries(userReactions).forEach(([repId, emoji]) => {
        if (!emoji) return;
        const icon  = document.getElementById('rbtn-icon-' + repId);
        const strip = document.getElementById('rstrip-' + repId);
        if (icon)  icon.textContent = emoji;
        if (strip) {
            strip.querySelectorAll('.react-pill').forEach(p => {
                if (p.dataset.emoji === emoji) p.classList.add('mine');
            });
        }
    });
});

function togglePicker(repId) {
    const picker = document.getElementById('picker-' + repId);
    if (picker) picker.classList.toggle('open');
}

function schedulePicker(repId, open) {
    clearPickerTimer(repId);
    pickerTimers[repId] = setTimeout(() => {
        const p = document.getElementById('picker-' + repId);
        if (p) { open ? p.classList.add('open') : p.classList.remove('open'); }
    }, open ? 120 : 300);
}

function clearPickerTimer(repId) {
    if (pickerTimers[repId]) {
        clearTimeout(pickerTimers[repId]);
        delete pickerTimers[repId];
    }
}

// Fermer picker au clic ailleurs
document.addEventListener('click', (e) => {
    if (!e.target.closest('.reactions-area')) {
        document.querySelectorAll('.emoji-picker-popup.open').forEach(p => p.classList.remove('open'));
    }
});

function chooseEmoji(repId, emoji) {
    const oldEmoji = userReactions[repId] || null;
    const picker   = document.getElementById('picker-' + repId);
    if (picker) picker.classList.remove('open');

    // Toggle off si même emoji
    let action = 'add';
    if (oldEmoji === emoji) {
        action = 'remove';
        userReactions[repId] = null;
    } else {
        userReactions[repId] = emoji;
    }
    localStorage.setItem('asc_reactions', JSON.stringify(userReactions));

    // Mise à jour optimiste du bouton
    const rbIcon = document.getElementById('rbtn-icon-' + repId);
    if (rbIcon) rbIcon.textContent = (action === 'remove') ? '❤️' : emoji;

    fetch('../Frontoffice/reaction_reponse.php', {
        method : 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body   : `id_rep=${repId}&emoji=${encodeURIComponent(emoji)}&action=${action}&old_emoji=${encodeURIComponent(oldEmoji || '')}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.reactions) {
            updateReactionStrip(repId, data.reactions);
        }
    })
    .catch(err => console.error('Erreur réaction:', err));
}

function updateReactionStrip(repId, reactions) {
    const strip = document.getElementById('rstrip-' + repId);
    if (!strip) return;
    strip.innerHTML = '';
    const myEmoji = userReactions[repId];
    Object.entries(reactions).forEach(([em, cnt]) => {
        if (cnt < 1) return;
        const pill = document.createElement('span');
        pill.className = 'react-pill' + (myEmoji === em ? ' mine' : '');
        pill.dataset.emoji = em;
        pill.innerHTML = `${em} <span>${cnt}</span>`;
        pill.onclick = () => chooseEmoji(repId, em);
        strip.appendChild(pill);
    });
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
    t.textContent = msg;
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
// Dark Mode
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
function removeGifReponse() {
    const input = document.querySelector('#reponseForm input[name="gif_url"]');
    if (input) input.value = '';
    const preview = document.getElementById('gifPreviewContainer');
    if (preview) preview.style.display = 'none';
    showToast('❌ GIF supprimé', '#64748b');
}
</script>

<script>
// ========== RÉSUMÉ IA ==========
async function generateSummary(postId) {
    const container = document.getElementById(`summary-${postId}`);
    const btn = document.getElementById(`summary-btn-${postId}`);
    if (!container) return;

    // Toggle
    if (container.style.display === 'block') {
        container.style.display = 'none';
        container.innerHTML = '';
        if (btn) btn.style.display = 'inline-flex';
        return;
    }

    container.style.display = 'block';
    container.innerHTML = '<div style="padding: 12px; background: #f0fdf4; border-radius: 12px;">⏳ Génération du résumé...</div>';
    if (btn) btn.style.display = 'none';

    let content = '';
    if (btn) {
        content = btn.getAttribute('data-content');
        try { if (content.startsWith('"')) content = JSON.parse(content); } catch(e) {}
    }

    if (!content || content.length < 30) {
        container.innerHTML = '<div style="background: #fee2e2; padding: 12px; border-radius: 8px;">❌ Texte trop court pour un résumé</div>';
        if (btn) btn.style.display = 'flex';
        return;
    }

    try {
        const formData = new FormData();
        formData.append('contenu', content);
        const response = await fetch('summarize_post.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (data.success) {
            container.innerHTML = `
                <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 12px; border-radius: 8px; margin-top: 10px;">
                    <strong>🤖 Résumé IA :</strong>
                    <p style="margin: 8px 0 0 0; font-size: 0.9rem;">${escapeHtml(data.summary)}</p>
                    <button onclick="hideSummary(${postId})" 
                            style="margin-top: 8px; background: none; border: none; color: #64748b; cursor: pointer;">✖ Fermer</button>
                </div>
            `;
        } else {
            container.innerHTML = `<div style="background: #fee2e2; padding: 12px; border-radius: 8px;">❌ ${escapeHtml(data.error || 'Erreur')}</div>`;
        }
    } catch(error) {
        container.innerHTML = `<div style="background: #fee2e2; padding: 12px; border-radius: 8px;">❌ Erreur: ${error.message}</div>`;
    }

    if (btn) btn.style.display = 'flex';
}

function hideSummary(postId) {
    const container = document.getElementById(`summary-${postId}`);
    const btn = document.getElementById(`summary-btn-${postId}`);
    if (container) { container.style.display = 'none'; container.innerHTML = ''; }
    if (btn) btn.style.display = 'inline-flex';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
<script>
// ===== SUGGESTIONS DE RÉPONSES IA =====
async function getAISuggestions(postId) {
    const container = document.getElementById('suggestionsContainer');
    const suggestionsList = document.getElementById('suggestionsList');
    
    if (!container || !suggestionsList) {
        console.log('Conteneur suggestions non trouvé');
        return;
    }
    
    // Afficher le conteneur avec animation
    container.style.display = 'block';
    suggestionsList.innerHTML = '<div style="text-align:center; padding:20px;"><i class="fas fa-spinner fa-spin"></i> Génération des suggestions...</div>';
    
    // Récupérer le contenu du post
    const postElement = document.querySelector('.post-content-full');
    const postContent = postElement ? postElement.innerText : '';
    
    try {
        const formData = new FormData();
        formData.append('post_content', postContent);
        
        const response = await fetch('../Backoffice/ai_suggest_reply.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('Erreur HTTP ' + response.status);
        }
        
        const data = await response.json();
        console.log('Suggestions reçues:', data);
        
        if (data.success && data.suggestions && data.suggestions.length > 0) {
            suggestionsList.innerHTML = '';
            data.suggestions.forEach((suggestion, index) => {
                const suggestionDiv = document.createElement('div');
                suggestionDiv.style.cssText = 'background: white; padding: 12px; border-radius: 10px; border-left: 4px solid #10b981; cursor: pointer; transition: all 0.2s; margin-bottom: 8px;';
                suggestionDiv.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="flex:1;">💡 ${escapeHtml(suggestion)}</span>
                        <button onclick="useSuggestion(this)" class="btn btn-primary btn-sm" style="padding: 4px 12px; margin-left: 10px;">Utiliser</button>
                    </div>
                `;
                suggestionDiv.onmouseover = () => suggestionDiv.style.transform = 'translateX(5px)';
                suggestionDiv.onmouseout = () => suggestionDiv.style.transform = 'translateX(0)';
                suggestionsList.appendChild(suggestionDiv);
            });
        } else {
            suggestionsList.innerHTML = '<div style="color:#666;text-align:center;padding:20px;">❌ ' + (data.error || 'Aucune suggestion disponible') + '</div>';
        }
    } catch (error) {
        console.error('Erreur:', error);
        suggestionsList.innerHTML = '<div style="color:#ef4444;text-align:center;padding:20px;">❌ Erreur de connexion: ' + error.message + '</div>';
    }
}

function useSuggestion(button) {
    const suggestionDiv = button.closest('div');
    const suggestionText = suggestionDiv.querySelector('span').innerText.replace('💡 ', '');
    const textarea = document.getElementById('reponseContent');
    
    if (textarea) {
        const currentText = textarea.value;
        if (currentText) {
            textarea.value = currentText + ' ' + suggestionText;
        } else {
            textarea.value = suggestionText;
        }
        textarea.focus();
        showToast('✅ Suggestion ajoutée !', '#10b981');
    }
    closeSuggestions();
}

function closeSuggestions() {
    const container = document.getElementById('suggestionsContainer');
    if (container) {
        container.style.display = 'none';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(msg, color) {
    const toast = document.createElement('div');
    toast.textContent = msg;
    Object.assign(toast.style, {
        position: 'fixed', bottom: '80px', left: '50%',
        transform: 'translateX(-50%)',
        background: color, color: 'white',
        padding: '10px 20px', borderRadius: '30px',
        zIndex: '10000', fontSize: '0.9rem',
        animation: 'fadeInScale 0.3s ease'
    });
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
}
</script>
<script>
// ===== AMÉLIORATION IA POUR LES RÉPONSES =====
document.addEventListener('DOMContentLoaded', function() {
    const btnEnhance     = document.getElementById('btnEnhanceReponse');
    const reponseContent = document.getElementById('reponseContent');
    const aiPreview      = document.getElementById('aiPreviewReponse');
    const aiPreviewText  = document.getElementById('aiPreviewTextReponse');
    const acceptBtn      = document.getElementById('acceptAIReponse');
    const rejectBtn      = document.getElementById('rejectAIReponse');

    // ✅ Vérifier que TOUS les éléments existent avant de continuer
    if (!btnEnhance || !reponseContent || !aiPreview || !aiPreviewText || !acceptBtn || !rejectBtn) {
        console.log('Éléments IA manquants — script ignoré');
        return;
    }

    let improvedText = '';

    btnEnhance.addEventListener('click', async function() {
        const originalText = reponseContent.value.trim();

        if (originalText.length < 5) {
            showToast('⚠️ Écrivez au moins 5 caractères', '#f59e0b');
            return;
        }

        const originalHTML = btnEnhance.innerHTML;
        btnEnhance.disabled = true;
        btnEnhance.innerHTML = '⏳';

        try {
            const formData = new FormData();
            formData.append('contenu', originalText);

            const response = await fetch('ai_enhance_reponse.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const data = await response.json();

            if (data.success) {
                improvedText = data.newContent;
                aiPreviewText.textContent = improvedText;
                aiPreview.style.display = 'block';
                reponseContent.style.opacity = '0.6';
                showToast('✅ Texte amélioré !', '#10b981');
            } else {
                showToast('❌ ' + (data.error || 'Erreur IA'), '#ef4444');
            }

        } catch (error) {
            console.error('Erreur enhance:', error);
            showToast('❌ Erreur: ' + error.message, '#ef4444');
        } finally {
            btnEnhance.disabled = false;
            btnEnhance.innerHTML = originalHTML;
        }
    });

    acceptBtn.addEventListener('click', function() {
        if (improvedText) {
            reponseContent.value = improvedText;
            aiPreview.style.display = 'none';
            reponseContent.style.opacity = '1';
            showToast('✅ Texte accepté !', '#10b981');
        }
    });

    rejectBtn.addEventListener('click', function() {
        aiPreview.style.display = 'none';
        reponseContent.style.opacity = '1';
        improvedText = '';
        showToast('❌ Amélioration annulée', '#64748b');
    });
});

</script>

<!-- ===== SCRIPT GIPHY ===== -->
<script>
// ===== GIPHY COMPLET =====
const GIPHY_API_KEY = 'api_key';

const giphyModal = document.getElementById('giphyModal');
const giphySearchInput = document.getElementById('giphySearchInput');
const giphyResults = document.getElementById('giphyResults');
const btnGifReponse = document.getElementById('btnGifReponse');
const closeGiphyModal = document.getElementById('closeGiphyModal');

// ===== OUVRIR / FERMER MODALE =====
btnGifReponse.addEventListener('click', function(e) {
    e.preventDefault();
    giphyModal.dataset.editMode = 'false';
    giphyModal.style.display = 'flex';
    giphySearchInput.value = '';
    giphyResults.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:40px;">Tapez pour chercher des GIFs...</div>';
});

closeGiphyModal.addEventListener('click', function() {
    giphyModal.style.display = 'none';
});

giphyModal.addEventListener('click', function(e) {
    if (e.target === giphyModal) giphyModal.style.display = 'none';
});

// ===== RECHERCHE =====
let searchTimeout;
giphySearchInput.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();

    if (!query) {
        giphyResults.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:40px;">Tapez pour chercher des GIFs...</div>';
        return;
    }

    searchTimeout = setTimeout(() => searchGifs(query), 300);
});

async function searchGifs(query) {
    const url = `https://api.giphy.com/v1/gifs/search?api_key=${GIPHY_API_KEY}&q=${encodeURIComponent(query)}&limit=20&rating=pg&lang=fr`;

    try {
        giphyResults.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#0ea5e9;padding:40px;"><i class="fas fa-spinner fa-spin"></i> Recherche...</div>';

        const response = await fetch(url);
        const data = await response.json();

        if (!data.data || data.data.length === 0) {
            giphyResults.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:40px;">Aucun GIF trouvé</div>';
            return;
        }

        giphyResults.innerHTML = '';
        data.data.forEach(gif => {
            const gifUrl     = gif.images.fixed_height.url;
            const gifDiv     = document.createElement('div');
            gifDiv.style.cssText = 'cursor:pointer;border-radius:10px;overflow:hidden;transition:transform 0.2s;';
            gifDiv.innerHTML = `<img src="${gifUrl}" style="width:100%;height:150px;object-fit:cover;border-radius:10px;display:block;">`;

            gifDiv.addEventListener('mouseover', () => gifDiv.style.transform = 'scale(1.05)');
            gifDiv.addEventListener('mouseout',  () => gifDiv.style.transform = 'scale(1)');
            gifDiv.addEventListener('click',     () => selectGif(gifUrl));

            giphyResults.appendChild(gifDiv);
        });

    } catch (error) {
        console.error('Erreur GIPHY:', error);
        giphyResults.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#ef4444;padding:40px;">❌ Erreur de connexion</div>';
    }
}

// ===== SÉLECTIONNER UN GIF =====
function selectGif(gifUrl) {
    const isEditMode = giphyModal.dataset.editMode === 'true';

    if (isEditMode) {
        // ---- Mode modification d'une réponse existante ----
        const form = giphyModal._editForm;
        if (form) {
            const gifInput  = form.querySelector('.edit-gif-url');
            if (gifInput) gifInput.value = gifUrl;

            let gifPreview = form.querySelector('.edit-gif-preview');
            if (!gifPreview) {
                gifPreview = document.createElement('div');
                gifPreview.className = 'edit-gif-preview';
                gifPreview.style.cssText = 'margin:10px 0;padding:12px;background:#fce7f3;border-radius:12px;border-left:4px solid #ec4899;';
                gifPreview.innerHTML = `
                    <strong style="font-size:0.85rem;">🎬 GIF sélectionné :</strong>
                    <img src="${gifUrl}" style="max-width:100%;max-height:200px;border-radius:8px;margin:8px 0;display:block;">
                    <button type="button" class="btn-remove-edit-gif btn btn-outline btn-sm">
                        <i class="fas fa-times"></i> Supprimer
                    </button>
                `;
                const textarea = form.querySelector('textarea');
                textarea.closest('div').insertAdjacentElement('afterend', gifPreview);

                gifPreview.querySelector('.btn-remove-edit-gif').addEventListener('click', function() {
                    gifInput.value = '';
                    gifPreview.style.display = 'none';
                    showToast('❌ GIF supprimé', '#64748b');
                });
            } else {
                gifPreview.querySelector('img').src = gifUrl;
                gifPreview.style.display = 'block';
            }
        }
        giphyModal.dataset.editMode = 'false';
        giphyModal._editForm = null;

    } else {
        // ---- Mode nouvelle réponse ----
        const form      = document.getElementById('reponseForm');
        const gifInput  = form.querySelector('input[name="gif_url"]');

        if (gifInput) gifInput.value = gifUrl;

        // Chercher ou créer l'aperçu
        let preview = form.querySelector('.gif-preview-reponse');
        if (!preview) {
            preview = document.createElement('div');
            preview.className = 'gif-preview-reponse';
            preview.style.cssText = 'margin-top:10px;padding:12px;background:#fce7f3;border-radius:12px;border-left:4px solid #ec4899;';
            preview.innerHTML = `
                <strong style="font-size:0.85rem;">🎬 GIF sélectionné :</strong>
                <img class="gif-preview-img" src="${gifUrl}" 
                     style="max-width:200px;max-height:150px;border-radius:8px;margin:8px 0;display:block;object-fit:contain;">
                <button type="button" class="btn-remove-gif-reponse btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Supprimer
                </button>
            `;

            // Insérer après le div du textarea
            const textareaWrapper = form.querySelector('#reponseContent').closest('div');
            textareaWrapper.insertAdjacentElement('afterend', preview);

            preview.querySelector('.btn-remove-gif-reponse').addEventListener('click', function() {
                if (gifInput) gifInput.value = '';
                preview.remove();
                showToast('❌ GIF supprimé', '#64748b');
            });
        } else {
            preview.querySelector('.gif-preview-img').src = gifUrl;
            preview.style.display = 'block';
        }
    }

    // Fermer la modale
    giphyModal.style.display = 'none';
    giphySearchInput.value   = '';
    giphyResults.innerHTML   = '<div style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:40px;">Tapez pour chercher des GIFs...</div>';
    showToast('🎬 GIF sélectionné !', '#ec4899');
}

// ===== GESTION GIF POUR MODIFICATION =====
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-edit-gif').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            if (form) {
                giphyModal.dataset.editMode = 'true';
                giphyModal._editForm       = form;
                giphyModal.style.display   = 'flex';
                giphySearchInput.value     = '';
                giphyResults.innerHTML     = '<div style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:40px;">Tapez pour chercher des GIFs...</div>';
            }
        });
    });

    document.querySelectorAll('.btn-remove-edit-gif').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            if (form) {
                const gifInput  = form.querySelector('.edit-gif-url');
                const gifPreview = form.querySelector('.edit-gif-preview');
                if (gifInput)   gifInput.value = '';
                if (gifPreview) gifPreview.style.display = 'none';
                showToast('❌ GIF supprimé', '#64748b');
            }
        });
    });
});
</script>
</body>
</html>