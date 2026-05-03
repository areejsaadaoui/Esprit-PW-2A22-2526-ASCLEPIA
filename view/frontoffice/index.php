<?php
require_once '../../Controller/LanguageController.php';
require_once '../../Controller/PharmacieC.php';
require_once '../../Controller/MedicamentC.php';
$pc = new pharmacieC();
$mc = new medicamentC();
$listePharmacies = $pc->listepharmacie();
// On récupère tout dans un tableau pour éviter d'épuiser l'itérateur PDO
$listeMedicaments = $mc->afficherMedicaments()->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ASCLEPIA - Plateforme médicale en ligne.">
  <title>ASCLEPIA — <?= tr('hero_badge') ?></title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Styles -->
  <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../assets/css/frontoffice.css?v=<?= time() ?>">
  <style>
    /* CSS pour les Notifications FrontOffice */
    .notification-container {
      position: relative;
    }
    .notif-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      width: 280px;
      background: var(--bg-card, white);
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      z-index: 1100;
      margin-top: 15px;
      overflow: hidden;
      display: none;
      border: 1px solid var(--border);
    }
    [data-theme="dark"] .notif-dropdown {
      background: #1e293b;
      border-color: rgba(255,255,255,0.1);
    }
    .notif-header {
      padding: 12px 16px;
      background: var(--bg);
      border-bottom: 1px solid var(--border);
      font-weight: 700;
      font-size: 0.9rem;
      color: var(--text);
    }
    .notif-item {
      padding: 12px 16px;
      border-bottom: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      gap: 4px;
      transition: background 0.2s;
      text-decoration: none;
      color: inherit;
    }
    .notif-item:hover {
      background: rgba(0,0,0,0.02);
    }
    [data-theme="dark"] .notif-item:hover {
      background: rgba(255,255,255,0.05);
    }
    .notif-title {
      font-weight: 600;
      font-size: 0.85rem;
      color: var(--text);
    }
    .notif-desc {
      font-size: 0.75rem;
      color: var(--text-muted);
    }
  </style>
</head>

<body>





<!-- ================================================
     NAVBAR
     ================================================ -->
<nav class="navbar" id="navbar">
  <a href="index.php" class="navbar-brand" style="display: flex; align-items: center; text-decoration: none;">
    <img src="../assets/image/logo.png?v=<?php echo time(); ?>" alt="ASCLEPIA Logo" style="height: 55px; object-fit: contain;">
  </a>

  <div class="nav-links" id="navLinks">
    <a href="#accueil" class="nav-link active"><?= tr('nav_home') ?></a>
    <a href="#services" class="nav-link"><?= tr('nav_services') ?></a>
    <a href="#pharmacies" class="nav-link"><?= tr('nav_pharmacies') ?></a>
    <a href="#produits" class="nav-link"><?= tr('nav_medicaments') ?></a>
    <a href="#assurances" class="nav-link"><?= tr('nav_insurances') ?></a>
    <a href="#forum" class="nav-link"><?= tr('nav_forum') ?></a>
    <a href="#avis" class="nav-link"><?= tr('nav_reviews') ?></a>
  </div>

  <div class="nav-actions">
    <!-- Language Toggle -->
    <div class="lang-toggle" style="display:flex; gap: 5px; margin-right: 15px; align-items:center;">
        <a href="?lang=fr" style="color: <?= $_SESSION['lang'] === 'fr' ? 'var(--primary)' : 'var(--text-muted)' ?>; font-weight: 700; text-decoration: none;">FR</a>
        <span style="color: var(--text-muted);">|</span>
        <a href="?lang=en" style="color: <?= $_SESSION['lang'] === 'en' ? 'var(--primary)' : 'var(--text-muted)' ?>; font-weight: 700; text-decoration: none;">EN</a>
    </div>

    <!-- Theme Toggle -->
    <button id="themeToggle" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--white); margin-right: 15px;" title="Activer le mode sombre">
        <i class="fa-solid fa-moon"></i>
    </button>

    <!-- Notification Bell -->
    <div class="notification-container" style="margin-right: 15px;">
        <?php 
          // Récupérer les médicaments en stock limité (ex: entre 1 et 5)
          $alertesStock = array_filter($listeMedicaments, function($m) {
              return $m['stock'] > 0 && $m['stock'] <= 5;
          });
          $countAlertes = count($alertesStock);
        ?>
        <button id="notifToggle" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--white); position: relative;" title="Alertes Stocks">
            <i class="fa-solid fa-bell"></i>
            <?php if($countAlertes > 0): ?>
                <span style="position: absolute; top: -5px; right: -5px; background: var(--primary); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.65rem; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                    <?= $countAlertes ?>
                </span>
            <?php endif; ?>
        </button>
        
        <div id="notifDropdown" class="notif-dropdown">
            <div class="notif-header">
                <?= tr('nav_notifications') ?? 'Alertes Stocks' ?>
            </div>
            <div style="max-height: 300px; overflow-y: auto;">
                <?php if($countAlertes > 0): ?>
                    <?php foreach($alertesStock as $alerte): ?>
                        <a href="#produits" class="notif-item">
                            <div class="notif-title"><?= htmlspecialchars($alerte['nom']) ?></div>
                            <div class="notif-desc" style="color: #f59e0b;">
                                <i class="fa-solid fa-clock"></i> <?= tr('md_stock_limited') ?? 'Bientôt en rupture !' ?> (<?= $alerte['stock'] ?>)
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                        <i class="fa-solid fa-bell-slash" style="font-size: 1.5rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                        <?= tr('no_notif') ?? 'Aucune alerte pour le moment.' ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <a href="login.html" class="btn btn-outline-white btn-sm d-none-mobile"><?= tr('btn_login') ?></a>
    <a href="login.html" class="btn btn-primary btn-sm"><?= tr('btn_register') ?></a>
    <div class="hamburger" id="hamburger" onclick="toggleMenu()">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>

