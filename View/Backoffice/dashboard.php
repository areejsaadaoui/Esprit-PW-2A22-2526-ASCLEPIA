<?php
session_start();
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';
include '../../Controller/ReponseController.php';
include '../../Controller/AvisController.php';
$adminNom = $_SESSION['user_nom'] ?? 'Administrateur';

$postC    = new PostController();
$reponseC = new ReponseController();
$posts    = $postC->listPosts();
$totalPosts = count($posts);

// Récupérer les avis
$avisC = new AvisController();

// ============ NOUVELLES DONNÉES INNOVANTES ============
// Top posts podium
$topPosts = $postC->getTopPosts(3);

// Posts signalés
$postsSignales = $postC->getPostsSignales(1);
$nbSignales    = count($postsSignales);

// Stats globales avancées
try {
    $statsGlobales = $postC->getStatsGlobales();
} catch (Exception $e) {
    $statsGlobales = ['total_posts'=>$totalPosts,'total_likes'=>0,'total_reponses'=>0,'total_signalements'=>0,'posts_today'=>0,'avec_media'=>0,'distribution_horaire'=>[]];
}

// Top post par réponses
$statsRep = $reponseC->getStatsReponses();

// Distribution horaire (heatmap)
$heatmapData = array_fill(0, 24, 0);
foreach (($statsGlobales['distribution_horaire'] ?? []) as $h) {
    $heatmapData[(int)$h['heure']] = (int)$h['nb'];
}


// Hashtags les plus utilisés (extraction depuis les contenus)
$allHashtags = [];
foreach ($posts as $p) {
    $tags = $postC->extractHashtags($p->getContenu()); // Appel via le controller
    foreach ($tags as $tag) {
        $allHashtags[$tag] = ($allHashtags[$tag] ?? 0) + 1;
    }
}

// Trier par popularité et prendre les 10 premiers
arsort($allHashtags);
$topHashtags = array_slice($allHashtags, 0, 10);
$totalHashtags = count($allHashtags);

// ======================================================

// Recherche
$searchTerm = '';
if (isset($_GET['ch']) && !empty($_GET['ch'])) {
    $searchTerm = $_GET['ch'];
    $filteredPosts = array_filter($posts, function($post) use ($searchTerm) {
        return stripos($post->getContenu(), $searchTerm) !== false;
    });
    $displayPosts = $filteredPosts;
} else {
    $displayPosts = $posts;
}

// Posts avec image
$postsWithImages = 0;
foreach ($posts as $post) {
    if (!empty($post->getImage())) $postsWithImages++;
}

// Derniers posts = tous (pour le tableau)
$latestPosts = $posts;

// Préparation des données pour le graphique (posts par mois, sur 12 mois)
$months = [];
$monthlyCounts = [];
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $months[] = date('M', strtotime("-$i months"));
    $count = 0;
    foreach ($posts as $post) {
        if (date('Y-m', strtotime($post->getDatePost())) == $month) $count++;
    }
    $monthlyCounts[] = $count;

// ================= GESTION DU TRI =================
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
$latestPosts = $posts; // met à jour les posts triés
// =================================================
// ================= PAGINATION =================
$postsPerPage = 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalPostsCount = count($latestPosts);
$totalPages = ceil($totalPostsCount / $postsPerPage);

// S'assurer que la page courante est valide
if ($currentPage < 1) $currentPage = 1;
if ($currentPage > $totalPages && $totalPages > 0) $currentPage = $totalPages;

// Calculer l'offset (décalage)
$offset = ($currentPage - 1) * $postsPerPage;

// Extraire uniquement les posts de la page courante
$paginatedPosts = array_slice($latestPosts, $offset, $postsPerPage);
// =============================================
}
// ========== STATISTIQUES DÉTAILLÉES DES MÉDIAS ==========
$postsWithImage = 0;      // Images uploadées
$postsWithGif = 0;        // GIFs (GIPHY)
$postsWithVideo = 0;      // Vidéos YouTube
$postsTextOnly = 0;       // Texte uniquement

foreach ($posts as $post) {
    $image = $post->getImage();
    
    if (!empty($image)) {
        // Vérifier si c'est un GIF
        if (strpos($image, '.gif') !== false || strpos($image, 'giphy.com') !== false) {
            $postsWithGif++;
        }
        // Vérifier si c'est une image uploadée
        elseif (strpos($image, 'uploads/') !== false) {
            $postsWithImage++;
        }
        // Sinon c'est une autre URL d'image
        else {
            $postsWithImage++;
        }
    }
    // Vérifier si le contenu contient une vidéo YouTube
    elseif (strpos($post->getContenu(), 'youtube.com') !== false || 
            strpos($post->getContenu(), 'youtu.be') !== false) {
        $postsWithVideo++;
    }
    else {
        $postsTextOnly++;
    }
}

