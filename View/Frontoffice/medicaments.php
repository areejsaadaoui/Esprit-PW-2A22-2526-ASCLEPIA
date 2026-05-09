<?php
require_once '../../Controller/LanguageController.php';
require_once '../../Controller/MedicamentC.php';

$mc = new medicamentC();

// Vérifier si un ID de pharmacie est passé en paramètre
$id_pharmacie = isset($_GET['id_pharmacie']) ? $_GET['id_pharmacie'] : null;

if ($id_pharmacie) {
    // Si un ID est fourni, on filtre (on peut ajouter une méthode au contrôleur ou filtrer le tableau)
    $listeMedicaments = $mc->afficherMedicaments()->fetchAll();
    $listeMedicaments = array_filter($listeMedicaments, function($m) use ($id_pharmacie) {
        return $m['id_pharmacie'] == $id_pharmacie;
    });
} else {
    $listeMedicaments = $mc->afficherMedicaments()->fetchAll();
}
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
  <style>
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
  <a href="index.php" class="navbar-brand" style="display: flex; align-items: center; text-decoration: none; gap: 10px;">
    <img src="../assets/image/logo.png?v=<?php echo time(); ?>" alt="ASCLEPIA Logo" style="height: 55px; object-fit: contain;">
    <span style="color: #10b981; font-weight: 700; font-size: 1.25rem; letter-spacing: -0.5px;">ASCLEPIA</span>
  </a>

  <div class="nav-links" id="navLinks">
    <a href="index.php#accueil" class="nav-link"><?= tr('nav_home') ?></a>
    <a href="index.php#services" class="nav-link"><?= tr('nav_services') ?></a>
    <a href="pharmacies.php" class="nav-link"><?= tr('nav_pharmacies') ?></a>
    <a href="medicaments.php" class="nav-link active"><?= tr('nav_medicaments') ?></a>
    <a href="index.php#assurances" class="nav-link"><?= tr('nav_insurances') ?></a>
  </div>

  <div class="nav-actions">
    <a href="login.html" class="btn btn-primary btn-sm"><?= tr('btn_register') ?></a>
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
