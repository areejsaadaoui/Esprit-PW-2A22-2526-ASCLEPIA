<?php
session_start();

// Vérifier si médecin ou admin est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'medecin'])) {
    header('Location: loginadmin.html');
    exit();
}

$user_role  = $_SESSION['user_role'];
$adminNom   = $_SESSION['user_nom']   ?? 'Utilisateur';
$adminEmail = $_SESSION['user_email'] ?? '';

require_once '../../config.php';
require_once '../../Controller/LanguageController.php';
require_once '../../Controller/OrdonnanceController.php';

$db_notif_lo = config::getConnexion();
$stmt_notif_lo = $db_notif_lo->query("SELECT nom_medicament AS nom, stock, id_medicament FROM medicament WHERE stock <= 5 ORDER BY stock ASC");
$alertes_stock_lo = $stmt_notif_lo->fetchAll();
$nb_alertes_lo = count($alertes_stock_lo);

$controller  = new OrdonnanceController(config::getConnexion());
$ordonnances = $controller->getAllOrdonnances();

// ── Sidebar active-link helpers ────────────────────────────────────────────
$cur = basename($_SERVER['PHP_SELF']);
if (!function_exists('docActive')) {
    function docActive(...$p){ global $cur; return in_array($cur,$p) ? 'class="active"' : ''; }
    function docSub(...$p)   { global $cur; return in_array($cur,$p) ? 'open' : ''; }
}
if (!function_exists('isActive')) {
    function isActive(...$pages)   { global $cur; return in_array($cur, $pages); }
    function isSubActive(...$pages){ global $cur; return in_array($cur, $pages) ? 'open' : ''; }
}
$path_norm = str_replace('\\', '/', $_SERVER['PHP_SELF']);
$is_admin_user_dash = ($cur === 'dashboard.php' && stripos($path_norm, 'backoffice') === false && stripos($path_norm, '/back/') !== false);
$is_consult_dash = ($cur === 'dashboard.php' && stripos($path_norm, 'backoffice') !== false);
$sub_consult_open = in_array($cur, ['list_consultation.php', 'list_ordonnance.php'], true) || $is_consult_dash;
$sub_assur_open = in_array($cur, ['assurancelist.php', 'contratList.php'], true);
$sub_pharma_open = in_array($cur, ['listepharmacie.php', 'listemedicament.php'], true);
$sub_forum_open = in_array($cur, ['dashboardf.php', 'postlist.php', 'postList.php', 'addpost.php'], true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordonnances - ASCLEPIA</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="../../assets/css/dark.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
        <!-- ── MÉDECIN SIDEBAR (chemins relatifs depuis View/backoffice/) ── -->
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
                    <a href="list_consultation.php" <?= docActive('list_consultation.php') ?>>
                        <i class="fa-solid fa-list"></i> Toutes les consultations
                    </a>
                    <a href="add_consultation.php" <?= docActive('add_consultation.php') ?>>
                        <i class="fa-solid fa-plus"></i> Nouvelle consultation
                    </a>
                </div>
            </div>

            <div class="nav-item has-sub <?= docSub('list_ordonnance.php','add_ordonnance.php','edit_ordonnance.php','delete_ordonnance.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= docActive('list_ordonnance.php','add_ordonnance.php','edit_ordonnance.php','delete_ordonnance.php') ?>>
                    <i class="fa-solid fa-file-prescription nav-icon"></i>
                    <span>Ordonnances</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= docSub('list_ordonnance.php','add_ordonnance.php','edit_ordonnance.php','delete_ordonnance.php') ?>">
                    <a href="list_ordonnance.php" <?= docActive('list_ordonnance.php') ?>>
                        <i class="fa-solid fa-list"></i> Toutes les ordonnances
                    </a>
                    <a href="add_ordonnance.php" <?= docActive('add_ordonnance.php') ?>>
                        <i class="fa-solid fa-plus"></i> Nouvelle ordonnance
                    </a>
                </div>
            </div>

            <div class="nav-item">
                <a href="calendrier.php" <?= docActive('calendrier.php') ?>>
                    <i class="fa-solid fa-calendar-days nav-icon"></i>
                    <span>Calendrier</span>
                </a>
            </div>

            <div class="nav-section-label">Autre</div>
            <div class="nav-item">
                <a href="../front/indexp.php">
                    <i class="fas fa-globe nav-icon"></i>
                    <span>Espace patient</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="../front/login.php">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </nav>

        <?php else: ?>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Principal</div>
            <div class="nav-item">
                <a href="../back/dashboard.php" <?= $is_admin_user_dash ? 'class="active"' : '' ?>>
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>
            <div class="nav-section-label">Gestion</div>
            <div class="nav-item has-sub <?= $sub_assur_open ? 'open' : '' ?>">
                <a onclick="toggleSubMenu(this)" <?= $sub_assur_open ? 'class="active"' : '' ?>>
                    <i class="fa-solid fa-shield-halved nav-icon"></i>
                    <span>Assurances &amp; Contrats</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= $sub_assur_open ? 'open' : '' ?>">
                    <a href="assurancelist.php" <?= isActive('assurancelist.php') ? 'class="active"' : '' ?>>Les assurances</a>
                    <a href="contrat/contratList.php" <?= isActive('contratList.php') ? 'class="active"' : '' ?>>Les contrats</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= $sub_consult_open ? 'open' : '' ?>">
                <a onclick="toggleSubMenu(this)" <?= (isActive('list_consultation.php', 'list_ordonnance.php') || $is_consult_dash) ? 'class="active"' : '' ?>>
                    <i class="fa-solid fa-file-contract nav-icon"></i>
                    <span>Ordonnances &amp; Consultations</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= $sub_consult_open ? 'open' : '' ?>">
                    <a href="dashboard.php" <?= $is_consult_dash ? 'class="active"' : '' ?>>Toutes les consultations</a>
                    <a href="list_consultation.php" <?= isActive('list_consultation.php') ? 'class="active"' : '' ?>>Les consultations</a>
                    <a href="list_ordonnance.php" <?= isActive('list_ordonnance.php') ? 'class="active"' : '' ?>>Les ordonnances</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= $sub_pharma_open ? 'open' : '' ?>">
                <a onclick="toggleSubMenu(this)" <?= $sub_pharma_open ? 'class="active"' : '' ?>>
                    <i class="fa-solid fa-prescription-bottle-medical nav-icon"></i>
                    <span>Pharmacies &amp; Médicaments</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= $sub_pharma_open ? 'open' : '' ?>">
                    <a href="listepharmacie.php" <?= isActive('listepharmacie.php') ? 'class="active"' : '' ?>>Les pharmacies</a>
                    <a href="listemedicament.php" <?= isActive('listemedicament.php') ? 'class="active"' : '' ?>>Les médicaments</a>
                </div>
            </div>
            <div class="nav-item">
                <a href="statistiques.php" <?= isActive('statistiques.php') ? 'class="active"' : '' ?>>
                    <i class="fa-solid fa-chart-pie nav-icon"></i>
                    <span><?= tr('bo_stats') ?></span>
                </a>
            </div>
            <div class="nav-item has-sub <?= $sub_forum_open ? 'open' : '' ?>">
                <a onclick="toggleSubMenu(this)" <?= $sub_forum_open ? 'class="active"' : '' ?>>
                    <i class="fas fa-comments nav-icon"></i>
                    <span>Forum</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= $sub_forum_open ? 'open' : '' ?>">
                    <a href="dashboardf.php" <?= isActive('dashboardf.php') ? 'class="active"' : '' ?>>📊 Dashboard Forum</a>
                    <a href="../Frontoffice/postlist.php" <?= isActive('postlist.php', 'postList.php') ? 'class="active"' : '' ?>>📝 Tous les posts</a>
                    <a href="addpost.php" <?= isActive('addpost.php') ? 'class="active"' : '' ?>>Ajouter un post</a>
                    <a href="dashboard.php" <?= $is_consult_dash ? 'class="active"' : '' ?>>Gestion des posts</a>
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
                <a href="../back/loginadmin.html">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </nav>
        <div class="sidebar-toolbar" style="padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.06); margin-top: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <button type="button" class="dark-toggle" onclick="toggleDark()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: rgba(255,255,255,0.7);" title="Mode sombre"><i class="fa-solid fa-moon"></i></button>
                <div class="notification-container" style="position: relative;">
                    <button type="button" id="notifToggleAdmin" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: rgba(255,255,255,0.7); position: relative;" title="Notifications de stock">
                        <i class="fa-solid fa-bell"></i>
                        <?php if ($nb_alertes_lo > 0): ?>
                            <span style="position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border-radius: 50%; font-size: 0.65rem; padding: 2px 5px; font-weight: bold;"><?= $nb_alertes_lo ?></span>
                        <?php endif; ?>
                    </button>
                    <div id="notifDropdownAdmin" style="display: none; position: absolute; bottom: 100%; left: 0; margin-bottom: 10px; width: 280px; background: var(--bg-card, white); border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 1001; color: var(--text-main);">
                        <div style="padding: 12px 15px; border-bottom: 1px solid var(--border); font-weight: 600;">Alertes de stock (<?= $nb_alertes_lo ?>)</div>
                        <div style="max-height: 220px; overflow-y: auto;">
                            <?php if ($nb_alertes_lo > 0): ?>
                                <?php foreach ($alertes_stock_lo as $al): ?>
                                    <a href="editmedicament.php?id_medicament=<?= (int)$al['id_medicament'] ?>" style="display: block; padding: 12px 15px; border-bottom: 1px solid var(--border); text-decoration: none; color: inherit;">
                                        <span style="font-weight: 600;"><?= htmlspecialchars($al['nom']) ?></span>
                                        <span style="color: #f59e0b; font-size: 0.8rem;">Stock: <?= (int)$al['stock'] ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="padding: 16px; text-align: center; color: var(--text-muted);">Aucune alerte.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 5px; align-items: center; font-size: 0.9rem;">
                    <a href="?lang=fr" style="color: <?= ($_SESSION['lang'] ?? 'fr') === 'fr' ? 'var(--primary)' : 'rgba(255,255,255,0.45)' ?>; font-weight: 700; text-decoration: none;">FR</a>
                    <span style="color: rgba(255,255,255,0.35);">|</span>
                    <a href="?lang=en" style="color: <?= ($_SESSION['lang'] ?? 'fr') === 'en' ? 'var(--primary)' : 'rgba(255,255,255,0.45)' ?>; font-weight: 700; text-decoration: none;">EN</a>
                </div>
            </div>
        </div>
        <div class="sidebar-footer">
            <div class="sidebar-version">Version 1.0</div>
        </div>
        <script>
        (function(){
            var b = document.getElementById('notifToggleAdmin'), d = document.getElementById('notifDropdownAdmin');
            if (b && d) {
                b.addEventListener('click', function(e) { e.stopPropagation(); d.style.display = d.style.display === 'block' ? 'none' : 'block'; });
                document.addEventListener('click', function(e) { if (!d.contains(e.target) && e.target !== b) d.style.display = 'none'; });
            }
        })();
        </script>
        <?php endif; ?>

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
                <button class="dark-toggle" onclick="toggleDark()" id="darkBtn" title="Mode sombre">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <a href="add_ordonnance.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Nouvelle ordonnance
                </a>
            </div>
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
                                <span class="badge badge-primary">Consultation #<?= $o['id_consultation'] ?></span>
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
</script>
</body>
<script src="../../assets/js/language-switcher.js"></script>
</html>