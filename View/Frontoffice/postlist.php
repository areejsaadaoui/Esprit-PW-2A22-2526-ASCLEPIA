<?php
include '../../Controller/PostController.php';

function embedYouTube($content) {
    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $content, $matches);
    
    if (isset($matches[1])) {
        $videoId = $matches[1];
        return '
        <div class="youtube-wrapper">
            <iframe src="https://www.youtube.com/embed/' . $videoId . '" 
            frameborder="0" allowfullscreen></iframe>
        </div>';
    }
    return $content;
}

function detectYouTubeInContent($content) {
    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $content, $matches);
    return isset($matches[1]);
}

$postC = new PostController();
$posts = $postC->listPosts();

// ========== FILTRE PAR HASHTAG ==========
$hashtagFilter = isset($_GET['hashtag']) ? trim($_GET['hashtag']) : '';
if ($hashtagFilter) {
    $posts = $postC->getPostsByHashtag($hashtagFilter);
}

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
    case 'likes_desc':
        usort($posts, function($a, $b) {
            return $b->getLikes() - $a->getLikes();
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


// ========== TRAITEMENT DU LIKE ==========
if (isset($_GET['like'])) {
    $id_post_like = (int)$_GET['like'];
    $postC->addLike($id_post_like);
    
    // Redirige vers la même page SANS le paramètre 'like'
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $currentOrder = isset($_GET['order']) ? $_GET['order'] : 'date_desc';
    
    header('Location: ?order=' . $currentOrder . '&page=' . $currentPage);
    exit;
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
    <style>
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

        /* ===== GRID RESPONSIVE ===== */
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Tablette : 2 colonnes */
        @media (max-width: 992px) {
            .posts-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Mobile : 1 colonne */
        @media (max-width: 576px) {
            .posts-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Chaque item centré */
        .grid-item {
            display: flex;
            justify-content: center;
        }

        /* ===== CARTES MODERNES ===== */
        .post-card {
            width: 100%;
            max-width: 400px;
            border-radius: 18px;
            padding: 14px !important;
            background: white;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            animation: fadeInScale 0.5s ease-out;
        }

        /* Effet hover stylé */
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.2);
        }

        /* Ligne gradient en haut */
        .post-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 4px;
            width: 100%;
            background: linear-gradient(90deg, #0ea5e9, #3b82f6);
        }

        /* ===== IMAGE ===== */
        .post-image {
            height: 140px !important;
            border-radius: 14px;
            transition: transform 0.3s ease;
        }

        .post-card:hover .post-image {
            transform: scale(1.03);
        }

        /* ===== AVATAR ===== */
        .post-avatar {
            width: 36px !important;
            height: 36px !important;
            font-size: 0.9rem !important;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            animation: floatSoft 3s infinite;
            transition: 0.2s;
        }
        
        .post-avatar:hover {
            transform: scale(1.1);
        }

        /* ===== TEXTE ===== */
        .post-content p {
            font-size: 0.85rem !important;
            line-height: 1.6;
            color: #555;
            margin-bottom: 12px;
        }

        /* ===== META ===== */
        .post-meta {
            margin-bottom: 10px !important;
        }

        .post-date {
            font-size: 0.75rem !important;
        }

        /* ===== BOUTONS ===== */
        .btn-sm {
            border-radius: 20px !important;
            transition: all 0.2s ease;
            padding: 5px 12px !important;
            font-size: 0.75rem !important;
        }

        .btn-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0,0,0,0.1);
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

        /* ===== FOOTER CARTE ===== */
        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 35px;
            gap: 8px;
        }

        .post-actions {
            display: flex;
            gap: 8px;
        }

        /* ===== BADGE VIDÉO ===== */
        .video-badge {
            position: absolute;
            top: 125px;
            right: 20px;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            color: white;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ===== YOUTUBE WRAPPER ===== */
        .youtube-wrapper {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%;
            margin-top: 10px;
            border-radius: 12px;
            overflow: hidden;
        }

        .youtube-wrapper iframe {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            border: none;
        }

        /* ===== PAGINATION ===== */
        .pagination {
            display: flex !important;
            justify-content: center !important;
            align-items: center;
            gap: 8px;
            margin-top: 50px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            min-width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .pagination .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: white;
            box-shadow: 0 4px 12px rgba(14,165,233,0.4);
        }

        .pagination .btn-outline {
            background: white;
            border: 1px solid #e2e8f0;
            color: #475569;
        }

        .pagination .btn-outline:hover {
            background: #0ea5e9;
            border-color: #0ea5e9;
            color: white;
            transform: translateY(-2px);
        }

        /* ===== BARRE FILTRE ===== */
        .sort-select {
            border-radius: 25px !important;
            padding: 6px 16px !important;
            font-size: 0.85rem;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            background: white;
            transition: all 0.2s ease;
        }

        .sort-select:hover {
            border-color: #0ea5e9;
        }

        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
            padding: 0 15px;
        }

        .filter-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .posts-count {
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #64748b;
        }

        /* ===== ESPACEMENT GLOBAL ===== */
        .section-padding {
            padding-top: 60px !important;
            padding-bottom: 60px !important;
            background: #f8fafc;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-tag {
            display: inline-block;
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            color: white;
            padding: 5px 15px;
            border-radius: 25px;
            font-size: 0.85rem;
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #1e293b;
        }

        .section-desc {
            color: #64748b;
        }

        /* ===== DARK MODE ===== */
        body.dark-mode {
            background: #1a1a2e !important;
        }

        body.dark-mode .section-padding {
            background: #1a1a2e !important;
        }

        body.dark-mode .post-card {
            background: #16213e !important;
            border-color: #2d2d44 !important;
        }

        body.dark-mode .post-content p {
            color: #cbd5e1 !important;
        }

        body.dark-mode .post-date,
        body.dark-mode .post-date i {
            color: #a0a0c0 !important;
        }

        body.dark-mode .post-avatar {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6) !important;
        }

        body.dark-mode .btn-outline {
            border-color: #475569 !important;
            color: #cbd5e1 !important;
            background: transparent !important;
        }

        body.dark-mode .btn-outline:hover {
            background: #334155 !important;
            border-color: #0ea5e9 !important;
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

        body.dark-mode .pagination .btn-outline {
            background: #16213e !important;
            border-color: #2d2d44 !important;
            color: #cbd5e1 !important;
        }

        body.dark-mode .pagination .btn-outline:hover {
            background: #0ea5e9 !important;
            border-color: #0ea5e9 !important;
            color: white !important;
        }

        body.dark-mode .sort-select {
            background: #16213e !important;
            color: white !important;
            border-color: #2d2d44 !important;
        }

        body.dark-mode .posts-count {
            background: #0f0f1a !important;
            color: #a0a0c0 !important;
        }

        body.dark-mode .section-title {
            color: white !important;
        }

        body.dark-mode .section-desc {
            color: #a0a0c0 !important;
        }

        body.dark-mode .navbar {
            background: #0f0f1a !important;
            border-bottom: 1px solid #2d2d44 !important;
        }

        body.dark-mode .footer {
            background: #0f0f1a !important;
            border-top: 1px solid #2d2d44 !important;
        }

        /* Bouton toggle */
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

        @media (max-width: 768px) {
            .section-padding {
                padding-top: 40px !important;
                padding-bottom: 40px !important;
            }
            .section-title {
                font-size: 1.5rem;
            }
            .filter-bar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        /* ===== BOUTON LIKE MODERNE ===== */
.like-btn {
    background: transparent;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 40px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #64748b;
}

.like-btn i {
    font-size: 1rem;
    transition: transform 0.2s ease;
}

.like-btn:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.like-btn:hover i {
    transform: scale(1.1);
}

.like-btn.liked {
    color: #ef4444;
}

.like-btn.liked i {
    animation: likePop 0.3s ease;
}

@keyframes likePop {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

/* Compteur de likes */
.like-count {
    font-weight: 600;
    font-size: 0.85rem;
}

/* Bouton Partager */
.share-btn {
    background: transparent;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 40px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #64748b;
}

.share-btn:hover {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    transform: translateY(-2px);
}

.share-btn i {
    font-size: 0.9rem;
}

/* Animation de partage */
@keyframes sharePop {
    0% { transform: scale(1); }
    50% { transform: scale(1.2) rotate(10deg); }
    100% { transform: scale(1); }
}

.share-btn.sharing i {
    animation: sharePop 0.4s ease;
}

/* Menu de partage (modal) */
.share-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 10000;
    justify-content: center;
    align-items: center;
}

.share-modal.active {
    display: flex;
}

.share-modal-content {
    background: white;
    border-radius: 20px;
    padding: 25px;
    max-width: 400px;
    width: 90%;
    animation: fadeInScale 0.3s ease;
}

body.dark-mode .share-modal-content {
    background: #16213e;
    color: white;
}

.share-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.share-modal-header h3 {
    margin: 0;
    font-size: 1.2rem;
}

.close-modal {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #64748b;
}

.share-options {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.share-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

body.dark-mode .share-option {
    background: #0f0f1a;
    border-color: #2d2d44;
}

.share-option:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.share-option i {
    font-size: 28px;
}

.share-option span {
    font-size: 0.8rem;
}

.share-link-container {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.share-link-container input {
    flex: 1;
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.85rem;
}

.copy-link-btn {
    padding: 8px 15px;
    background: #0ea5e9;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.copy-link-btn:hover {
    background: #3b82f6;
    transform: scale(1.02);
}
/* Carte cliquable */
.post-card-link {
    text-decoration: none;
    display: block;
    transition: transform 0.3s ease;
}

.post-card-link:hover {
    transform: translateY(-5px);
}

.post-card-link:hover .post-card {
    box-shadow: 0 20px 35px -12px rgba(0,0,0,0.2);
}

/* Empêcher la propagation du hover sur les boutons */
.post-card-link .btn,
.post-card-link .like-btn,
.post-card-link .share-btn,
.post-card-link .post-actions a {
    position: relative;
    z-index: 10;
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

<section class="section-padding">
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
        <div class="filter-bar">
            <div class="filter-left">
                <i class="fas fa-sort"></i>
                <span style="font-weight: 500;">Trier par :</span>
                
                <form method="GET" action="" id="orderForm" style="display: inline;">
                    <select name="order" class="sort-select" onchange="this.form.submit()">
                        <option value="date_desc" <?= ($orderBy == 'date_desc') ? 'selected' : '' ?>>📅 Date récente → ancienne</option>
                        <option value="date_asc" <?= ($orderBy == 'date_asc') ? 'selected' : '' ?>>📅 Date ancienne → récente</option>
                        <option value="length_desc" <?= ($orderBy == 'length_desc') ? 'selected' : '' ?>>📄 Texte plus long</option>
                        <option value="length_asc" <?= ($orderBy == 'length_asc') ? 'selected' : '' ?>>📄 Texte plus court</option>
                        <option value="likes_desc" <?= ($orderBy == 'likes_desc') ? 'selected' : '' ?>>🔥 Les plus aimés</option>
                    </select>
                </form>
            </div>
            
            <div class="posts-count">
                <i class="fas fa-chart-line"></i> <?= $totalPosts ?> discussions au total
                <?php if ($hashtagFilter): ?>
                    — filtre <strong>#<?= htmlspecialchars($hashtagFilter) ?></strong>
                    <a href="?" style="color:#ef4444;margin-left:6px;text-decoration:none;" title="Retirer le filtre">✖</a>
                <?php endif; ?>
            </div>
        </div>
        <!-- Grille des posts -->
<div class="posts-grid">
    <?php foreach ($postsAPaginer as $post): 
        $contenuOriginal = $post->getContenu();
        $hasVideo = detectYouTubeInContent($contenuOriginal);
        $textePur = strip_tags($contenuOriginal);
        $contenuAvecYouTube = embedYouTube($contenuOriginal);
    ?>
        <div class="grid-item">
             <a href="../Backoffice/showpost.php?id=<?= $post->getIdPost() ?>" class="post-card-link" style="text-decoration: none; display: block;">
            <div class="card post-card">
                <?php if ($hasVideo): ?>
                    <div class="video-badge">
                        <i class="fa-solid fa-video"></i>
                        🎬 Vidéo YouTube
                    </div>
                <?php endif; ?>
                
                <!-- Image ou GIF -->
                <?php 
                $mediaPath = $post->getImage();
                if (!empty($mediaPath)):
                    $isGif = (strpos($mediaPath, '.gif') !== false || strpos($mediaPath, 'giphy.com') !== false);
                    if (!$isGif && !filter_var($mediaPath, FILTER_VALIDATE_URL)) {
                        $mediaPath = '../Backoffice/' . $mediaPath;
                    }
                    $imgStyle = $isGif ? 'object-fit: contain; max-height: 140px;' : 'object-fit: cover; height: 140px;';
                ?>
                    <img src="<?= $mediaPath ?>" 
                         alt="Post media" 
                         class="post-image"
                         style="width: 100%; <?= $imgStyle ?> border-radius: 12px; margin-bottom: 12px; cursor: pointer;"
                         onclick="window.location.href='../Backoffice/showpost.php?id=<?= $post->getIdPost() ?>'">
                <?php endif; ?>

                <!-- Post meta -->
                <div class="post-meta" style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div class="post-avatar" style="background: linear-gradient(135deg, #0ea5e9, #3b82f6);">
                        <?= strtoupper(substr($post->getIdUtilisateur() ?? 'U', 0, 2)) ?>
                    </div>
                    <div>
                        <div class="post-date">
                            <i class="fa-regular fa-calendar"></i>
                            <?= (new DateTime($post->getDatePost()))->format('d/m/Y à H:i') ?>
                        </div>
                    </div>
                </div>

                <!-- Post content -->
                <div class="post-content">
                    <p>
                        <?= nl2br(htmlspecialchars(substr($textePur, 0, 120))) ?>
                        <?php if (strlen($textePur) > 120): ?>...<?php endif; ?>
                    </p>
                    <?php if ($hasVideo): ?>
                        <?= $contenuAvecYouTube ?>
                    <?php endif; ?>
                    <!-- Badge hashtags détectés -->
                    <?php $tags = $post->extractHashtags(); if (!empty($tags)): ?>
                    <div style="display:flex;gap:5px;flex-wrap:wrap;margin-top:8px;">
                        <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                        <a href="?hashtag=<?= urlencode($tag) ?>&order=<?= $orderBy ?>"
                           style="background:#dbeafe;color:#3b82f6;padding:2px 9px;border-radius:20px;font-size:0.7rem;text-decoration:none;font-weight:600;">
                            #<?= htmlspecialchars($tag) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <!-- Badge sentiment -->
                    <div style="margin-top:8px;"><?= $post->getSentimentBadge() ?></div>
                </div>

                <!-- Post footer -->
                <div class="post-footer">
<!-- Bouton LIKE avec cœur rouge si déjà liké -->
<?php 
// Vérifier si ce post a été liké (via session ou cookie)
$likedPosts = isset($_COOKIE['liked_posts']) ? explode(',', $_COOKIE['liked_posts']) : [];
$isLiked = in_array($post->getIdPost(), $likedPosts);
?>

<a href="?like=<?= $post->getIdPost() ?>&order=<?= $orderBy ?>&page=<?= $pageActuelle ?>" 
   class="like-btn"
   style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; <?= $isLiked ? 'color: #ef4444;' : 'color: #64748b;' ?>">
    <i class="<?= $isLiked ? 'fa-solid' : 'fa-regular' ?> fa-heart" style="<?= $isLiked ? 'color: #ef4444;' : '' ?>"></i>
    <span class="like-count" style="<?= $isLiked ? 'color: #ef4444;' : '' ?>"><?= $post->getLikes() ?></span>
</a>
<?php
// Détection automatique de l'URL de base
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $host . '/posts/';
?>
<!-- Bouton PARTAGER simplifié qui fonctionne -->
<button class="share-btn" onclick="copierLien(<?= $post->getIdPost() ?>)">
    <i class="fa-solid fa-share-alt"></i>
    Partager
</button>

<!-- Bouton SIGNALER (AJAX) -->
<?php
$signaledPosts = isset($_COOKIE['signaled_posts']) ? explode(',', $_COOKIE['signaled_posts']) : [];
$isSignaled = in_array($post->getIdPost(), $signaledPosts);
?>
<button class="share-btn signal-btn-<?= $post->getIdPost() ?>"
        onclick="signalerPost(<?= $post->getIdPost() ?>, this)"
        style="<?= $isSignaled ? 'color:#ef4444;' : 'color:#64748b;' ?>">
    <i class="fas fa-flag"></i>
    <span class="signal-txt"><?= $isSignaled ? 'Signalé' : 'Signaler' ?></span>
</button>
   
                    
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


        <!-- PAGINATION CENTRÉE -->
        <?php if ($nombreDePages > 1): ?>
            <div class="pagination">
                <?php if ($pageActuelle > 1): ?>
                    <a href="?order=<?= urlencode($orderBy) ?>&page=<?= $pageActuelle - 1 ?>" class="btn btn-outline btn-sm">◀ Précédent</a>
                <?php else: ?>
                    <span class="btn btn-outline btn-sm disabled" style="opacity:0.5; cursor: not-allowed;">◀ Précédent</span>
                <?php endif; ?>

                <?php 
                $startPage = max(1, $pageActuelle - 2);
                $endPage = min($nombreDePages, $pageActuelle + 2);
                
                if ($startPage > 1): ?>
                    <a href="?order=<?= urlencode($orderBy) ?>&page=1" class="btn btn-outline btn-sm">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="btn btn-outline btn-sm disabled">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <?php if ($i == $pageActuelle): ?>
                        <span class="btn btn-primary btn-sm"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?order=<?= urlencode($orderBy) ?>&page=<?= $i ?>" class="btn btn-outline btn-sm"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($endPage < $nombreDePages): ?>
                    <?php if ($endPage < $nombreDePages - 1): ?>
                        <span class="btn btn-outline btn-sm disabled">...</span>
                    <?php endif; ?>
                    <a href="?order=<?= urlencode($orderBy) ?>&page=<?= $nombreDePages ?>" class="btn btn-outline btn-sm"><?= $nombreDePages ?></a>
                <?php endif; ?>

                <?php if ($pageActuelle < $nombreDePages): ?>
                    <a href="?order=<?= urlencode($orderBy) ?>&page=<?= $pageActuelle + 1 ?>" class="btn btn-outline btn-sm">Suivant ▶</a>
                <?php else: ?>
                    <span class="btn btn-outline btn-sm disabled" style="opacity:0.5; cursor: not-allowed;">Suivant ▶</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="row" style="gap: 48px; flex-wrap: wrap;">
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

function toggleMenu() {
    const navLinks = document.getElementById('navLinks');
    const hamburger = document.getElementById('hamburger');
    
    if (navLinks) {
        navLinks.classList.toggle('active');
        hamburger.classList.toggle('active');
    }
}
</script>

<script>
function toggleLike(button, postId, orderBy, page) {
    // Changement visuel immédiat (optimiste)
    const icon = button.querySelector('i');
    const countSpan = button.querySelector('.like-count');
    const isLiked = button.classList.contains('liked');
    let currentCount = parseInt(countSpan.textContent);
    
    // Animation et changement visuel
    if (isLiked) {
        // Unlike
        icon.className = 'fa-regular fa-heart';
        button.classList.remove('liked');
        countSpan.textContent = currentCount - 1;
    } else {
        // Like
        icon.className = 'fa-solid fa-heart';
        button.classList.add('liked');
        countSpan.textContent = currentCount + 1;
        // Animation du cœur
        icon.style.animation = 'likePop 0.3s ease';
        setTimeout(() => { icon.style.animation = ''; }, 300);
    }
    
    // Envoi de la requête AJAX
    fetch('toggle_like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id_post=' + postId + '&action=' + (isLiked ? 'unlike' : 'like')
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Si erreur, on restaure l'état précédent
            if (isLiked) {
                icon.className = 'fa-solid fa-heart';
                button.classList.add('liked');
                countSpan.textContent = currentCount;
            } else {
                icon.className = 'fa-regular fa-heart';
                button.classList.remove('liked');
                countSpan.textContent = currentCount;
            }
            console.error('Erreur:', data.error);
        } else {
            // Mettre à jour le cookie pour suivre les likes
            updateLikeCookie(postId, !isLiked);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        // Restauration en cas d'erreur réseau
        if (isLiked) {
            icon.className = 'fa-solid fa-heart';
            button.classList.add('liked');
            countSpan.textContent = currentCount;
        } else {
            icon.className = 'fa-regular fa-heart';
            button.classList.remove('liked');
            countSpan.textContent = currentCount;
        }
    });
}

function updateLikeCookie(postId, liked) {
    // Récupérer les cookies existants
    let likedPosts = [];
    if (document.cookie.match(/liked_posts=([^;]+)/)) {
        likedPosts = document.cookie.match(/liked_posts=([^;]+)/)[1].split(',');
    }
    
    if (liked) {
        // Ajouter le postId s'il n'existe pas
        if (!likedPosts.includes(postId.toString())) {
            likedPosts.push(postId);
        }
    } else {
        // Retirer le postId
        const index = likedPosts.indexOf(postId.toString());
        if (index > -1) {
            likedPosts.splice(index, 1);
        }
    }
    
    // Mettre à jour le cookie (expire dans 30 jours)
    document.cookie = 'liked_posts=' + likedPosts.join(',') + ';path=/;max-age=2592000';
}

// Animation pour le like
const style = document.createElement('style');
style.textContent = `
   /* Style pour le bouton like */
.like-btn {
    transition: all 0.2s ease;
}

.like-btn:hover {
    transform: scale(1.05);
}

.like-btn:hover i {
    animation: heartBeat 0.3s ease;
}

@keyframes heartBeat {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}
`;
document.head.appendChild(style);
</script>


<script>
function copierLien(postId) {
    // Le lien complet vers showpost.php
    var lien = 'http://localhost/posts_validation/posts/View/Backoffice/showpost.php?id=' + postId;
    
    // Copier dans le presse-papier
    navigator.clipboard.writeText(lien).then(function() {
        // Notification de succès
        var msg = document.createElement('div');
        msg.innerHTML = '✓ Lien copié !';
        msg.style.position = 'fixed';
        msg.style.bottom = '20px';
        msg.style.left = '50%';
        msg.style.transform = 'translateX(-50%)';
        msg.style.background = '#10b981';
        msg.style.color = 'white';
        msg.style.padding = '10px 20px';
        msg.style.borderRadius = '30px';
        msg.style.zIndex = '9999';
        document.body.appendChild(msg);
        setTimeout(function() { msg.remove(); }, 2000);
    }).catch(function() {
        alert('Lien à copier : ' + lien);
    });
}

// ============ SIGNALEMENT AJAX ============
function signalerPost(postId, btn) {
    let signaled = JSON.parse(localStorage.getItem('signaled_posts_list') || '[]');
    const isSignaled = signaled.includes(postId);
    const action = isSignaled ? 'unsignal' : 'signal';

    fetch('signal_post.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id_post=' + postId + '&action=' + action
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (action === 'signal') {
                signaled.push(postId);
                btn.style.color = '#ef4444';
                btn.querySelector('.signal-txt').textContent = 'Signalé';
            } else {
                signaled = signaled.filter(id => id !== postId);
                btn.style.color = '#64748b';
                btn.querySelector('.signal-txt').textContent = 'Signaler';
            }
            localStorage.setItem('signaled_posts_list', JSON.stringify(signaled));

            // Toast
            const t = document.createElement('div');
            t.innerHTML = action === 'signal' ? '⚠️ Post signalé aux modérateurs' : '✓ Signalement retiré';
            Object.assign(t.style, {
                position:'fixed', bottom:'20px', left:'50%', transform:'translateX(-50%)',
                background: action === 'signal' ? '#f59e0b' : '#10b981', color:'white',
                padding:'10px 22px', borderRadius:'30px', zIndex:'9999', fontWeight:'600'
            });
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 2200);
        }
    })
    .catch(err => console.error('Erreur signalement:', err));
}
</script>

</body>
</html>