// Double vérification pour les vidéos (au cas où il y a une image ET une vidéo)
foreach ($posts as $post) {
    $hasVideo = (strpos($post->getContenu(), 'youtube.com') !== false || 
                 strpos($post->getContenu(), 'youtu.be') !== false);
    if ($hasVideo && !empty($post->getImage())) {
        // Si déjà compté comme image, on ajuste
        if (strpos($post->getImage(), '.gif') !== false || strpos($post->getImage(), 'giphy.com') !== false) {
            $postsWithGif--;
            $postsWithGif = max(0, $postsWithGif);
        } else {
            $postsWithImage--;
            $postsWithImage = max(0, $postsWithImage);
        }
        $postsWithVideo++;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASCLEPIA - Dashboard Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
     <style>
        /* ===== BOUTON TRI MODERNE ===== */
.filter-bar-dashboard {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.sort-menu-dashboard {
    position: relative;
    display: inline-block;
}

.btn-sort-dashboard {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 40px;
    padding: 10px 20px;
    font-size: 0.9rem;
    font-weight: 500;
    color: #1e293b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s;
}

.btn-sort-dashboard:hover {
    border-color: #0ea5e9;
    background: #f8fafc;
}

.btn-sort-dashboard i:last-child {
    transition: transform 0.2s;
}

.sort-menu-content-dashboard {
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 8px;
    background: white;
    min-width: 220px;
    border-radius: 16px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
    z-index: 1000;
    overflow: hidden;
}

.sort-option-dashboard {
    padding: 12px 18px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.85rem;
    color: #334155;
}

.sort-option-dashboard:hover {
    background: #f0f9ff;
    color: #0ea5e9;
}

.sort-option-dashboard i {
    width: 20px;
}

.search-wrapper-dashboard {
    position: relative;
}

.search-wrapper-dashboard i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.search-wrapper-dashboard input {
    padding: 10px 16px 10px 42px;
    border: 2px solid #e2e8f0;
    border-radius: 40px;
    width: 260px;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.search-wrapper-dashboard input:focus {
    border-color: #0ea5e9;
    outline: none;
    box-shadow: 0 0 0 3px rgba(14,165,233,0.1);
}
/* Animation pour les barres du graphique */
@keyframes barGrow {
    from { height: 0; opacity: 0; }
    to { height: attr(data-height); opacity: 1; }
}

/* Stats grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

.stat-circle {
    text-align: center;
    padding: 24px 20px;
    background: var(--white);
    border-radius: 24px;
    box-shadow: var(--shadow);
    transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    border: 1px solid rgba(0,0,0,0.03);
    position: relative;
    overflow: hidden;
}

.stat-circle::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--gradient-primary);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.stat-circle:hover::before {
    transform: scaleX(1);
}

.stat-circle:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 30px -12px rgba(0,0,0,0.15);
}

.circle {
    width: 85px;
    height: 85px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    font-size: 2rem;
    font-weight: 800;
    color: white;
    transition: transform 0.3s;
}

.stat-circle:hover .circle {
    transform: scale(1.05);
}

.circle.blue { background: linear-gradient(135deg, #3b82f6, #1e40af); }
.circle.green { background: linear-gradient(135deg, #10b981, #047857); }
.circle.orange { background: linear-gradient(135deg, #f59e0b, #b45309); }
.circle.purple { background: linear-gradient(135deg, #8b5cf6, #5b21b6); }

.stat-circle h3 {
    font-size: 2rem;
    margin-bottom: 6px;
    font-weight: 800;
}

.stat-circle p {
    color: var(--text-muted);
    font-size: 0.85rem;
    margin-bottom: 8px;
}

/* Graphique barres */
.bar-chart {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    height: 250px;
    margin-top: 20px;
}

.bar-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.bar {
    width: 100%;
    background: var(--gradient-primary);
    border-radius: 8px;
    transition: height 0.8s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    min-height: 4px;
}

.bar-value {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--primary);
}

.bar-label {
    font-size: 0.7rem;
    color: var(--text-muted);
}

/* Liens rapides */
.quick-links-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin: 30px 0 40px;
}

.quick-card {
    text-align: center;
    padding: 28px 20px;
    background: var(--white);
    border-radius: 28px;
    box-shadow: var(--shadow);
    text-decoration: none;
    transition: all 0.3s;
    border: 1px solid var(--border);
    animation: pulse 2.5s ease-in-out infinite;
}

@keyframes pulse {
    0% { transform: scale(1); box-shadow: var(--shadow); }
    50% { transform: scale(1.02); box-shadow: var(--shadow-lg); }
    100% { transform: scale(1); box-shadow: var(--shadow); }
}

.quick-card i {
    font-size: 2.2rem;
    display: block;
    margin-bottom: 12px;
    transition: transform 0.2s;
}

.quick-card span {
    font-size: 1rem;
    font-weight: 600;
    color: var(--dark);
}

.quick-card:nth-child(1) i { color: var(--primary); }
.quick-card:nth-child(2) i { color: var(--accent); }

.quick-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary);
}

.quick-card:hover i {
    transform: scale(1.1);
}

