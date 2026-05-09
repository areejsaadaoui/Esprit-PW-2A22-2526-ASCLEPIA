<?php
session_start();
require_once '../../Controller/LanguageController.php';
require_once '../../Controller/PharmacieC.php';
include '../../Controller/UserController.php';

// === SESSION (comme assurancefront.php) ===
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userId     = $_SESSION['user_id']    ?? null;
$userNom    = $_SESSION['user_nom']   ?? '';
$userRole   = $_SESSION['user_role']  ?? '';
$isAdmin    = ($userRole === 'admin');

$userC      = new UserController();
$userAvatar = ($isLoggedIn && $userId) ? $userC->getAvatarByUserId($userId) : 'default';

$pc = new pharmacieC();
$listePharmacies = $pc->listepharmacie();

// Stock alerts — need medicaments data
require_once '../../Controller/MedicamentC.php';
$mc = new medicamentC();
$_allMeds = $mc->afficherMedicaments()->fetchAll();
$alertesStock = array_filter($_allMeds, function($m) { return $m['stock'] > 0 && $m['stock'] <= 5; });
$countAlertes = count($alertesStock);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Toutes les pharmacies partenaires d'ASCLEPIA.">
  <title>Pharmacies — ASCLEPIA</title>

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
    [data-theme="dark"] .pharmacie-card { background: #1e293b; border-color: rgba(255,255,255,0.08); }
    [data-theme="dark"] h3 { color: #f1f5f9; }
    [data-theme="dark"] input { background: #1e293b !important; color: #e2e8f0 !important; border-color: rgba(255,255,255,0.12) !important; }
    [data-theme="dark"] input::placeholder { color: #64748b; }
    [data-theme="dark"] section { background: #0f172a !important; }
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
    <a href="pharmacies.php" class="nav-link active"><?= tr('nav_pharmacies') ?></a>
    <a href="medicaments.php" class="nav-link"><?= tr('nav_medicaments') ?></a>
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

<section class="section-padding" style="margin-top: 80px; background: var(--white);">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">
        <i class="fa-solid fa-hospital"></i>
        <?= tr('ph_tag') ?>
      </div>
      <h2 class="section-title"><?= tr('ph_title') ?></h2>
      <p class="section-desc"><?= tr('ph_desc') ?></p>
    </div>

    <!-- Search bar -->
    <div style="max-width: 480px; margin: 0 auto 40px; position: relative;">
      <i class="fa-solid fa-search" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--gray-light);"></i>
      <input type="text" placeholder="<?= tr('ph_search') ?>" id="pharmSearch"
        style="width: 100%; padding: 14px 18px 14px 48px; border: 2px solid var(--border); border-radius: var(--radius-full); font-size: 0.95rem; outline: none; background: var(--bg); font-family: var(--font-main);"
        onkeyup="filterPharmacies()">
    </div>

    <div class="row" id="pharmaciesGrid">
      <?php if (!empty($listePharmacies)): ?>
        <?php foreach ($listePharmacies as $p): ?>
          <div class="col-4 pharm-item" data-nom="<?= strtolower(htmlspecialchars($p['nom'])) ?>">
            <div class="card pharmacie-card" style="gap: 16px; flex-direction: column; padding: 24px; height: 100%;">
              <div class="d-flex align-center" style="gap: 16px;">
                <div class="icon-box" style="background: linear-gradient(135deg,#10b981,#059669);">
                  <i class="fa-solid fa-mortar-pestle"></i>
                </div>
                <div>
                  <h3 style="font-size: 1rem; margin-bottom: 2px;"><?= htmlspecialchars($p['nom']) ?></h3>
                  <span class="badge badge-success"><?= tr('ph_badge_open') ?></span>
                </div>
              </div>
              <div style="display: flex; flex-direction: column; gap: 6px;">
                <div class="d-flex align-center gap-1" style="font-size: 0.84rem; color: var(--text-muted);">
                  <i class="fa-solid fa-location-dot" style="color: var(--primary); width: 16px;"></i>
                  <?= htmlspecialchars($p['adresse']) ?>
                </div>
                <div class="d-flex align-center gap-1" style="font-size: 0.84rem; color: var(--text-muted);">
                  <i class="fa-solid fa-phone" style="color: var(--primary); width: 16px;"></i>
                  <?= htmlspecialchars($p['telephone']) ?>
                </div>
              </div>
              <div class="d-flex" style="gap: 8px; margin-top: auto; padding-top: 15px;">
                  <a href="medicaments.php?id_pharmacie=<?= $p['id_pharmacie'] ?>" class="btn btn-outline btn-sm" style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fa-solid fa-pills"></i> Voir médicaments
                  </a>
                  <a href="https://www.google.com/maps/search/<?= urlencode($p['nom'] . ' ' . $p['adresse']) ?>" 
                     target="_blank" 
                     class="btn btn-primary btn-sm" style="width: 42px; display: flex; align-items: center; justify-content: center;" title="Ouvrir dans Google Maps">
                    <i class="fa-solid fa-location-arrow"></i>
                  </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; width: 100%; color: var(--text-muted);"><?= tr('ph_empty') ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
function filterPharmacies() {
    let input = document.getElementById('pharmSearch').value.toLowerCase();
    let items = document.getElementsByClassName('pharm-item');
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