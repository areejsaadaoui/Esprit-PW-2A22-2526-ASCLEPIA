<?php
// assurancelist.php - Page admin sécurisée
session_start();

// Vérifier si l'admin est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: loginadmin.html');
    exit();
}

// Récupérer les infos de l'admin connecté
$adminNom   = $_SESSION['user_nom']   ?? 'Administrateur';
$adminEmail = $_SESSION['user_email'] ?? '';
include '../../../Controller/ContratController.php';
$contratC = new ContratController();
$list     = $contratC->listContrats();

$contrats     = [];
$totalMontant = 0;
$statuts      = [];

foreach ($list as $c) {
    $contrats[]    = $c;
    $totalMontant += $c['montant'];
    $statuts[]     = $c['statut'];
}

if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="contrats_' . date('Ymd_His') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<html><head><meta charset='UTF-8'></head><body>";
    echo "<table border='0' cellpadding='0' cellspacing='0' style='font-family:Calibri,Arial,sans-serif; width:100%; border-collapse:collapse;'>";
    echo "<tr><td colspan='7' style='background:#0ea5e9;color:#fff;font-size:18px;font-weight:bold;padding:12px 14px;'>ASCLEPIA - Export Contrats</td></tr>";
    echo "<tr><td colspan='7' style='padding:8px 14px;color:#475569;font-size:12px;'>Date export: " . date('d/m/Y H:i') . " | Total: " . count($contrats) . " contrat(s)</td></tr>";
    echo "<tr style='background:#e0f2fe;color:#0f172a;font-weight:bold;'>
            <td style='border:1px solid #cbd5e1;padding:8px;'>ID Contrat</td>
            <td style='border:1px solid #cbd5e1;padding:8px;'>Assurance</td>
            <td style='border:1px solid #cbd5e1;padding:8px;'>Type</td>
            <td style='border:1px solid #cbd5e1;padding:8px;'>Date debut</td>
            <td style='border:1px solid #cbd5e1;padding:8px;'>Date fin</td>
            <td style='border:1px solid #cbd5e1;padding:8px;'>Montant (DT)</td>
            <td style='border:1px solid #cbd5e1;padding:8px;'>Statut</td>
          </tr>";

    $i = 0;
    foreach ($contrats as $c) {
        $bg = $i % 2 === 0 ? '#ffffff' : '#f8fafc';
        echo "<tr style='background:{$bg};'>
                <td style='border:1px solid #e2e8f0;padding:7px;'>" . (int)$c['id_contrat'] . "</td>
                <td style='border:1px solid #e2e8f0;padding:7px;'>" . htmlspecialchars($c['nom_assurance']) . "</td>
                <td style='border:1px solid #e2e8f0;padding:7px;'>" . htmlspecialchars(!empty($c['type_assurance']) ? $c['type_assurance'] : 'N/A') . "</td>
                <td style='border:1px solid #e2e8f0;padding:7px;'>" . htmlspecialchars($c['date_d']) . "</td>
                <td style='border:1px solid #e2e8f0;padding:7px;'>" . htmlspecialchars(!empty($c['date_f']) ? $c['date_f'] : '-') . "</td>
                <td style='border:1px solid #e2e8f0;padding:7px; mso-number-format:\"0.00\";'>" . number_format((float)$c['montant'], 2, '.', '') . "</td>
                <td style='border:1px solid #e2e8f0;padding:7px;'>" . htmlspecialchars($c['statut']) . "</td>
              </tr>";
        $i++;
    }
    echo "</table></body></html>";
    exit;
}

$count        = count($contrats);
$montantMoyen = $count > 0 ? $totalMontant / $count : 0;
$statutCount  = array_count_values($statuts);
$statutPop    = $count > 0 ? array_search(max($statutCount), $statutCount) : 'N/A';

