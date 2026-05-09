<?php
// indexp.php - Dans View/front/
session_start();

require_once '../../config.php';

// Utiliser la classe config pour la connexion
$conn = config::getConnexion();

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userNom = $_SESSION['user_nom'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole = $_SESSION['user_role'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

// Récupérer l'avatar de l'utilisateur
$userAvatar = 'default';
if ($isLoggedIn && $userId) {
    try {
        $sql = "SELECT avatar_style FROM utilisateur WHERE id_user = :id_user";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id_user' => $userId]);
        $row = $stmt->fetch();
        if ($row && !empty($row['avatar_style'])) {
            $userAvatar = $row['avatar_style'];
        }
    } catch (Exception $e) {
        $userAvatar = 'default';
    }
}
?>
<?php
require_once '../../Controller/ContratController.php';
require_once '../../Controller/PharmacieC.php';
require_once '../../Controller/MedicamentC.php';
require_once '../../Controller/UserController.php';

$controller = new ContratController();
$topAssurances = $controller->getTopAssurances();

$pc = new pharmacieC();
$mc = new medicamentC();
$listePharmacies = $pc->listepharmacie()->fetchAll();
$listeMedicaments = $mc->afficherMedicaments()->fetchAll();

// Limiter l'affichage à 3 éléments pour l'aperçu
$pharmaciesApercu = array_slice($listePharmacies, 0, 3);
$medicamentsApercu = array_slice($listeMedicaments, 0, 3);

// Récupérer tous les médecins pour la section dédiée
$userC = new UserController();
$listeMedecins = $userC->getAllMedecins();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ASCLEPIA - Plateforme médicale en ligne. Consultations, ordonnances, pharmacies, assurances et forum santé.">
  <title>ASCLEPIA — Votre Plateforme Médicale</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Styles -->
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/frontoffice.css">
  <link rel="stylesheet" href="../assets/css/avatar.css">
  <style>



/* Avis dynamiques : mêmes cartes vitrées que .avis-card (voir frontoffice.css) */
    .avis-section #avisList {
      position: relative;
      z-index: 1;
      gap: 24px !important;
    }
    .avis-section #avisList > .col-4 {
      display: flex;
    }
    .avis-section #avisList .avis-card {
      width: 100%;
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100%;
    }
    .avis-section .avis-dynamic-actions {
      display: flex;
      gap: 8px;
      margin-top: 16px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }
    .avis-section .avis-dynamic-actions .btn {
      border: 1px solid rgba(255,255,255,0.35);
      background: rgba(255,255,255,0.08);
      color: #fff;
      font-size: 0.8rem;
    }
    .avis-section .avis-dynamic-actions .btn:hover {
      background: rgba(255,255,255,0.18);
      border-color: rgba(14,165,233,0.5);
    }
    .avis-section .avis-dynamic-actions .btn-danger {
      border-color: rgba(239,68,68,0.55);
      background: rgba(239,68,68,0.15);
      color: #fecaca;
    }
    .avis-section .pagination-container {
      margin-top: 40px;
      position: relative;
      z-index: 1;
    }
    .avis-section #avisPagination .pagination {
      display: inline-flex;
      align-items: center;
      gap: 16px;
      background: rgba(255,255,255,0.06);
      backdrop-filter: blur(12px);
      padding: 16px 24px;
      border-radius: 40px;
      border: 1px solid rgba(255,255,255,0.12);
      box-shadow: 0 8px 28px rgba(0,0,0,0.2);
    }
    .avis-section #avisPagination .btn-outline {
      border-color: rgba(255,255,255,0.45);
      color: #fff;
      background: transparent;
    }
    .avis-section #avisPagination .btn-outline:hover:not(:disabled) {
      background: rgba(255,255,255,0.12);
      border-color: rgba(14,165,233,0.6);
      color: #fff;
    }
    .avis-section #avisPagination .btn-outline:disabled {
      opacity: 0.35;
    }
    .avis-section #pageInfo.page-info {
      color: rgba(255,255,255,0.85);
      font-weight: 600;
      font-size: 0.9rem;
    }
    .avis-section .avis-list-status {
      color: rgba(255,255,255,0.65);
      text-align: center;
      width: 100%;
      padding: 32px 16px;
    }

/* Modal stars */
.modal-star {
  font-size: 36px;
  cursor: pointer;
  color: #cbd5e1;
  transition: all 0.2s ease;
}

.modal-star.active, .modal-star:hover {
  color: #fbbf24;
  transform: translateY(-1px);
}

/* ---- Médecins Section ---- */
.medecin-card {
  background: var(--bg-card);
  border-radius: var(--radius-lg);
  padding: 32px 24px 24px;
  box-shadow: var(--shadow);
  border: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 14px;
  height: 100%;
  transition: var(--transition);
}
.medecin-card:hover {
  transform: translateY(-6px);
  box-shadow: var(--shadow-hover);
}
.medecin-avatar-ring {
  width: 88px;
  height: 88px;
  border-radius: 50%;
  background: var(--gradient-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 800;
  color: white;
  letter-spacing: 1px;
  box-shadow: 0 6px 20px rgba(14,165,233,0.30);
  flex-shrink: 0;
}
.medecin-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--dark);
  margin: 0;
}
.medecin-specialite {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--primary);
  background: rgba(14,165,233,0.10);
  padding: 4px 14px;
  border-radius: var(--radius-full);
  display: inline-block;
}
.medecin-info-row {
  font-size: 0.82rem;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  gap: 6px;
  justify-content: center;
}

/* Médecins pagination (light section) */
.medecins-section .pagination-container {
  margin-top: 40px;
  text-align: center;
}
.medecins-section .pagination {
  display: inline-flex;
  align-items: center;
  gap: 16px;
  background: var(--bg-card);
  padding: 14px 28px;
  border-radius: 40px;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}
.medecins-section .page-info {
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--text-muted);
}

  </style>
</head>

<body>

<script>
  //zeineb
// ========== VARIABLES SESSION POUR JAVASCRIPT ==========
var sessionUserId = <?= json_encode($userId ?? 0) ?>;
var sessionUserRole = <?= json_encode($userRole ?? '') ?>;
var sessionIsLoggedIn = <?= json_encode($isLoggedIn) ?>;
</script>
<!-- ================================================
     NAVBAR (avec session)
     ================================================ -->
