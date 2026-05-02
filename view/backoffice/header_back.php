<?php
require_once '../../Controller/LanguageController.php';
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
    
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <!-- Theme Toggle -->
        <button id="themeToggle" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--white);" title="Activer le mode sombre">
            <i class="fa-solid fa-moon"></i>
        </button>

        <!-- Language Toggle -->
        <div class="lang-toggle" style="display:flex; gap: 5px; align-items:center; font-size: 0.9rem;">
            <a href="?lang=fr" style="color: <?= $_SESSION['lang'] === 'fr' ? 'var(--primary)' : 'rgba(255,255,255,0.5)' ?>; font-weight: 700; text-decoration: none;">FR</a>
            <span style="color: rgba(255,255,255,0.5);">|</span>
            <a href="?lang=en" style="color: <?= $_SESSION['lang'] === 'en' ? 'var(--primary)' : 'rgba(255,255,255,0.5)' ?>; font-weight: 700; text-decoration: none;">EN</a>
        </div>
    </div>

    <a href="addpharmacie.php" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
      <i class="fa-solid fa-plus"></i> <?= tr('bo_add_pharmacy') ?>
    </a>
  </div>
</aside>

<!-- Main Content Wrapper -->
<div class="main-content">
<script src="../assets/js/theme.js?v=<?= time() ?>"></script>
