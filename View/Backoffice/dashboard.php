<?php
session_start();
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';

$postC = new PostController();
$posts = $postC->listPosts();
$totalPosts = count($posts);

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
    <!-- Chart.js pour le graphique -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .animate-fadeUp {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }
        .animate-fadeLeft {
            animation: fadeInLeft 0.6s ease forwards;
            opacity: 0;
        }

        /* Cartes statistiques */
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

        /* Barre de recherche améliorée */
        .search-wrapper {
            position: relative;
            transition: all 0.3s;
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
            width: 100%;
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

        /* Cartes liens rapides (animation pulsation continue + hover) */
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

        /* Graphique */
        .chart-container {
            background: var(--white);
            border-radius: 24px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            transition: transform 0.3s;
        }
        .chart-container:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
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
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar identique à votre version -->
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
                    <a href="addpost.php">➕ Ajouter un post</a>
                    <a href="#search">🔍 Rechercher</a>
                    <a href="#recent">📋 Tous les posts</a>
                </div>
            </div>
            <div class="nav-item"><a href="addpost.php"><i class="fas fa-plus-circle nav-icon"></i><span>Nouveau post</span></a></div>
            <div class="nav-item"><a href="showpost.php"><i class="fas fa-eye nav-icon"></i><span>Tous les posts</span></a></div>
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

        <div class="page-content">
            <!-- Statistiques en cercles (animées) -->
            <div id="stats" class="stats-grid">
                <div class="stat-circle animate-fadeUp" style="animation-delay:0.1s">
                    <div class="circle blue"><?= $totalPosts ?></div>
                    <h3><?= $totalPosts ?></h3>
                    <p>Total posts</p>
                    <small>📈 +12% ce mois</small>
                </div>
                <div class="stat-circle animate-fadeUp" style="animation-delay:0.2s">
                    <div class="circle green"><?= $postsWithImages ?></div>
                    <h3><?= $postsWithImages ?></h3>
                    <p>Avec image</p>
                    <small>🖼️ <?= $totalPosts ? round(($postsWithImages/$totalPosts)*100) : 0 ?>% du total</small>
                </div>
                <div class="stat-circle animate-fadeUp" style="animation-delay:0.3s">
                    <div class="circle orange"><?= date('m') ?></div>
                    <h3><?= date('m') ?></h3>
                    <p>Posts ce mois</p>
                    <small>📅 <?= date('F Y') ?></small>
                </div>
                <div class="stat-circle animate-fadeUp" style="animation-delay:0.4s">
                    <div class="circle purple"><?= count($latestPosts) ?></div>
                    <h3><?= count($latestPosts) ?></h3>
                    <p>Au total</p>
                    <small>✨ Tous les posts</small>
                </div>
            </div>

            <!-- Graphique d'évolution (Chart.js) -->
            <div class="chart-container animate-fadeLeft" style="animation-delay:0.2s">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3><i class="fas fa-chart-line" style="color:var(--primary)"></i> Évolution des posts (12 mois)</h3>
                </div>
                <canvas id="postsChart" width="400" height="200" style="max-height: 280px;"></canvas>
            </div>

            <!-- Liens rapides (2 cartes animées) -->
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
                <form method="GET" action="">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" name="ch" placeholder="Rechercher par mot-clé..." value="<?= htmlspecialchars($searchTerm) ?>">
                    </div>
                </form>
                <?php if ($searchTerm): ?>
                    <div class="result-count" style="margin-top: 20px;">
                        <i class="fas fa-chart-simple"></i> <?= count($displayPosts) ?> résultat(s) pour "<strong><?= htmlspecialchars($searchTerm) ?></strong>"
                        <a href="dashboard.php" style="float:right;">✖ Effacer</a>
                    </div>
                    <div class="search-results">
                        <?php if (count($displayPosts) > 0): ?>
                            <?php foreach ($displayPosts as $post): ?>
                                <div class="post-result">
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
                <div style="padding:20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                    <h3><i class="fas fa-table-list"></i> Tous les posts</h3>
                    <a href="../Frontoffice/postlist.php" class="btn btn-outline btn-sm">Gérer <i class="fas fa-arrow-right"></i></a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table">
                        <thead><tr><th>ID</th><th>Contenu</th><th>Image</th><th>Date</th><th>Actions</th></tr></thead>
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

<!-- Toast de notification (exemple si action via GET) -->
<div id="toastMsg" class="toast-notify"><i class="fas fa-check-circle"></i> <span id="toastText"></span></div>

<script>
    // Sidebar toggle
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

    // Graphique avec Chart.js
    const ctx = document.getElementById('postsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: 'Nombre de posts',
                data: <?= json_encode($monthlyCounts) ?>,
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14,165,233,0.05)',
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#0ea5e9',
                pointBorderColor: '#fff',
                pointHoverRadius: 6,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top' }, tooltip: { mode: 'index' } },
            scales: { y: { beginAtZero: true, grid: { color: '#e2e8f0' }, title: { display: true, text: 'Posts' } },
                      x: { grid: { display: false }, title: { display: true, text: 'Mois' } } }
        }
    });

    // Afficher une notification toast si un message est passé en GET (ex: ?success=deleted)
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
</script>
</body>
</html>