/* Hashtags */
.hashtags-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.hashtag-cloud-item {
    transition: all 0.2s;
}

.hashtag-cloud-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(14,165,233,0.2);
}

/* Post result */
.search-wrapper {
    position: relative;
    display: flex;
    gap: 10px;
    align-items: center;
}

.search-wrapper i {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-light);
}

.search-wrapper input {
    flex: 1;
    padding: 14px 20px 14px 48px;
    border: 2px solid var(--border);
    border-radius: 60px;
    font-size: 1rem;
    transition: all 0.2s;
    background: var(--white);
}

.search-wrapper input:focus {
    border-color: var(--primary);
    outline: none;
}

.btn-go {
    padding: 14px 24px;
    border-radius: 60px;
    background: var(--primary);
    color: white;
    border: none;
    cursor: pointer;
    font-weight: 600;
}

.btn-go:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

.post-result {
    transition: all 0.2s;
}

.post-result:hover {
    background: rgba(14,165,233,0.05);
    transform: translateX(5px);
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    padding: 20px;
    flex-wrap: wrap;
    border-top: 1px solid var(--border);
}

.pagination-btn, .pagination-num {
    transition: all 0.2s;
}

.pagination-btn:hover, .pagination-num:hover {
    transform: translateY(-2px);
}

/* Stats card pour médias */
.stats-card {
    background: var(--white);
    border-radius: 24px;
    padding: 24px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border: 1px solid var(--border);
}

.stats-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f1f5f9;
}

.stats-card-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--dark);
}

.stats-card-header h3 i {
    margin-right: 8px;
    color: var(--primary);
}

.stats-badge {
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #64748b;
}

/* Layout camembert */
.pie-chart-layout {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
    flex-wrap: wrap;
}

.pie-chart-left {
    position: relative;
    flex-shrink: 0;
}

.pie-center-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    background: white;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pie-center-text .pie-total {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--dark);
}

.pie-center-text .pie-label {
    font-size: 0.6rem;
    color: #64748b;
}

.stats-list-right {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 15px;
    background: #f8fafc;
    border-radius: 12px;
    transition: all 0.2s;
}

.stat-item:hover {
    background: #f1f5f9;
    transform: translateX(5px);
}

.stat-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
}

.stat-label {
    flex: 1;
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--dark);
}

.stat-number {
    font-size: 1rem;
    font-weight: 700;
    color: var(--dark);
}

.stat-percent {
    font-size: 0.85rem;
    color: #64748b;
    min-width: 60px;
    text-align: right;
}

