<?php
session_start();
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';
include '../../Controller/ReponseController.php';
include '../../Controller/AvisController.php';

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
    $tags = $postC->extractHashtags($p->getContenu());
    foreach ($tags as $tag) {
        $allHashtags[$tag] = ($allHashtags[$tag] ?? 0) + 1;
    }
}
arsort($allHashtags);
$topHashtags = array_slice($allHashtags, 0, 10, true);
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
        /* ---------- ANIMATIONS GLOBALES ---------- */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: var(--shadow); }
            50% { transform: scale(1.02); box-shadow: var(--shadow-lg); }
            100% { transform: scale(1); box-shadow: var(--shadow); }
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        /* Animation pour les barres du graphique */
        @keyframes barGrow {
            from { height: 0; opacity: 0; }
            to { height: attr(data-height); opacity: 1; }
        }

        .animate-fadeUp {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }
        .animate-fadeLeft {
            animation: fadeInLeft 0.6s ease forwards;
            opacity: 0;
        }
        /* Barre de recherche améliorée */
        .search-wrapper {
            position: relative;
            transition: all 0.3s;
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
            transition: color 0.2s;
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
            box-shadow: 0 0 0 4px rgba(14,165,233,0.15);
            outline: none;
        }
        .search-wrapper:hover i {
            color: var(--primary);
        }
        .btn-go {
            padding: 14px 24px;
            border-radius: 60px;
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-go:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Cartes liens rapides */
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

        /* Graphique à barres */
        .bar-chart-container {
            background: var(--white);
            border-radius: 24px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            transition: transform 0.3s;
        }
        .bar-chart-container:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
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

        /* Lignes du tableau animées */
        .table tbody tr {
            transition: all 0.2s;
        }
        .table tbody tr:hover {
            background: rgba(14,165,233,0.05);
            transform: translateX(4px);
        }

        /* Boutons d'action */
        .btn-sm {
            transition: all 0.2s;
        }
        .btn-sm:hover {
            transform: translateY(-2px);
        }

        /* Notification toast */
        .toast-notify {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--dark);
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            font-size: 0.9rem;
        }
        .toast-notify.show {
            transform: translateX(0);
        }


/* Dark mode */
body.dark-mode .legend-label {
    color: #e2e8f0;
}

body.dark-mode .legend-count {
    color: white;
}

body.dark-mode .legend-triangle:hover {
    background: rgba(255,255,255,0.05);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
}

.legend-color {
    width: 14px;
    height: 14px;
    border-radius: 50%;
}

