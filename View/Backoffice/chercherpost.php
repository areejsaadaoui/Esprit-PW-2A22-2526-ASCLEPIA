<?php
// View/Backoffice/chercherpost.php
session_start();
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';

$postC = new PostController();
$posts = $postC->listPosts();

$searchTerm = '';
$results = [];

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $searchTerm = trim($_GET['q']);
    $results = array_filter($posts, function($post) use ($searchTerm) {
        return stripos($post->getContenu(), $searchTerm) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ASCLEPIA - Rechercher un post</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
    <style>
        .search-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 28px;
            box-shadow: var(--shadow);
        }
        .search-box {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
        }
        .search-box input {
            flex: 1;
            padding: 14px 20px;
            border: 2px solid var(--border);
            border-radius: 60px;
            font-size: 1rem;
            transition: 0.2s;
        }
        .search-box input:focus {
            border-color: var(--primary);
            outline: none;
        }
        .result-item {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            transition: 0.2s;
        }
        .result-item:hover {
            background: var(--bg);
        }
        .result-meta {
            display: flex;
            gap: 20px;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 10px;
        }
        .badge-image {
            background: var(--accent);
            color: white;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar (identique à votre dashboard) -->
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
            <div class="nav-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt nav-icon"></i><span>Tableau de bord</span></a></div>
            <div class="nav-item"><a href="addpost.php"><i class="fas fa-plus-circle nav-icon"></i><span>Nouveau post</span></a></div>
            <div class="nav-item"><a href="showpost.php"><i class="fas fa-eye nav-icon"></i><span>Tous les posts</span></a></div>
            <div class="nav-item active"><a href="chercherpost.php"><i class="fas fa-search nav-icon"></i><span>Rechercher</span></a></div>
            <div class="nav-section-label">Autres</div>
            <div class="nav-item"><a href="../Frontoffice/index.html"><i class="fas fa-globe nav-icon"></i><span>Voir le site</span></a></div>
        </nav>
        <div class="sidebar-footer">
            <a href="#" class="btn btn-outline-white btn-sm" style="width:100%; justify-content:center;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <div>
                    <div class="page-title">Rechercher un post</div>
                    <div class="breadcrumb"><span>Accueil</span><span>/</span><span>Recherche</span></div>
                </div>
            </div>
            <div class="topbar-right"><div class="topbar-user"><i class="fas fa-user-circle" style="font-size:1.5rem;"></i><div><div class="name">Admin</div><div class="role">Administrateur</div></div></div></div>
        </div>

        <div class="page-content">
            <div class="search-container">
                <form method="GET" action="">
                    <div class="search-box">
                        <input type="text" name="q" placeholder="Mot-clé (ex: diabète, pharmacie...)" value="<?= htmlspecialchars($searchTerm) ?>">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Chercher</button>
                    </div>
                </form>

                <?php if ($searchTerm !== ''): ?>
                    <div class="result-count" style="margin-bottom:20px;">
                        <i class="fas fa-chart-simple"></i> <?= count($results) ?> résultat(s) pour "<strong><?= htmlspecialchars($searchTerm) ?></strong>"
                    </div>
                    <?php if (count($results) > 0): ?>
                        <?php foreach ($results as $post): ?>
                            <div class="result-item">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <strong><i class="fas fa-comment"></i> Post #<?= $post->getIdPost() ?></strong>
                                    <?php if (!empty($post->getImage())): ?>
                                        <span class="badge-image"><i class="fas fa-image"></i> Avec image</span>
                                    <?php endif; ?>
                                </div>
                                <p style="margin:12px 0;"><?= nl2br(htmlspecialchars(substr($post->getContenu(), 0, 200))) ?>…</p>
                                <div class="result-meta">
                                    <span><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($post->getDatePost())) ?></span>
                                    <span><i class="fas fa-user"></i> Utilisateur #<?= $post->getIdUtilisateur() ?></span>
                                </div>
                                <div style="margin-top: 15px;">
                                    <a href="showpost.php?id=<?= $post->getIdPost() ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Voir</a>
                                    <a href="updatepost.php?id=<?= $post->getIdPost() ?>" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> Modifier</a>
                                    <a href="deletepost.php?id=<?= $post->getIdPost() ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce post ?')"><i class="fas fa-trash"></i> Supprimer</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Aucun post ne contient ce terme.</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
    }
</script>

</body>
</html>