<!-- ================================================
     HERO SECTION
     ================================================ -->
<section class="hero" id="accueil">
  <div class="hero-glow hero-glow-1"></div>
  <div class="hero-glow hero-glow-2"></div>

  <div class="container">
    <div class="d-flex align-center justify-between" style="gap: 48px;">

      <!-- Left Content -->
      <div class="hero-content animate-fadeInUp">
        <div class="hero-badge">
          <i class="fa-solid fa-circle-check"></i>
          <?= tr('hero_badge') ?>
        </div>

        <h1 class="hero-title">
          <?= tr('hero_title') ?>
        </h1>

        <p class="hero-subtitle">
          <?= tr('hero_subtitle') ?>
        </p>

        <div class="hero-actions">
          <a href="login.html" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-user-plus"></i>
            <?= tr('hero_btn_start') ?>
          </a>
          <a href="#services" class="btn btn-outline-white btn-lg">
            <i class="fa-solid fa-play"></i>
            <?= tr('hero_btn_discover') ?>
          </a>
        </div>

        <div class="hero-stats">
          <div class="hero-stat">
            <div class="number">500<span>+</span></div>
            <div class="label"><?= tr('stat_doctors') ?></div>
          </div>
          <div class="hero-stat">
            <div class="number">50<span>K+</span></div>
            <div class="label"><?= tr('stat_patients') ?></div>
          </div>
          <div class="hero-stat">
            <div class="number">120<span>+</span></div>
            <div class="label"><?= tr('stat_pharmacies') ?></div>
          </div>
          <div class="hero-stat">
            <div class="number">98<span>%</span></div>
            <div class="label"><?= tr('stat_satisfaction') ?></div>
          </div>
        </div>
      </div>

      <!-- Right Visual -->
      <div class="hero-visual d-none-mobile" style="flex: 0 0 400px;">
        <!-- Floating card 1 -->
        <div class="hero-card-float hero-card-1">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 36px; height: 36px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;">✅</div>
            <div>
              <div style="font-size: 0.78rem; font-weight: 700; color: white;">Consultation approuvée</div>
              <div style="font-size: 0.7rem; color: rgba(255,255,255,0.5);">Dr. Lamine Ben Ali · il y a 5 min</div>
            </div>
          </div>
        </div>

        <!-- Main card -->
        <div class="hero-main-card">
          <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 3rem; margin-bottom: 8px;">🏥</div>
            <h3 style="color: white; font-size: 1.1rem; margin-bottom: 4px;">Tableau de bord</h3>
            <p style="color: rgba(255,255,255,0.5); font-size: 0.82rem;">Votre espace personnel</p>
          </div>

          <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="background: rgba(255,255,255,0.06); border-radius: 12px; padding: 14px; display: flex; justify-content: space-between; align-items: center;">
              <span style="color: rgba(255,255,255,0.7); font-size: 0.85rem;">🩺 Consultations</span>
              <span style="color: var(--primary); font-weight: 700;">12</span>
            </div>
            <div style="background: rgba(255,255,255,0.06); border-radius: 12px; padding: 14px; display: flex; justify-content: space-between; align-items: center;">
              <span style="color: rgba(255,255,255,0.7); font-size: 0.85rem;">💊 Ordonnances</span>
              <span style="color: var(--accent); font-weight: 700;">5</span>
            </div>
            <div style="background: rgba(255,255,255,0.06); border-radius: 12px; padding: 14px; display: flex; justify-content: space-between; align-items: center;">
              <span style="color: rgba(255,255,255,0.7); font-size: 0.85rem;">🛡️ Assurance active</span>
              <span class="badge badge-success" style="font-size: 0.72rem;">Active</span>
            </div>
          </div>
        </div>

        <!-- Floating card 2 -->
        <div class="hero-card-float hero-card-2">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="font-size: 1.5rem;">💊</div>
            <div>
              <div style="font-size: 0.78rem; font-weight: 700; color: white;">Médicament disponible</div>
              <div style="font-size: 0.7rem; color: rgba(255,255,255,0.5);">Pharmacie Al Amal · Prêt</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ================================================
     SERVICES SECTION (5 Modules)
     ================================================ -->