<nav class="navbar" id="navbar">
  <a href="indexp.php" class="navbar-brand">
     <div class="navbar-logo">🏥</div>
    <div class="navbar-name">ASC<span>LEPIA</span></div>
  </a>

  <div class="nav-links" id="navLinks">
    <a href="#accueil" class="nav-link active">Accueil</a>
    <a href="#services" class="nav-link">Services</a>
    <a href="#pharmacies" class="nav-link">Pharmacies</a>
    <a href="#produits" class="nav-link">Médicaments</a>
    <a href="#assurances" class="nav-link">Assurances</a>
    <a href="#forum" class="nav-link">Post&Reponse</a>
    <a href="#avis" class="nav-link">Avis</a>
    <a href="#medecins" class="nav-link">Médecins</a>
  </div>

  <div class="nav-actions">
    <div style="display:flex; align-items:center; gap:10px; margin-right:15px;">
        <!-- Theme Toggle -->
        <button id="themeToggle" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--white);" title="Mode Sombre/Clair">
            <i class="fa-solid fa-moon"></i>
        </button>

    </div>

    <?php if ($isLoggedIn): ?>
      <div style="display: flex; align-items: center; gap: 12px;">
        <div class="avatar-css avatar-<?php echo $userAvatar; ?> small"></div>
        <span style="color: white; font-weight: 500;">Bonjour, <?php echo htmlspecialchars($userNom); ?></span>
        
        <!-- Afficher le bouton Profile seulement si ce n'est pas un admin -->
        <?php if ($userRole !== 'admin'): ?>
          <a href="profile.php" class="btn btn-outline-white btn-sm">
            <i class="fa-solid fa-face-smile"></i> Profile
          </a>
        <?php endif; ?>
        
        <?php if ($userRole === 'admin'): ?>
          <a href="../back/dashboard.php" class="btn btn-outline-white btn-sm">
            <i class="fa-solid fa-shield-haltered"></i> Admin
          </a>
        <?php endif; ?>
        
        <a href="../back/logout.php" class="btn btn-outline-white btn-sm">
          <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
        </a>
      </div>
    <?php else: ?>
      <a href="login.html" class="btn btn-outline-white btn-sm">Se connecter</a>
      <a href="loginuser.html" class="btn btn-primary btn-sm">S'inscrire</a>
    <?php endif; ?>
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
          Plateforme médicale certifiée
        </div>

        <h1 class="hero-title">
          Votre santé,<br>
          <span class="highlight">notre priorité</span><br>
          absolue
        </h1>

        <p class="hero-subtitle">
          ASCLEPIA réunit médecins, pharmacies et assurances en un seul endroit.
          Gérez vos consultations, ordonnances et remboursements facilement.
        </p>

        <div class="hero-actions">
          <?php if (!$isLoggedIn): ?>
            <a href="login.html" class="btn btn-primary btn-lg">
              <i class="fa-solid fa-user-plus"></i>
              Commencer gratuitement
            </a>
          <?php endif; ?>
          <a href="#services" class="btn btn-outline-white btn-lg">
            <i class="fa-solid fa-play"></i>
            Découvrir
          </a>
        </div>

        <div class="hero-stats">
          <div class="hero-stat">
            <div class="number">500<span>+</span></div>
            <div class="label">Médecins</div>
          </div>
          <div class="hero-stat">
            <div class="number">50<span>K+</span></div>
            <div class="label">Patients</div>
          </div>
          <div class="hero-stat">
            <div class="number">120<span>+</span></div>
            <div class="label">Pharmacies</div>
          </div>
          <div class="hero-stat">
            <div class="number">98<span>%</span></div>
            <div class="label">Satisfaction</div>
          </div>
        </div>
      </div>

      <!-- Right Visual -->
      <div class="hero-visual d-none-mobile" style="flex: 0 0 400px;">
        <div class="hero-card-float hero-card-1">
          <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 36px; height: 36px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;">✅</div>
            <div>
              <div style="font-size: 0.78rem; font-weight: 700; color: white;">Consultation approuvée</div>
              <div style="font-size: 0.7rem; color: rgba(255,255,255,0.5);">Dr. Lamine Ben Ali · il y a 5 min</div>
            </div>
          </div>
        </div>

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
     SERVICES SECTION
     ================================================ -->
<section class="section-padding services-section" id="services">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">
        <i class="fa-solid fa-star"></i>
        Nos Modules
      </div>
      <h2 class="section-title">5 Services pour votre santé</h2>
      <p class="section-desc">
        Une plateforme complète qui couvre tous vos besoins médicaux, de la consultation jusqu'au remboursement.
      </p>
    </div>

    <div class="row">

      <!-- Module 1: Authentification -->
      <div class="col-4">
        <div class="card service-card">
          <div class="icon-box icon-box-lg" style="background: linear-gradient(135deg,#6366f1,#8b5cf6);">
            <i class="fa-solid fa-user-shield"></i>
          </div>
          <h3>Espace Personnel</h3>
          <p>Créez votre compte patient ou médecin. Accédez à votre espace sécurisé avec gestion complète de votre profil.</p>
          <?php if (!$isLoggedIn): ?>
            <a href="loginuser.html" class="btn btn-outline btn-sm mt-3">
              S'inscrire <i class="fa-solid fa-arrow-right"></i>
            </a>
          <?php else: ?>
            <a href="profile.php" class="btn btn-outline btn-sm mt-3">
              Mon profil <i class="fa-solid fa-arrow-right"></i>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Module 2: Consultation -->
      <div class="col-4">
        <div class="card service-card">
          <div class="icon-box icon-box-lg" style="background: linear-gradient(135deg,#0ea5e9,#06b6d4);">
            <i class="fa-solid fa-stethoscope"></i>
          </div>
          <h3>Consultations & Ordonnances</h3>
          <p>Suivez vos consultations et accédez à vos ordonnances numériques. Diagnostics et notes médicales centralisés.</p>
          <a href="../frontoffice/consultation_patient.php" class="btn btn-outline btn-sm mt-3">
            Voir <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>

      <!-- Module 3: Pharmacie -->
      <div class="col-4">
        <div class="card service-card">
          <div class="icon-box icon-box-lg" style="background: linear-gradient(135deg,#10b981,#059669);">
            <i class="fa-solid fa-pills"></i>
          </div>
          <h3>Pharmacies & Médicamentos</h3>
          <p>Trouvez les médicaments disponibles dans les pharmacies partenaires. Vérifiez les stocks en temps réel.</p>
          <a href="../Frontoffice/pharmacies.php" class="btn btn-outline btn-sm mt-3">
            Explorer <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>

      <!-- Module 4: Assurance -->
      <div class="col-4">
        <div class="card service-card">
          <div class="icon-box icon-box-lg" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <h3>Assurances & Contrats</h3>
          <p>Gérez vos contrats d'assurance santé. Consultez vos taux de remboursement et dates de validité.</p>
         <a href="../frontoffice/assurancefront.php" class="btn btn-outline btn-sm mt-3">
            Consulter <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>
      </div>

     <!-- Module 5: Forum -->
