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
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/frontoffice.css">
  <style>
    /* Custom styles for BackOffice CRUD */
    .admin-container {
      padding-top: 120px;
      padding-bottom: 60px;
      min-height: 80vh;
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

<nav class="navbar scrolled" id="navbar">
  <a href="../frontoffice/index.php" class="navbar-brand">
    <div class="navbar-logo">⚕️</div>
    <div class="navbar-name">ASC<span>LEPIA</span></div>
  </a>

  <div class="nav-links" id="navLinks">
    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Tableau de Bord</a>
    <a href="listepharmacie.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'listepharmacie.php' ? 'active' : ''; ?>">Gestion Pharmacies</a>
    <a href="listemedicament.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'listemedicament.php' ? 'active' : ''; ?>">Gestion Médicaments</a>
    <a href="../frontoffice/index.php" class="nav-link">Site Public</a>
  </div>

  <div class="nav-actions">
    <a href="addpharmacie.php" class="btn btn-primary btn-sm">
      <i class="fa-solid fa-plus"></i> Ajouter une Pharmacie
    </a>
    <div class="hamburger" id="hamburger" onclick="toggleMenu()">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>