/* Tri et recherche modernes */
.filter-bar-dashboard {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.search-wrapper-dashboard {
    position: relative;
}

.search-wrapper-dashboard i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.search-wrapper-dashboard input {
    padding: 8px 16px 8px 42px;
    border: 2px solid #e2e8f0;
    border-radius: 40px;
    width: 260px;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.search-wrapper-dashboard input:focus {
    border-color: var(--primary);
    outline: none;
}

.sort-select {
    padding: 8px 12px;
    border-radius: 40px;
    border: 2px solid #e2e8f0;
    background: white;
    cursor: pointer;
}

/* Dark mode overrides */
body.dark-mode .stat-circle,
body.dark-mode .stats-card,
body.dark-mode .quick-card {
    background: #16213e;
    border-color: #2d2d44;
}

body.dark-mode .stats-card-header {
    border-bottom-color: #2d2d44;
}

body.dark-mode .stats-card-header h3 {
    color: white;
}

body.dark-mode .pie-center-text {
    background: #16213e;
}

body.dark-mode .pie-center-text .pie-total {
    color: white;
}

body.dark-mode .stat-item {
    background: #0f0f1a;
}

body.dark-mode .stat-item:hover {
    background: #1a1a2e;
}

body.dark-mode .stat-label,
body.dark-mode .stat-number {
    color: #e2e8f0;
}

body.dark-mode .sort-select {
    background: #16213e;
    border-color: #2d2d44;
    color: white;
}

body.dark-mode .search-wrapper-dashboard input {
    background: #16213e;
    border-color: #2d2d44;
    color: white;
}

/* Responsive */
@media (max-width: 992px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .pie-chart-layout {
        flex-direction: column;
        text-align: center;
    }
    
    .stats-list-right {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-card {
        padding: 16px;
    }
    
    .page-content {
        padding: 16px;
    }
}
</style>
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">⚕️</div>
            <div class="sidebar-title">ASC<span>LEPIA</span></div>
        </div>
        <div class="sidebar-user">
            <div class="user-avatar">AD</div>
            <div class="user-info">
                <div class="name">Administrateur</div>
                <div class="role">Super Admin</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Principal</div>
             <div class="nav-item">
                <a href="../back/dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>
            
            <div class="nav-item has-sub">
               <a href="../Backoffice/dashboard.php">
        <i class="fas fa-comments nav-icon"></i>
        <span>Forum</span>
    </a>
                <div class="sub-menu">
                    <a href="../Frontoffice/postList.php">Tous les posts</a>
                    <a href="addpost.php">Ajouter un post</a>
                    <a href="dashboard.php">Gestion des posts</a>
                </div>
            </div>
            <div class="nav-section-label">configuration</div>
            <div class="nav-item"><a href="../front/indexp.php"><i class="fas fa-globe nav-icon"></i><span>Voir le site</span></a></div>
        </nav>
        <div class="sidebar-footer">
            <a href="#" class="btn btn-outline-white btn-sm" style="width:100%; justify-content:center;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <div class="page-title">Gestion des utilisateurs</div>
                    <div class="breadcrumb">
                        <span>Accueil</span>
                        <span>/</span>
                        <span>Utilisateurs</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <button class="topbar-btn" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <div class="topbar-user">
                    <i class="fas fa-user-circle" style="font-size: 1.5rem;"></i>
                    <div>
                        <div class="name"><?= htmlspecialchars($adminNom) ?></div>
                        <div class="role">Administrateur</div>
                    </div>
                </div>
            </div>
        </div>

 <div class="page-content">

    <!-- Camembert Médias dans les posts -->
    <div class="stats-card">
        <div class="stats-card-header">
            <h3><i class="fas fa-chart-pie"></i> Médias dans les posts</h3>
            <span class="stats-badge">4 catégories</span>
        </div>
        
        <div class="pie-chart-layout">
            <div class="pie-chart-left">
                <canvas id="mediaPieCanvas" width="250" height="250" style="width: 250px; height: 250px;"></canvas>
                <div class="pie-center-text">
                    <span class="pie-total"><?= $totalPosts ?></span>
                    <span class="pie-label">TOTAL POSTS</span>
                </div>
            </div>
            
            <div class="stats-list-right">
                <div class="stat-item">
                    <span class="stat-dot" style="background: #3b82f6;"></span>
                    <span class="stat-label">Images uploadées</span>
                    <span class="stat-number"><?= $postsWithImage ?></span>
                    <span class="stat-percent">(<?= round(($postsWithImage/$totalPosts)*100) ?>%)</span>
                </div>
                <div class="stat-item">
                    <span class="stat-dot" style="background: #ec4899;"></span>
                    <span class="stat-label">GIFs animés</span>
                    <span class="stat-number"><?= $postsWithGif ?></span>
                    <span class="stat-percent">(<?= round(($postsWithGif/$totalPosts)*100) ?>%)</span>
                </div>
                <div class="stat-item">
                    <span class="stat-dot" style="background: #f59e0b;"></span>
                    <span class="stat-label">Vidéos YouTube</span>
                    <span class="stat-number"><?= $postsWithVideo ?></span>
                    <span class="stat-percent">(<?= round(($postsWithVideo/$totalPosts)*100) ?>%)</span>
                </div>
                <div class="stat-item">
                    <span class="stat-dot" style="background: #ef4444;"></span>
                    <span class="stat-label">Texte uniquement</span>
                    <span class="stat-number"><?= $postsTextOnly ?></span>
                    <span class="stat-percent">(<?= round(($postsTextOnly/$totalPosts)*100) ?>%)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Hashtags tendance -->
    <div class="stats-card">
        <div class="stats-card-header">
            <h3><i class="fas fa-hashtag"></i> Hashtags tendance</h3>
            <span class="stats-badge">Populaires</span>
        </div>
        <div class="hashtags-cloud">
            <?php if (!empty($topHashtags)): ?>
                <?php
                $maxHt = max($topHashtags) ?: 1;
                $tagColors = ['#0ea5e9','#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4','#ec4899'];
                $i = 0;
                foreach ($topHashtags as $tag => $count):
                    $size = 0.8 + ($count / $maxHt) * 0.6;
                    $color = $tagColors[$i % count($tagColors)];
                ?>
                    <a href="?hashtag=<?= urlencode($tag) ?>" class="hashtag-cloud-item"
                       style="background:<?= $color ?>15;color:<?= $color ?>;border:1px solid <?= $color ?>30;
                              padding:8px 16px;border-radius:40px;text-decoration:none;
                              font-size:<?= round($size, 2) ?>rem;font-weight:600;transition:all 0.2s;
                              display:inline-flex;align-items:center;gap:8px;">
                        #<?= htmlspecialchars($tag) ?>
                        <span style="background:<?= $color ?>30;color:white;padding:2px 8px;border-radius:20px;font-size:0.7rem;"><?= $count ?></span>
                    </a>
                <?php $i++; endforeach; ?>
            <?php else: ?>
                <p class="empty-state">Aucun hashtag détecté pour le moment</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Graphique évolution -->
    <div class="stats-card">
        <div class="stats-card-header">
            <h3><i class="fas fa-chart-line"></i> Évolution des publications</h3>
            <span class="stats-badge">12 derniers mois</span>
        </div>
        <div class="bar-chart" id="barChart">
            <?php 
            $maxValue = max($monthlyCounts) ?: 1;
            foreach ($months as $index => $month): 
                $height = ($monthlyCounts[$index] / $maxValue) * 180;
            ?>
                <div class="bar-item">
                    <div class="bar-value"><?= $monthlyCounts[$index] ?></div>
                    <div class="bar" style="height: <?= $height ?>px;"></div>
                    <div class="bar-label"><?= $month ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Liens rapides -->
    <div class="quick-links-grid">
        <a href="addpost.php" class="quick-card">
            <i class="fas fa-plus-circle"></i>
            <span>➕ Ajouter un post</span>
        </a>
        <a href="../Frontoffice/postlist.php" class="quick-card">
            <i class="fas fa-globe"></i>
            <span>🌍 Voir le forum public</span>
        </a>
    </div>
        
        <input type="hidden" id="selectedPostId" value="">

        <?php if ($searchTerm): ?>
            <div class="result-count" style="margin-top: 20px;">
                <i class="fas fa-chart-simple"></i> <?= count($displayPosts) ?> résultat(s) pour "<strong><?= htmlspecialchars($searchTerm) ?></strong>"
                <a href="dashboard.php" style="float:right;">✖ Effacer</a>
            </div>
            <div class="search-results">
                <?php if (count($displayPosts) > 0): ?>
                    <?php foreach ($displayPosts as $post): ?>
                        <div class="post-result" data-post-id="<?= $post->getIdPost() ?>" style="padding: 15px; border-bottom: 1px solid var(--border); cursor: pointer; transition: 0.2s;">
                            <h4><i class="fas fa-comment"></i> Post #<?= $post->getIdPost() ?></h4>
                            <p><?= htmlspecialchars(substr($post->getContenu(), 0, 150)) ?>…</p>
                            <small><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($post->getDatePost())) ?>
                            <?php if (!empty($post->getImage())): ?> <span style="color:var(--accent)"><i class="fas fa-image"></i> Avec image</span><?php endif; ?>
                            </small>
                            <div class="post-actions" style="margin-top:10px">
                                <a href="showpost.php?id=<?= $post->getIdPost() ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Voir</a>
                                <a href="deletepost.php?id=<?= $post->getIdPost() ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce post ?')"><i class="fas fa-trash"></i> Supprimer</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Aucun post correspondant.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tableau de tous les posts -->
    <div id="recent" class="card" style="padding:0; overflow:hidden; border-radius: 28px;">
       <!-- Barre de filtre moderne -->
<div style="padding:20px; border-bottom:1px solid var(--border);">
    <div class="filter-bar-dashboard">
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <div class="search-wrapper-dashboard">
                <i class="fas fa-search"></i>
                <input type="text" id="searchPostTable" placeholder="Rechercher un post..." onkeyup="filterPostTable()">
            </div>
            
            <!-- Bouton Trier moderne -->
            <div class="sort-menu-dashboard">
                <button class="btn-sort-dashboard" onclick="toggleSortMenuDashboard()">
                    <i class="fas fa-arrow-down-wide-short" style="color: #0ea5e9;"></i>
                    Trier
                    <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
                </button>
                <div id="sortMenuDashboard" class="sort-menu-content-dashboard" style="display: none;">
                    <div class="sort-option-dashboard" onclick="applySortDashboard('date_desc')" data-sort="date_desc">
                        <i class="fas fa-calendar-alt"></i> Date (plus récent)
                    </div>
                    <div class="sort-option-dashboard" onclick="applySortDashboard('date_asc')" data-sort="date_asc">
                        <i class="fas fa-calendar-alt"></i> Date (plus ancien)
                    </div>
                    <div style="height: 1px; background: #e2e8f0; margin: 4px 0;"></div>
                    <div class="sort-option-dashboard" onclick="applySortDashboard('likes_desc')" data-sort="likes_desc">
                        <i class="fas fa-heart" style="color: #ef4444;"></i> Plus aimés
                    </div>
                    <div class="sort-option-dashboard" onclick="applySortDashboard('likes_asc')" data-sort="likes_asc">
                        <i class="fas fa-heart" style="color: #ef4444;"></i> Moins aimés
                    </div>
                    <div style="height: 1px; background: #e2e8f0; margin: 4px 0;"></div>
                    <div class="sort-option-dashboard" onclick="applySortDashboard('length_desc')" data-sort="length_desc">
                        <i class="fas fa-align-left"></i> Plus longs
                    </div>
                    <div class="sort-option-dashboard" onclick="applySortDashboard('length_asc')" data-sort="length_asc">
                        <i class="fas fa-align-left"></i> Plus courts
                    </div>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="../Frontoffice/postlist.php" class="btn btn-outline btn-sm">Gérer <i class="fas fa-arrow-right"></i></a>
            <a href="export_csv.php" class="btn btn-outline btn-sm" style="border-color:#10b981;color:#10b981;">
                <i class="fas fa-file-csv"></i> Exporter CSV
            </a>
        </div>
    </div>
</div>
        <div style="overflow-x:auto;">
    <table class="table" id="postsTable">
        <thead>
            <tr><th>ID</th><th>Contenu</th><th>Média</th><th>Likes</th><th>Réponses</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody id="postsTableBody">
            <!-- Les posts seront chargés dynamiquement par JS -->
        </tbody>
    </table>
</div>
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-container" style="display: flex; justify-content: center; align-items: center; gap: 8px; padding: 20px; flex-wrap: wrap; border-top: 1px solid var(--border);">
            <?php if ($currentPage > 1): ?>
                <a href="?page=1<?= isset($_GET['order']) ? '&order=' . $_GET['order'] : '' ?>" class="pagination-btn" style="min-width:36px;height:36px;border-radius:50%;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;"><i class="fas fa-angle-double-left"></i></a>
            <?php else: ?>
                <span class="pagination-disabled" style="min-width:36px;height:36px;border-radius:50%;background:#cbd5e1;color:white;display:flex;align-items:center;justify-content:center;opacity:0.5;"><i class="fas fa-angle-double-left"></i></span>
            <?php endif; ?>
            
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage-1 ?><?= isset($_GET['order']) ? '&order=' . $_GET['order'] : '' ?>" class="pagination-btn" style="padding:0 14px;height:36px;border-radius:40px;background:var(--primary);color:white;display:flex;align-items:center;gap:5px;"><i class="fas fa-angle-left"></i> Précédent</a>
            <?php else: ?>
                <span class="pagination-disabled" style="padding:0 14px;height:36px;border-radius:40px;background:#cbd5e1;color:white;opacity:0.5;"><i class="fas fa-angle-left"></i> Précédent</span>
            <?php endif; ?>
            
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);
                if ($startPage > 1) echo '<span style="min-width:36px;height:36px;display:flex;align-items:center;justify-content:center;">...</span>';
                for ($i = $startPage; $i <= $endPage; $i++):
                    $isActive = ($i == $currentPage);
                ?>
                    <a href="?page=<?= $i ?><?= isset($_GET['order']) ? '&order=' . $_GET['order'] : '' ?>" class="pagination-num" style="min-width:36px;height:36px;border-radius:50%;background:<?= $isActive ? 'var(--primary-dark)' : 'var(--primary)' ?>;color:white;display:flex;align-items:center;justify-content:center;<?= $isActive ? 'transform:scale(1.05);font-weight:bold;' : '' ?>"><?= $i ?></a>
                <?php endfor;
                if ($endPage < $totalPages) echo '<span style="min-width:36px;height:36px;display:flex;align-items:center;justify-content:center;">...</span>';
                ?>
            </div>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage+1 ?><?= isset($_GET['order']) ? '&order=' . $_GET['order'] : '' ?>" class="pagination-btn" style="padding:0 14px;height:36px;border-radius:40px;background:var(--primary);color:white;display:flex;align-items:center;gap:5px;">Suivant <i class="fas fa-angle-right"></i></a>
            <?php else: ?>
                <span class="pagination-disabled" style="padding:0 14px;height:36px;border-radius:40px;background:#cbd5e1;color:white;opacity:0.5;">Suivant <i class="fas fa-angle-right"></i></span>
            <?php endif; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $totalPages ?><?= isset($_GET['order']) ? '&order=' . $_GET['order'] : '' ?>" class="pagination-btn" style="min-width:36px;height:36px;border-radius:50%;background:var(--primary);color:white;display:flex;align-items:center;justify-content:center;"><i class="fas fa-angle-double-right"></i></a>
            <?php else: ?>
                <span class="pagination-disabled" style="min-width:36px;height:36px;border-radius:50%;background:#cbd5e1;color:white;opacity:0.5;"><i class="fas fa-angle-double-right"></i></span>
            <?php endif; ?>
        </div>
        <div style="text-align:center;padding:10px 20px 20px;color:var(--text-muted);font-size:0.85rem;border-top:1px solid var(--border);">
            <i class="fas fa-info-circle"></i> Affichage des posts <?= $offset + 1 ?> à <?= min($offset + $postsPerPage, $totalPostsCount) ?> sur un total de <?= $totalPostsCount ?> posts
        </div>
        <?php endif; ?>
    </div>

    <!-- Modération Posts signalés -->
    <?php if ($nbSignales > 0): ?>
    <div class="card" style="padding:0;overflow:hidden;border-radius:28px;border-left:5px solid #ef4444;margin-bottom:28px;">
        <div style="padding:18px 20px;background:linear-gradient(135deg,#fee2e2,#fef2f2);display:flex;justify-content:space-between;align-items:center;">
            <h3 style="color:#dc2626;margin:0;"><i class="fas fa-shield-halved"></i> ⚠️ Modération — Posts signalés</h3>
            <span style="background:#ef4444;color:white;padding:4px 14px;border-radius:20px;font-weight:700;"><?= $nbSignales ?> alerte(s)</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="table" style="margin:0;">
                <thead><tr><th>ID</th><th>Contenu</th><th>Signalements</th><th>Likes</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($postsSignales as $ps): ?>
                    <tr>
                        <td><?= $ps['id_post'] ?></td>
                        <td style="max-width:300px;"><?= htmlspecialchars(substr($ps['contenu'], 0, 80)) ?>…</td>
                        <td><span style="background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-weight:700;">🚩 <?= $ps['signalements'] ?></span></td>
                        <td>❤️ <?= $ps['likes'] ?></td>
                        <td>
                            <a href="showpost.php?id=<?= $ps['id_post'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                            <a href="deletepost.php?id=<?= $ps['id_post'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce post signalé ?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div> <!-- Fin page-content -->
    </main>
<script>
    // Toggle sidebar
    
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
    }

    // Sous-menu
    function toggleSubMenu(element) {
        const parent = element.closest('.has-sub');
        parent.classList.toggle('open');
        const subMenu = parent.querySelector('.sub-menu');
        if (subMenu) subMenu.classList.toggle('open');
    }

    // Smooth scroll
    document.querySelectorAll('.sub-menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(targetId);
                if (target) target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Animations 
    const bars = document.querySelectorAll('.bar');
    bars.forEach(bar => {
        const height = bar.style.height;
        bar.style.height = '0px';
        setTimeout(() => {
            bar.style.height = height;
        }, 200);
    });
// voir
    const goToPostBtn = document.getElementById('goToPostBtn');
    const searchInput = document.getElementById('searchInput');
    const selectedPostId = document.getElementById('selectedPostId');

    goToPostBtn.addEventListener('click', function() {
        
        if (selectedPostId.value) {
            window.location.href = 'showpost.php?id=' + selectedPostId.value;
        } 

        else if (searchInput.value.trim() !== '') {
           
            document.getElementById('searchForm').submit();
        } else {
            alert('Veuillez entrer un mot-clé ou sélectionner un post');
        }
    });

    document.querySelectorAll('.post-result').forEach(result => {
        result.addEventListener('click', function(e) {
           
            if (e.target.tagName === 'A' || e.target.closest('a')) return;
            
            const postId = this.getAttribute('data-post-id');
            selectedPostId.value = postId;
            
            
            document.querySelectorAll('.post-result').forEach(r => {
                r.style.background = '';
            });
            this.style.background = 'rgba(14,165,233,0.1)';
            
           
            const btn = document.getElementById('goToPostBtn');
            btn.innerHTML = '<i class="fas fa-arrow-right"></i> Voir le post #' + postId;
        });
    });
    // ===== ANIMATION DES COMPTEURS =====
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start);
        }
    }, 16);
}

