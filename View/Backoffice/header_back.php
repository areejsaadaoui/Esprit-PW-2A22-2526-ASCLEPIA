<?php
require_once '../../Controller/LanguageController.php';
require_once '../../config.php';

$db_notif = config::getConnexion();
$stmt_notif = $db_notif->query("SELECT nom_medicament AS nom, stock, id_medicament FROM medicament WHERE stock <= 5 ORDER BY stock ASC");
$alertes_stock = $stmt_notif->fetchAll();
$nb_alertes = count($alertes_stock);

$hcur = basename($_SERVER['PHP_SELF']);
$hpath = str_replace('\\', '/', $_SERVER['PHP_SELF']);
$h_admin_tb = ($hcur === 'dashboard.php' && stripos($hpath, 'backoffice') === false && stripos($hpath, '/back/') !== false);
$h_consult_dash = ($hcur === 'dashboard.php' && stripos($hpath, 'backoffice') !== false);
$h_sub_consult = in_array($hcur, ['list_consultation.php', 'list_ordonnance.php'], true) || $h_consult_dash;
$h_sub_assur = in_array($hcur, ['assurancelist.php', 'contratList.php'], true);
$h_sub_pharma = in_array($hcur, ['listepharmacie.php', 'listemedicament.php'], true);
$h_sub_forum = in_array($hcur, ['dashboardf.php', 'postlist.php', 'postList.php', 'addpost.php'], true);
$h_nom = $_SESSION['user_nom'] ?? 'Administrateur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ASCLEPIA - Gestion des pharmacies.">
  <title>ASCLEPIA — Gestion Pharmacies</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../assets/css/backoffice.css?v=<?= time() ?>">
  <style>
    .admin-container {
      padding: 40px 30px;
      flex: 1;
    }
    .crud-card {
      background: white;
      border-radius: var(--radius-lg);
      padding: 30px;
      box-shadow: var(--shadow-sm);
      margin-top: 20px;
    }
    .table-responsive { overflow-x: auto; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }
    th {
      font-weight: 700;
      color: var(--text-main);
      background: var(--bg);
    }
    tr:hover { background: rgba(0,0,0,0.02); }
    .action-btns { display: flex; gap: 10px; }
    .form-group { margin-bottom: 20px; }
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--text-main);
    }
    .form-control {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid var(--border);
      border-radius: var(--radius-md);
      font-family: inherit;
    }
    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }
    .error-message {
      color: #ef4444;
      font-size: 0.8rem;
      margin-top: 5px;
      display: none;
    }
    .is-invalid { border-color: #ef4444 !important; }
    .is-invalid:focus { box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important; }
  </style>
</head>
<body>

<div class="admin-wrapper">

<aside class="sidebar">
    <a href="../back/dashboard.php" class="sidebar-brand">
        <div class="sidebar-logo">🏥</div>
        <div class="sidebar-title">ASCL<span>EPIA</span></div>
    </a>
    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($h_nom, 0, 2)) ?></div>
        <div class="user-info">
            <div class="name"><?= htmlspecialchars($h_nom) ?></div>
            <div class="role">Super Admin</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Principal</div>
        <div class="nav-item">
            <a href="../back/dashboard.php" <?= $h_admin_tb ? 'class="active"' : '' ?>>
                <i class="fas fa-tachometer-alt nav-icon"></i>
                <span>Tableau de bord</span>
            </a>
        </div>

        <div class="nav-section-label">Gestion</div>

        <div class="nav-item has-sub <?= $h_sub_assur ? 'open' : '' ?>">
            <a onclick="toggleSubMenu(this)" <?= $h_sub_assur ? 'class="active"' : '' ?>>
                <i class="fa-solid fa-shield-halved nav-icon"></i>
                <span>Assurances &amp; Contrats</span>
                <i class="fas fa-chevron-right nav-arrow"></i>
            </a>
            <div class="sub-menu <?= $h_sub_assur ? 'open' : '' ?>">
                <a href="assurancelist.php" <?= $hcur === 'assurancelist.php' ? 'class="active"' : '' ?>>Les assurances</a>
                <a href="contrat/contratList.php" <?= $hcur === 'contratList.php' ? 'class="active"' : '' ?>>Les contrats</a>
            </div>
        </div>

        <div class="nav-item has-sub <?= $h_sub_consult ? 'open' : '' ?>">
            <a onclick="toggleSubMenu(this)" <?= (in_array($hcur, ['list_consultation.php', 'list_ordonnance.php'], true) || $h_consult_dash) ? 'class="active"' : '' ?>>
                <i class="fa-solid fa-file-contract nav-icon"></i>
                <span>Ordonnances &amp; Consultations</span>
                <i class="fas fa-chevron-right nav-arrow"></i>
            </a>
            <div class="sub-menu <?= $h_sub_consult ? 'open' : '' ?>">
                <a href="dashboard.php" <?= $h_consult_dash ? 'class="active"' : '' ?>>Toutes les consultations</a>
                <a href="list_consultation.php" <?= $hcur === 'list_consultation.php' ? 'class="active"' : '' ?>>Les consultations</a>
                <a href="list_ordonnance.php" <?= $hcur === 'list_ordonnance.php' ? 'class="active"' : '' ?>>Les ordonnances</a>
            </div>
        </div>

        <div class="nav-item has-sub <?= $h_sub_pharma ? 'open' : '' ?>">
            <a onclick="toggleSubMenu(this)" <?= $h_sub_pharma ? 'class="active"' : '' ?>>
                <i class="fa-solid fa-prescription-bottle-medical nav-icon"></i>
                <span>Pharmacies &amp; Médicaments</span>
                <i class="fas fa-chevron-right nav-arrow"></i>
            </a>
            <div class="sub-menu <?= $h_sub_pharma ? 'open' : '' ?>">
                <a href="listepharmacie.php" <?= $hcur === 'listepharmacie.php' ? 'class="active"' : '' ?>>Les pharmacies</a>
                <a href="listemedicament.php" <?= $hcur === 'listemedicament.php' ? 'class="active"' : '' ?>>Les médicaments</a>
            </div>
        </div>

        <div class="nav-item">
            <a href="statistiques.php" <?= $hcur === 'statistiques.php' ? 'class="active"' : '' ?>>
                <i class="fa-solid fa-chart-pie nav-icon"></i>
                <span><?= tr('bo_stats') ?></span>
            </a>
        </div>

        <div class="nav-item has-sub <?= $h_sub_forum ? 'open' : '' ?>">
            <a onclick="toggleSubMenu(this)" <?= $h_sub_forum ? 'class="active"' : '' ?>>
                <i class="fas fa-comments nav-icon"></i>
                <span>Forum</span>
                <i class="fas fa-chevron-right nav-arrow"></i>
            </a>
            <div class="sub-menu <?= $h_sub_forum ? 'open' : '' ?>">
                <a href="dashboardf.php" <?= $hcur === 'dashboardf.php' ? 'class="active"' : '' ?>>📊 Dashboard Forum</a>
                <a href="../Frontoffice/postlist.php" <?= ($hcur === 'postlist.php' || $hcur === 'postList.php') ? 'class="active"' : '' ?>>📝 Tous les posts</a>
                <a href="addpost.php" <?= $hcur === 'addpost.php' ? 'class="active"' : '' ?>>Ajouter un post</a>
                <a href="dashboard.php" <?= $h_consult_dash ? 'class="active"' : '' ?>>Gestion des posts</a>
            </div>
        </div>

        <div class="nav-section-label">Configuration</div>
        <div class="nav-item">
            <a href="../front/indexp.php" <?= $hcur === 'indexp.php' ? 'class="active"' : '' ?>>
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
            <button type="button" id="themeToggle" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: rgba(255,255,255,0.7);" title="Mode sombre">
                <i class="fa-solid fa-moon"></i>
            </button>
            <div class="notification-container" style="position: relative;">
                <button type="button" id="notifToggle" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: rgba(255,255,255,0.7); position: relative;" title="Notifications de stock">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($nb_alertes > 0): ?>
                        <span style="position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border-radius: 50%; font-size: 0.65rem; padding: 2px 5px; font-weight: bold;"><?= $nb_alertes ?></span>
                    <?php endif; ?>
                </button>
                <div id="notifDropdown" style="display: none; position: absolute; bottom: 100%; left: 0; margin-bottom: 10px; width: 280px; background: var(--bg-card, white); border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 1001; color: var(--text-main);">
                    <div style="padding: 12px 15px; border-bottom: 1px solid var(--border); font-weight: 600; font-size: 0.95rem;">Alertes de stock (<?= $nb_alertes ?>)</div>
                    <div style="max-height: 220px; overflow-y: auto;">
                        <?php if ($nb_alertes > 0): ?>
                            <?php foreach ($alertes_stock as $alerte): ?>
                                <a href="editmedicament.php?id_medicament=<?= (int)$alerte['id_medicament'] ?>" style="display: flex; flex-direction: column; padding: 12px 15px; border-bottom: 1px solid var(--border); text-decoration: none; color: inherit;">
                                    <span style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($alerte['nom']) ?></span>
                                    <?php if ((int)$alerte['stock'] === 0): ?>
                                        <span style="color: #ef4444; font-size: 0.8rem;"><i class="fa-solid fa-circle-exclamation"></i> Rupture (0)</span>
                                    <?php else: ?>
                                        <span style="color: #f59e0b; font-size: 0.8rem;"><i class="fa-solid fa-triangle-exclamation"></i> Stock critique (<?= (int)$alerte['stock'] ?>)</span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: 0.9rem;">Aucune alerte.</div>
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
</aside>

<script>
function toggleSubMenu(el) {
    var p = el && el.closest('.has-sub');
    if (p) {
        p.classList.toggle('open');
        var s = p.querySelector('.sub-menu');
        if (s) s.classList.toggle('open');
    }
}
document.addEventListener('DOMContentLoaded', function() {
    var notifBtn = document.getElementById('notifToggle');
    var notifDropdown = document.getElementById('notifDropdown');
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function(e) {
            if (!notifDropdown.contains(e.target) && e.target !== notifBtn) {
                notifDropdown.style.display = 'none';
            }
        });
    }
});
</script>

<div class="main-content">
<script src="../assets/js/theme.js?v=<?= time() ?>"></script>