<section class="section-padding services-section" id="services">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">
        <i class="fa-solid fa-star"></i>
        <?= tr('srv_tag') ?>
      </div>
      <h2 class="section-title"><?= tr('srv_title') ?></h2>
      <p class="section-desc">
        <?= tr('srv_desc') ?>
      </p>
    </div>

    <div class="row">

      <!-- Module 1: Authentification -->
      <div class="col-4">
        <div class="card service-card">
          <div class="icon-box icon-box-lg" style="background: linear-gradient(135deg,#6366f1,#8b5cf6);">
            <i class="fa-solid fa-user-shield"></i>
          </div>
          <h3><?= tr('srv_1_title') ?></h3>
          <p><?= tr('srv_1_desc') ?></p>
          <a href="login.html" class="btn btn-outline btn-sm mt-3">
            <?= tr('srv_1_btn') ?> <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>

      <!-- Module 2: Consultation -->
      <div class="col-4">
        <div class="card service-card">
          <div class="icon-box icon-box-lg" style="background: linear-gradient(135deg,#0ea5e9,#06b6d4);">
            <i class="fa-solid fa-stethoscope"></i>
          </div>
          <h3><?= tr('srv_2_title') ?></h3>
          <p><?= tr('srv_2_desc') ?></p>
          <a href="consultation.php" class="btn btn-outline btn-sm mt-3">
            <?= tr('srv_2_btn') ?> <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>

      <!-- Module 3: Pharmacie -->
      <div class="col-4">
        <div class="card service-card">
          <div class="icon-box icon-box-lg" style="background: linear-gradient(135deg,#10b981,#059669);">
            <i class="fa-solid fa-pills"></i>
          </div>
          <h3><?= tr('srv_3_title') ?></h3>
          <p><?= tr('srv_3_desc') ?></p>
          <a href="../backoffice/listepharmacie.php" class="btn btn-outline btn-sm mt-3">
            <?= tr('srv_3_btn') ?> <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>

      <!-- Module 4: Assurance -->
      <div class="col-4">
        <div class="card service-card">
          <div class="icon-box icon-box-lg" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <h3><?= tr('srv_4_title') ?></h3>
          <p><?= tr('srv_4_desc') ?></p>
          <a href="assurance.php" class="btn btn-outline btn-sm mt-3">
            <?= tr('srv_4_btn') ?> <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>

      <!-- Module 5: Forum -->
      <div class="col-4">
        <div class="card service-card">
          <div class="icon-box icon-box-lg" style="background: linear-gradient(135deg,#ec4899,#db2777);">
            <i class="fa-solid fa-comments"></i>
          </div>
          <h3><?= tr('srv_5_title') ?></h3>
          <p><?= tr('srv_5_desc') ?></p>
          <a href="forum.php" class="btn btn-outline btn-sm mt-3">
            <?= tr('srv_5_btn') ?> <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>

      <!-- CTA Card -->
      <div class="col-4">
        <div class="card service-card" style="background: var(--gradient-hero); border: none;">
          <div style="font-size: 3rem; margin-bottom: 16px;">🚀</div>
          <h3 style="color: white;"><?= tr('srv_cta_title') ?></h3>
          <p style="color: rgba(255,255,255,0.7);"><?= tr('srv_cta_desc') ?></p>
          <a href="login.html" class="btn btn-outline-white btn-sm mt-3">
            <?= tr('srv_cta_btn') ?> <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ================================================
     PHARMACIES SECTION
     ================================================ -->
<section class="section-padding" id="pharmacies" style="background: var(--white);">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">
        <i class="fa-solid fa-pills"></i>
        <?= tr('ph_tag') ?>
      </div>
      <h2 class="section-title"><?= tr('ph_title') ?></h2>
      <p class="section-desc"><?= tr('ph_desc') ?></p>
    </div>

    <!-- Search bar -->
    <div style="max-width: 480px; margin: 0 auto 48px; position: relative;">
      <i class="fa-solid fa-search" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--gray-light);"></i>
      <input type="text" placeholder="<?= tr('ph_search') ?>" id="pharmSearch"
        style="width: 100%; padding: 14px 18px 14px 48px; border: 2px solid var(--border); border-radius: var(--radius-full); font-size: 0.95rem; outline: none; background: var(--bg); font-family: var(--font-main);"
        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
    </div>

    <div class="row" id="pharmaciesGrid">

      <?php if (!empty($listePharmacies)): ?>
        <?php foreach ($listePharmacies as $p): ?>
          <div class="col-4 pharm-item" data-nom="<?= strtolower(htmlspecialchars($p['nom'])) ?>">
            <div class="card pharmacie-card" style="gap: 16px; flex-direction: column; padding: 24px;">
              <div class="d-flex align-center" style="gap: 16px;">
                <div class="icon-box" style="background: linear-gradient(135deg,#10b981,#059669);">
                  <i class="fa-solid fa-mortar-pestle"></i>
                </div>
                <div>
                  <h3 style="font-size: 1rem; margin-bottom: 2px;"><?= htmlspecialchars($p['nom']) ?></h3>
                  <!-- Badge statique pour l'instant pour éviter les erreurs de BDD -->
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
                <div class="d-flex align-center gap-1" style="font-size: 0.84rem; color: var(--text-muted);">
                  <i class="fa-solid fa-envelope" style="color: var(--primary); width: 16px;"></i>
                  <?= htmlspecialchars($p['email']) ?>
                </div>
              </div>
              <a href="../backoffice/listepharmacie.php" class="btn btn-outline btn-sm" style="align-self: flex-start;">
                <?= tr('ph_btn_view') ?>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; width: 100%; color: var(--text-muted);"><?= tr('ph_empty') ?></p>
      <?php endif; ?>

      <!-- Message aucun résultat -->
      <div id="pharmNoResult" style="display:none; text-align:center; width:100%; padding: 40px 0; color: var(--text-muted);">
        <i class="fa-solid fa-magnifying-glass" style="font-size: 2.5rem; margin-bottom: 16px; opacity:.35;"></i>
        <p style="font-size:1rem; font-weight:600;"><?= tr('ph_no_result') ?></p>
      </div>

    </div>
  </div>
</section>

<!-- ================================================
     MEDICAMENTS SECTION
     ================================================ -->