$typeAssurance = [];
$montantsParMois = [];
foreach ($contrats as $c) {
    $type = !empty($c['type_assurance']) ? $c['type_assurance'] : 'N/A';
    $typeAssurance[] = $type;

    $monthKey = date('Y-m', strtotime($c['date_d']));
    if (!isset($montantsParMois[$monthKey])) {
        $montantsParMois[$monthKey] = 0;
    }
    $montantsParMois[$monthKey] += (float)$c['montant'];
}
$typeCount = array_count_values($typeAssurance);
ksort($montantsParMois);

$moisLabels = [];
$moisValues = [];
foreach ($montantsParMois as $monthKey => $value) {
    $dt = DateTime::createFromFormat('Y-m', $monthKey);
    $moisLabels[] = $dt ? $dt->format('M Y') : $monthKey;
    $moisValues[] = round($value, 2);
}

$perPage     = 5;
$totalItems  = count($contrats);
$totalPages  = max(1, ceil($totalItems / $perPage));
$currentPage = isset($_GET['page']) ? max(1, min((int)$_GET['page'], $totalPages)) : 1;
$offset      = ($currentPage - 1) * $perPage;
$paginated   = array_slice($contrats, $offset, $perPage);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrats - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <link rel="stylesheet" href="../../../assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .pagination { display:flex; align-items:center; justify-content:center; gap:8px; margin-top:24px; }
        .pagination a, .pagination span { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:var(--radius); font-size:0.88rem; font-weight:600; border:1px solid var(--border); background:var(--white); color:var(--text); text-decoration:none; transition:var(--transition-fast); }
        .pagination a:hover { background:var(--primary); color:white; border-color:var(--primary); }
        .pagination span.active { background:var(--primary); color:white; border-color:var(--primary); }
        .pagination span.disabled { opacity:0.4; }
        .results-info { font-size:0.85rem; color:var(--text-muted); margin-bottom:12px; }
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:18px; margin-bottom:20px; }
        .chart-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius-lg); padding:16px; box-shadow:var(--shadow-sm); opacity:0; transform:translateY(14px) scale(0.98); animation:cardIn .6s ease forwards; }
        .chart-card:nth-child(2) { animation-delay:.12s; }
        .chart-card h3 { font-size:0.95rem; margin-bottom:12px; color:var(--dark); }
        .table thead th.sortable { cursor:pointer; user-select:none; position:relative; }
        .table thead th.sortable::after { content:'\2195'; font-size:0.75rem; margin-left:6px; color:var(--text-muted); }
        .table thead th.sortable.asc::after { content:'\2191'; color:var(--primary); }
        .table thead th.sortable.desc::after { content:'\2193'; color:var(--primary); }
        @keyframes cardIn { to { opacity:1; transform:translateY(0) scale(1); } }
    </style>
</head>
<body>
<div class="admin-wrapper">

    <aside class="sidebar">
    <a href="dashboard.php" class="sidebar-brand">
        <div class="sidebar-logo">🏥</div>
        <div class="sidebar-title">ASCL<span>EPIA</span></div>
    </a>

    <div class="sidebar-user">
    <div class="user-avatar" id="adminAvatar">
        <?php echo strtoupper(substr($adminNom, 0, 2)); ?>
    </div>
    <div class="user-info">
        <div class="name" id="adminName">
            <?php echo htmlspecialchars($adminNom); ?>
        </div>
        <div class="role">Super Admin</div>
    </div>