<div class="col-4">
    <div class="card service-card">
        <div class="icon-box icon-box-lg" style="background: linear-gradient(135deg,#ec4899,#db2777);">
            <i class="fa-solid fa-comments"></i>
        </div>
        <h3>Forum & Communauté</h3>
        <p>Partagez vos expériences, posez des questions. Rejoignez notre communauté de patients et professionnels de santé.</p>
        <?php if ($isLoggedIn): ?>
            <!-- Utilisateur connecté : accès direct au forum -->
            <a href="../Frontoffice/postlist.php" class="btn btn-outline btn-sm mt-3">
                Participer <i class="fa-solid fa-arrow-right"></i>
            </a>
        <?php else: ?>
            <!-- Utilisateur non connecté : redirection vers inscription -->
            <a href="javascript:void(0);" 
               onclick="checkForumAccess()" 
               class="btn btn-outline btn-sm mt-3">
                Participer <i class="fa-solid fa-arrow-right"></i>
            </a>
        <?php endif; ?>
    </div>
</div>

      <!-- CTA Card -->
      <div class="col-4">
        <div class="card service-card" style="background: var(--gradient-hero); border: none;">
          <div style="font-size: 3rem; margin-bottom: 16px;">🚀</div>
          <h3 style="color: white;">Commencez aujourd'hui</h3>
          <p style="color: rgba(255,255,255,0.7);">Rejoignez des milliers de patients qui gèrent leur santé intelligemment avec ASCLEPIA.</p>
          <?php if (!$isLoggedIn): ?>
            <a href="loginuser.html" class="btn btn-outline-white btn-sm mt-3">
              Créer un compte <i class="fa-solid fa-arrow-right"></i>
            </a>
          <?php else: ?>
            <a href="profile.php" class="btn btn-outline-white btn-sm mt-3">
              Accéder à mon espace <i class="fa-solid fa-arrow-right"></i>
            </a>
          <?php endif; ?>
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
        Réseau Pharmacies
      </div>
      <h2 class="section-title">Pharmacies Partenaires</h2>
      <p class="section-desc">Trouvez les médicaments dont vous avez besoin dans notre réseau de pharmacies vérifiées.</p>
    </div>

    <div style="max-width: 480px; margin: 0 auto 48px; position: relative;">
      <i class="fa-solid fa-search" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--gray-light);"></i>
      <input type="text" placeholder="Rechercher une pharmacie ou médicament..." id="pharmSearch"
        style="width: 100%; padding: 14px 18px 14px 48px; border: 2px solid var(--border); border-radius: var(--radius-full); font-size: 0.95rem; outline: none; background: var(--bg); font-family: var(--font-main);"
        onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'">
    </div>

    <!-- Carte Interactive -->
    <div id="pharmacyMap" style="height: 400px; border-radius: 20px; margin-bottom: 48px; border: 2px solid var(--border); box-shadow: var(--shadow-sm); z-index: 1;"></div>
    
    <!-- Leaflet CSS/JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <div class="row" id="pharmaciesGrid">
      <?php if (!empty($pharmaciesApercu)): ?>
        <?php foreach ($pharmaciesApercu as $p): ?>
          <div class="col-4 pharm-item" data-nom="<?= strtolower(htmlspecialchars($p['nom'])) ?>">
            <div class="card pharmacie-card" style="gap: 16px; flex-direction: column; padding: 24px; height: 100%;">
              <div class="d-flex align-center" style="gap: 16px;">
                <div class="icon-box" style="background: linear-gradient(135deg,#10b981,#059669);">
                  <i class="fa-solid fa-mortar-pestle"></i>
                </div>
                <div>
                  <h3 style="font-size: 1rem; margin-bottom: 2px;"><?= htmlspecialchars($p['nom']) ?></h3>
                  <span class="badge badge-success">Ouverte</span>
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
              <a href="../Frontoffice/medicaments.php?id_pharmacie=<?= $p['id_pharmacie'] ?>" class="btn btn-outline btn-sm" style="align-self: flex-start; margin-top: auto;">
                Voir médicaments
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; width: 100%; color: var(--text-muted);">Aucune pharmacie disponible.</p>
      <?php endif; ?>

      <div class="col-12 text-center" style="margin-top: 32px; width: 100%;">
        <a href="../Frontoffice/pharmacies.php" class="btn btn-primary btn-lg">
          <i class="fa-solid fa-eye"></i> Voir toutes les pharmacies
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ================================================
     MEDICAMENTS SECTION (Nouveau)
     ================================================ -->