// Détecter quand les compteurs deviennent visibles
const observerOptions = {
    threshold: 0.3,
    rootMargin: "0px 0px -50px 0px"
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const counters = entry.target.querySelectorAll('.counter');
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                if (target > 0 && !counter.classList.contains('animated')) {
                    counter.classList.add('animated');
                    animateCounter(counter, target);
                }
            });
        }
    });
}, observerOptions);
</script>
<button class="theme-toggle" id="themeToggle">
    <i class="fas fa-moon"></i>
</button>

<script>
// ===== DARK / LIGHT MODE GLOBAL =====
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
<script>
// ===== DESSINER LE CAMEMBERT GRAND =====
function drawPieChart() {
    const canvas = document.getElementById('mediaPieCanvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const width = 250;
    const height = 250;
    canvas.width = width;
    canvas.height = height;
    
    const centerX = width / 2;
    const centerY = height / 2;
    const radius = 100;
    
    // Données dans l'ordre (sens horaire)
    const data = [
        { value: <?= $postsWithImage ?>, color: '#3b82f6' },
        { value: <?= $postsWithGif ?>, color: '#ec4899' },
        { value: <?= $postsWithVideo ?>, color: '#f59e0b' },
        { value: <?= $postsTextOnly ?>, color: '#ef4444' }
    ];
    
    const total = <?= $totalPosts ?>;
    let startAngle = -Math.PI / 2; // Commencer à 12h
    
    ctx.clearRect(0, 0, width, height);
    
    data.forEach(item => {
        if (item.value === 0) return;
        
        const sliceAngle = (item.value / total) * Math.PI * 2;
        const endAngle = startAngle + sliceAngle;
        
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.arc(centerX, centerY, radius, startAngle, endAngle);
        ctx.closePath();
        
        ctx.fillStyle = item.color;
        ctx.fill();
        
        // Contour blanc entre les parts
        ctx.strokeStyle = 'white';
        ctx.lineWidth = 2.5;
        ctx.stroke();
        
        startAngle = endAngle;
    });
    
    // Cercle central blanc
    ctx.beginPath();
    ctx.arc(centerX, centerY, 32, 0, Math.PI * 2);
    ctx.fillStyle = 'white';
    ctx.fill();
    ctx.strokeStyle = '#e2e8f0';
    ctx.lineWidth = 1.5;
    ctx.stroke();
}

drawPieChart();

// Redessiner si le thème change
const themeToggle = document.getElementById('themeToggle');
if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        setTimeout(drawPieChart, 100);
    });
}
drawPieChart();
</script>