.legend-color.green { background: #10b981; box-shadow: 0 0 5px rgba(16,185,129,0.5); }
.legend-color.red { background: #ef4444; box-shadow: 0 0 5px rgba(239,68,68,0.5); }

.legend-text {
    color: var(--text-muted);
}

.legend-percent {
    font-weight: 600;
    color: var(--dark);
}
.pie-tooltip {
    position: absolute;
    background: #0f172a;
    color: white;
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 0.8rem;
    pointer-events: none;
    opacity: 0;
    transform: translate(-50%, -120%);
    transition: all 0.2s ease;
    white-space: nowrap;
    z-index: 1000;
}

.pie-tooltip.show {
    opacity: 1;
}

.pie-chart {
    position: relative;
}
/* Animation de pulsation au survol */
.stat-main-circle:hover .pie-chart {
    animation: pulseGlow 1.5s infinite;
}

@keyframes pulseGlow {
    0% { filter: drop-shadow(0 0 0 rgba(14,165,233,0)); }
    50% { filter: drop-shadow(0 0 15px rgba(14,165,233,0.4)); }
    100% { filter: drop-shadow(0 0 0 rgba(14,165,233,0)); }
}

/* ===== BARRE DE TRI ===== */
.sort-bar {
    background: white;
    padding: 15px 20px;
    border-radius: 28px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
}
.sort-select {
    padding: 8px 15px;
    border-radius: 40px;
    border: 1px solid var(--border);
}

/* ===== SIDEBAR TOGGLE ===== */
@media (max-width: 1024px) {
    .sidebar {
        transform: translateX(-260px);
        transition: transform 0.3s ease;
    }
    .sidebar.open {
        transform: translateX(0);
    }
    .sidebar-toggle {
        display: flex;
    }
}
.sidebar-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 1.3rem;
    cursor: pointer;
    margin-right: 15px;
}
/* ===== DARK MODE ASCLEPIA - SEULEMENT LE FOND DE LA PAGE ===== */

/* ===== DARK MODE – VERSION FINALE COMPLÈTE ===== */

body.dark-mode {
    background-color: #1a1a2e !important;
}

/* Sidebar */
body.dark-mode .sidebar {
    background: #0f0f1a !important;
    border-right: 1px solid #2d2d44 !important;
}

body.dark-mode .sidebar-brand,
body.dark-mode .sidebar-user {
    background: #0f0f1a !important;
    border-bottom-color: #2d2d44 !important;
}

body.dark-mode .sidebar-title,
body.dark-mode .sidebar-user .name {
    color: white !important;
}

body.dark-mode .sidebar-user .role {
    color: #a0a0c0 !important;
}

body.dark-mode .sidebar-nav a {
    color: #c0c0d0 !important;
}

body.dark-mode .sidebar-nav a:hover,
body.dark-mode .sidebar-nav a.active {
    background: #1a1a2e !important;
    color: white !important;
}

/* Conteneurs */
body.dark-mode .main-content,
body.dark-mode .page-content {
    background-color: #1a1a2e !important;
}

body.dark-mode .card,
body.dark-mode .stat-card,
body.dark-mode .stat-main-circle,
body.dark-mode .quick-card,
body.dark-mode .bar-chart-container,
body.dark-mode .table-container,
body.dark-mode .sort-bar,
body.dark-mode .search-wrapper,
body.dark-mode .search-wrapper input {
    background: #16213e !important;
    border-color: #2d2d44 !important;
}

/* ===== TRIER PAR (label et icône) ===== */
body.dark-mode .sort-bar span:first-child,
body.dark-mode .sort-bar i.fa-sort,
body.dark-mode .sort-bar .fa-sort + span {
    color: white !important;
}

/* ===== COMPTEUR DE POSTS ===== */
body.dark-mode .sort-bar div:last-child,
body.dark-mode .sort-bar div:last-child i,
body.dark-mode .sort-bar div:last-child span {
    color: white !important;
}

/* ===== CHIFFRES DES STATS ===== */
body.dark-mode .stat-number,
body.dark-mode .counter,
body.dark-mode .big-counter,
body.dark-mode .bar-value {
    color: white !important;
}


/* Liens rapides */
body.dark-mode .quick-card span {
    color: white !important;
}

body.dark-mode .quick-card i {
    color: #0ea5e9 !important;
}

body.dark-mode .quick-card:hover {
    background: #1a2a4a !important;
    border-color: #0ea5e9 !important;
}

/* Tableau */
body.dark-mode .table th {
    background: #0f0f1f !important;
    color: #e0e0e0 !important;
}

body.dark-mode .table td {
    border-bottom-color: #2d2d44 !important;
    color: #c0c0d0 !important;
}

/* Textes généraux */
body.dark-mode h1, body.dark-mode h2, body.dark-mode h3, body.dark-mode h4,
body.dark-mode .page-title, body.dark-mode .post-author,
body.dark-mode .breadcrumb span, body.dark-mode .stat-info h3, body.dark-mode .card h3,
body.dark-mode .post-meta strong, body.dark-mode .post-meta .post-author {
    color: #ffffff !important;
}

body.dark-mode .text-muted, body.dark-mode .post-stat, body.dark-mode .section-desc,
body.dark-mode .bar-label, body.dark-mode small, body.dark-mode .legend-text,
body.dark-mode .result-count {
    color: #a0a0c0 !important;
}

/* Recherche */
body.dark-mode .search-wrapper input {
    color: white !important;
    background: #16213e !important;
}

body.dark-mode .search-wrapper input::placeholder {
    color: #a0a0c0 !important;
}

body.dark-mode .search-wrapper i {
    color: #a0a0c0 !important;
}

body.dark-mode .post-result {
    background: #16213e !important;
    border-bottom-color: #2d2d44 !important;
}

body.dark-mode .post-result h4,
body.dark-mode .post-result p,
body.dark-mode .post-result small {
    color: #ffffff !important;
}

/* Topbar reste blanche */
body.dark-mode .topbar {
    background: white !important;
    border-bottom: 1px solid #e2e8f0 !important;
}

body.dark-mode .topbar .page-title,
body.dark-mode .topbar .breadcrumb span,
body.dark-mode .topbar-user .name {
    color: #1e293b !important;
}

/* Boutons */
body.dark-mode .btn-primary {
    background: #0ea5e9 !important;
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

/* Footer */
body.dark-mode .footer {
    background: #0f0f1a !important;
    border-top-color: #2d2d44 !important;
    color: #a0a0c0 !important;
}

/* Bouton toggle */
.theme-toggle {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--primary);
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

/* ===== PAGINATION STYLES - CERCLES MODERNES ===== */
.pagination-btn, .pagination-num {
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pagination-btn:hover, .pagination-num:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Dark mode */
body.dark-mode .pagination-btn,
body.dark-mode .pagination-num {
    background: #0ea5e9;
}

body.dark-mode .pagination-disabled {
    background: #334155 !important;
}
/* ===== STATISTIQUES MODERNES ===== */
.stats-modern {
    margin-bottom: 40px;
}

/* Grille KPI */
.stats-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.kpi-card {
    background: white;
    border-radius: 24px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: all 0.3s;
    border: 1px solid #e2e8f0;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.kpi-icon {
    width: 50px;
    height: 50px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
}

.kpi-icon.blue { background: linear-gradient(135deg, #0ea5e9, #3b82f6); color: white; }
.kpi-icon.green { background: linear-gradient(135deg, #10b981, #059669); color: white; }
.kpi-icon.purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; }
.kpi-icon.orange { background: linear-gradient(135deg, #f59e0b, #ea580c); color: white; }

.kpi-value {
    font-size: 2rem;
    font-weight: 800;
    color: #1e293b;
    line-height: 1;
}

.kpi-label {
    font-size: 0.8rem;
    color: #64748b;
    margin: 5px 0;
}

.kpi-trend {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 20px;
    display: inline-block;
}

.kpi-trend.up {
    background: #d1fae5;
    color: #059669;
}

.kpi-trend.down {
    background: #fee2e2;
    color: #dc2626;
}

/* Deux colonnes */
.stats-two-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    margin-bottom: 30px;
}

/* Cartes stats */
.stats-card {
    background: white;
    border-radius: 24px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
    transition: all 0.3s;
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
}

.stats-card.full-width {
    grid-column: span 2;
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
    color: #1e293b;
}

.stats-card-header h3 i {
    margin-right: 8px;
    color: #0ea5e9;
}

.stats-badge {
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #64748b;
}
/* ===== STATISTIQUES STYLE CARTES ===== */
.stats-card {
    background: white;
    border-radius: 24px;
    padding: 24px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
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
    color: #1e293b;
}

.stats-card-header h3 i {
    margin-right: 8px;
    color: #0ea5e9;
}

.stats-badge {
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #64748b;
}

/* ===== CAMEMBERT GRAND À GAUCHE ===== */
.stats-card {
    background: white;
    border-radius: 24px;
    padding: 24px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
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
    color: #1e293b;
}

.stats-card-header h3 i {
    margin-right: 8px;
    color: #0ea5e9;
}

.stats-badge {
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #64748b;
}

/* Layout 2 colonnes : camembert à gauche, légende à droite */
.pie-chart-layout {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;
    flex-wrap: wrap;
}

/* Camembert à gauche - GRAND */
.pie-chart-left {
    position: relative;
    flex-shrink: 0;
}

.pie-chart-left canvas {
    display: block;
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
    color: #1e293b;
    line-height: 1;
}

.pie-center-text .pie-label {
    font-size: 0.6rem;
    color: #64748b;
}

/* Légende à droite */
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
    display: inline-block;
    flex-shrink: 0;
}

.stat-label {
    flex: 1;
    font-size: 0.9rem;
    font-weight: 500;
    color: #1e293b;
}

.stat-number {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    min-width: 35px;
    text-align: right;
}

.stat-percent {
    font-size: 0.85rem;
    color: #64748b;
    min-width: 60px;
    text-align: right;
}

/* Dark mode */
body.dark-mode .stats-card {
    background: #16213e;
    border-color: #2d2d44;
}

body.dark-mode .stats-card-header {
    border-bottom-color: #2d2d44;
}

body.dark-mode .stats-card-header h3 {
    color: white;
}

body.dark-mode .stats-badge {
    background: #1e293b;
    color: #94a3b8;
}

body.dark-mode .stat-item {
    background: #0f0f1a;
}

body.dark-mode .stat-item:hover {
    background: #1a1a2e;
}

body.dark-mode .stat-label {
    color: #e2e8f0;
}

body.dark-mode .stat-number {
    color: white;
}

body.dark-mode .stat-percent {
    color: #94a3b8;
}

body.dark-mode .pie-center-text {
    background: #16213e;
}

body.dark-mode .pie-center-text .pie-total {
    color: white;
}

body.dark-mode .pie-center-text .pie-label {
    color: #94a3b8;
}

/* Responsive */
@media (max-width: 768px) {
    .pie-chart-layout {
        flex-direction: column;
        text-align: center;
    }
    
    .stats-list-right {
        width: 100%;
    }
}
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar -->
   <aside class="sidebar">
    <a href="dashboard.php" class="sidebar-brand">
        <div class="sidebar-logo">🏥</div>
        <div class="sidebar-title">ASCL<span>EPIA</span></div>
    </a>

    <div class="sidebar-user">
        <div class="user-avatar" id="adminAvatar">
            <?php echo strtoupper(substr($adminNom ?? 'A', 0, 2)); ?>
        </div>
        <div class="user-info">
            <div class="name" id="adminName">
                <?php echo htmlspecialchars($adminNom ?? 'Administrateur'); ?>
            </div>
            <div class="role">Super Admin</div>
        </div>
    </div>

    <nav class="sidebar-nav">

        <div class="nav-section-label">Menu Principal</div>

        <div class="nav-item">
            <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'class="active"' : ''; ?>>
                <i class="fas fa-tachometer-alt nav-icon"></i>
                <span>Tableau de bord</span>
            </a>
        </div>

        <div class="nav-section-label">Gestion</div>

        <div class="nav-item">
            <a href="../backoffice/assurancelist.php" <?php echo basename($_SERVER['PHP_SELF']) === 'assurancelist.php' ? 'class="active"' : ''; ?>>
                <i class="fa-solid fa-shield-halved nav-icon"></i>
                <span>Assurances</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="../backoffice/contrat/contratList.php" <?php echo basename($_SERVER['PHP_SELF']) === 'contratList.php' ? 'class="active"' : ''; ?>>
                <i class="fa-solid fa-file-contract nav-icon"></i>
                <span>Contrats</span>
            </a>
        </div>
         <div class="nav-item">
            <a href="../backoffice/list_consultation.php" <?php echo basename($_SERVER['PHP_SELF']) === 'list_consultation.php' ? 'class="active"' : ''; ?>>
                <i class="fa-solid fa-file-contract nav-icon"></i>
                <span>consultations</span>
            </a>
             <a href="../backoffice/list_ordonnance.php" <?php echo basename($_SERVER['PHP_SELF']) === 'list_ordonnance.php' ? 'class="active"' : ''; ?>>
                <i class="fa-solid fa-file-contract nav-icon"></i>
                <span>ordonnances</span>
            </a>

        </div>

         <div class="nav-item has-sub">
    <a onclick="toggleSubMenu(this)">
        <i class="fas fa-comments nav-icon"></i>
        <span>Forum</span>
        <i class="fas fa-chevron-right nav-arrow"></i>
    </a>
    <div class="sub-menu">
        <a href="dashboardf.php">📊 Dashboard Forum</a>
        <a href="../Frontoffice/postlist.php">📝 Tous les posts</a>
    </div>
</div>

        <div class="nav-section-label">Configuration</div>

        <div class="nav-item">
            <a href="../front/indexp.php">
                <i class="fas fa-globe nav-icon"></i>
                <span>Voir le site</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="loginadmin.html">
                <i class="fas fa-sign-out-alt nav-icon"></i>
                <span>Déconnexion</span>
            </a>
        </div>

    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-version">Version 1.0</div>
    </div>
</aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <div><div class="page-title">Tableau de bord</div><div class="breadcrumb"><span>Accueil</span><span>/</span><span>Dashboard</span></div></div>
            </div>
            <div class="topbar-right"><div class="topbar-user"><i class="fas fa-user-circle" style="font-size:1.5rem;"></i><div><div class="name">Admin</div><div class="role">Administrateur</div></div></div></div>
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

    <!-- Barre de recherche -->
    <div id="search" class="card" style="padding: 24px; margin-bottom: 30px; border-radius: 32px;">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-search"></i> Rechercher un post</h3>
        <form method="GET" action="" id="searchForm">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" name="ch" id="searchInput" placeholder="Rechercher par mot-clé..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="button" class="btn-go" id="goToPostBtn">
                    <i class="fas fa-arrow-right"></i> Voir
                </button>
            </div>
        </form>
        
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
        <div style="padding:20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <h3><i class="fas fa-table-list"></i> Tous les posts</h3>
            <div style="display:flex; gap:10px; align-items:center;">
                <div>
                    <span><i class="fas fa-sort"></i> Filtre :</span>
                    <form method="GET" action="" id="orderForm" style="display:inline-block;">
                        <select name="order" class="sort-select" onchange="this.form.submit()">
                            <option value="date_desc" <?= ($orderBy ?? 'date_desc') == 'date_desc' ? 'selected' : '' ?>>📅 Date décroissante</option>
                            <option value="date_asc" <?= ($orderBy ?? '') == 'date_asc' ? 'selected' : '' ?>>📅 Date croissante</option>
                            <option value="length_desc" <?= ($orderBy ?? '') == 'length_desc' ? 'selected' : '' ?>>📄 Plus long</option>
                            <option value="length_asc" <?= ($orderBy ?? '') == 'length_asc' ? 'selected' : '' ?>>📄 Plus court</option>
                        </select>
                    </form>
                </div>
                <a href="../Frontoffice/postlist.php" class="btn btn-outline btn-sm">Gérer <i class="fas fa-arrow-right"></i></a>
                <a href="export_csv.php" class="btn btn-outline btn-sm" style="border-color:#10b981;color:#10b981;">
                    <i class="fas fa-file-csv"></i> Exporter CSV
                </a>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr><th>ID</th><th>Contenu</th><th>Média</th><th>Likes</th><th>Réponses</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $postsPerPage = 10;
                    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $totalPostsCount = count($latestPosts);
                    $totalPages = ceil($totalPostsCount / $postsPerPage);
                    if ($currentPage < 1) $currentPage = 1;
                    if ($currentPage > $totalPages && $totalPages > 0) $currentPage = $totalPages;
                    $offset = ($currentPage - 1) * $postsPerPage;
                    $postsToShow = array_slice($latestPosts, $offset, $postsPerPage);
                    ?>
                    
                    <?php if (empty($postsToShow)): ?>
                        <tr><td colspan="7" style="text-align:center">Aucun post</td></tr>
                    <?php else: ?>
                        <?php foreach ($postsToShow as $post): ?>
                            <tr>
                                <td><?= $post->getIdPost() ?></td>
                                <td style="max-width:300px"><?= htmlspecialchars(substr($post->getContenu(),0,70)) ?>…</td>
                                <td style="text-align:center"><?= !empty($post->getImage()) ? '<i class="fas fa-check-circle" style="color:var(--accent)"></i>' : '<i class="fas fa-times-circle" style="color:var(--gray-light)"></i>' ?></td>
                                <td style="text-align:center;font-weight:600;">❤️ <?= $post->getLikes() ?></td>
                                <td><a href="../Frontoffice/listrep.php?id_post=<?= $post->getIdPost() ?>" class="btn btn-info btn-sm"><i class="fas fa-comments"></i> Voir</a></td>
                                <td><?= date('d/m/Y', strtotime($post->getDatePost())) ?></td>
                                <td class="table-actions">
                                    <a href="showpost.php?id=<?= $post->getIdPost() ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                                    <a href="deletepost.php?id=<?= $post->getIdPost() ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
</body>
</html>