<section class="section-padding" id="produits" style="background: var(--bg);">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">
        <i class="fa-solid fa-pills"></i>
        Médicaments
      </div>
      <h2 class="section-title">Produits Disponibles</h2>
      <p class="section-desc">Découvrez notre sélection de médicaments essentiels.</p>
    </div>

    <div class="row">
      <?php if (!empty($medicamentsApercu)): ?>
        <?php foreach ($medicamentsApercu as $m): ?>
          <div class="col-4 med-item" data-nom="<?= strtolower(htmlspecialchars($m['nom'])) ?>">
            <div class="card product-card" style="padding: 0; overflow: hidden; height: 100%; display: flex; flex-direction: column;">
              <div style="height: 180px; overflow: hidden; background: #eee; display: flex; align-items: center; justify-content: center;">
                <?php 
                  $imgPath = htmlspecialchars($m['images']);
                  if(empty($m['images'])) {
                    $imgPath = "https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&q=80&w=400";
                  }
                ?>
                <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($m['nom']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
              </div>
              <div style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column; gap: 10px;">
                <h3 style="font-size: 1.1rem; margin: 0;"><?= htmlspecialchars($m['nom']) ?></h3>
                <div style="color: var(--primary); font-weight: 700; font-size: 1.2rem;"><?= number_format($m['prix'], 3) ?> DT</div>
                <div style="font-size: 0.85rem; color: <?= $m['stock'] > 0 ? 'var(--accent)' : 'var(--danger)' ?>;">
                  <i class="fa-solid <?= $m['stock'] > 0 ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
                  <?= $m['stock'] > 0 ? 'En Stock' : 'Rupture' ?>
                </div>
                <a href="../Frontoffice/medicaments.php" class="btn btn-outline btn-sm" style="margin-top: auto;">
                  Détails
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; width: 100%; color: var(--text-muted);">Aucun médicament disponible.</p>
      <?php endif; ?>

      <div class="col-12 text-center" style="margin-top: 32px; width: 100%;">
        <a href="../Frontoffice/medicaments.php" class="btn btn-primary btn-lg">
          <i class="fa-solid fa-plus-circle"></i> En savoir plus / Voir tout
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ================================================
     ASSURANCES SECTION
     ================================================ -->
<section class="section-padding" id="assurances" style="background: var(--bg);">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">
        <i class="fa-solid fa-shield-halved"></i>
        Protection Santé
      </div>
      <h2 class="section-title">Nos Partenaires Assurance</h2>
      <p class="section-desc">Des couvertures adaptées à chaque profil avec les meilleurs taux de remboursement.</p>
    </div>

    <div class="row" style="padding-top: 20px;">
    <?php foreach($topAssurances as $index => $a): ?>
      <div class="col-4">
        <div class="card assurance-card" <?= $index === 0 ? 'style="border: 2px solid var(--primary); position: relative;"' : '' ?>>
          <?php if($index === 0): ?>
            <div style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%);">
              <span class="badge badge-primary" style="padding: 6px 16px; font-size: 0.75rem;">⭐ Populaire</span>
            </div>
          <?php endif; ?>
          <div class="icon-box icon-box-lg" style="margin: 0 auto 20px; background: var(--gradient-primary);">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <h3><?= htmlspecialchars($a['nom_assurance']) ?></h3>
          <p style="font-size: 0.85rem; color: var(--text-muted); margin: 8px 0;">
            <?= htmlspecialchars($a['description']) ?>
          </p>
          <div class="rate"><?= $a['taux_remboursement'] ?><span>%</span></div>
          <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">Taux de remboursement</div>
          <div class="progress-bar-wrap">
            <div class="progress-bar" style="width: <?= $a['taux_remboursement'] ?>%;"></div>
          </div>
          <a href="../frontoffice/assurancefront.php" class="btn btn-primary btn-sm mt-3" style="width: 100%; justify-content: center;">
            En savoir plus
          </a>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
</section>
<!-- ================================================
     FORUM SECTION - 3 DERNIERS POSTS DE LA BD
     ================================================ -->
<section class="section-padding" id="forum" style="background: var(--white);">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">
        <i class="fa-solid fa-comments"></i>
        Communauté
      </div>
      <h2 class="section-title">Forum Santé</h2>
      <p class="section-desc">Échangez avec la communauté. Posez vos questions et partagez votre expérience.</p>
    </div>

    <div class="row">
      <div class="col-4">
        <div class="card post-card">
          <div class="post-meta">
            <div class="post-avatar">MA</div>
            <div>
              <div class="post-author">Mohamed Amri</div>
              <div class="post-date">Il y a 2 heures</div>
            </div>
            <h2 class="section-title">Forum Santé</h2>
            <p class="section-desc">Échangez avec la communauté. Posez vos questions et partagez votre expérience.</p>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <?php if ($isLoggedIn): ?>
                <a href="../Frontoffice/postlist.php" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-comments"></i>
                    Voir tout le forum
                </a>
            <?php else: ?>
                <a href="loginuser.html" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-comments"></i>
                    Connectez-vous pour accéder au forum
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ================================================
     AVIS SECTION
     ================================================ -->
<section class="section-padding avis-section" id="avis">
  <div class="container" style="position: relative; z-index: 1;">
    <div class="section-header">
      <div class="section-tag">⭐ Témoignages</div>
      <h2 class="section-title">Ce que disent nos patients</h2>
      <p class="section-desc" style="color: rgba(255,255,255,0.6);">Découvrez les avis réels de notre communauté — tous les utilisateurs peuvent nous faire part de leur expérience.</p>
    </div>

    <div class="row justify-center" id="avisList"></div>

    <div id="avisPagination" class="pagination-container" style="text-align: center; margin-top: 40px; display: none;">
      <div class="pagination">
        <button type="button" id="prevPage" class="btn btn-outline" disabled>&laquo; Précédent</button>
        <span id="pageInfo" class="page-info"></span>
        <button type="button" id="nextPage" class="btn btn-outline">Suivant &raquo;</button>
      </div>
    </div>

    <div style="text-align: center; margin-top: 40px;">
      <?php if ($isLoggedIn): ?>
        <button type="button" onclick="openAvisModal()" class="btn btn-primary btn-lg">
          <i class="fas fa-star"></i> Donner mon avis
        </button>
      <?php else: ?>
        <a href="loginuser.html" class="btn btn-primary btn-lg">
          <i class="fas fa-star"></i> Connectez-vous pour donner votre avis
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ================================================
     MÉDECINS SECTION
     ================================================ -->