<script>
// ===== TRI ET RECHERCHE MODERNE POUR DASHBOARD =====
let currentSortDashboard = 'date_desc';
let allDashboardPosts = <?= json_encode(array_map(function($post) {
    return [
        'id' => $post->getIdPost(),
        'contenu' => $post->getContenu(),
        'image' => $post->getImage(),
        'likes' => $post->getLikes(),
        'date' => $post->getDatePost()
    ];
}, $latestPosts)) ?>;

// Ajouter le nombre de réponses à chaque post
<?php
// Compter les réponses pour chaque post
$postReponses = [];
foreach ($latestPosts as $p) {
    $reponses = $reponseC->getReponsesByPost($p->getIdPost());
    $postReponses[$p->getIdPost()] = count($reponses);
}
?>
let postReponses = <?= json_encode($postReponses) ?>;

function toggleSortMenuDashboard() {
    const menu = document.getElementById('sortMenuDashboard');
    const chevron = document.querySelector('.btn-sort-dashboard i:last-child');
    
    if (menu.style.display === 'none' || menu.style.display === '') {
        menu.style.display = 'block';
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        
        setTimeout(() => {
            document.addEventListener('click', function closeMenu(e) {
                if (!menu.contains(e.target) && !e.target.closest('.btn-sort-dashboard')) {
                    menu.style.display = 'none';
                    if (chevron) chevron.style.transform = 'rotate(0deg)';
                    document.removeEventListener('click', closeMenu);
                }
            });
        }, 100);
    } else {
        menu.style.display = 'none';
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }
}

