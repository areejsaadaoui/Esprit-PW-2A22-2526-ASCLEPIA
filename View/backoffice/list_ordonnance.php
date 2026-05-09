<?php
session_start();

// Vérifier si l'admin est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: loginadmin.html');
    exit();
}

// Récupérer les infos de l'admin connecté
$adminNom   = $_SESSION['user_nom']   ?? 'Administrateur';
$adminEmail = $_SESSION['user_email'] ?? '';
require_once '../../config.php';
require_once '../../Controller/OrdonnanceController.php';

$controller = new OrdonnanceController(config::getConnexion());
$ordonnances = $controller->getAllOrdonnances();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordonnances - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dark.css">
</head>
<body>
<div class="admin-wrapper">

    <aside class="sidebar">
        <a href="#" class="sidebar-brand">
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

    <?php
        $current = basename($_SERVER['PHP_SELF']);
        $current_path = $_SERVER['PHP_SELF'];

        // Helper to check if current page matches any of the given filenames
        function isActive(...$pages) {
            global $current;
            return in_array($current, $pages);
        }

        // Helper to check if sub-menu should be open (any child is active)
        function isSubActive(...$pages) {
            global $current;
            return in_array($current, $pages) ? 'open' : '';
        }
    ?>

    <div class="nav-section-label">Menu Principal</div>

    <div class="nav-item">
        <a href="../back/dashboard.php" <?= isActive('dashboard.php') ? 'class="active"' : '' ?>>
            <i class="fas fa-tachometer-alt nav-icon"></i>
            <span>Tableau de bord</span>
        </a>
    </div>

    <div class="nav-section-label">Gestion</div>

    <!-- Assurances & Contrats -->
    <div class="nav-item has-sub <?= isSubActive('assurancelist.php', 'contratList.php') ?>">
        <a onclick="toggleSubMenu(this)" <?= isActive('assurancelist.php', 'contratList.php') ? 'class="active"' : '' ?>>
            <i class="fa-solid fa-shield-halved nav-icon"></i>
            <span>Assurances &amp; Contrats</span>
            <i class="fas fa-chevron-right nav-arrow"></i>
        </a>
        <div class="sub-menu">
            <a href="../backoffice/assurancelist.php"
               <?= isActive('assurancelist.php') ? 'class="active"' : '' ?>>
               Les assurances
            </a>
            <a href="contrat/contratList.php"
               <?= isActive('contratList.php') ? 'class="active"' : '' ?>>
               Les contrats
            </a>
        </div>
    </div>

    <!-- Ordonnances & Consultations -->
    <div class="nav-item has-sub <?= isSubActive('dashboard.php', 'list_consultation.php', 'list_ordonnance.php') ?>">
        <a onclick="toggleSubMenu(this)" <?= isActive('list_consultation.php', 'list_ordonnance.php') ? 'class="active"' : '' ?>>
            <i class="fa-solid fa-file-contract nav-icon"></i>
            <span>Ordonnances &amp; Consultations</span>
            <i class="fas fa-chevron-right nav-arrow"></i>
        </a>
        <div class="sub-menu">
            <a href="../backoffice/dashboard.php"
               <?= isActive('dashboard.php') ? 'class="active"' : '' ?>>
               Toutes les consultations
            </a>
            <a href="../backoffice/list_consultation.php"
               <?= isActive('list_consultation.php') ? 'class="active"' : '' ?>>
               Les consultations
            </a>
            <a href="../backoffice/list_ordonnance.php"
               <?= isActive('list_ordonnance.php') ? 'class="active"' : '' ?>>
               Les ordonnances
            </a>
        </div>
    </div>

    <!-- Forum -->
    <div class="nav-item has-sub <?= isSubActive('postList.php', 'addpost.php', 'dashboard.php') ?>">
        <a onclick="toggleSubMenu(this)" <?= isActive('postList.php', 'addpost.php') ? 'class="active"' : '' ?>>
            <i class="fas fa-comments nav-icon"></i>
            <span>Forum</span>
            <i class="fas fa-chevron-right nav-arrow"></i>
        </a>
        <div class="sub-menu">
            <a href="../Frontoffice/postList.php"
               <?= isActive('postList.php') ? 'class="active"' : '' ?>>
               Tous les posts
            </a>
            <a href="../Frontoffice/addpost.php"
               <?= isActive('addpost.php') ? 'class="active"' : '' ?>>
               Ajouter un post
            </a>
            <a href="../backoffice/dashboard.php"
               <?= isActive('dashboard.php') ? 'class="active"' : '' ?>>
               Gestion des posts
            </a>
        </div>
    </div>

    <div class="nav-section-label">Configuration</div>

    <div class="nav-item">
        <a href="../front/indexp.php" <?= isActive('indexp.php') ? 'class="active"' : '' ?>>
            <i class="fas fa-globe nav-icon"></i>
            <span>Voir le site</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="../back/loginadmin.html" <?= isActive('loginadmin.html') ? 'class="active"' : '' ?>>
            <i class="fas fa-sign-out-alt nav-icon"></i>
            <span>Déconnexion</span>
        </a>
    </div>