<section class="section-padding" id="produits" style="background: var(--bg);">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">
        <i class="fa-solid fa-pills"></i>
        <?= tr('md_tag') ?>
      </div>
      <h2 class="section-title"><?= tr('md_title') ?></h2>
      <p class="section-desc"><?= tr('md_desc') ?></p>
    </div>

    <div class="row">
      <?php if (!empty($listeMedicaments)): ?>
        <?php foreach ($listeMedicaments as $m): ?>
          <div class="col-4">
            <div class="card product-card">
              <div class="product-image-container">
                <?php 
                  $imgPath = htmlspecialchars($m['images']);
                  // Si l'image est vide ou n'existe pas, on met un placeholder premium
                  if(empty($m['images'])) {
                    $imgPath = "https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&q=80&w=400";
                  }
                ?>
                <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($m['nom']) ?>" class="product-img" onerror="this.src='https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&q=80&w=400'">
                <span class="product-category"><?= htmlspecialchars($m['categorie']) ?></span>
                
                <!-- QR Code Overlay (Caché par défaut) -->
                <?php 
                  $qrData = "ASCLEPIA - " . $m['nom'] . " (" . $m['categorie'] . ") | Prix: " . number_format($m['prix'],3) . " DT";
                  $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);
                ?>
                <button class="product-qr-btn" onclick="this.nextElementSibling.classList.toggle('show')" title="Scanner les infos">
                  <i class="fa-solid fa-qrcode"></i>
                </button>
                <div class="product-qr-code">
                   <img src="<?= $qrUrl ?>" alt="QR" style="width: 120px; height: 120px;">
                   <div class="qr-info-text">Scannez pour les détails</div>
                </div>
              </div>
              <div class="product-content">
                <h3 class="product-name"><?= htmlspecialchars($m['nom']) ?></h3>
                <div class="product-price"><?= number_format($m['prix'], 3) ?> DT</div>
                <div class="product-stock <?= $m['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                  <i class="fa-solid <?= $m['stock'] > 0 ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
                  <?= $m['stock'] > 0 ? tr('md_in_stock') : tr('md_out_stock') ?>
                </div>
                <div class="product-actions">
                  <button class="btn btn-primary btn-sm"><?= tr('md_btn_buy') ?></button>
                  <button class="btn btn-outline btn-sm"><i class="fa-solid fa-eye"></i></button>
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

<style>
  /* Product Section Premium Styles */
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
  .product-card:hover .product-img {
    transform: scale(1.1);
  }
  .no-image-placeholder {
    font-size: 3rem;
    color: var(--gray-light);
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

  /* QR Code Overlay */
  .product-qr-btn {
    position: absolute;
    bottom: 10px;
    left: 10px;
    width: 34px;
    height: 34px;
    background: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    cursor: pointer;
    box-shadow: var(--shadow-sm);
    z-index: 5;
    transition: var(--transition);
    border: none;
  }
  .product-qr-btn:hover {
    background: var(--primary);
    color: white;
    transform: scale(1.1);
  }
  .product-qr-code {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.95);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
    z-index: 4;
    padding: 20px;
    text-align: center;
  }
  .product-image-container:hover .product-qr-code.show {
    opacity: 1;
    visibility: visible;
  }
  .qr-info-text {
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--primary);
    margin-top: 8px;
    text-transform: uppercase;
  }
  
  .product-actions {
    margin-top: auto;
    display: flex;
    gap: 10px;
  }
    margin-top: 8px;
    text-transform: uppercase;
  }

  /* ---- CHATBOT STYLES ---- */
  .chatbot-bubble {
    position: fixed !important;
    bottom: 30px !important;
    right: 30px !important;
    width: 65px !important;
    height: 65px !important;
    background: var(--gradient-primary) !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: white !important;
    font-size: 1.6rem !important;
    cursor: pointer !important;
    box-shadow: 0 10px 30px rgba(14,165,233,0.5) !important;
    z-index: 999999 !important;
    transition: var(--transition) !important;
  }
  .chatbot-bubble:hover {
    transform: scale(1.1) rotate(15deg);
  }
  .chatbot-window {
    position: fixed;
    bottom: 100px;
    right: 30px;
    width: 350px;
    height: 500px;
    background: var(--bg-card);
    border-radius: 24px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 2000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px);
    transition: var(--transition);
    border: 1px solid var(--border);
  }
  .chatbot-window.open {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }
  .chatbot-header {
    background: var(--gradient-primary);
    padding: 20px;
    color: white;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .chatbot-header .bot-avatar {
    width: 40px; height: 40px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
  }
  .chatbot-messages {
    flex-grow: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: var(--bg);
  }
  .msg {
    max-width: 80%;
    padding: 12px 16px;
    border-radius: 18px;
    font-size: 0.9rem;
    line-height: 1.4;
  }
  .msg-bot {
    align-self: flex-start;
    background: white;
    color: var(--dark);
    border-bottom-left-radius: 4px;
    box-shadow: var(--shadow-sm);
  }
  .msg-user {
    align-self: flex-end;
    background: var(--primary);
    color: white;
    border-bottom-right-radius: 4px;
  }
  .chatbot-input-area {
    padding: 15px;
    background: var(--bg-card);
    border-top: 1px solid var(--border);
    display: flex;
    gap: 10px;
  }
  .chatbot-input-area input {
    flex-grow: 1;
    border: 1px solid var(--border);
    padding: 10px 15px;
    border-radius: var(--radius-full);
    outline: none;
    font-size: 0.9rem;
  }
  .chatbot-send-btn {
    background: var(--primary);
    color: white;
    border: none;
    width: 40px; height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
  }
  
  [data-theme="dark"] .msg-bot {
      background: #334155;
      color: white;
  }
</style>

<!-- ================================================
     ASSURANCES SECTION
     ================================================ -->
<section class="section-padding" id="assurances" style="background: var(--bg);">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">
        <i class="fa-solid fa-shield-halved"></i>
        <?= tr('as_tag') ?>
      </div>
      <h2 class="section-title"><?= tr('as_title') ?></h2>
      <p class="section-desc"><?= tr('as_desc') ?></p>
    </div>

    <div class="row">

      <div class="col-4">
        <div class="card assurance-card">
          <div class="icon-box icon-box-lg" style="margin: 0 auto 20px; background: linear-gradient(135deg,#f59e0b,#d97706);">
            <i class="fa-solid fa-star"></i>
          </div>
          <h3>STAR Assurance</h3>
          <p style="font-size: 0.85rem; color: var(--text-muted); margin: 8px 0;"><?= tr('as_1_desc') ?></p>
          <div class="rate">85<span>%</span></div>
          <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;"><?= tr('as_rate_label') ?></div>
          <div class="progress-bar-wrap">
            <div class="progress-bar" style="width: 85%;"></div>
          </div>
          <a href="assurance.php" class="btn btn-primary btn-sm mt-3" style="width: 100%; justify-content: center;">
            <?= tr('as_btn_more') ?>
          </a>
        </div>
      </div>

      <div class="col-4">
        <div class="card assurance-card" style="border: 2px solid var(--primary); position: relative;">
          <div style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%);">
            <span class="badge badge-primary" style="padding: 6px 16px; font-size: 0.75rem;"><?= tr('as_popular') ?></span>
          </div>
          <div class="icon-box icon-box-lg" style="margin: 0 auto 20px; background: var(--gradient-primary);">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <h3>CNAM Plus</h3>
          <p style="font-size: 0.85rem; color: var(--text-muted); margin: 8px 0;"><?= tr('as_2_desc') ?></p>
          <div class="rate">90<span>%</span></div>
          <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;"><?= tr('as_rate_label') ?></div>
          <div class="progress-bar-wrap">
            <div class="progress-bar" style="width: 90%;"></div>
          </div>
          <a href="assurance.php" class="btn btn-primary btn-sm mt-3" style="width: 100%; justify-content: center;">
            <?= tr('as_btn_more') ?>
          </a>
        </div>
      </div>

      <div class="col-4">
        <div class="card assurance-card">
          <div class="icon-box icon-box-lg" style="margin: 0 auto 20px; background: linear-gradient(135deg,#6366f1,#8b5cf6);">
            <i class="fa-solid fa-gem"></i>
          </div>
          <h3>GAT Premium</h3>
          <p style="font-size: 0.85rem; color: var(--text-muted); margin: 8px 0;"><?= tr('as_3_desc') ?></p>
          <div class="rate">95<span>%</span></div>
          <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;"><?= tr('as_rate_label') ?></div>
          <div class="progress-bar-wrap">
            <div class="progress-bar" style="width: 95%; background: linear-gradient(90deg,#6366f1,#8b5cf6);"></div>
          </div>
          <a href="assurance.php" class="btn btn-outline btn-sm mt-3" style="width: 100%; justify-content: center;">
            <?= tr('as_btn_more') ?>
          </a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ================================================
     FORUM / POSTS SECTION
     ================================================ -->