function applySortDashboard(sortType) {
    currentSortDashboard = sortType;
    
    let sortedPosts = [...allDashboardPosts];
    
    switch(sortType) {
        case 'date_desc':
            sortedPosts.sort((a, b) => new Date(b.date) - new Date(a.date));
            break;
        case 'date_asc':
            sortedPosts.sort((a, b) => new Date(a.date) - new Date(b.date));
            break;
        case 'likes_desc':
            sortedPosts.sort((a, b) => b.likes - a.likes);
            break;
        case 'likes_asc':
            sortedPosts.sort((a, b) => a.likes - b.likes);
            break;
        case 'length_desc':
            sortedPosts.sort((a, b) => b.contenu.length - a.contenu.length);
            break;
        case 'length_asc':
            sortedPosts.sort((a, b) => a.contenu.length - b.contenu.length);
            break;
    }
    
    renderPostTable(sortedPosts);
    
    document.getElementById('sortMenuDashboard').style.display = 'none';
    const chevron = document.querySelector('.btn-sort-dashboard i:last-child');
    if (chevron) chevron.style.transform = 'rotate(0deg)';
    
    document.querySelectorAll('.sort-option-dashboard').forEach(opt => {
        opt.classList.remove('active');
        if (opt.getAttribute('data-sort') === sortType) {
            opt.classList.add('active');
        }
    });
}

