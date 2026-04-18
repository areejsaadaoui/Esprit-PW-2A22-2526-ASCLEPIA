<?php
include '../../controller/AssuranceController.php';
$assuranceC = new AssuranceController();
$list       = $assuranceC->listAssurances();

$assurances = [];
foreach ($list as $a) {
    $assurances[] = $a;
}

$types = array_unique(array_column($assurances, 'TYPE'));

$compare1 = null;
$compare2 = null;
if (isset($_GET['comp1'], $_GET['comp2'])) {
    foreach ($assurances as $a) {
        if ($a['id_assurance'] == $_GET['comp1']) $compare1 = $a;
        if ($a['id_assurance'] == $_GET['comp2']) $compare2 = $a;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Assurances - ASCLEPIA</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/frontoffice.css">
    <link rel="stylesheet" href="../../assets/css/assurance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body id="body">

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <a href="#" class="navbar-brand">
            <div class="navbar-logo">🏥</div>
            <div class="navbar-name">ASCL<span>EPIA</span></div>
        </a>
        <div class="nav-links" id="navLinks">
            <a href="#" class="nav-link">Accueil</a>
            <a href="#" class="nav-link active">Assurances</a>
            <a href="#" class="nav-link">Médecins</a>
            <a href="#" class="nav-link">Contact</a>
        </div>
        <div class="nav-actions">
            <a href="#" class="btn btn-outline-white btn-sm">Connexion</a>
            <a href="#" class="btn btn-primary btn-sm">S'inscrire</a>
        </div>
        <div class="hamburger" onclick="document.getElementById('navLinks').classList.toggle('open')">
            <span></span><span></span><span></span>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero" style="min-height: 40vh; padding: 120px 0 60px;">
        <div class="hero-glow hero-glow-1"></div>
        <div class="hero-glow hero-glow-2"></div>
        <div class="container">
            <div class="hero-content" style="max-width:100%; text-align:center;">
                <div class="hero-badge">🛡️ Nos offres</div>
                <h1 class="hero-title">Choisissez votre <span class="highlight">assurance santé</span></h1>
                <p class="hero-subtitle" style="margin: 0 auto;">Des formules adaptées à chaque besoin, pour vous et votre famille.</p>
            </div>
        </div>
    </section>

    <!-- ASSURANCES SECTION -->
    <section class="section-padding" style="background: var(--bg);">
        <div class="container">

            <!-- COMPARATEUR RÉSULTAT -->
            <?php if ($compare1 && $compare2): ?>
            <div class="comparateur-section">
                <h2>⚖️ Comparaison des assurances</h2>
                <table class="compare-table">
                    <thead>
                        <tr>
                            <th>Critère</th>
                            <th><?= htmlspecialchars($compare1['nom_assurance']) ?></th>
                            <th><?= htmlspecialchars($compare2['nom_assurance']) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Type</td>
                            <td><?= htmlspecialchars($compare1['TYPE']) ?></td>
                            <td><?= htmlspecialchars($compare2['TYPE']) ?></td>
                        </tr>
                        <tr>
                            <td>Prix / mois</td>
                            <td class="<?= $compare1['prix'] <= $compare2['prix'] ? 'winner' : 'loser' ?>">
                                <?= number_format($compare1['prix'], 2) ?> DT
                                <?= $compare1['prix'] < $compare2['prix'] ? '✅' : '' ?>
                            </td>
                            <td class="<?= $compare2['prix'] <= $compare1['prix'] ? 'winner' : 'loser' ?>">
                                <?= number_format($compare2['prix'], 2) ?> DT
                                <?= $compare2['prix'] < $compare1['prix'] ? '✅' : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Durée</td>
                            <td class="<?= $compare1['duree'] >= $compare2['duree'] ? 'winner' : 'loser' ?>">
                                <?= $compare1['duree'] ?> mois
                                <?= $compare1['duree'] > $compare2['duree'] ? '✅' : '' ?>
                            </td>
                            <td class="<?= $compare2['duree'] >= $compare1['duree'] ? 'winner' : 'loser' ?>">
                                <?= $compare2['duree'] ?> mois
                                <?= $compare2['duree'] > $compare1['duree'] ? '✅' : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Remboursement</td>
                            <td class="<?= $compare1['taux_remboursement'] >= $compare2['taux_remboursement'] ? 'winner' : 'loser' ?>">
                                <?= $compare1['taux_remboursement'] ?>%
                                <?= $compare1['taux_remboursement'] > $compare2['taux_remboursement'] ? '✅' : '' ?>
                            </td>
                            <td class="<?= $compare2['taux_remboursement'] >= $compare1['taux_remboursement'] ? 'winner' : 'loser' ?>">
                                <?= $compare2['taux_remboursement'] ?>%
                                <?= $compare2['taux_remboursement'] > $compare1['taux_remboursement'] ? '✅' : '' ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="margin-top:16px; text-align:right;">
                    <a href="assurancefront.php" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-xmark"></i> Effacer la comparaison
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- BARRE DE RECHERCHE -->
            <div class="front-search">
                <span class="search-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="searchInput" placeholder="Rechercher une assurance...">
            </div>

            <!-- FILTRES PAR TYPE -->
            <div class="filter-btns">
                <button class="filter-btn active" onclick="filtrerType(this, '')">Tous</button>
                <?php foreach ($types as $t): ?>
                <button class="filter-btn" onclick="filtrerType(this, '<?= htmlspecialchars($t) ?>')">
                    <?= htmlspecialchars($t) ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- CARTES -->
            <div class="row" id="cartesContainer" style="justify-content: center;">
                <?php foreach ($assurances as $a): ?>
                <div class="col-4 carte-assurance show"
                     data-nom="<?= strtolower(htmlspecialchars($a['nom_assurance'])) ?>"
                     data-type="<?= htmlspecialchars($a['TYPE']) ?>">
                    <div class="card assurance-card" style="text-align:center;">
                        <div class="icon-box icon-box-lg" style="margin: 0 auto 16px;">🛡️</div>
                        <span class="badge badge-primary" style="margin: 0 auto 12px;"><?= htmlspecialchars($a['TYPE']) ?></span>
                        <h3 style="font-size:1.2rem; margin-bottom: 10px;"><?= htmlspecialchars($a['nom_assurance']) ?></h3>
                        <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:16px; line-height:1.6;">
                            <?= htmlspecialchars($a['description']) ?>
                        </p>
                        <div class="rate"><?= number_format($a['prix'], 2) ?> DT <span>/ mois</span></div>
                        <div class="progress-bar-wrap" style="margin: 12px 0 6px;">
                            <div class="progress-bar" style="width: <?= $a['taux_remboursement'] ?>%"></div>
                        </div>
                        <p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:8px;">
                            Remboursement : <strong style="color:var(--primary)"><?= $a['taux_remboursement'] ?>%</strong>
                        </p>
                        <p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:16px;">
                            <i class="fa-regular fa-clock"></i> Durée : <?= $a['duree'] ?> mois
                        </p>
                        <label class="compare-check">
                            <input type="checkbox" class="compare-checkbox"
                                   value="<?= $a['id_assurance'] ?>"
                                   data-nom="<?= htmlspecialchars($a['nom_assurance']) ?>">
                            Ajouter à la comparaison
                        </label>
                        <a href="#" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:12px;">
                            Souscrire <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- MESSAGE AUCUN RÉSULTAT -->
            <div id="aucunResultat" style="display:none; text-align:center; padding:60px; color:var(--text-muted);">
                <div style="font-size:3rem; margin-bottom:16px;">🔍</div>
                <h3>Aucune assurance trouvée</h3>
            </div>

        </div>
    </section>

    <!-- BARRE COMPARATEUR -->
    <div class="compare-bar" id="compareBar">
        <p>Comparer : <span id="compareNames">-</span></p>
        <div style="display:flex; gap:12px;">
            <button onclick="resetCompare()" class="btn btn-outline-white btn-sm">
                <i class="fa-solid fa-xmark"></i> Annuler
            </button>
            <button onclick="lancerComparaison()" class="btn btn-primary btn-sm" id="btnComparer" disabled>
                <i class="fa-solid fa-scale-balanced"></i> Comparer
            </button>
        </div>
    </div>

    <!-- BOUTON MODE SOMBRE -->
    <button class="dark-toggle" id="darkToggle" onclick="toggleDark()" title="Mode sombre">
        🌙
    </button>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>© 2025 ASCLEPIA. Tous droits réservés.</p>
                <p>Fait avec ❤️ par l'équipe ASCLEPIA</p>
            </div>
        </div>
    </footer>

<script>
    // Navbar scroll
    window.addEventListener('scroll', function() {
        document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
    });

    // ---- MODE SOMBRE ----
    var darkMode = localStorage.getItem('darkMode') === 'true';

    function appliquerDark() {
        if (darkMode) {
            document.getElementById('body').classList.add('dark-mode');
            document.getElementById('darkToggle').textContent = '☀️';
        } else {
            document.getElementById('body').classList.remove('dark-mode');
            document.getElementById('darkToggle').textContent = '🌙';
        }
    }

    function toggleDark() {
        darkMode = !darkMode;
        localStorage.setItem('darkMode', darkMode);
        appliquerDark();
    }

    // Appliquer au chargement
    appliquerDark();

    // ---- RECHERCHE + FILTRE EN TEMPS RÉEL AVEC ANIMATION ----
    var activeType = '';

    function filtrer() {
        var search = document.getElementById('searchInput').value.toLowerCase().trim();
        var cartes = document.querySelectorAll('.carte-assurance');
        var visible = 0;

        cartes.forEach(function(carte) {
            var nom  = carte.dataset.nom;
            var type = carte.dataset.type;

            var matchSearch = search === '' || nom.indexOf(search) !== -1;
            var matchType   = activeType === '' || type === activeType;

            if (matchSearch && matchType) {
                // Afficher avec animation
                carte.classList.remove('hide');
                carte.style.display = '';
                carte.classList.add('show');
                visible++;
            } else {
                // Cacher avec animation
                carte.classList.remove('show');
                carte.classList.add('hide');
                setTimeout(function(c) {
                    return function() {
                        if (c.classList.contains('hide')) {
                            c.style.display = 'none';
                        }
                    };
                }(carte), 250);
            }
        });

        setTimeout(function() {
            var v = document.querySelectorAll('.carte-assurance.show').length;
            document.getElementById('aucunResultat').style.display = v === 0 ? 'block' : 'none';
        }, 300);
    }

    // Recherche en temps réel
    document.getElementById('searchInput').addEventListener('input', function() {
        filtrer();
    });

    // Filtre par type
    function filtrerType(btn, type) {
        activeType = type;
        document.querySelectorAll('.filter-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        btn.classList.add('active');
        filtrer();
    }

    // ---- COMPARATEUR ----
    var selected = [];

    document.querySelectorAll('.compare-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var id  = this.value;
            var nom = this.dataset.nom;
            if (this.checked) {
                if (selected.length >= 2) {
                    this.checked = false;
                    alert('Vous pouvez comparer seulement 2 assurances !');
                    return;
                }
                selected.push({ id: id, nom: nom });
            } else {
                selected = selected.filter(function(s) { return s.id !== id; });
            }
            majBarre();
        });
    });

    function majBarre() {
        var bar   = document.getElementById('compareBar');
        var names = document.getElementById('compareNames');
        var btn   = document.getElementById('btnComparer');
        if (selected.length > 0) {
            bar.classList.add('visible');
            names.textContent = selected.map(function(s) { return s.nom; }).join(' vs ');
            btn.disabled = selected.length < 2;
        } else {
            bar.classList.remove('visible');
        }
    }

    function lancerComparaison() {
        if (selected.length === 2) {
            window.location.href = 'assurancefront.php?comp1=' + selected[0].id + '&comp2=' + selected[1].id;
        }
    }

    function resetCompare() {
        selected = [];
        document.querySelectorAll('.compare-checkbox').forEach(function(cb) { cb.checked = false; });
        majBarre();
    }
</script>
</body>
</html>