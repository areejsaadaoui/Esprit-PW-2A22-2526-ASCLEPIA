<?php
session_start();
require_once '../../Controller/LanguageController.php';
require_once '../../Controller/MedicamentC.php';
include '../../Controller/UserController.php';

// === SESSION (comme assurancefront.php) ===
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userId     = $_SESSION['user_id']    ?? null;
$userNom    = $_SESSION['user_nom']   ?? '';
$userRole   = $_SESSION['user_role']  ?? '';
$isAdmin    = ($userRole === 'admin');

$userC      = new UserController();
$userAvatar = ($isLoggedIn && $userId) ? $userC->getAvatarByUserId($userId) : 'default';

$mc = new medicamentC();

// Vérifier si un ID de pharmacie est passé en paramètre
$id_pharmacie = isset($_GET['id_pharmacie']) ? $_GET['id_pharmacie'] : null;

if ($id_pharmacie) {
    $listeMedicaments = $mc->afficherMedicaments()->fetchAll();
    $listeMedicaments = array_filter($listeMedicaments, function($m) use ($id_pharmacie) {
        return $m['id_pharmacie'] == $id_pharmacie;
    });
} else {
    $listeMedicaments = $mc->afficherMedicaments()->fetchAll();
}

// Stock alerts (computed from full list regardless of filter)
$_allMedsForAlert = $mc->afficherMedicaments()->fetchAll();
$alertesStock = array_filter($_allMedsForAlert, function($m) { return $m['stock'] > 0 && $m['stock'] <= 5; });
$countAlertes = count($alertesStock);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Catalogue complet des médicaments - ASCLEPIA.">
  <title>Médicaments — ASCLEPIA</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Styles -->
  <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../assets/css/frontoffice.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../assets/css/avatar.css">
  <style>
    .nav-user-info { display:flex; align-items:center; gap:8px; color:white; font-size:0.9rem; }
    .nav-user-info .user-avatar { width:32px; height:32px; border-radius:50%; object-fit:cover; border:2px solid rgba(255,255,255,0.4); }

    /* Notifications */
    .notification-container { position: relative; }
    .notif-dropdown {
      position: absolute; top: 100%; right: 0; width: 280px;
      background: var(--bg-card, white); border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 1100;
      margin-top: 15px; overflow: hidden; display: none;
      border: 1px solid var(--border);
    }
    [data-theme="dark"] .notif-dropdown { background: #1e293b; border-color: rgba(255,255,255,0.1); }
    .notif-header {
      padding: 12px 16px; background: var(--bg);
      border-bottom: 1px solid var(--border);
      font-weight: 700; font-size: 0.9rem; color: var(--text);
    }
    .notif-item {
      padding: 12px 16px; border-bottom: 1px solid var(--border);
      display: flex; flex-direction: column; gap: 4px;
      transition: background 0.2s; text-decoration: none; color: inherit;
    }
    .notif-item:hover { background: rgba(0,0,0,0.02); }
    [data-theme="dark"] .notif-item:hover { background: rgba(255,255,255,0.05); }
    .notif-title { font-weight: 600; font-size: 0.85rem; color: var(--text); }
    .notif-desc { font-size: 0.75rem; color: var(--text-muted); }

    /* Dark theme overrides */
    [data-theme="dark"] body { background: #0f172a; color: #e2e8f0; }
    [data-theme="dark"] .card,
    [data-theme="dark"] .product-card { background: #1e293b; border-color: rgba(255,255,255,0.08); }
    [data-theme="dark"] .product-name { color: #f1f5f9; }
    [data-theme="dark"] .product-image-container { background: #1e293b; }
    [data-theme="dark"] .product-category { background: #0f172a; }
    [data-theme="dark"] input { background: #1e293b !important; color: #e2e8f0 !important; border-color: rgba(255,255,255,0.12) !important; }
    [data-theme="dark"] input::placeholder { color: #64748b; }
    [data-theme="dark"] section { background: #0f172a !important; }

    .product-card {
        padding: 0;
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
    }
    .product-image-container {
        height: 200px;
        background: var(--bg);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .product-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .product-category {
        position: absolute;
        top: 15px;
        right: 15px;
        background: var(--bg-card);
        padding: 4px 12px;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--primary);
        box-shadow: var(--shadow-sm);
        z-index: 2;
    }
    .product-content {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        flex-grow: 1;
    }
    .product-name {
        font-size: 1.15rem;
        margin: 0;
        color: var(--dark);
    }
    .product-price {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--primary);
    }
    .product-stock {
        font-size: 0.82rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .in-stock { color: var(--accent); }
    .out-of-stock { color: var(--danger); }
  </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <a href="../front/indexp.php" class="navbar-brand">
            <div class="navbar-logo">🏥</div>
            <div class="navbar-name">ASCL<span>EPIA</span></div>
        </a>

  <div class="nav-links" id="navLinks">
    <a href="../front/indexp.php" class="nav-link"><?= tr('nav_home') ?></a>
    <a href="../front/indexp.php#services" class="nav-link"><?= tr('nav_services') ?></a>
    <a href="pharmacies.php" class="nav-link"><?= tr('nav_pharmacies') ?></a>
    <a href="medicaments.php" class="nav-link active"><?= tr('nav_medicaments') ?></a>
    <a href="assurancefront.php" class="nav-link"><?= tr('nav_insurances') ?></a>
  </div>

  <div class="nav-actions">
    <div style="display:flex; align-items:center; gap:10px; margin-right:15px;">
      <!-- Theme Toggle -->
      <button id="themeToggle" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:var(--white);" title="Mode Sombre/Clair">
        <i class="fa-solid fa-moon"></i>
      </button>
      <!-- Notification Bell -->
      <div class="notification-container">
        <button id="notifToggle" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:var(--white); position:relative;" title="Alertes Stocks">
          <i class="fa-solid fa-bell"></i>
          <?php if($countAlertes > 0): ?>
            <span style="position:absolute; top:-5px; right:-5px; background:var(--primary); color:white; border-radius:50%; width:18px; height:18px; font-size:0.65rem; display:flex; align-items:center; justify-content:center; font-weight:700;">
              <?= $countAlertes ?>
            </span>
          <?php endif; ?>
        </button>
        <div id="notifDropdown" class="notif-dropdown">
          <div class="notif-header">Alertes Stocks</div>
          <div style="max-height:300px; overflow-y:auto;">
            <?php if($countAlertes > 0): ?>
              <?php foreach($alertesStock as $alerte): ?>
                <a href="medicaments.php" class="notif-item">
                  <div class="notif-title"><?= htmlspecialchars($alerte['nom']) ?></div>
                  <div class="notif-desc" style="color:#f59e0b;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Stock faible: <?= $alerte['stock'] ?> restants
                  </div>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <div style="padding:20px; text-align:center; color:var(--text-muted); font-size:0.85rem;">Aucune alerte.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php if ($isLoggedIn): ?>
      <div class="nav-user-info">
        <div class="avatar-css small avatar-<?= htmlspecialchars($userAvatar) ?>"></div>
        <span><?= htmlspecialchars($userNom) ?></span>
      </div>
      <?php if ($isAdmin): ?>
        <a href="../back/dashboard.php" class="btn btn-outline-white btn-sm">
          <i class="fa-solid fa-gauge"></i> Admin
        </a>
      <?php endif; ?>
      <a href="../back/logout.php" class="btn btn-outline-white btn-sm">
        <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
      </a>
    <?php else: ?>
      <a href="login.html" class="btn btn-outline-white btn-sm"><?= tr('btn_login') ?></a>
      <a href="../front/loginuser.html" class="btn btn-primary btn-sm"><?= tr('btn_register') ?></a>
    <?php endif; ?>
  </div>
  <div class="hamburger" onclick="document.getElementById('navLinks').classList.toggle('open')">
    <span></span><span></span><span></span>
  </div>
</nav>

<section class="section-padding" style="margin-top: 80px; background: var(--bg);">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">
        <i class="fa-solid fa-pills"></i>
        <?= tr('md_tag') ?>
      </div>
      <h2 class="section-title"><?= tr('md_title') ?></h2>
      <p class="section-desc"><?= tr('md_desc') ?></p>
    </div>

    <!-- Search bar -->
    <div style="max-width: 480px; margin: 0 auto 40px; position: relative;">
      <i class="fa-solid fa-search" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--gray-light);"></i>
      <input type="text" placeholder="Rechercher un médicament..." id="medSearch"
        style="width: 100%; padding: 14px 18px 14px 48px; border: 2px solid var(--border); border-radius: var(--radius-full); font-size: 0.95rem; outline: none; background: var(--bg-card); font-family: var(--font-main);"
        onkeyup="filterMeds()">
    </div>

    <div class="row" id="medsGrid">
      <?php if (!empty($listeMedicaments)): ?>
        <?php foreach ($listeMedicaments as $m): ?>
          <div class="col-4 med-item" data-nom="<?= strtolower(htmlspecialchars($m['nom'])) ?>">
            <div class="card product-card">
              <div class="product-image-container">
                <?php 
                  $imgPath = htmlspecialchars($m['images']);
                  if(empty($m['images'])) {
                    $imgPath = "https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&q=80&w=400";
                  }
                ?>
                <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($m['nom']) ?>" class="product-img">
                <span class="product-category"><?= htmlspecialchars($m['categorie']) ?></span>
              </div>
              <div class="product-content">
                <h3 class="product-name"><?= htmlspecialchars($m['nom']) ?></h3>
                <div class="product-price"><?= number_format($m['prix'], 3) ?> DT</div>
                <div class="product-stock <?= $m['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                  <i class="fa-solid <?= $m['stock'] > 0 ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
                  <?= $m['stock'] > 0 ? tr('md_in_stock') : tr('md_out_stock') ?>
                </div>
                <div class="product-actions" style="margin-top: 10px;">
                  <button class="btn btn-primary btn-sm" style="flex: 1;"><?= tr('md_btn_buy') ?></button>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; width: 100%; color: var(--text-muted);"><?= tr('md_empty') ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
function filterMeds() {
    let input = document.getElementById('medSearch').value.toLowerCase();
    let items = document.getElementsByClassName('med-item');
    for (let i = 0; i < items.length; i++) {
        let name = items[i].getAttribute('data-nom');
        items[i].style.display = name.includes(input) ? "" : "none";
    }
}

document.addEventListener('DOMContentLoaded', function() {
  // ---- Dark Theme ----
  const themeToggle = document.getElementById('themeToggle');
  const root = document.documentElement;
  const savedTheme = localStorage.getItem('theme') || 'light';
  root.setAttribute('data-theme', savedTheme);
  if (themeToggle) {
    const icon = themeToggle.querySelector('i');
    if (savedTheme === 'dark' && icon) icon.classList.replace('fa-moon', 'fa-sun');
    themeToggle.addEventListener('click', () => {
      const newTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
      const newIcon = themeToggle.querySelector('i');
      if (newTheme === 'dark') newIcon.classList.replace('fa-moon', 'fa-sun');
      else newIcon.classList.replace('fa-sun', 'fa-moon');
    });
  }

  // ---- Notifications ----
  const notifToggle = document.getElementById('notifToggle');
  const notifDropdown = document.getElementById('notifDropdown');
  if (notifToggle && notifDropdown) {
    notifToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
    });
    document.addEventListener('click', () => { notifDropdown.style.display = 'none'; });
  }
});
</script>

<footer class="footer" style="background: var(--dark); color: white; padding: 60px 0 30px;">
    <div class="container text-center">
        <p>&copy; <?= date('Y') ?> ASCLEPIA. Tous droits réservés.</p>
    </div>
</footer>

</body>
</html>