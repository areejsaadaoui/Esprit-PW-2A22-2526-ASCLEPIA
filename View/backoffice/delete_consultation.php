<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'medecin'])) {
    header('Location: loginadmin.html');
    exit();
}

$user_role = $_SESSION['user_role'];
$adminNom  = $_SESSION['user_nom'] ?? 'Utilisateur';

$cur = basename($_SERVER['PHP_SELF']);
if (!function_exists('docActive')) {
    function docActive(...$p){ global $cur; return in_array($cur, $p) ? 'class="active"' : ''; }
    function docSub(...$p)   { global $cur; return in_array($cur, $p) ? 'open' : ''; }
}
if (!function_exists('isActive')) {
    function isActive(...$pages)   { global $cur; return in_array($cur, $pages); }
    function isSubActive(...$pages){ global $cur; return in_array($cur, $pages) ? 'open' : ''; }
}

require_once '../../config.php';
require_once '../../Controller/ConsultationController.php';

$controller = new ConsultationController(config::getConnexion());

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$consultation = $controller->getConsultationById($id);

if (!$consultation) {
    die("Consultation introuvable.");
}

if (isset($_GET['confirm']) && $_GET['confirm'] === 'oui') {
    $controller->deleteConsultation($id);
    header('Location: list_consultation.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer Consultation - ASCLEPIA Admin</title>
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
            <div class="user-avatar"><?= strtoupper(substr($adminNom, 0, 2)) ?></div>
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($adminNom) ?></div>
                <div class="role"><?= $user_role === 'admin' ? 'Super Admin' : 'Médecin' ?></div>
            </div>
        </div>

        <?php if ($user_role === 'medecin'): ?>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Principal</div>
            <div class="nav-item">
                <a href="../front/indexd.php" <?= docActive('indexd.php') ?>>
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>
            <div class="nav-section-label">Activité</div>
            <div class="nav-item has-sub <?= docSub('list_consultation.php','add_consultation.php','edit_consultation.php','delete_consultation.php','calendrier.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= docActive('list_consultation.php','add_consultation.php','edit_consultation.php','delete_consultation.php') ?>>
                    <i class="fa-solid fa-stethoscope nav-icon"></i>
                    <span>Consultations</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= docSub('list_consultation.php','add_consultation.php','edit_consultation.php','delete_consultation.php') ?>">
                    <a href="list_consultation.php" <?= docActive('list_consultation.php') ?>><i class="fa-solid fa-list"></i> Toutes les consultations</a>
                    <a href="add_consultation.php" <?= docActive('add_consultation.php') ?>><i class="fa-solid fa-plus"></i> Nouvelle consultation</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= docSub('list_ordonnance.php','add_ordonnance.php','edit_ordonnance.php','delete_ordonnance.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= docActive('list_ordonnance.php','add_ordonnance.php','edit_ordonnance.php','delete_ordonnance.php') ?>>
                    <i class="fa-solid fa-file-prescription nav-icon"></i>
                    <span>Ordonnances</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= docSub('list_ordonnance.php','add_ordonnance.php','edit_ordonnance.php','delete_ordonnance.php') ?>">
                    <a href="list_ordonnance.php" <?= docActive('list_ordonnance.php') ?>><i class="fa-solid fa-list"></i> Toutes les ordonnances</a>
                    <a href="add_ordonnance.php" <?= docActive('add_ordonnance.php') ?>><i class="fa-solid fa-plus"></i> Nouvelle ordonnance</a>
                </div>
            </div>
            <div class="nav-item">
                <a href="calendrier.php" <?= docActive('calendrier.php') ?>><i class="fa-solid fa-calendar-days nav-icon"></i><span>Calendrier</span></a>
            </div>
            <div class="nav-section-label">Autre</div>
            <div class="nav-item">
                <a href="../front/indexp.php"><i class="fas fa-globe nav-icon"></i><span>Espace patient</span></a>
            </div>
            <div class="nav-item">
                <a href="../front/login.php"><i class="fas fa-sign-out-alt nav-icon"></i><span>Déconnexion</span></a>
            </div>
        </nav>
        <?php else: ?>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Principal</div>
            <div class="nav-item">
                <a href="../back/dashboard.php" <?= isActive('dashboard.php') ? 'class="active"' : '' ?>><i class="fas fa-tachometer-alt nav-icon"></i><span>Tableau de bord</span></a>
            </div>
            <div class="nav-section-label">Gestion</div>
            <div class="nav-item has-sub <?= isSubActive('assurancelist.php','contratList.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= isActive('assurancelist.php','contratList.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-shield-halved nav-icon"></i><span>Assurances &amp; Contrats</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu">
                    <a href="../backoffice/assurancelist.php" <?= isActive('assurancelist.php') ? 'class="active"' : '' ?>>Les assurances</a>
                    <a href="contrat/contratList.php" <?= isActive('contratList.php') ? 'class="active"' : '' ?>>Les contrats</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= isSubActive('list_consultation.php','list_ordonnance.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= isActive('list_consultation.php','list_ordonnance.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-file-contract nav-icon"></i><span>Ordonnances &amp; Consultations</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu">
                    <a href="dashboard.php" <?= isActive('dashboard.php') ? 'class="active"' : '' ?>>Vue d’ensemble consultations</a>
                    <a href="list_consultation.php" <?= isActive('list_consultation.php') ? 'class="active"' : '' ?>>Les consultations</a>
                    <a href="list_ordonnance.php" <?= isActive('list_ordonnance.php') ? 'class="active"' : '' ?>>Les ordonnances</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= isSubActive('listepharmacie.php','listemedicament.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= isActive('listepharmacie.php','listemedicament.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-prescription-bottle-medical nav-icon"></i><span>Pharmacies &amp; Médicaments</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu">
                    <a href="listepharmacie.php" <?= isActive('listepharmacie.php') ? 'class="active"' : '' ?>>Les pharmacies</a>
                    <a href="listemedicament.php" <?= isActive('listemedicament.php') ? 'class="active"' : '' ?>>Les médicaments</a>
                </div>
            </div>
            <div class="nav-item">
                <a href="calendrier.php" <?= isActive('calendrier.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-calendar-days nav-icon"></i><span>Calendrier</span></a>
            </div>
            <div class="nav-item has-sub <?= isSubActive('postlist.php','postList.php','addpost.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= isActive('postlist.php','postList.php','addpost.php') ? 'class="active"' : '' ?>><i class="fas fa-comments nav-icon"></i><span>Forum</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu">
                    <a href="../Frontoffice/postlist.php" <?= isActive('postlist.php','postList.php') ? 'class="active"' : '' ?>>Tous les posts</a>
                    <a href="addpost.php" <?= isActive('addpost.php') ? 'class="active"' : '' ?>>Ajouter un post</a>
                    <a href="dashboard.php" <?= isActive('dashboard.php') ? 'class="active"' : '' ?>>Gestion des posts</a>
                </div>
            </div>
            <div class="nav-section-label">Configuration</div>
            <div class="nav-item">
                <a href="../front/indexp.php"><i class="fas fa-globe nav-icon"></i><span>Voir le site</span></a>
            </div>
            <div class="nav-item">
                <a href="../back/loginadmin.html"><i class="fas fa-sign-out-alt nav-icon"></i><span>Déconnexion</span></a>
            </div>
        </nav>
        <?php endif; ?>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <div class="page-title">Supprimer Consultation</div>
                    <div class="breadcrumb">
                        <a href="list_consultation.php">Consultations</a>
                        <span>/</span>
                        <span>Supprimer</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
    <button class="dark-toggle" onclick="toggleDark()" id="darkBtn" title="Mode sombre">
        <i class="fa-solid fa-moon"></i>
    </button>
</div>
        </div>

        <div class="page-content">
            <div class="card" style="max-width:600px; margin:0 auto;">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-trash" style="color:var(--danger)"></i>
                        Confirmer la suppression
                    </div>
                </div>

                <div class="alert alert-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Es-tu sûr de vouloir supprimer cette consultation ? Cette action est irréversible.
                </div>

                <div class="form-group">
                    <label class="form-label">Date</label>
                    <p><?= $consultation->getDateConsultation() ?></p>
                </div>

                <div class="form-group">
                    <label class="form-label">Diagnostique</label>
                    <p><?= htmlspecialchars($consultation->getDiagnostique()) ?></p>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <p><?= htmlspecialchars($consultation->getNotes()) ?></p>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <a href="delete_consultation.php?id=<?= $id ?>&confirm=oui" class="btn btn-danger">
                        <i class="fa-solid fa-trash"></i> Oui, supprimer
                    </a>
                    <a href="list_consultation.php" class="btn btn-outline">
                        <i class="fa-solid fa-xmark"></i> Annuler
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });

    function toggleSubMenu(el) {
        var navItem = el.closest('.nav-item');
        var isOpen  = navItem.classList.contains('open');
        document.querySelectorAll('.nav-item.has-sub.open').forEach(function(item) {
            item.classList.remove('open');
            var sub = item.querySelector('.sub-menu');
            if (sub) sub.classList.remove('open');
        });
        if (!isOpen) {
            navItem.classList.add('open');
            var sub = navItem.querySelector('.sub-menu');
            if (sub) sub.classList.add('open');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.nav-item.has-sub.open').forEach(function (item) {
            var sub = item.querySelector('.sub-menu');
            if (sub) sub.classList.add('open');
        });
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
</script>
</body>
<script src="../../assets/js/language-switcher.js"></script>
</html>