</nav>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <div class="page-title">Ordonnances</div>
                    <div class="breadcrumb">
                        <a href="#">Dashboard</a>
                        <span>/</span>
                        <span>Ordonnances</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="add_ordonnance.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Nouvelle ordonnance
                </a>
            </div>
        </div>
        <div class="topbar-right">
    <button class="dark-toggle" onclick="toggleDark()" id="darkBtn" title="Mode sombre">
        <i class="fa-solid fa-moon"></i>
    </button>
</div>

        <div class="page-content">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date création</th>
                            <th>Consultation</th>
                            <th>Médicaments</th>
                            <th>Durée</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ordonnances)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="icon">📋</div>
                                    <h3>Aucune ordonnance</h3>
                                    <p>Commencez par ajouter une ordonnance.</p>
                                    <a href="add_ordonnance.php" class="btn btn-primary btn-sm">+ Ajouter</a>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($ordonnances as $o): ?>
                        <tr>
                            <td><strong>#<?= $o['id_ordonnance'] ?></strong></td>
                            <td><?= $o['date_creation'] ?></td>
                            <td>
                                <span class="badge badge-primary">
                                    Consultation #<?= $o['id_consultation'] ?>
                                </span>
                                <br>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($o['date_consultation'])) ?></small>
                            </td>
                            <td><?= htmlspecialchars(substr($o['medicaments'], 0, 50)) ?>...</td>
                            <td><?= $o['duree_traitement'] ?> jours</td>
                            <td>
                                <div class="actions">
                                    <a href="ordonnance_pdf.php?id=<?= $o['id_ordonnance'] ?>" class="btn btn-primary btn-sm" target="_blank">
                                        <i class="fa-solid fa-file-pdf"></i> PDF
                                    </a>
                                    <a href="edit_ordonnance.php?id=<?= $o['id_ordonnance'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fa-solid fa-pen"></i> Modifier
                                    </a>
                                    <a href="delete_ordonnance.php?id=<?= $o['id_ordonnance'] ?>" class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });
    // MODE SOMBRE
function toggleDark() {
    document.body.classList.toggle('dark-mode');
    const btn = document.getElementById('darkBtn');
    const isDark = document.body.classList.contains('dark-mode');
    btn.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
    localStorage.setItem('darkMode', isDark);
}

if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
    document.getElementById('darkBtn').innerHTML = '<i class="fa-solid fa-sun"></i>';
}
function toggleSubMenu(el) {
    var navItem = el.closest('.nav-item');
    var isOpen  = navItem.classList.contains('open');

    // Close all open sub-menus first
    document.querySelectorAll('.nav-item.has-sub.open').forEach(function(item) {
        item.classList.remove('open');
        var sub = item.querySelector('.sub-menu');
        if (sub) sub.classList.remove('open');
    });

    // If it wasn't open, open it now
    if (!isOpen) {
        navItem.classList.add('open');
        var sub = navItem.querySelector('.sub-menu');
        if (sub) sub.classList.add('open');
    }
}

// Auto-open on page load — directly add classes, DO NOT simulate a click
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.nav-item.has-sub.open').forEach(function (item) {
        var sub = item.querySelector('.sub-menu');
        if (sub) sub.classList.add('open');
    });
});
</script>
</body>
<script src="../../assets/js/language-switcher.js"></script>
</html>