</div>

    <nav class="sidebar-nav">

    <?php
        $current = basename($_SERVER['PHP_SELF']);
        $current_path = $_SERVER['PHP_SELF'];

        // Helper to check if current page matches any of the given filenames
        function isActive(...$pages) {
            global $current;
            return in_array($current, $pages);
        }

        // Helper to check if sub-menu should be open (any child is active)
        function isSubActive(...$pages) {
            global $current;
            return in_array($current, $pages) ? 'open' : '';
        }
    ?>

    <div class="nav-section-label">Menu Principal</div>

    <div class="nav-item">
        <a href="../../back/dashboard.php" <?= isActive('dashboard.php') ? 'class="active"' : '' ?>>
            <i class="fas fa-tachometer-alt nav-icon"></i>
            <span>Tableau de bord</span>
        </a>
    </div>

    <div class="nav-section-label">Gestion</div>

    <!-- Assurances & Contrats -->
    <div class="nav-item has-sub <?= isSubActive('assurancelist.php', 'contratList.php') ?>">
        <a onclick="toggleSubMenu(this)" <?= isActive('assurancelist.php', 'contratList.php') ? 'class="active"' : '' ?>>
            <i class="fa-solid fa-shield-halved nav-icon"></i>
            <span>Assurances &amp; Contrats</span>
            <i class="fas fa-chevron-right nav-arrow"></i>
        </a>
        <div class="sub-menu">
            <a href="../../backoffice/assurancelist.php"
               <?= isActive('assurancelist.php') ? 'class="active"' : '' ?>>
               Les assurances
            </a>
            <a href="contratList.php"
               <?= isActive('contratList.php') ? 'class="active"' : '' ?>>
               Les contrats
            </a>
        </div>
    </div>

    <!-- Ordonnances & Consultations -->
    <div class="nav-item has-sub <?= isSubActive('dashboard.php', 'list_consultation.php', 'list_ordonnance.php') ?>">
        <a onclick="toggleSubMenu(this)" <?= isActive('list_consultation.php', 'list_ordonnance.php') ? 'class="active"' : '' ?>>
            <i class="fa-solid fa-file-contract nav-icon"></i>
            <span>Ordonnances &amp; Consultations</span>
            <i class="fas fa-chevron-right nav-arrow"></i>
        </a>
        <div class="sub-menu">
            <a href="../../backoffice/dashboard.php"
               <?= isActive('dashboard.php') ? 'class="active"' : '' ?>>
               Toutes les consultations
            </a>
            <a href="../../backoffice/list_consultation.php"
               <?= isActive('list_consultation.php') ? 'class="active"' : '' ?>>
               Les consultations
            </a>
            <a href="../../backoffice/list_ordonnance.php"
               <?= isActive('list_ordonnance.php') ? 'class="active"' : '' ?>>
               Les ordonnances
            </a>
        </div>
    </div>

    <!-- Pharmacies & Médicaments -->
    <div class="nav-item has-sub <?= isSubActive('listepharmacie.php', 'listemedicament.php') ?>">
        <a onclick="toggleSubMenu(this)" <?= isActive('listepharmacie.php', 'listemedicament.php') ? 'class="active"' : '' ?>>
            <i class="fa-solid fa-prescription-bottle-medical nav-icon"></i>
            <span>Pharmacies &amp; Médicaments</span>
            <i class="fas fa-chevron-right nav-arrow"></i>
        </a>
        <div class="sub-menu">
            <a href="../../backoffice/listepharmacie.php"
               <?= isActive('listepharmacie.php') ? 'class="active"' : '' ?>>
               Les pharmacies
            </a>
            <a href="../../backoffice/listemedicament.php"
               <?= isActive('listemedicament.php') ? 'class="active"' : '' ?>>
               Les médicaments
            </a>
        </div>
    </div>

    <!-- Forum -->
    <div class="nav-item has-sub <?= isSubActive('postList.php', 'addpost.php', 'dashboard.php') ?>">
        <a onclick="toggleSubMenu(this)" <?= isActive('postList.php', 'addpost.php') ? 'class="active"' : '' ?>>
            <i class="fas fa-comments nav-icon"></i>
            <span>Forum</span>
            <i class="fas fa-chevron-right nav-arrow"></i>
        </a>
        <div class="sub-menu">
            <a href="../../Frontoffice/postList.php"
               <?= isActive('postList.php') ? 'class="active"' : '' ?>>
               Tous les posts
            </a>
            <a href="addpost.php"
               <?= isActive('addpost.php') ? 'class="active"' : '' ?>>
               Ajouter un post
            </a>
            <a href="dashboard.php"
               <?= isActive('dashboard.php') ? 'class="active"' : '' ?>>
               Gestion des posts
            </a>
        </div>
    </div>

    <div class="nav-section-label">Configuration</div>

    <div class="nav-item">
        <a href="../../front/indexp.php" <?= isActive('indexp.php') ? 'class="active"' : '' ?>>
            <i class="fas fa-globe nav-icon"></i>
            <span>Voir le site</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="../../back/loginadmin.html" <?= isActive('loginadmin.html') ? 'class="active"' : '' ?>>
            <i class="fas fa-sign-out-alt nav-icon"></i>
            <span>Déconnexion</span>
        </a>
    </div>

