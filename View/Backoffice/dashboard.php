<?php
session_start();

// Forcer l'admin directement (pour les tests)
// À enlever quand vous aurez un vrai système d'authentification
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = 'Administrateur';

include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';

$postC = new PostController();

// Récupérer tous les posts
$posts = $postC->listPosts();

// Trier par date décroissante (du plus récent au plus ancien)
usort($posts, function($a, $b) {
    return strtotime($b->getDatePost()) - strtotime($a->getDatePost());
});

// Statistiques
$totalPosts = count($posts);
$totalImages = 0;
$totalMois = 0;
$moisActuel = date('m');
$anneeActuelle = date('Y');

foreach ($posts as $post) {
    if (!empty($post->getImage())) {
        $totalImages++;
    }
    $datePost = new DateTime($post->getDatePost());
    if ($datePost->format('m') == $moisActuel && $datePost->format('Y') == $anneeActuelle) {
        $totalMois++;
    }
}

// Derniers posts (5 derniers)
$latestPosts = $posts;

// Données pour le graphique (12 derniers mois)
$months = [];
$statsData = [];
for ($i = 11; $i >= 0; $i--) {
    $mois = date('m', strtotime("-$i months"));
    $annee = date('Y', strtotime("-$i months"));
    $months[] = date('M', strtotime("-$i months"));
    
    $count = 0;
    foreach ($posts as $post) {
        $datePost = new DateTime($post->getDatePost());
        if ($datePost->format('m') == $mois && $datePost->format('Y') == $annee) {
            $count++;
        }
    }
    $statsData[] = $count;
}
$maxValue = max($statsData) ?: 1;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASCLEPIA — Tableau de bord Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
    <link rel="stylesheet" href="../assets/css/stat.css">
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
                <div class="name"><?php echo $_SESSION['user_name']; ?></div>
                <div class="role">Administrateur</div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Principal</div>
            
            <div class="nav-item">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="addpost.php">
                    <i class="fas fa-plus-circle nav-icon"></i>
                    <span>Nouveau post</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="../Frontoffice/postList.php">
                    <i class="fas fa-comments nav-icon"></i>
                    <span>Tous les posts</span>
                </a>
            </div>
            
            <div class="nav-section-label">Configuration</div>
            
            <div class="nav-item">
                <a href="../frontoffice/index.html">
                    <i class="fas fa-globe nav-icon"></i>
                    <span>Voir le site</span>
                </a>
            </div>
        </nav>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="btn btn-outline-white btn-sm" style="width: 100%; justify-content: center;">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
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
                    <div class="page-title">Dashboard</div>
                    <div class="breadcrumb">
                        <span>Accueil</span>
                        <span>/</span>
                        <span>Tableau de bord</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-user">
                    <i class="fas fa-user-circle" style="font-size: 1.5rem;"></i>
                    <div>
                        <div class="name">Admin</div>
                        <div class="role">Administrateur</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="page-content">
            <!-- Bannière de bienvenue -->
            <div class="welcome-banner">
                <div>
                    <h1>Bonjour, Administrateur ! 👋</h1>
                    <p>Bienvenue sur votre tableau de bord. Gérez tous les posts depuis cet espace.</p>
                </div>
                <a href="addpost.php" class="btn btn-white" style="background: white; color: var(--primary);">
                    <i class="fas fa-plus"></i> Nouveau post
                </a>
            </div>
            
            <!-- Statistiques -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 40px;">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total des posts</h3>
                        <div class="stat-number"><?php echo $totalPosts; ?></div>
                    </div>
                    <div class="stat-icon blue">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Posts ce mois</h3>
                        <div class="stat-number"><?php echo $totalMois; ?></div>
                    </div>
                    <div class="stat-icon green">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Avec image</h3>
                        <div class="stat-number"><?php echo $totalImages; ?></div>
                    </div>
                    <div class="stat-icon orange">
                        <i class="fas fa-image"></i>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Moyenne/mois</h3>
                        <div class="stat-number"><?php echo $totalPosts > 0 ? round($totalPosts / 12) : 0; ?></div>
                    </div>
                    <div class="stat-icon purple">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            
            <!-- Graphique -->
            <div class="card" style="padding: 24px; margin-bottom: 40px;">
                <h3 style="font-size: 1rem; margin-bottom: 20px;">
                    <i class="fas fa-chart-bar" style="color: var(--primary);"></i> Évolution des posts (12 derniers mois)
                </h3>
                <div class="bar-chart">
                    <?php foreach ($months as $index => $month): 
                        $height = ($statsData[$index] / $maxValue) * 150;
                    ?>
                        <div class="bar-item">
                            <div class="bar-value" style="font-size: 0.7rem; font-weight: 600; color: var(--primary);"><?php echo $statsData[$index]; ?></div>
                            <div class="bar" style="height: <?php echo $height; ?>px;"></div>
                            <div class="bar-label" style="font-size: 0.7rem; color: var(--text-muted);"><?php echo $month; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Derniers posts -->
            <div class="card" style="padding: 0; overflow: hidden;">
                <div style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 1rem;">
                        <i class="fas fa-clock"></i> Derniers posts
                    </h3>
                   
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Contenu</th>
                                <th>Image</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </thead>
                            <tbody>
                                <?php if (empty($latestPosts)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px;">
                                            <i class="fas fa-inbox" style="font-size: 2rem; color: var(--gray-light);"></i>
                                            <p>Aucun post trouvé</p>
                                            <a href="addpost.php" class="btn btn-primary btn-sm">Créer le premier post</a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($latestPosts as $post): ?>
                                        <tr>
                                            <td style="width: 60px;"><?php echo $post->getIdPost(); ?></td>
                                            <td style="max-width: 400px;">
                                                <?php echo htmlspecialchars(substr($post->getContenu(), 0, 80)); ?>
                                                <?php if (strlen($post->getContenu()) > 80): ?>...<?php endif; ?>
                                            </td>
                                            <td style="width: 80px; text-align: center;">
                                                <?php if (!empty($post->getImage())): ?>
                                                    <i class="fas fa-check-circle" style="color: var(--accent); font-size: 1.2rem;"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle" style="color: var(--gray-light); font-size: 1.2rem;"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td style="white-space: nowrap;">
                                                <?php echo (new DateTime($post->getDatePost()))->format('d/m/Y H:i'); ?>
                                            </td>
                                            <td class="table-actions">
                                                <a href="../Frontoffice/postList.php?id=<?php echo $post->getIdPost(); ?>" class="btn btn-outline btn-sm" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="updatepost.php?id=<?php echo $post->getIdPost(); ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <a href="deletepost.php?id=<?php echo $post->getIdPost(); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce post ?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
<script src="../Frontoffice/addpost.js"></script>

</body>
</html>