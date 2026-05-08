<?php
require_once '../../Controller/LanguageController.php';
require_once '../../config.php';

// Logique de Notification Dynamique : Rupture de stock
$db_notif = config::getConnexion();
$stmt_notif = $db_notif->query("SELECT nom, stock, id_medicament FROM medicament WHERE stock <= 5 ORDER BY stock ASC");
$alertes_stock = $stmt_notif->fetchAll();
$nb_alertes = count($alertes_stock);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ASCLEPIA - Gestion des pharmacies.">
  <title>ASCLEPIA — Gestion Pharmacies</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Styles -->
  <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../assets/css/backoffice.css?v=<?= time() ?>">
  <style>
    /* Custom styles for BackOffice CRUD */
    body {
      display: flex;
      margin: 0;
      min-height: 100vh;
      background-color: var(--bg);
    }
    .sidebar {
      width: 260px;
      background: var(--dark);
      color: var(--white);
      position: fixed;
      height: 100vh;
      left: 0;
      top: 0;
      padding: 20px 0;
      display: flex;
      flex-direction: column;
      z-index: 1000;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    .sidebar .navbar-brand {
      padding: 0 20px 20px;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }
    .sidebar .navbar-logo {
      font-size: 1.8rem;
    }
    .sidebar .navbar-name {
      font-size: 1.3rem;
      font-weight: 800;
      color: var(--white);
      letter-spacing: 1px;
    }
    .sidebar .navbar-name span {
      color: var(--primary);
    }
    .sidebar .nav-links {
      display: flex;
      flex-direction: column;
      gap: 5px;
      padding: 0 15px;
    }
    .sidebar .nav-link {
      color: var(--gray-light);
      padding: 12px 15px;
      border-radius: var(--radius-sm);
      text-decoration: none;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 500;
      font-size: 0.95rem;
    }
    .sidebar .nav-link i {
      width: 20px;
      text-align: center;
      font-size: 1.1rem;
    }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
      color: var(--white);
      background: rgba(255,255,255,0.1);
    }
    .sidebar .nav-link.active {
      background: var(--primary);
      color: white;
    }
    .sidebar .nav-actions {
      margin-top: auto;
      padding: 20px;
      border-top: 1px solid rgba(255,255,255,0.05);
    }
    .main-content {
      flex: 1;
      margin-left: 260px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      width: calc(100% - 260px);
    }
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
    .table-responsive {
      overflow-x: auto;
    }
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
    tr:hover {
      background: rgba(0,0,0,0.02);
    }
    .action-btns {
      display: flex;
      gap: 10px;
    }
    .form-group {
      margin-bottom: 20px;
    }
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
    .is-invalid {
      border-color: #ef4444 !important;
    }
    .is-invalid:focus {
      box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
  </style>
</head>

<body>

<!-- Sidebar -->
<aside class="sidebar">
  <a href="../frontoffice/index.php" class="navbar-brand" style="display: flex; justify-content: center; margin: 15px; padding: 10px; text-decoration: none;">
    <img src="../assets/image/logo.png?v=<?php echo time(); ?>" alt="ASCLEPIA Logo" style="height: 55px; object-fit: contain; max-width: 100%;">
  </a>

  <div class="nav-links" id="navLinks">
    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-chart-line"></i> <?= tr('bo_dashboard') ?>
    </a>
    <a href="listepharmacie.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'listepharmacie.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-house-medical"></i> <?= tr('bo_pharmacies') ?>
    </a>
    <a href="listemedicament.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'listemedicament.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-pills"></i> <?= tr('bo_medicaments') ?>
    </a>
    <a href="statistiques.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'statistiques.php' ? 'active' : ''; ?>">
      <i class="fa-solid fa-chart-pie"></i> <?= tr('bo_stats') ?>
    </a>
    <a href="../frontoffice/index.php" class="nav-link">
      <i class="fa-solid fa-globe"></i> <?= tr('bo_public_site') ?>
    </a>
  </div>

  <div class="nav-actions" style="display: flex; flex-direction: column; gap: 15px; margin-top: auto; padding: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; position: relative;">
        <!-- Theme Toggle -->
        <button id="themeToggle" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--white);" title="Activer le mode sombre">
            <i class="fa-solid fa-moon"></i>
        </button>

        <!-- Notification Bell -->
        <div class="notification-container" style="position: relative;">
            <button id="notifToggle" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--white); position: relative;" title="Notifications de Stock">
                <i class="fa-solid fa-bell"></i>
                <?php if($nb_alertes > 0): ?>
                    <span class="badge" style="position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border-radius: 50%; font-size: 0.65rem; padding: 2px 5px; font-weight: bold;">
                        <?= $nb_alertes ?>
                    </span>
                <?php endif; ?>
            </button>
            
            <!-- Notification Dropdown -->
            <div id="notifDropdown" class="notif-dropdown" style="display: none; position: absolute; bottom: 100%; left: 0; margin-bottom: 15px; width: 280px; background: var(--bg-card, white); border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 1001; color: var(--text-main);">
                <div style="padding: 12px 15px; border-bottom: 1px solid var(--border); font-weight: 600; font-size: 0.95rem; color: var(--text-main);">
                    Alertes de Stock (<?= $nb_alertes ?>)
                </div>
                <div style="max-height: 250px; overflow-y: auto;">
                    <?php if($nb_alertes > 0): ?>
                        <?php foreach($alertes_stock as $alerte): ?>
                            <a href="editmedicament.php?id_medicament=<?= $alerte['id_medicament'] ?>" style="display: flex; flex-direction: column; padding: 12px 15px; border-bottom: 1px solid var(--border); text-decoration: none; color: inherit; transition: background 0.2s;">
                                <span style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($alerte['nom']) ?></span>
                                <?php if($alerte['stock'] == 0): ?>
                                    <span style="color: #ef4444; font-size: 0.8rem; font-weight: 500;"><i class="fa-solid fa-circle-exclamation"></i> Rupture de stock (0)</span>
                                <?php else: ?>
                                    <span style="color: #f59e0b; font-size: 0.8rem; font-weight: 500;"><i class="fa-solid fa-triangle-exclamation"></i> Stock critique (<?= $alerte['stock'] ?> restants)</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 20px 15px; text-align: center; color: var(--text-muted); font-size: 0.9rem;">
                            <i class="fa-solid fa-check-circle" style="color: #10b981; font-size: 1.5rem; margin-bottom: 8px; display: block;"></i>
                            Tous les stocks sont normaux.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Language Toggle -->
        <div class="lang-toggle" style="display:flex; gap: 5px; align-items:center; font-size: 0.9rem;">
            <a href="?lang=fr" style="color: <?= $_SESSION['lang'] === 'fr' ? 'var(--primary)' : 'rgba(255,255,255,0.5)' ?>; font-weight: 700; text-decoration: none;">FR</a>
            <span style="color: rgba(255,255,255,0.5);">|</span>
            <a href="?lang=en" style="color: <?= $_SESSION['lang'] === 'en' ? 'var(--primary)' : 'rgba(255,255,255,0.5)' ?>; font-weight: 700; text-decoration: none;">EN</a>
        </div>
    </div>

    <!-- Toggle Script for Dropdown -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notifBtn = document.getElementById('notifToggle');
            const notifDropdown = document.getElementById('notifDropdown');
            const notifSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');
            
            if (notifBtn && notifDropdown) {
                notifBtn.addEventListener('click', function(e) {
                    e.stopPropagation();

                    // Jouer le son si le menu s'ouvre
                    if (notifDropdown.style.display !== 'block') {
                        notifSound.play().catch(e => console.log("Audio play blocked"));
                    }

                    notifDropdown.style.display = notifDropdown.style.display === 'none' ? 'block' : 'none';
                });
                
                document.addEventListener('click', function(e) {
                    if (!notifDropdown.contains(e.target) && e.target !== notifBtn) {
                        notifDropdown.style.display = 'none';
                    }
                });
            }
        });
    </script>

    <a href="addpharmacie.php" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
      <i class="fa-solid fa-plus"></i> <?= tr('bo_add_pharmacy') ?>
    </a>
  </div>
</aside>

<!-- Main Content Wrapper -->
<div class="main-content">
<script src="../assets/js/theme.js?v=<?= time() ?>"></script>