<section class="section-padding medecins-section" id="medecins" style="background: var(--white);">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">
        <i class="fa-solid fa-user-doctor"></i>
        Notre Équipe
      </div>
      <h2 class="section-title">Nos Médecins</h2>
      <p class="section-desc">Des professionnels de santé qualifiés à votre service. Prenez rendez-vous en quelques clics.</p>
    </div>

    <div class="row" id="medecinsList">
      <?php if (!empty($listeMedecins)): ?>
        <?php foreach ($listeMedecins as $med): 
          $initials = strtoupper(mb_substr($med['nom'] ?? 'DR', 0, 2));
        ?>
          <div class="col-4 medecin-item" style="display: none;">
            <div class="medecin-card">
              <div class="medecin-avatar-ring"><?= htmlspecialchars($initials) ?></div>
              <h3 class="medecin-name">Dr. <?= htmlspecialchars($med['nom']) ?></h3>
              <?php if (!empty($med['specialite'])): ?>
                <span class="medecin-specialite">
                  <i class="fa-solid fa-stethoscope"></i>
                  <?= htmlspecialchars($med['specialite']) ?>
                </span>
              <?php endif; ?>
              <?php if (!empty($med['adresse'])): ?>
                <div class="medecin-info-row">
                  <i class="fa-solid fa-location-dot" style="color: var(--primary);"></i>
                  <?= htmlspecialchars($med['adresse']) ?>
                </div>
              <?php endif; ?>
              <?php if (!empty($med['numero'])): ?>
                <div class="medecin-info-row">
                  <i class="fa-solid fa-phone" style="color: var(--primary);"></i>
                  <?= htmlspecialchars($med['numero']) ?>
                </div>
              <?php endif; ?>
              <a href="consultation.php?id_medecin=<?= $med['id_user'] ?>" 
                 class="btn btn-primary btn-sm" 
                 style="width: 100%; justify-content: center; margin-top: auto;">
                <i class="fa-solid fa-calendar-check"></i>
                Prendre rendez-vous
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; width: 100%; color: var(--text-muted);">Aucun médecin disponible pour le moment.</p>
      <?php endif; ?>
    </div>

    <div id="medecinsPagination" class="pagination-container" style="display: none;">
      <div class="pagination">
        <button type="button" id="medPrevPage" class="btn btn-outline" disabled>&laquo; Précédent</button>
        <span id="medPageInfo" class="page-info"></span>
        <button type="button" id="medNextPage" class="btn btn-outline">Suivant &raquo;</button>
      </div>
    </div>
  </div>
</section>

<!-- Modal pour ajouter un avis -->
<div id="avisModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
  <div class="modal-content" style="background: white; border-radius: 32px; padding: 40px; max-width: 550px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(15,23,42,0.22); border: 1px solid rgba(148,163,184,0.2);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2 id="modalTitle" style="font-size: 1.8rem; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 12px;"><i class="fas fa-star"></i> Donner mon avis</h2>
      <button type="button" onclick="closeAvisModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
    </div>

    <div id="avisModalMsg" style="display: none;"></div>

    <form id="avisModalForm" enctype="multipart/form-data">
      <input type="hidden" name="id_avis" id="modalAvisId" value="">
      
      <div style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; color: #0f172a; margin-bottom: 10px;">⭐ Votre note</label>
        <div style="display: flex; gap: 12px; margin-bottom: 10px;" id="modalStarsContainer">
          <span class="modal-star" data-note="1">★</span>
          <span class="modal-star" data-note="2">★</span>
          <span class="modal-star" data-note="3">★</span>
          <span class="modal-star" data-note="4">★</span>
          <span class="modal-star" data-note="5">★</span>
        </div>
        <input type="hidden" name="note" id="modalNoteInput" value="0">
        <div id="modalNoteError" style="color:#ef4444; font-size:0.8rem; margin-top:5px; display:none;">Veuillez sélectionner une note</div>
      </div>

      <div style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; color: #0f172a; margin-bottom: 10px;">📝 Votre avis</label>
        <textarea name="contenu" id="modalContenuInput" rows="5" placeholder="Décrivez votre expérience... (minimum 10 caractères)" style="width: 100%; padding: 14px; border: 1px solid #cbd5e1; border-radius: 18px; font-family: inherit; font-size: 0.95rem; transition: all 0.2s ease; resize: vertical; background: white;"></textarea>
        <div id="modalContenuError" style="color:#ef4444; font-size:0.8rem; margin-top:5px; display:none;">L'avis doit contenir au moins 10 caractères</div>
      </div>

      <div style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 700; color: #0f172a; margin-bottom: 10px;">🖼️ Image (optionnel)</label>
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
          <label style="background: #eff6ff; padding: 12px 20px; border-radius: 40px; cursor: pointer; font-weight: 600; color: #0f172a;" onclick="document.getElementById('modalImageInput').click()">
            <i class="fas fa-upload"></i> Choisir une image
          </label>
          <input type="file" id="modalImageInput" name="image" accept="image/*" style="display: none;">
          <span id="modalFileName" style="color:#64748b; font-size:0.85rem;">Aucun fichier sélectionné</span>
        </div>
        <div id="modalImagePreview"></div>
        <div id="modalImageError" style="color:#ef4444; font-size:0.8rem; margin-top:5px; display:none;">Format ou taille invalide (max 5MB)</div>
      </div>

      <div style="display: flex; gap: 12px;">
        <button id="modalSubmitButton" type="submit" class="btn btn-primary" style="flex: 1;"><i class="fas fa-paper-plane"></i> Publier mon avis</button>
      </div>
    </form>
  </div>
</div>


<!-- ================================================
     CTA SECTION
     ================================================ -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content">
      <div class="section-tag" style="justify-content: center; margin-bottom: 20px;">
        <i class="fa-solid fa-rocket"></i>
        Rejoignez-nous
      </div>
      <h2>Prêt à prendre soin de votre santé ?</h2>
      <p>Créez votre compte gratuitement et accédez à tous les services ASCLEPIA dès aujourd'hui.</p>
      <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
        <?php if (!$isLoggedIn): ?>
          <a href="loginuser.html" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-user-plus"></i>
            Créer un compte patient
          </a>
          <a href="login.html" class="btn btn-outline-white btn-lg">
            <i class="fa-solid fa-sign-in-alt"></i>
            Se connecter
          </a>
        <?php else: ?>
          <a href="profile.php" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-calendar-check"></i>
            Mon profil
          </a>
          <a href="../frontoffice/ordonnance_patient.php" class="btn btn-outline-white btn-lg">
            <i class="fa-solid fa-file-prescription"></i>
            Mes ordonnances
          </a>
        <?php endif; ?>
      </div>
    </div>
    <p style="color: #475569; margin-bottom: 30px; font-size: 0.95rem; text-align: center;">Partagez votre expérience avec la communauté</p>
  </div>
</section>


<!-- ================================================
     FOOTER
     ================================================ -->