function renderPostTable(posts) {
    const tbody = document.getElementById('postsTableBody');
    if (!tbody) return;
    
    const searchTerm = document.getElementById('searchPostTable')?.value.toLowerCase() || '';
    const filteredPosts = searchTerm ? posts.filter(p => p.contenu.toLowerCase().includes(searchTerm)) : posts;
    
    if (filteredPosts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center">Aucun post trouvé</td></tr>';
        return;
    }
    
    tbody.innerHTML = filteredPosts.map(post => `
        <tr>
            <td>${post.id}</td>
            <td style="max-width:300px">${escapeHtml(post.contenu.substring(0, 70))}…</td>
            <td style="text-align:center">${post.image ? '<i class="fas fa-check-circle" style="color:#10b981"></i>' : '<i class="fas fa-times-circle" style="color:#94a3b8"></i>'}</td>
            <td style="text-align:center;font-weight:600;">❤️ ${post.likes}</td>
            <td style="text-align:center"><a href="../Frontoffice/listrep.php?id_post=${post.id}" class="btn btn-info btn-sm"><i class="fas fa-comments"></i> ${postReponses[post.id] || 0}</a></td>
            <td>${new Date(post.date).toLocaleDateString('fr-FR')}</td>
            <td class="table-actions">
                <a href="showpost.php?id=${post.id}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                <a href="deletepost.php?id=${post.id}" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
            </td>
        </td>
    `).join('');
}

function filterPostTable() {
    renderPostTable([...allDashboardPosts]);
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Option active au chargement
document.addEventListener('DOMContentLoaded', function() {
    renderPostTable([...allDashboardPosts]);
    document.querySelectorAll('.sort-option-dashboard').forEach(opt => {
        if (opt.getAttribute('data-sort') === 'date_desc') {
            opt.classList.add('active');
        }
    });
});
</script>
</body>
</html>