<section class="section-padding" id="forum" style="background: var(--white);">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">
        <i class="fa-solid fa-comments"></i>
        <?= tr('fr_tag') ?>
      </div>
      <h2 class="section-title"><?= tr('fr_title') ?></h2>
      <p class="section-desc"><?= tr('fr_desc') ?></p>
    </div>

    <div class="row">

      <div class="col-4">
        <div class="card post-card">
          <div class="post-meta">
            <div class="post-avatar">MA</div>
            <div>
              <div class="post-author"><?= tr('fr_post1_author') ?></div>
              <div class="post-date"><?= tr('fr_post1_date') ?></div>
            </div>
            <span class="badge badge-primary" style="margin-left: auto;">💬 12</span>
          </div>
          <h3><?= tr('fr_post1_title') ?></h3>
          <p><?= tr('fr_post1_desc') ?></p>
          <div class="post-footer">
            <div class="post-stat"><i class="fa-regular fa-heart"></i> 24 <?= tr('fr_likes') ?></div>
            <div class="post-stat"><i class="fa-regular fa-comment"></i> 12 <?= tr('fr_replies') ?></div>
            <a href="forum.php" class="btn btn-outline btn-sm"><?= tr('fr_btn_read') ?></a>
          </div>
        </div>
      </div>

      <div class="col-4">
        <div class="card post-card">
          <div class="post-meta">
            <div class="post-avatar" style="background: linear-gradient(135deg,#10b981,#059669);">SB</div>
            <div>
              <div class="post-author"><?= tr('fr_post2_author') ?></div>
              <div class="post-date"><?= tr('fr_post2_date') ?></div>
            </div>
            <span class="badge badge-success" style="margin-left: auto;">💬 8</span>
          </div>
          <h3><?= tr('fr_post2_title') ?></h3>
          <p><?= tr('fr_post2_desc') ?></p>
          <div class="post-footer">
            <div class="post-stat"><i class="fa-regular fa-heart"></i> 18 <?= tr('fr_likes') ?></div>
            <div class="post-stat"><i class="fa-regular fa-comment"></i> 8 <?= tr('fr_replies') ?></div>
            <a href="forum.php" class="btn btn-outline btn-sm"><?= tr('fr_btn_read') ?></a>
          </div>
        </div>
      </div>

      <div class="col-4">
        <div class="card post-card">
          <div class="post-meta">
            <div class="post-avatar" style="background: linear-gradient(135deg,#f59e0b,#d97706);">KM</div>
            <div>
              <div class="post-author"><?= tr('fr_post3_author') ?></div>
              <div class="post-date"><?= tr('fr_post3_date') ?></div>
            </div>
            <span class="badge badge-warning" style="margin-left: auto;">💬 31</span>
          </div>
          <h3><?= tr('fr_post3_title') ?></h3>
          <p><?= tr('fr_post3_desc') ?></p>
          <div class="post-footer">
            <div class="post-stat"><i class="fa-regular fa-heart"></i> 45 <?= tr('fr_likes') ?></div>
            <div class="post-stat"><i class="fa-regular fa-comment"></i> 31 <?= tr('fr_replies') ?></div>
            <a href="forum.php" class="btn btn-outline btn-sm"><?= tr('fr_btn_read') ?></a>
          </div>
        </div>
      </div>

    </div>

    <div style="text-align: center; margin-top: 40px;">
      <a href="forum.php" class="btn btn-primary btn-lg">
        <i class="fa-solid fa-comments"></i>
        <?= tr('fr_btn_all') ?>
      </a>
    </div>
  </div>