<footer class="footer">
  <div class="container">
    <div class="row" style="gap: 48px;">

      <div style="flex: 0 0 260px;">
        <div class="footer-brand">
          <div class="navbar-brand" style="margin-bottom: 16px;">
            <div class="navbar-logo">⚕️</div>
            <div class="navbar-name" style="font-size: 1.2rem;">ASC<span class="text-primary">LEPIA</span></div>
          </div>
          <p>Votre plateforme médicale complète. Consultations, ordonnances, pharmacies, assurances et communauté santé.</p>
          <div class="social-links">
            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="footer-section">
          <h4>Services</h4>
          <ul class="footer-links">
            <li><a href="consultation.php"><i class="fa-solid fa-stethoscope"></i> Consultations</a></li>
            <li><a href="consultation.php"><i class="fa-solid fa-file-prescription"></i> Ordonnances</a></li>
            <li><a href="addpharmacie.php"><i class="fa-solid fa-pills"></i> Pharmacies</a></li>
            <li><a href="../frontoffice/assurancefront.php"><i class="fa-solid fa-shield-halved"></i> Assurances</a></li>
            <li><a href="forum.php"><i class="fa-solid fa-comments"></i> Forum santé</a></li>
          </ul>
        </div>
      </div>

      <div class="col">
        <div class="footer-section">
          <h4>Liens utiles</h4>
          <ul class="footer-links">
            <li><a href="indexp.php"><i class="fa-solid fa-home"></i> Accueil</a></li>
            <?php if (!$isLoggedIn): ?>
              <li><a href="loginuser.html"><i class="fa-solid fa-user-plus"></i> S'inscrire</a></li>
              <li><a href="login.html"><i class="fa-solid fa-sign-in-alt"></i> Se connecter</a></li>
            <?php else: ?>
              <li><a href="profile.php"><i class="fa-solid fa-user"></i> Mon profil</a></li>
              <li><a href="choose_avatar.php"><i class="fa-solid fa-face-smile"></i> Changer avatar</a></li>
              <li><a href="../back/logout.php"><i class="fa-solid fa-sign-out-alt"></i> Déconnexion</a></li>
            <?php endif; ?>
            <li><a href="#avis"><i class="fa-solid fa-star"></i> Témoignages</a></li>
            <li><a href="#"><i class="fa-solid fa-file-lines"></i> Confidentialité</a></li>
          </ul>
        </div>
      </div>

      <div class="col">
        <div class="footer-section">
          <h4>Contact</h4>
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
      <p>© 2026 <a href="indexp.php">ASCLEPIA</a>. Tous droits réservés.</p>
      <p>Conçu avec ❤️ pour une meilleure santé</p>
    </div>
  </div>
</footer>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // ---- Navbar & Navigation ----
    const navbar = document.getElementById('navbar');
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('section[id], div[id]');

    window.addEventListener('scroll', () => {
      if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 30);
      
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

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          const navLinksContainer = document.getElementById('navLinks');
          if (navLinksContainer) navLinksContainer.classList.remove('open');
        }
      });
    });

    // ---- Theme Toggle ----
    const themeToggle = document.getElementById('themeToggle');
    const root = document.documentElement;
    const savedTheme = localStorage.getItem('theme') || 'light';
    root.setAttribute('data-theme', savedTheme);
    
    if (themeToggle) {
      const icon = themeToggle.querySelector('i');
      if (savedTheme === 'dark' && icon) icon.classList.replace('fa-moon', 'fa-sun');
      
      themeToggle.addEventListener('click', () => {
        const currentTheme = root.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        const newIcon = themeToggle.querySelector('i');
        if (newTheme === 'dark') {
          newIcon.classList.replace('fa-moon', 'fa-sun');
        } else {
          newIcon.classList.replace('fa-sun', 'fa-moon');
        }
      });
    }



    // ---- Carte Interactive (Leaflet) ----
    if (document.getElementById('pharmacyMap')) {
      try {
        const map = L.map('pharmacyMap').setView([36.8065, 10.1815], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const pharmacyIcon = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/883/883356.png',
            iconSize: [38, 38],
            iconAnchor: [19, 38],
            popupAnchor: [0, -38]
        });

        <?php foreach ($listePharmacies as $p): 
            $lat = 36.8065 + (rand(-100, 100) / 2000); 
            $lng = 10.1815 + (rand(-100, 100) / 2000);
        ?>
        L.marker([<?= $lat ?>, <?= $lng ?>], {icon: pharmacyIcon})
            .addTo(map)
            .bindPopup(`
                <div style="font-family: 'Inter', sans-serif; padding: 5px;">
                    <h4 style="margin: 0 0 5px; color: #10b981;"><?= htmlspecialchars($p['nom']) ?></h4>
                    <p style="margin: 0; font-size: 0.8rem; color: #666;"><?= htmlspecialchars($p['adresse']) ?></p>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <a href="../Frontoffice/medicaments.php?id_pharmacie=<?= $p['id_pharmacie'] ?>" style="font-size: 0.75rem; color: #0ea5e9; font-weight: bold; text-decoration: none;">Voir médicaments</a>
                        <a href="https://www.google.com/maps/search/<?= urlencode($p['nom'] . ' ' . $p['adresse']) ?>" target="_blank" style="font-size: 0.75rem; color: #666; text-decoration: none;">Google Maps</a>
                    </div>
                </div>
            `);
        <?php endforeach; ?>
        
        // Force refresh pour corriger les problèmes d'affichage
        setTimeout(() => map.invalidateSize(), 500);
      } catch (e) {
        console.error("Erreur Leaflet:", e);
      }
    }

    // ---- Recherche Temps Réel ----
    const pharmSearch = document.getElementById('pharmSearch');
    if (pharmSearch) {
      pharmSearch.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        const items = document.querySelectorAll('.pharm-item, .med-item');
        items.forEach(item => {
          const nom = item.getAttribute('data-nom') || '';
          item.style.display = nom.includes(query) ? '' : 'none';
        });
      });
    }

    // ---- Médecins Pagination ----
    (function() {
      const MED_PER_PAGE = 3;
      const allItems = Array.from(document.querySelectorAll('.medecin-item'));
      const total = allItems.length;
      if (total === 0) return;

      let currentMedPage = 1;
      const totalMedPages = Math.ceil(total / MED_PER_PAGE);

      const pagination = document.getElementById('medecinsPagination');
      const prevBtn   = document.getElementById('medPrevPage');
      const nextBtn   = document.getElementById('medNextPage');
      const pageInfo  = document.getElementById('medPageInfo');

      if (total > MED_PER_PAGE && pagination) pagination.style.display = 'block';

      function showMedPage(page) {
        currentMedPage = page;
        const start = (page - 1) * MED_PER_PAGE;
        allItems.forEach((el, i) => {
          el.style.display = (i >= start && i < start + MED_PER_PAGE) ? '' : 'none';
        });
        if (pageInfo) pageInfo.textContent = `Page ${page} sur ${totalMedPages}`;
        if (prevBtn)  prevBtn.disabled  = page === 1;
        if (nextBtn)  nextBtn.disabled  = page === totalMedPages;
      }

      showMedPage(1);

      if (prevBtn) prevBtn.addEventListener('click', () => { if (currentMedPage > 1) showMedPage(currentMedPage - 1); });
      if (nextBtn) nextBtn.addEventListener('click', () => { if (currentMedPage < totalMedPages) showMedPage(currentMedPage + 1); });
    })();

    // ---- Animations ----
    const progressBars = document.querySelectorAll('.progress-bar');
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.width = entry.target.getAttribute('data-width') || entry.target.style.width;
        }
      });
    }, { threshold: 0.5 });
    progressBars.forEach(bar => observer.observe(bar));

    const cards = document.querySelectorAll('.card, .avis-card');
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
    cards.forEach(card => {
      card.style.opacity = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      cardObserver.observe(card);
    });

  });

  function toggleMenu() {
    const navLinks = document.getElementById('navLinks');
    if (navLinks) navLinks.classList.toggle('open');
  }


   // zeineb 
