<?php
session_start();
require_once __DIR__ . '/langue.php';
include '../../controller/AssuranceController.php';
$assuranceC = new AssuranceController();
$list       = $assuranceC->listAssurances();

if (!function_exists('i18n_t')) {
    function i18n_t($key, $lang = 'fr') {
        global $I18N_TR;
        return $I18N_TR[$lang][$key] ?? $I18N_TR['fr'][$key] ?? $key;
    }
}

if (!function_exists('i18n_lang_url')) {
    function i18n_lang_url($newLang) {
        $params = $_GET;
        $params['lang'] = $newLang;
        return basename($_SERVER['PHP_SELF']) . '?' . http_build_query($params);
    }
}

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

$i18n = i18n_boot('fr');
$lang = $i18n['lang'];
$isRtl = $i18n['isRtl'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(i18n_t('assurancefront_title', $lang)) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/frontoffice.css">
    <link rel="stylesheet" href="../../assets/css/assurance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .pagination { display:flex; align-items:center; justify-content:center; gap:8px; margin-top:24px; flex-wrap:wrap; }
        .pagination button, .pagination span { display:inline-flex; align-items:center; justify-content:center; min-width:38px; height:38px; padding:0 10px; border-radius:var(--radius); font-size:0.88rem; font-weight:600; border:1px solid var(--border); background:var(--white); color:var(--text); }
        .pagination button { cursor:pointer; transition:var(--transition-fast); }
        .pagination button:hover { background:var(--primary); color:white; border-color:var(--primary); }
        .pagination button.active { background:var(--primary); color:white; border-color:var(--primary); }
        .pagination button:disabled { opacity:0.4; cursor:not-allowed; }
        .page-info { text-align:center; font-size:0.82rem; color:var(--text-muted); margin-top:8px; }

        /* RTL adjustments (Arabic) */
        html[dir="rtl"] body { direction: rtl; text-align: right; }
        html[dir="rtl"] .nav-links { direction: rtl; }
        html[dir="rtl"] .compare-bar { direction: rtl; }
    </style>
</head>
<body id="body">

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <a href="#" class="navbar-brand">
            <div class="navbar-logo">🏥</div>
            <div class="navbar-name">ASCL<span>EPIA</span></div>
        </a>
        <div class="nav-links" id="navLinks">
            <a href="../front/indexp.php" class="nav-link"><?= htmlspecialchars(i18n_t('home', $lang)) ?></a>
            <a href="#" class="nav-link active"><?= htmlspecialchars(i18n_t('insurances', $lang)) ?></a>
            <a href="#" class="nav-link"><?= htmlspecialchars(i18n_t('doctors', $lang)) ?></a>
            <a href="#" class="nav-link"><?= htmlspecialchars(i18n_t('contact', $lang)) ?></a>
        </div>
        <div class="nav-actions">
            <a href="#" class="btn btn-outline-white btn-sm"><?= htmlspecialchars(i18n_t('login', $lang)) ?></a>
            <a href="#" class="btn btn-primary btn-sm"><?= htmlspecialchars(i18n_t('signup', $lang)) ?></a>
            <div style="display:flex; gap:8px; align-items:center; margin-left:12px;">
                <a class="btn btn-outline-white btn-sm" href="<?= htmlspecialchars(i18n_lang_url('fr')) ?>">FR</a>
                <a class="btn btn-outline-white btn-sm" href="<?= htmlspecialchars(i18n_lang_url('en')) ?>">EN</a>
                <a class="btn btn-outline-white btn-sm" href="<?= htmlspecialchars(i18n_lang_url('ar')) ?>">AR</a>
            </div>
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
                <div class="hero-badge"><?= htmlspecialchars(i18n_t('offers', $lang)) ?></div>
                <h1 class="hero-title"><?= htmlspecialchars(i18n_t('choose_health_insurance', $lang)) ?></h1>
                <p class="hero-subtitle" style="margin: 0 auto;"><?= htmlspecialchars(i18n_t('subtitle', $lang)) ?></p>
            </div>
        </div>
    </section>

    <!-- ASSURANCES SECTION -->
    <section class="section-padding" style="background: var(--bg);">
        <div class="container">

            <!-- COMPARATEUR RÉSULTAT -->
            <?php if ($compare1 && $compare2): ?>
            <div class="comparateur-section">
                <h2><?= htmlspecialchars(i18n_t('compare_title', $lang)) ?></h2>
                <table class="compare-table">
                    <thead>
                        <tr>
                            <th><?= htmlspecialchars(i18n_t('criterion', $lang)) ?></th>
                            <th><?= htmlspecialchars($compare1['nom_assurance']) ?></th>
                            <th><?= htmlspecialchars($compare2['nom_assurance']) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars(i18n_t('type', $lang)) ?></td>
                            <td><?= htmlspecialchars($compare1['TYPE']) ?></td>
                            <td><?= htmlspecialchars($compare2['TYPE']) ?></td>
                        </tr>
                        <tr>
                            <td><?= htmlspecialchars(i18n_t('price_per_month', $lang)) ?></td>
                            <td class="<?= $compare1['prix'] <= $compare2['prix'] ? 'winner' : 'loser' ?>">
                                <?= number_format($compare1['prix'], 2) ?> <?= htmlspecialchars(i18n_t('dt', $lang)) ?>
                                <?= $compare1['prix'] < $compare2['prix'] ? '✅' : '' ?>
                            </td>
                            <td class="<?= $compare2['prix'] <= $compare1['prix'] ? 'winner' : 'loser' ?>">
                                <?= number_format($compare2['prix'], 2) ?> <?= htmlspecialchars(i18n_t('dt', $lang)) ?>
                                <?= $compare2['prix'] < $compare1['prix'] ? '✅' : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?= htmlspecialchars(i18n_t('duration', $lang)) ?></td>
                            <td class="<?= $compare1['duree'] >= $compare2['duree'] ? 'winner' : 'loser' ?>">
                                <?= $compare1['duree'] ?> <?= htmlspecialchars(i18n_t('months', $lang)) ?>
                                <?= $compare1['duree'] > $compare2['duree'] ? '✅' : '' ?>
                            </td>
                            <td class="<?= $compare2['duree'] >= $compare1['duree'] ? 'winner' : 'loser' ?>">
                                <?= $compare2['duree'] ?> <?= htmlspecialchars(i18n_t('months', $lang)) ?>
                                <?= $compare2['duree'] > $compare1['duree'] ? '✅' : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?= htmlspecialchars(i18n_t('refund', $lang)) ?></td>
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
                    <a href="assurancefront.php?<?= htmlspecialchars(http_build_query(['lang' => $lang])) ?>" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-xmark"></i> <?= htmlspecialchars(i18n_t('clear_compare', $lang)) ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- BARRE DE RECHERCHE -->
            <div class="front-search">
                <span class="search-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="searchInput" placeholder="<?= htmlspecialchars(i18n_t('search_placeholder', $lang)) ?>">
            </div>

            <!-- FILTRES PAR TYPE -->
            <div class="filter-btns">
                <button class="filter-btn active" onclick="filtrerType(this, '')"><?= htmlspecialchars(i18n_t('all', $lang)) ?></button>
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
                        <div class="rate"><?= number_format($a['prix'], 2) ?> <?= htmlspecialchars(i18n_t('dt', $lang)) ?> <span><?= htmlspecialchars(i18n_t('per_month_short', $lang)) ?></span></div>
                        <div class="progress-bar-wrap" style="margin: 12px 0 6px;">
                            <div class="progress-bar" style="width: <?= $a['taux_remboursement'] ?>%"></div>
                        </div>
                        <p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:8px;">
                            <?= htmlspecialchars(i18n_t('refund_label', $lang)) ?> <strong style="color:var(--primary)"><?= $a['taux_remboursement'] ?>%</strong>
                        </p>
                        <p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:16px;">
                            <i class="fa-regular fa-clock"></i> <?= htmlspecialchars(i18n_t('duration_label', $lang)) ?> <?= $a['duree'] ?> <?= htmlspecialchars(i18n_t('months', $lang)) ?>
                        </p>
                        <label class="compare-check">
                            <input type="checkbox" class="compare-checkbox"
                                   value="<?= $a['id_assurance'] ?>"
                                   data-nom="<?= htmlspecialchars($a['nom_assurance']) ?>">
                            <?= htmlspecialchars(i18n_t('add_compare', $lang)) ?>
                        </label>
                        <a href="souscrireContrat.php?<?= htmlspecialchars(http_build_query(['id_assurance' => $a['id_assurance'], 'lang' => $lang])) ?>" 
   class="btn btn-primary" 
   style="width:100%; justify-content:center; margin-top:12px;">
    <?= htmlspecialchars(i18n_t('subscribe', $lang)) ?> <i class="fa-solid fa-arrow-right"></i>
</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- MESSAGE AUCUN RÉSULTAT -->
            <div id="aucunResultat" style="display:none; text-align:center; padding:60px; color:var(--text-muted);">
                <div style="font-size:3rem; margin-bottom:16px;">🔍</div>
                <h3><?= htmlspecialchars(i18n_t('no_result_title', $lang)) ?></h3>
            </div>

            <div id="paginationContainer" class="pagination"></div>
            <div id="paginationInfo" class="page-info"></div>

        </div>
    </section>

    <!-- BARRE COMPARATEUR -->
    <div class="compare-bar" id="compareBar">
        <p><?= htmlspecialchars(i18n_t('compare_label', $lang)) ?> <span id="compareNames">-</span></p>
        <div style="display:flex; gap:12px;">
            <button onclick="resetCompare()" class="btn btn-outline-white btn-sm">
                <i class="fa-solid fa-xmark"></i> <?= htmlspecialchars(i18n_t('cancel', $lang)) ?>
            </button>
            <button onclick="lancerComparaison()" class="btn btn-primary btn-sm" id="btnComparer" disabled>
                <i class="fa-solid fa-scale-balanced"></i> <?= htmlspecialchars(i18n_t('compare', $lang)) ?>
            </button>
        </div>
    </div>
<button class="mes-contrats-fab" onclick="goToContrats()">
        <i class="fa-solid fa-file-contract"></i>
        <span>Mes contrats</span>
    </button>
   <button class="ai-fab" onclick="goToDoctor()" title="Dr. ASCLEPIA">
        🤖
    </button>
    <div id="transitionOverlay"></div>
    <!-- BOUTON MODE SOMBRE -->
    <button class="dark-toggle" id="darkToggle" onclick="toggleDark()" title="<?= htmlspecialchars(i18n_t('dark_mode', $lang)) ?>">
        🌙
    </button>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p><?= htmlspecialchars(i18n_t('footer_rights', $lang)) ?></p>
                <p><?= htmlspecialchars(i18n_t('footer_made_by', $lang)) ?></p>
            </div>
        </div>
    </footer>

<script>
    var i18n = <?= json_encode([
        'alert_only_2' => i18n_t('alert_only_2', $lang),
        'page' => i18n_t('page', $lang),
        'on' => i18n_t('on', $lang),
    ], JSON_UNESCAPED_UNICODE) ?>;
    var currentLang = <?= json_encode($lang, JSON_UNESCAPED_UNICODE) ?>;

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

    // ---- RECHERCHE + FILTRE + PAGINATION ----
    var activeType = '';
    var currentPage = 1;
    var itemsPerPage = 6;

    function getCartesFiltrees() {
        var search = document.getElementById('searchInput').value.toLowerCase().trim();
        var cartes = document.querySelectorAll('.carte-assurance');
        var filtres = [];

        cartes.forEach(function(carte) {
            var nom  = carte.dataset.nom;
            var type = carte.dataset.type;

            var matchSearch = search === '' || nom.indexOf(search) !== -1;
            var matchType   = activeType === '' || type === activeType;

            if (matchSearch && matchType) {
                filtres.push(carte);
            }
        });

        return filtres;
    }

    function renderPagination(totalPages) {
        var container = document.getElementById('paginationContainer');
        var info = document.getElementById('paginationInfo');
        if (!container || !info) return;

        if (totalPages <= 1) {
            container.innerHTML = '';
            info.textContent = '';
            return;
        }

        var html = '';
        html += '<button type="button" ' + (currentPage === 1 ? 'disabled' : '') + ' onclick="changePage(' + (currentPage - 1) + ')"><i class="fa-solid fa-chevron-left"></i></button>';
        for (var i = 1; i <= totalPages; i++) {
            html += '<button type="button" class="' + (i === currentPage ? 'active' : '') + '" onclick="changePage(' + i + ')">' + i + '</button>';
        }
        html += '<button type="button" ' + (currentPage === totalPages ? 'disabled' : '') + ' onclick="changePage(' + (currentPage + 1) + ')"><i class="fa-solid fa-chevron-right"></i></button>';
        container.innerHTML = html;
        info.textContent = (i18n.page || 'Page') + ' ' + currentPage + ' ' + (i18n.on || 'sur') + ' ' + totalPages;
    }

    function filtrer() {
        var toutesCartes = document.querySelectorAll('.carte-assurance');
        var cartesFiltrees = getCartesFiltrees();
        var totalFiltrees = cartesFiltrees.length;
        var totalPages = Math.max(1, Math.ceil(totalFiltrees / itemsPerPage));

        if (currentPage > totalPages) {
            currentPage = 1;
        }

        var start = (currentPage - 1) * itemsPerPage;
        var end = start + itemsPerPage;

        toutesCartes.forEach(function(carte) {
            carte.style.display = 'none';
            carte.classList.remove('show');
            carte.classList.add('hide');
        });

        cartesFiltrees.slice(start, end).forEach(function(carte) {
            carte.classList.remove('hide');
            carte.classList.add('show');
            carte.style.display = '';
        });

        document.getElementById('aucunResultat').style.display = totalFiltrees === 0 ? 'block' : 'none';
        renderPagination(totalFiltrees === 0 ? 1 : totalPages);
    }

    // Recherche en temps réel
    document.getElementById('searchInput').addEventListener('input', function() {
        currentPage = 1;
        filtrer();
    });

    // Filtre par type
    function filtrerType(btn, type) {
        activeType = type;
        currentPage = 1;
        document.querySelectorAll('.filter-btn').forEach(function(b) {
            b.classList.remove('active');
        });
        btn.classList.add('active');
        filtrer();
    }

    function changePage(page) {
        currentPage = page;
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
                    alert(i18n.alert_only_2 || 'Vous pouvez comparer seulement 2 assurances !');
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
           window.location.href = '/projetweb/View/frontoffice/assurancefront.php?comp1='
    + selected[0].id + '&comp2=' + selected[1].id + '&lang=' + encodeURIComponent(currentLang);
        }
    }

    function resetCompare() {
        selected = [];
        document.querySelectorAll('.compare-checkbox').forEach(function(cb) { cb.checked = false; });
        majBarre();
    }
    function goToContrats() {
        document.getElementById('transitionOverlay').classList.add('animate');
        setTimeout(function() {
            window.location.href = 'mesContrats.php?<?= htmlspecialchars(http_build_query(['lang' => $lang])) ?>';
        }, 480);
    }
  function goToDoctor() {
        document.getElementById('transitionOverlay').classList.add('animate');
        setTimeout(function() {
            window.location.href = 'doctorAI.php';
        }, 480);
    }

    filtrer();
</script>
</body>
</html>