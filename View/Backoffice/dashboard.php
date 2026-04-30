<?php
session_start();
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';
include '../../Controller/AvisController.php';

$postC = new PostController();
$posts = $postC->listPosts();
$totalPosts = count($posts);

// Récupérer les avis

$avisC = new AvisController();


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
/* ===== CERCLE CAMEMBERT (donut) ANIMÉ ===== */
.stats-single {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 50px;
}

.stat-main-circle {
    text-align: center;
    background: white;
    padding: 30px;
    border-radius: 40px;
    box-shadow: var(--shadow);
    transition: all 0.3s;
}

.stat-main-circle:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.pie-chart {
    width: 220px;
    height: 220px;
    margin: 0 auto;
}

.pie-green {
    transition: all 1.2s ease-out;
}
.pie-red {
    transition: all 1.2s ease-out;
}

.pie-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.pie-segment {
    transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.pie-total {
    dominant-baseline: middle;
}

.pie-label {
    dominant-baseline: middle;
}

.pie-legend {
    margin-top: 25px;
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
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

/* ===== CAMEMBERT (TOTAL POSTS) ===== */
body.dark-mode .pie-total {
    fill: white !important;
}

body.dark-mode .pie-label {
    fill: #a0a0c0 !important;
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
            <div class="nav-item has-sub open">
                <a onclick="toggleSubMenu(this)">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span>Tableau de bord</span>
                    <i class="fas fa-chevron-down nav-arrow"></i>
                </a>
                <div class="sub-menu">
                    <a href="#stats">📊 Statistiques</a>
                    <a href="#search">🔍 Rechercher</a>
                    <a href="#recent">📋 Tous les posts</a>
                </div>
            </div>
            <div class="nav-item"><a href="addpost.php"><i class="fas fa-plus-circle nav-icon"></i><span>Nouveau post</span></a></div>
            <div class="nav-section-label">Autres</div>
            <div class="nav-item"><a href="../Frontoffice/index.html"><i class="fas fa-globe nav-icon"></i><span>Voir le site</span></a></div>
        </nav>
        <div class="sidebar-footer">
            <a href="#" class="btn btn-outline-white btn-sm" style="width:100%; justify-content:center;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
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

        <div class="page-content" >
       <!-- Statistiques : camembert animé (posts avec/sans image) -->
<div id="stats" class="stats-single">
    <div class="stat-main-circle">
       <div class="pie-chart" id="pieChart">
    <svg viewBox="0 0 100 100" class="pie-svg" style="transform: rotate(-90deg);">
        <!-- Cercle de fond -->
        <circle cx="50" cy="50" r="45" fill="none" stroke="#e2e8f0" stroke-width="10"/>
        
        <!-- Partie ROUGE (posts sans image) -->
        <circle class="pie-segment pie-red" 
                cx="50" cy="50" r="45" 
                fill="none" stroke="#ef4444" 
                stroke-width="10" 
                stroke-dasharray="283" 
                stroke-dashoffset="283"
                stroke-linecap="round"/>
        
        <!-- Partie VERTE (posts avec image) -->
        <circle class="pie-segment pie-green" 
                cx="50" cy="50" r="45" 
                fill="none" stroke="#10b981" 
                stroke-width="10" 
                stroke-dasharray="283" 
                stroke-dashoffset="283"
                stroke-linecap="round"/>
        
        <!-- TEXTE (non roté) -->
        <g style="transform: rotate(90deg); transform-origin: 50px 50px;">
            <text x="50" y="45" text-anchor="middle" class="pie-total" font-size="22" font-weight="800"><?= $totalPosts ?></text>
<text x="50" y="62" text-anchor="middle" class="pie-label" font-size="8">TOTAL POSTS</text>
        </g>
    </svg>
</div>
        
        <div class="pie-legend">
            <div class="legend-item">
                <span class="legend-color green"></span>
                <span class="legend-text"><?= $postsWithImages ?> posts avec image</span>
                <span class="legend-percent"><?= round(($postsWithImages/$totalPosts)*100) ?>%</span>
            </div>
            <div class="legend-item">
                <span class="legend-color red"></span>
                <span class="legend-text"><?= ($totalPosts - $postsWithImages) ?> posts sans image</span>
                <span class="legend-percent"><?= round((($totalPosts - $postsWithImages)/$totalPosts)*100) ?>%</span>
            </div>
        </div>
    </div>
</div>

            <!-- Graphique à barres (au lieu de courbe) -->
            <div class="bar-chart-container animate-fadeLeft" style="animation-delay:0.2s">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3><i class="fas fa-chart-bar" style="color:var(--primary)"></i> Évolution des posts (12 mois)</h3>
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
           

            <!-- Barre de recherche avec bouton "Voir" -->
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
                
                <!-- Champ caché pour l'ID du post sélectionné -->
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
                 <!-- Barre de tri -->
<div>
    <span><i class="fas fa-sort"></i> Filtre :</span>
    <form method="GET" action="" id="orderForm">
        <select name="order" class="sort-select" onchange="this.form.submit()">
            <option value="date_desc" <?= ($orderBy ?? 'date_desc') == 'date_desc' ? 'selected' : '' ?>>📅 Date décroissante</option>
            <option value="date_asc" <?= ($orderBy ?? '') == 'date_asc' ? 'selected' : '' ?>>📅 Date croissante</option>
            <option value="length_desc" <?= ($orderBy ?? '') == 'length_desc' ? 'selected' : '' ?>>📄 Plus long </option>
            <option value="length_asc" <?= ($orderBy ?? '') == 'length_asc' ? 'selected' : '' ?>>📄 Plus court </option>
        </select>
    </form>
</div>
            </div>

            <!-- Tableau de tous les posts -->
            <div id="recent" class="card" style="padding:0; overflow:hidden; border-radius: 28px;">
                <div style="padding:20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                    <h3><i class="fas fa-table-list"></i> Tous les posts</h3>
                    <a href="../Frontoffice/postlist.php" class="btn btn-outline btn-sm">Gérer <i class="fas fa-arrow-right"></i></a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table">
                        <thead><tr><th>ID</th><th>Contenu</th><th>Image</th><th>Date</th><th>Commentaires</th><th>Actions</th></tr></thead>
                        <th>ID</th>
                        <tbody>
                            <?php if (empty($latestPosts)): ?>
                                <tr><td colspan="5" style="text-align:center">Aucun post</td></tr>
                            <?php else: ?>
                                <?php foreach ($latestPosts as $post): ?>
                                    <tr>
                                        <td><?= $post->getIdPost() ?></td>
                                        <td style="max-width:400px"><?= htmlspecialchars(substr($post->getContenu(),0,80)) ?>…</td>
                                        <td style="text-align:center"><?= !empty($post->getImage()) ? '<i class="fas fa-check-circle" style="color:var(--accent)"></i>' : '<i class="fas fa-times-circle" style="color:var(--gray-light)"></i>' ?></td>
                                        <td><?= date('d/m/Y', strtotime($post->getDatePost())) ?></td>
                                        <td class="table-actions">
    <a href="../Frontoffice/listrep.php?id_post=<?= $post->getIdPost() ?>" 
       class="btn btn-info btn-sm" 
       title="Voir les réponses de ce post">
        <i class="fas fa-comments"></i> Voir réponses
    </a>
</td>
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
            </div>
        </div>
       
    </main>
</div>




<!-- Toast de notification -->
<div id="toastMsg" class="toast-notify"><i class="fas fa-check-circle"></i> <span id="toastText"></span></div>

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

    // Afficher une notification si un message est passé en GET
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        let msg = '';
        if (urlParams.get('success') === 'deleted') msg = 'Post supprimé avec succès !';
        else msg = 'Action réussie !';
        const toast = document.getElementById('toastMsg');
        document.getElementById('toastText').innerText = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
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

    // Sidebar toggle (bouton ☰)
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
    }

    // Sous-menus déroulants
    function toggleSubMenu(element) {
        const parent = element.closest('.has-sub');
        parent.classList.toggle('open');
        const subMenu = parent.querySelector('.sub-menu');
        if (subMenu) subMenu.classList.toggle('open');
    }

  // ===== ANIMATION DU CAMEMBERT =====
// Animation + clic du camembert
const total = <?= $totalPosts ?>;
const withImg = <?= $postsWithImages ?>;
const withoutImg = total - withImg;
const percentGreen = total ? (withImg / total) : 0;      // Partie VERTE
const percentRed = total ? (withoutImg / total) : 0;    // Partie ROUGE

const circumference = 2 * Math.PI * 45; // ≈ 283

const greenCircle = document.querySelector('.pie-green');
const redCircle = document.querySelector('.pie-red');

if (greenCircle && redCircle) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Le vert est par-dessus, le rouge en dessous
                greenCircle.style.strokeDashoffset = circumference * (1 - percentGreen);
                redCircle.style.strokeDashoffset = circumference * (1 - (percentGreen + percentRed));
                observer.disconnect();
            }
        });
    }, { threshold: 0.3 });
    observer.observe(document.querySelector('.stat-main-circle'));
}
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
</body>
</html>