</section>

<!-- ================================================
     AVIS / TÉMOIGNAGES SECTION
     ================================================ -->
<section class="section-padding avis-section" id="avis">
  <div class="container" style="position: relative; z-index: 1;">
    <div class="section-header">
      <div class="section-tag">⭐ <?= tr('rv_tag') ?></div>
      <h2 class="section-title"><?= tr('rv_title') ?></h2>
      <p class="section-desc" style="color: rgba(255,255,255,0.6);"><?= tr('rv_desc') ?></p>
    </div>

    <div class="row">

      <div class="col-4">
        <div class="avis-card">
          <div class="avis-quote">"</div>
          <p class="avis-text">
            <?= tr('rv_1_text') ?>
          </p>
          <div class="stars">★★★★★</div>
          <div class="avis-author mt-2">
            <div class="avis-avatar">LB</div>
            <div class="avis-author-info">
              <div class="name">Leila Bchir</div>
              <div class="role"><?= tr('rv_1_author') ?> · Tunis</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-4">
        <div class="avis-card">
          <div class="avis-quote">"</div>
          <p class="avis-text">
            <?= tr('rv_3_text') ?>
          </p>
          <div class="stars">★★★★★</div>
          <div class="avis-author mt-2">
            <div class="avis-avatar" style="background: linear-gradient(135deg,#6366f1,#8b5cf6);">AM</div>
            <div class="avis-author-info">
              <div class="name">Dr. Ahmed Mrad</div>
              <div class="role"><?= tr('rv_3_author') ?> · Sfax</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-4">
        <div class="avis-card">
          <div class="avis-quote">"</div>
          <p class="avis-text">
            <?= tr('rv_2_text') ?>
          </p>
          <div class="stars">★★★★☆</div>
          <div class="avis-author mt-2">
            <div class="avis-avatar" style="background: linear-gradient(135deg,#10b981,#059669);">FZ</div>
            <div class="avis-author-info">
              <div class="name">Fatma Zouari</div>
              <div class="role"><?= tr('rv_2_author') ?> · Sousse</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ================================================
     CTA SECTION
     ================================================ -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <div class="section-tag" style="justify-content: center; margin-bottom: 20px;">
        <i class="fa-solid fa-rocket"></i>
        <?= tr('srv_cta_title') ?>
      </div>
      <h2><?= tr('srv_cta_title') ?></h2>
      <p><?= tr('srv_cta_desc') ?></p>
      <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
        <a href="login.php" class="btn btn-primary btn-lg">
          <i class="fa-solid fa-user-plus"></i>
          <?= tr('srv_cta_btn') ?>
        </a>
        <a href="login.php" class="btn btn-outline-white btn-lg">
          <i class="fa-solid fa-sign-in-alt"></i>
          <?= tr('btn_login') ?>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ================================================
     FOOTER
     ================================================ -->
<footer class="footer">
  <div class="container">
    <div class="row" style="gap: 48px;">

      <!-- Brand -->
      <div style="flex: 0 0 260px;">
        <div class="footer-brand">
          <div class="navbar-brand" style="margin-bottom: 16px; display: flex; align-items: center;">
            <img src="../assets/image/logo.png?v=<?php echo time(); ?>" alt="ASCLEPIA Logo" style="height: 65px; object-fit: contain;">
          </div>
          <p><?= tr('ft_desc') ?></p>
          <div class="social-links">
            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>
      </div>

      <!-- Services -->
      <div class="col">
        <div class="footer-section">
          <h4><?= tr('nav_services') ?></h4>
          <ul class="footer-links">
            <li><a href="consultation.php"><i class="fa-solid fa-stethoscope"></i> <?= tr('nav_services') ?></a></li>
            <li><a href="consultation.php"><i class="fa-solid fa-file-prescription"></i> <?= tr('hc_prescriptions') ?></a></li>
            <li><a href="../backoffice/listepharmacie.php"><i class="fa-solid fa-pills"></i> <?= tr('nav_pharmacies') ?></a></li>
            <li><a href="assurance.php"><i class="fa-solid fa-shield-halved"></i> <?= tr('nav_insurances') ?></a></li>
            <li><a href="forum.php"><i class="fa-solid fa-comments"></i> <?= tr('nav_forum') ?></a></li>
          </ul>
        </div>
      </div>

      <!-- Liens -->
      <div class="col">
        <div class="footer-section">
          <h4><?= tr('ft_links') ?></h4>
          <ul class="footer-links">
            <li><a href="index.php"><i class="fa-solid fa-home"></i> <?= tr('nav_home') ?></a></li>
            <li><a href="login.html"><i class="fa-solid fa-user-plus"></i> <?= tr('btn_register') ?></a></li>
            <li><a href="login.html"><i class="fa-solid fa-sign-in-alt"></i> <?= tr('btn_login') ?></a></li>
            <li><a href="#avis"><i class="fa-solid fa-star"></i> <?= tr('nav_reviews') ?></a></li>
            <li><a href="#"><i class="fa-solid fa-file-lines"></i> <?= tr('ft_privacy') ?></a></li>
          </ul>
        </div>
      </div>

      <!-- Contact -->
      <div class="col">
        <div class="footer-section">
          <h4><?= tr('ft_contact') ?></h4>
          <div class="footer-contact-item">
            <i class="fa-solid fa-location-dot icon"></i>
            <span>Rue de l'Innovation, Tunis 1002, Tunisie</span>
          </div>
          <div class="footer-contact-item">
            <i class="fa-solid fa-phone icon"></i>
            <span>+216 71 000 000</span>
          </div>
          <div class="footer-contact-item">
            <i class="fa-solid fa-envelope icon"></i>
            <span>contact@asclepia.tn</span>
          </div>
          <div class="footer-contact-item">
            <i class="fa-solid fa-clock icon"></i>
            <span>24h/7j — Service disponible en permanence</span>
          </div>
        </div>
      </div>

    </div>

    <div class="footer-bottom">
      <p>© 2026 <a href="index.php">ASCLEPIA</a>. <?= tr('ft_rights') ?></p>
      <p>Conçu avec  pour une meilleure santé</p>
    </div>
  </div>

  </div>