function checkForumAccess() {
    if (confirm("Vous devez être inscrit et connecté pour accéder au forum. Souhaitez-vous vous inscrire ou vous connecter ?")) {
        window.location.href = "loginuser.html";
    }
}

</script>
<script>
// ========== VARIABLES SESSION POUR JS ==========
var sessionUserId = <?= json_encode($userId ?? 0) ?>;
var sessionUserRole = <?= json_encode($userRole ?? '') ?>;
var isLoggedIn = <?= json_encode($isLoggedIn) ?>;

// ========== FONCTIONS AVIS ==========

function openAvisModal() {
    if (!isLoggedIn) {
        window.location.href = "loginuser.html";
        return;
    }
    document.getElementById('modalAvisId').value = '';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-star"></i> Donner mon avis';
    document.getElementById('modalSubmitButton').innerHTML = '<i class="fas fa-paper-plane"></i> Publier mon avis';
    document.getElementById('modalNoteInput').value = '0';
    document.getElementById('modalContenuInput').value = '';
    document.getElementById('modalImageInput').value = '';
    document.getElementById('modalImagePreview').innerHTML = '';
    document.getElementById('modalFileName').textContent = 'Aucun fichier sélectionné';
    document.getElementById('avisModalMsg').style.display = 'none';
    document.getElementById('avisModal').style.display = 'flex';
    
    const stars = document.querySelectorAll('.modal-star');
    stars.forEach(s => s.classList.remove('active'));
}

function closeAvisModal() {
    document.getElementById('avisModal').style.display = 'none';
}

// Variables AVIS
let currentPage = 1;
const itemsPerPage = 6;
let totalAvis = 0;
let allAvis = [];

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function formatDateFr(isoOrMysql) {
    if (!isoOrMysql) return '';
    try {
        return new Date(isoOrMysql).toLocaleString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    } catch(e) { return isoOrMysql; }
}

/** Décoration identique aux cartes statiques (.stars, avatar dégradés) */
const AVIS_AVATAR_GRADS = [
    '',
    ' style="background: linear-gradient(135deg,#6366f1,#8b5cf6);"',
    ' style="background: linear-gradient(135deg,#10b981,#059669);"',
    ' style="background: linear-gradient(135deg,#f59e0b,#ea580c);"',
    ' style="background: linear-gradient(135deg,#ec4899,#db2777);"'
];

function avisStarsLine(note) {
    const n = Math.max(0, Math.min(5, Math.round(Number(note) || 0)));
    return '\u2605'.repeat(n) + '\u2606'.repeat(5 - n);
}

function avisAuthorInitials(name) {
    const s = String(name || 'U').trim();
    const parts = s.split(/\s+/).filter(Boolean).slice(0, 2);
    const ini = parts.map(p => p.charAt(0)).join('').toUpperCase().slice(0, 2);
    return ini || '?';
}

function showNotification(msg, type) {
    const div = document.createElement('div');
    div.textContent = msg;
    div.style.cssText = `position:fixed; top:20px; right:20px; padding:12px 24px; border-radius:12px; color:white; z-index:10000; background:${type === 'success' ? '#10b981' : '#ef4444'};`;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 3000);
}

function updatePagination() {
    const totalPages = Math.ceil(totalAvis / itemsPerPage);
    const pageInfo = document.getElementById('pageInfo');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    if (pageInfo) pageInfo.textContent = `Page ${currentPage} sur ${totalPages}`;
    if (prevBtn) prevBtn.disabled = currentPage === 1;
    if (nextBtn) nextBtn.disabled = currentPage === totalPages;
}

async function loadAllAvis() {
    const list = document.getElementById('avisList');
    if (!list) return;
    list.innerHTML = '<div class="col-12"><p class="avis-list-status">Chargement des avis...</p></div>';
    try {
        const res = await fetch('../Frontoffice/list_avis.php?limit=1000');
        const data = await res.json();
        if (data.success && Array.isArray(data.avis)) {
            allAvis = data.avis;
            totalAvis = allAvis.length;
            if (totalAvis === 0) {
                list.innerHTML = '<div class="col-12"><p class="avis-list-status">Aucun avis pour le moment — soyez le premier à partager votre expérience.</p></div>';
                const pag = document.getElementById('avisPagination');
                if (pag) pag.style.display = 'none';
                return;
            }
            document.getElementById('avisPagination').style.display = totalAvis > itemsPerPage ? 'block' : 'none';
            loadAvisPage(1);
        } else {
            list.innerHTML = '<div class="col-12"><p class="avis-list-status">Erreur chargement avis.</p></div>';
        }
    } catch(e) {
        console.error(e);
        list.innerHTML = '<div class="col-12"><p class="avis-list-status">Erreur de connexion.</p></div>';
    }
}

