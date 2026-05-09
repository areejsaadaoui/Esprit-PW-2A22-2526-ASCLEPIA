<?php
require_once '../../Controller/LanguageController.php';
require_once '../../Controller/PharmacieC.php';

$pc = new pharmacieC();
$listePharmacies = $pc->listepharmacie();
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
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <a href="index.php" class="navbar-brand" style="display: flex; align-items: center; text-decoration: none; gap: 10px;">
    <img src="../assets/image/logo.png?v=<?php echo time(); ?>" alt="ASCLEPIA Logo" style="height: 55px; object-fit: contain;">
    <span style="color: #10b981; font-weight: 700; font-size: 1.25rem; letter-spacing: -0.5px;">ASCLEPIA</span>
  </a>

  <div class="nav-links" id="navLinks">
    <a href="index.php#accueil" class="nav-link"><?= tr('nav_home') ?></a>
    <a href="index.php#services" class="nav-link"><?= tr('nav_services') ?></a>
    <a href="pharmacies.php" class="nav-link active"><?= tr('nav_pharmacies') ?></a>
    <a href="medicaments.php" class="nav-link"><?= tr('nav_medicaments') ?></a>
    <a href="index.php#assurances" class="nav-link"><?= tr('nav_insurances') ?></a>
  </div>

  <div class="nav-actions">
    <a href="login.html" class="btn btn-primary btn-sm"><?= tr('btn_register') ?></a>
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
        if (name.includes(input)) {
            items[i].style.display = "";
        } else {
            items[i].style.display = "none";
        }
    }
}
</script>

<footer class="footer" style="background: var(--dark); color: white; padding: 60px 0 30px;">
    <div class="container text-center">
        <p>&copy; <?= date('Y') ?> ASCLEPIA. Tous droits réservés.</p>
    </div>
</footer>

</body>
</html>