</footer>

<!-- ================================================
     SCRIPTS
     ================================================ -->

<!-- ================================================
     CHATBOT UI
     ================================================ -->
<div class="chatbot-bubble" id="chatBubble">
    <i class="fa-solid fa-robot"></i>
</div>

<div class="chatbot-window" id="chatWindow">
    <div class="chatbot-header">
        <div class="bot-avatar"><i class="fa-solid fa-robot"></i></div>
        <div>
            <div style="font-weight: 700; font-size: 1rem;">ASCLEPIA AI</div>
            <div style="font-size: 0.75rem; opacity: 0.8;">Assistant Médical Virtuel</div>
        </div>
        <button id="closeChat" style="margin-left: auto; background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="chatbot-messages" id="chatMessages">
        <div class="msg msg-bot animate-fadeInUp">
            Bonjour ! Je suis l'assistant ASCLEPIA. Comment puis-je vous aider aujourd'hui ? 😊
        </div>
    </div>
    <form class="chatbot-input-area" id="chatForm">
        <input type="text" id="chatInput" placeholder="Posez votre question ici..." autocomplete="off">
        <button type="submit" class="chatbot-send-btn">
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </form>
</div>

<script src="../assets/js/theme.js?v=<?= time() ?>"></script>
<script>
  // ---- Chatbot Logic ----
  const chatBubble = document.getElementById('chatBubble');
  const chatWindow = document.getElementById('chatWindow');
  const closeChat = document.getElementById('closeChat');
  const chatForm = document.getElementById('chatForm');
  const chatInput = document.getElementById('chatInput');
  const chatMessages = document.getElementById('chatMessages');

  chatBubble.addEventListener('click', () => {
    chatWindow.classList.toggle('open');
  });

  closeChat.addEventListener('click', () => {
    chatWindow.classList.remove('open');
  });

  function addMessage(text, isUser = false) {
    const msg = document.createElement('div');
    msg.className = `msg ${isUser ? 'msg-user' : 'msg-bot'} animate-fadeInUp`;
    msg.textContent = text;
    chatMessages.appendChild(msg);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  function getBotResponse(input) {
    const lowerInput = input.toLowerCase();
    
    if (lowerInput.includes('bonjour') || lowerInput.includes('salut')) {
      return "Bonjour ! Je suis là pour vous aider à trouver des médicaments ou des pharmacies. Que cherchez-vous ?";
    }
    if (lowerInput.includes('médicament') || lowerInput.includes('pills')) {
      return "Nous avons un large catalogue de médicaments (Analgésiques, Vitamines, etc.). Vous pouvez les voir dans la section 'Produits'.";
    }
    if (lowerInput.includes('douleur') || lowerInput.includes('tête')) {
      return "Pour une douleur légère, nos pharmaciens conseillent souvent des analgésiques comme le Paracétamol. Voulez-vous voir la liste ?";
    }
    if (lowerInput.includes('pharmacie') || lowerInput.includes('garde')) {
      return "Vous pouvez trouver toutes les pharmacies partenaires et leurs horaires dans la section 'Nos Pharmacies'.";
    }
    if (lowerInput.includes('merci')) {
      return "Je vous en prie ! Prenez soin de votre santé. 💙";
    }
    
    // Fallback : Simulation d'appel API
    return "C'est une excellente question. En tant qu'IA ASCLEPIA, je vous conseille de consulter nos spécialistes ou de vérifier la disponibilité de nos produits en stock.";
  }

  chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const text = chatInput.value.trim();
    if (text === '') return;

    addMessage(text, true);
    chatInput.value = '';

    // Simulation de réflexion de l'IA
    setTimeout(() => {
      const response = getBotResponse(text);
      addMessage(response, false);
    }, 800);
  });

  // ---- Navbar scroll effect ----
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 30);
  });

  // ---- Mobile menu ----
  function toggleMenu() {
    document.getElementById('navLinks').classList.toggle('open');
  }

  // ---- Smooth scroll for nav links ----
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        document.getElementById('navLinks').classList.remove('open');
      }
    });
  });

  // ---- Active nav link on scroll ----
  const sections = document.querySelectorAll('section[id], div[id]');
  const navLinks = document.querySelectorAll('.nav-link');

  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
      const sectionTop = section.offsetTop - 100;
      if (window.scrollY >= sectionTop) current = section.getAttribute('id');
    });
    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href') === `#${current}`) link.classList.add('active');
    });
  });

  // ---- Animate progress bars on scroll ----
  const progressBars = document.querySelectorAll('.progress-bar');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.width = entry.target.getAttribute('data-width') || entry.target.style.width;
      }
    });
  }, { threshold: 0.5 });
  progressBars.forEach(bar => observer.observe(bar));

  // ---- Animate counters ----
  function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const step = target / (duration / 16);
    const timer = setInterval(() => {
      start += step;
      if (start >= target) { start = target; clearInterval(timer); }
      element.textContent = Math.floor(start).toLocaleString();
    }, 16);
  }

  // ---- Cards entrance animation ----
  const cards = document.querySelectorAll('.card, .avis-card, .pharmacie-card');
  const cardObserver = new IntersectionObserver(entries => {
    entries.forEach((entry, i) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
        }, i * 80);
        cardObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
  cards.forEach(card => cardObserver.observe(card));

  // ---- Recherche pharmacie en temps réel ----
  const pharmSearch = document.getElementById('pharmSearch');
  if (pharmSearch) {
    pharmSearch.addEventListener('input', function () {
      const query = this.value.trim().toLowerCase();
      const items  = document.querySelectorAll('.pharm-item');
      const noResult = document.getElementById('pharmNoResult');
      let visible = 0;

      items.forEach(function (item) {
        const nom = item.getAttribute('data-nom') || '';
        if (nom.includes(query)) {
          item.style.display = '';
          // Micro-animation d'apparition
          item.style.opacity  = '0';
          item.style.transform = 'translateY(12px)';
          setTimeout(function () {
            item.style.transition = 'opacity .3s ease, transform .3s ease';
            item.style.opacity  = '1';
            item.style.transform = 'translateY(0)';
          }, 20);
          visible++;
        } else {
          item.style.display = 'none';
        }
      });

      noResult.style.display = (visible === 0 && query !== '') ? 'block' : 'none';
    });
  }

  // ---- Notification Toggle ----
  document.addEventListener('DOMContentLoaded', () => {
    const notifToggle = document.getElementById('notifToggle');
    const notifDropdown = document.getElementById('notifDropdown');
    const notifSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');

    if(notifToggle) {
      notifToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        
        // Jouer le son si le menu s'ouvre
        const isOpen = notifDropdown.style.display === 'block';
        if (!isOpen) {
          notifSound.play().catch(p => console.log("Audio blocked"));
          notifDropdown.style.display = 'block';
        } else {
          notifDropdown.style.display = 'none';
        }
      });
      
      document.addEventListener('click', () => {
        if(notifDropdown) notifDropdown.style.display = 'none';
      });
    }
  });