</nav>
    <div class="sidebar-footer">
        <div class="sidebar-version">Version 1.0</div>
    </div>
</aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <div class="page-title">Contrats</div>
                    <div class="breadcrumb">
                        <a href="#">Dashboard</a><span>/</span><span>Contrats</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="?export=excel" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </a>
                <a href="addContrat.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Nouveau contrat
                </a>
            </div>
        </div>

        <div class="page-content">

            <!-- STAT CARDS -->
            <div class="row mb-4">
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-card-icon blue"><i class="fa-solid fa-file-contract"></i></div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= $count ?></div>
                            <div class="stat-card-label">Total Contrats</div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-card-icon green"><i class="fa-solid fa-money-bill-wave"></i></div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= number_format($totalMontant, 0) ?> DT</div>
                            <div class="stat-card-label">Montant Total</div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-card-icon cyan"><i class="fa-solid fa-calculator"></i></div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= number_format($montantMoyen, 0) ?> DT</div>
                            <div class="stat-card-label">Montant Moyen</div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-card-icon purple"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= $statutPop ?></div>
                            <div class="stat-card-label">Statut dominant</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="chart-card">
                    <h3>Répartition des contrats par type</h3>
                    <canvas id="typePieChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Montants des contrats par mois</h3>
                    <canvas id="monthBarChart"></canvas>
                </div>
            </div>

            <!-- FILTRES -->
            <div class="filters-bar">
                <div class="d-flex align-center gap-2" style="flex:1; flex-wrap:wrap;">
                    <div class="search-bar">
                        <span class="icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" id="searchInput" placeholder="Rechercher par assurance...">
                    </div>
                    <select id="statutFilter" class="form-control" style="width:auto;">
                        <option value="">Tous les statuts</option>
                        <?php foreach (['Actif', 'Expiré', 'Annulé'] as $s): ?>
                            <option value="<?= $s ?>"><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="resetFilters" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-xmark"></i> Réinitialiser
                    </button>
                </div>
            </div>

            <div id="resultsInfo" class="results-info"><?= count($paginated) ?> résultat(s) trouvé(s)</div>

            <!-- TABLE -->
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort-key="id">#</th>
                            <th class="sortable" data-sort-key="assurance">Assurance</th>
                            <th class="sortable" data-sort-key="dated">Date début</th>
                            <th class="sortable" data-sort-key="datef">Date fin</th>
                            <th class="sortable" data-sort-key="montant">Montant (DT)</th>
                            <th class="sortable" data-sort-key="statut">Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="contratsTableBody">
                        <?php if (count($paginated) === 0): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="icon">📄</div>
                                    <h3>Aucun contrat</h3>
                                    <p>Commencez par ajouter un contrat.</p>
                                    <a href="addContrat.php" class="btn btn-primary btn-sm">+ Ajouter</a>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($paginated as $c): ?>
                        <tr class="contrat-row"
                            data-id="<?= (int)$c['id_contrat'] ?>"
                            data-assurance="<?= strtolower(htmlspecialchars($c['nom_assurance'])) ?>"
                            data-statut="<?= htmlspecialchars($c['statut']) ?>"
                            data-dated="<?= htmlspecialchars($c['date_d']) ?>"
                            data-datef="<?= htmlspecialchars($c['date_f'] ?? '') ?>"
                            data-montant="<?= (float)$c['montant'] ?>">
                            <td><?= $c['id_contrat'] ?></td>
                            <td><strong><?= htmlspecialchars($c['nom_assurance']) ?></strong></td>
                            <td><?= $c['date_d'] ?></td>
                            <td><?= $c['date_f'] ?? '—' ?></td>
                            <td><?= number_format($c['montant'], 2) ?> DT</td>
                            <td>
                                <?php
                                $badgeClass = 'badge-gray';
                                if ($c['statut'] === 'Actif')   $badgeClass = 'badge-success';
                                if ($c['statut'] === 'Expiré')  $badgeClass = 'badge-warning';
                                if ($c['statut'] === 'Annulé')  $badgeClass = 'badge-danger';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($c['statut']) ?></span>
                            </td>
                            <td>
                                <div class="actions">
                                    <form method="POST" action="updateContrat.php" style="display:inline">
                                        <input type="hidden" name="id" value="<?= $c['id_contrat'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm">
                                            <i class="fa-solid fa-pen"></i> Modifier
                                        </button>
                                    </form>
                                    <a href="deleteContrat.php?id=<?= $c['id_contrat'] ?>"
                                       onclick="return confirm('Confirmer la suppression ?')"
                                       class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr id="noResultsRow" style="display:none;">
                            <td colspan="7">
                                <div class="empty-state" style="padding:24px;">
                                    <div class="icon">🔍</div>
                                    <h3>Aucun résultat</h3>
                                    <p>Aucun contrat ne correspond aux filtres actuels.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?>"><i class="fa-solid fa-chevron-left"></i></a>
                <?php else: ?>
                    <span class="disabled"><i class="fa-solid fa-chevron-left"></i></span>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $currentPage): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>"><i class="fa-solid fa-chevron-right"></i></a>
                <?php else: ?>
                    <span class="disabled"><i class="fa-solid fa-chevron-right"></i></span>
                <?php endif; ?>
            </div>
            <div style="text-align:center; font-size:0.82rem; color:var(--text-muted); margin-top:8px;">
                Page <?= $currentPage ?> sur <?= $totalPages ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });

    var searchInput  = document.getElementById('searchInput');
    var statutFilter = document.getElementById('statutFilter');
    var resetFilters = document.getElementById('resetFilters');
    var resultsInfo  = document.getElementById('resultsInfo');
    var noResultsRow = document.getElementById('noResultsRow');
    var rows         = document.querySelectorAll('.contrat-row');

    function filtrerContrats() {
        var search = searchInput.value.toLowerCase().trim();
        var statut = statutFilter.value;
        var visible = 0;

        rows.forEach(function(row) {
            var assurance = row.dataset.assurance;
            var rowStatut = row.dataset.statut;
            var matchSearch = search === '' || assurance.indexOf(search) !== -1;
            var matchStatut = statut === '' || rowStatut === statut;

            if (matchSearch && matchStatut) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        resultsInfo.textContent = visible + ' résultat(s) trouvé(s)';
        if (noResultsRow) {
            noResultsRow.style.display = visible === 0 ? '' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filtrerContrats);
    }
    if (statutFilter) {
        statutFilter.addEventListener('change', filtrerContrats);
    }
    if (resetFilters) {
        resetFilters.addEventListener('click', function() {
            searchInput.value = '';
            statutFilter.value = '';
            filtrerContrats();
        });
    }

    var sortableHeaders = document.querySelectorAll('th.sortable');
    var currentSortKey = '';
    var currentSortDir = 'asc';

    function trierContrats(key, dir) {
        var tbody = document.getElementById('contratsTableBody');
        if (!tbody) return;
        var noResults = document.getElementById('noResultsRow');
        var rowsArr = Array.from(tbody.querySelectorAll('.contrat-row'));

        rowsArr.sort(function(a, b) {
            var av = a.dataset[key] || '';
            var bv = b.dataset[key] || '';
            var aNum = parseFloat(av);
            var bNum = parseFloat(bv);
            var isDate = key === 'dated' || key === 'datef';
            var cmp;
            if (isDate) {
                cmp = av.localeCompare(bv);
            } else if (!isNaN(aNum) && !isNaN(bNum)) {
                cmp = aNum - bNum;
            } else {
                cmp = av.localeCompare(bv, 'fr', { sensitivity: 'base' });
            }
            return dir === 'asc' ? cmp : -cmp;
        });

        rowsArr.forEach(function(r) { tbody.appendChild(r); });
        if (noResults) tbody.appendChild(noResults);
    }

    sortableHeaders.forEach(function(th) {
        th.addEventListener('click', function() {
            var key = th.dataset.sortKey;
            if (!key) return;
            if (currentSortKey === key) {
                currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortKey = key;
                currentSortDir = 'asc';
            }

            sortableHeaders.forEach(function(h) {
                h.classList.remove('asc', 'desc');
            });
            th.classList.add(currentSortDir);
            trierContrats(currentSortKey, currentSortDir);
        });
    });

    var typeLabels = <?= json_encode(array_keys($typeCount)) ?>;
    var typeValues = <?= json_encode(array_values($typeCount)) ?>;
    var monthLabels = <?= json_encode($moisLabels) ?>;
    var monthValues = <?= json_encode($moisValues) ?>;
    var pieColors = ['#0ea5e9', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6'];
    var sharedAnim = {
        duration: 1600,
        easing: 'easeOutExpo'
    };

    if (document.getElementById('typePieChart') && typeLabels.length > 0) {
        new Chart(document.getElementById('typePieChart'), {
            type: 'pie',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeValues,
                    backgroundColor: pieColors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 20
                }]
            },
            options: {
                animation: {
                    ...sharedAnim,
                    animateRotate: true,
                    animateScale: true
                },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    if (document.getElementById('monthBarChart') && monthLabels.length > 0) {
        var monthCtx = document.getElementById('monthBarChart').getContext('2d');
        var barGradient = monthCtx.createLinearGradient(0, 0, 0, 280);
        barGradient.addColorStop(0, '#38bdf8');
        barGradient.addColorStop(1, '#0ea5e9');

        new Chart(document.getElementById('monthBarChart'), {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Montant total (DT)',
                    data: monthValues,
                    backgroundColor: barGradient,
                    borderRadius: 12,
                    borderSkipped: false,
                    hoverBackgroundColor: '#0284c7'
                }]
            },
            options: {
                animation: {
                    ...sharedAnim,
                    delay: function(context) {
                        return context.dataIndex * 90;
                    }
                },
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
    function toggleSubMenu(el) {
    var navItem = el.closest('.nav-item');
    var isOpen  = navItem.classList.contains('open');

    // Close all open sub-menus first
    document.querySelectorAll('.nav-item.has-sub.open').forEach(function(item) {
        item.classList.remove('open');
        var sub = item.querySelector('.sub-menu');
        if (sub) sub.classList.remove('open');
    });

    // If it wasn't open, open it now
    if (!isOpen) {
        navItem.classList.add('open');
        var sub = navItem.querySelector('.sub-menu');
        if (sub) sub.classList.add('open');
    }
}

// Auto-open on page load — directly add classes, DO NOT simulate a click
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.nav-item.has-sub.open').forEach(function (item) {
        var sub = item.querySelector('.sub-menu');
        if (sub) sub.classList.add('open');
    });
});
    
    
</script>
</body>
</html>