function loadAvisPage(page) {
    currentPage = page;
    const list = document.getElementById('avisList');
    const start = (page - 1) * itemsPerPage;
    const avisToShow = allAvis.slice(start, start + itemsPerPage);
    list.innerHTML = '';
    
    avisToShow.forEach((a, idx) => {
        const note = Number(a.note || 0);
        const starsLine = avisStarsLine(note);
        const gIdx = (start + idx) % AVIS_AVATAR_GRADS.length;
        const gradAttr = AVIS_AVATAR_GRADS[gIdx];
        const auteurLabel = escapeHtml(a.auteur || 'Utilisateur');
        const initials = escapeHtml(avisAuthorInitials(a.auteur));

        const isOwner = (a.id_utilisateur == sessionUserId);
        const isAdmin = (sessionUserRole === 'admin');
        const canModify = (isOwner || isAdmin);
        
        list.innerHTML += `
            <div class="col-4" style="min-width:280px;">
                <div class="avis-card">
                  <div class="avis-quote">"</div>
                  <p class="avis-text">${escapeHtml(a.contenu || '')}</p>
                  <div class="stars">${starsLine}</div>
                  <div class="avis-author mt-2">
                    <div class="avis-avatar"${gradAttr}>${initials}</div>
                    <div class="avis-author-info">
                      <div class="name">${auteurLabel}</div>
                      <div class="role">${escapeHtml(formatDateFr(a.date_avis))}</div>
                    </div>
                  </div>
                  ${canModify ? `
                    <div class="avis-dynamic-actions">
                      ${(isOwner || isAdmin) ? `<button type="button" onclick="openEditModal(${a.id_avis})" class="btn btn-sm">Modifier</button>` : ''}
                      <button type="button" onclick="deleteAvis(${a.id_avis})" class="btn btn-sm btn-danger">Supprimer</button>
                    </div>
                  ` : ''}
                </div>
            </div>
        `;
    });
    updatePagination();
}

async function openEditModal(id) {
    if (!isLoggedIn) {
        window.location.href = "loginuser.html";
        return;
    }
    try {
        const res = await fetch(`../Frontoffice/get_avis.php?id=${id}`);
        const data = await res.json();
        if (data.success) {
            const avis = data.avis;
            const isOwn = avis.id_utilisateur == sessionUserId;
            if (!isOwn && sessionUserRole !== 'admin') {
                showNotification('Vous ne pouvez modifier que vos propres avis', 'error');
                return;
            }
            document.getElementById('modalAvisId').value = avis.id_avis;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Modifier mon avis';
            document.getElementById('modalSubmitButton').innerHTML = '<i class="fas fa-save"></i> Enregistrer';
            document.getElementById('modalNoteInput').value = avis.note;
            document.getElementById('modalContenuInput').value = avis.contenu;
            document.getElementById('avisModal').style.display = 'flex';
            const note = parseInt(avis.note);
            document.querySelectorAll('.modal-star').forEach((s, i) => {
                if (i < note) s.classList.add('active');
                else s.classList.remove('active');
            });
        }
    } catch(err) {
        showNotification('Erreur chargement', 'error');
    }
}

async function deleteAvis(id) {
    if (!confirm('Supprimer cet avis ?')) return;
    
    // Vérifier les droits avant d'envoyer
    const avis = allAvis.find(a => a.id_avis == id);
    if (avis && avis.id_utilisateur != sessionUserId && sessionUserRole !== 'admin') {
        showNotification('Vous ne pouvez supprimer que vos propres avis', 'error');
        return;
    }
    
    const fd = new FormData();
    fd.append('id_avis', id);
    try {
        const res = await fetch('../Frontoffice/delete_avis.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showNotification('Avis supprimé', 'success');
            loadAllAvis();
        } else {
            showNotification(data.error || 'Erreur suppression', 'error');
        }
    } catch(err) {
        showNotification('Erreur réseau', 'error');
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadAllAvis();
    
    // Stars modal
    const starsContainer = document.getElementById('modalStarsContainer');
    if (starsContainer) {
        starsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-star')) {
                const val = parseInt(e.target.dataset.note);
                document.getElementById('modalNoteInput').value = val;
                document.querySelectorAll('.modal-star').forEach((s, i) => {
                    if (i < val) s.classList.add('active');
                    else s.classList.remove('active');
                });
            }
        });
    }
    
    // Modal form submit
    const avisForm = document.getElementById('avisModalForm');
    if (avisForm) {
        avisForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const fd = new FormData(e.target);
            const isEdit = fd.get('id_avis');
            const endpoint = isEdit ? '../Frontoffice/update_avis.php' : '../Frontoffice/submit_avis.php';
            const btn = e.target.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
            
            try {
                const res = await fetch(endpoint, { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    closeAvisModal();
                    loadAllAvis();
                    showNotification(isEdit ? 'Avis modifié' : 'Avis ajouté', 'success');
                } else {
                    showNotification(data.error || 'Erreur', 'error');
                }
            } catch(err) {
                showNotification('Erreur réseau', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = isEdit ? '<i class="fas fa-save"></i> Enregistrer' : '<i class="fas fa-paper-plane"></i> Publier mon avis';
            }
        });
    }
    
    // Image preview
    const modalImageInput = document.getElementById('modalImageInput');
    if (modalImageInput) {
        modalImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('modalImagePreview');
            const fileName = document.getElementById('modalFileName');
            if (file) {
                fileName.textContent = file.name;
                const reader = new FileReader();
                reader.onload = (ev) => { preview.innerHTML = `<img src="${ev.target.result}" style="max-width:120px; border-radius:8px; margin-top:10px;">`; };
                reader.readAsDataURL(file);
            } else {
                fileName.textContent = 'Aucun fichier sélectionné';
                preview.innerHTML = '';
            }
        });
    }
    
    // Pagination buttons
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    if (prevBtn) {
        prevBtn.addEventListener('click', function() { if (currentPage > 1) loadAvisPage(currentPage - 1); });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function() { const total = Math.ceil(totalAvis / itemsPerPage); if (currentPage < total) loadAvisPage(currentPage + 1); });
    }
});

</script>

</body>
</html>