</script>

    }
  });
</script>

<!-- CHATBOT GROQ -->
<style>
  #groq-bubble {
    position: fixed !important; bottom: 30px !important; right: 30px !important; width: 60px !important; height: 60px !important;
    background: #0ea5e9 !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important;
    cursor: pointer !important; box-shadow: 0 10px 25px rgba(14,165,233,0.4) !important; z-index: 100000 !important;
  }
  #groq-window {
    position: fixed !important; bottom: 100px !important; right: 30px !important; width: 350px !important; height: 450px !important;
    background: white !important; border-radius: 20px !important; box-shadow: 0 15px 40px rgba(0,0,0,0.2) !important;
    display: none; flex-direction: column !important; overflow: hidden !important; z-index: 100000 !important; border: 1px solid #e2e8f0 !important;
  }
</style>

<div id="groq-bubble" onclick="toggleGroq()">
  <i class="fas fa-robot" style="color: white; font-size: 24px;"></i>
</div>

<div id="groq-window">
  <div style="background: #0ea5e9; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
    <span style="font-weight: bold;">ASCLEPIA Groq AI</span>
    <button onclick="toggleGroq()" style="background: none; border: none; color: white; cursor: pointer; font-size: 20px;">&times;</button>
  </div>
  <div id="groq-messages" style="flex: 1; padding: 15px; overflow-y: auto; background: #f8fafc; display: flex; flex-direction: column; gap: 10px;">
    <div style="background: white; padding: 10px; border-radius: 10px; font-size: 0.9rem; align-self: flex-start; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
      Bonjour ! Je suis propulsé par Groq. Comment puis-je vous aider ?
    </div>
  </div>
  <form id="groq-form" style="padding: 15px; border-top: 1px solid #eee; display: flex; gap: 10px;">
    <input type="text" id="groq-input" placeholder="Posez votre question..." style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 5px; outline: none;">
    <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">OK</button>
  </form>
</div>

<script>
  function toggleGroq() {
    const win = document.getElementById('groq-window');
    if (win.style.display === 'flex') {
        win.style.display = 'none';
    } else {
        win.style.display = 'flex';
    }
  }

  document.getElementById('groq-form').onsubmit = async (e) => {
    e.preventDefault();
    const input = document.getElementById('groq-input');
    const msgBox = document.getElementById('groq-messages');
    const text = input.value.trim();
    if (!text) return;

    const uMsg = document.createElement('div');
    uMsg.style.cssText = "background: #0ea5e9; color: white; padding: 10px; border-radius: 10px; align-self: flex-end; max-width: 80%; font-size: 0.9rem;";
    uMsg.textContent = text;
    msgBox.appendChild(uMsg);
    input.value = '';

    const loading = document.createElement('div');
    loading.textContent = "Groq réfléchit...";
    loading.style.fontSize = "0.8rem";
    msgBox.appendChild(loading);
    msgBox.scrollTop = msgBox.scrollHeight;

    try {
      const resp = await fetch('GroqHandler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text })
      });
      const data = await resp.json();
      if(msgBox.contains(loading)) msgBox.removeChild(loading);

      const aiMsg = document.createElement('div');
      aiMsg.style.cssText = "background: white; padding: 10px; border-radius: 10px; align-self: flex-start; max-width: 80%; font-size: 0.9rem; box-shadow: 0 2px 5px rgba(0,0,0,0.05);";
      if (data.choices && data.choices[0]) {
          aiMsg.textContent = data.choices[0].message.content;
      } else {
          // Afficher le message d'erreur précis
          const errText = data.error?.message || data.error || "Réponse invalide";
          aiMsg.textContent = "Erreur Groq : " + errText;
          console.error("Détails de l'erreur :", data);
      }
      msgBox.appendChild(aiMsg);
    } catch (err) {
      loading.textContent = "Erreur de connexion.";
    }
    msgBox.scrollTop = msgBox.scrollHeight;
  };
</script>

</body>
</html>



