<?php
include '../../Controller/AssuranceController.php';
include '../../Controller/ContratController.php';
$assuranceC = new AssuranceController();
$contratC   = new ContratController();
$list       = $assuranceC->listAssurances();
$contracts  = $contratC->listContrats();

// Calcul des stats
$assurances = [];
$totalPrix = 0;
$totalTaux = 0;
$types = [];

foreach ($list as $a) {
    $assurances[] = $a;
    $totalPrix += $a['prix'];
    $totalTaux += $a['taux_remboursement'];
    $types[] = $a['TYPE'];
}

if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="assurances_' . date('Ymd_His') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<html><head><meta charset='UTF-8'></head><body>";
    echo "<table border='0' cellpadding='0' cellspacing='0' style='font-family:Calibri,Arial,sans-serif; width:100%; border-collapse:collapse;'>";
    echo "<tr><td colspan='6' style='background:#0ea5e9;color:#fff;font-size:18px;font-weight:bold;padding:12px 14px;'>ASCLEPIA - Export Assurances</td></tr>";
    echo "<tr><td colspan='6' style='padding:8px 14px;color:#475569;font-size:12px;'>Date export: " . date('d/m/Y H:i') . " | Total: " . count($assurances) . " assurance(s)</td></tr>";
    echo "<tr style='background:#e0f2fe;color:#0f172a;font-weight:bold;'>
            <td style='border:1px solid #cbd5e1;padding:8px;'>ID Assurance</td>
            <td style='border:1px solid #cbd5e1;padding:8px;'>Nom</td>
            <td style='border:1px solid #cbd5e1;padding:8px;'>Type</td>
            <td style='border:1px solid #cbd5e1;padding:8px;'>Prix (DT)</td>
            <td style='border:1px solid #cbd5e1;padding:8px;'>Duree (mois)</td>
            <td style='border:1px solid #cbd5e1;padding:8px;'>Taux remboursement (%)</td>
          </tr>";

    $i = 0;
    foreach ($assurances as $a) {
        $bg = $i % 2 === 0 ? '#ffffff' : '#f8fafc';
        echo "<tr style='background:{$bg};'>
                <td style='border:1px solid #e2e8f0;padding:7px;'>" . (int)$a['id_assurance'] . "</td>
                <td style='border:1px solid #e2e8f0;padding:7px;'>" . htmlspecialchars($a['nom_assurance']) . "</td>
                <td style='border:1px solid #e2e8f0;padding:7px;'>" . htmlspecialchars($a['TYPE']) . "</td>
                <td style='border:1px solid #e2e8f0;padding:7px; mso-number-format:\"0.00\";'>" . number_format((float)$a['prix'], 2, '.', '') . "</td>
                <td style='border:1px solid #e2e8f0;padding:7px;'>" . (int)$a['duree'] . "</td>
                <td style='border:1px solid #e2e8f0;padding:7px; mso-number-format:\"0\";'>" . (float)$a['taux_remboursement'] . "</td>
              </tr>";
        $i++;
    }
    echo "</table></body></html>";
    exit;
}

$count      = count($assurances);
$prixMoyen  = $count > 0 ? $totalPrix / $count : 0;
$tauxMoyen  = $count > 0 ? $totalTaux / $count : 0;

// Type le plus populaire
$typeCount  = array_count_values($types);
$typePop    = $count > 0 ? array_search(max($typeCount), $typeCount) : 'N/A';
$uniqueTypes = array_values(array_unique($types));
sort($uniqueTypes);

$montantsParMois = [];
foreach ($contracts as $c) {
    $monthKey = date('Y-m', strtotime($c['date_d']));
    if (!isset($montantsParMois[$monthKey])) {
        $montantsParMois[$monthKey] = 0;
    }
    $montantsParMois[$monthKey] += (float)$c['montant'];
}
ksort($montantsParMois);

$moisLabels = [];
$moisValues = [];
foreach ($montantsParMois as $monthKey => $value) {
    $dt = DateTime::createFromFormat('Y-m', $monthKey);
    $moisLabels[] = $dt ? $dt->format('M Y') : $monthKey;
    $moisValues[] = round($value, 2);
}

$perPage     = 5;
$totalItems  = count($assurances);
$totalPages  = max(1, ceil($totalItems / $perPage));
$currentPage = isset($_GET['page']) ? max(1, min((int)$_GET['page'], $totalPages)) : 1;
$offset      = ($currentPage - 1) * $perPage;
$paginated   = array_slice($assurances, $offset, $perPage);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assurances - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
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

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <a href="#" class="sidebar-brand">
            <div class="sidebar-logo">🏥</div>
            <div class="sidebar-title">ASCL<span>EPIA</span></div>
        </a>
        <div class="sidebar-user">
            <div class="user-avatar">A</div>
            <div class="user-info">
                <div class="name">Administrateur</div>
                <div class="role">Super Admin</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Gestion</div>
            <div class="nav-item">
                <a href="#" class="active">
                    <span class="nav-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    Assurances
                </a>
            </div>
            <div class="nav-item">
                <a href="contrat/contratList.php">
                    <span class="nav-icon"><i class="fa-solid fa-file-contract"></i></span>
                    Contrats
                </a>
            </div>
        </nav>
    </aside>

    <!-- MAIN -->
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <div class="page-title">Assurances</div>
                    <div class="breadcrumb">
                        <a href="#">Dashboard</a>
                        <span>/</span>
                        <span>Assurances</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="?export=excel" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </a>
                <a href="addAssurance.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Nouvelle assurance
                </a>
            </div>
        </div>

        <!-- PAGE CONTENT -->
        <div class="page-content">

            <!-- STAT CARDS -->
            <div class="row mb-4">
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-card-icon blue">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= $count ?></div>
                            <div class="stat-card-label">Total Assurances</div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-card-icon green">
                            <i class="fa-solid fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= number_format($prixMoyen, 0) ?> DT</div>
                            <div class="stat-card-label">Prix Moyen / mois</div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-card-icon purple">
                            <i class="fa-solid fa-trophy"></i>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= $typePop ?></div>
                            <div class="stat-card-label">Type le plus populaire</div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-card-icon cyan">
                            <i class="fa-solid fa-percent"></i>
                        </div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= number_format($tauxMoyen, 1) ?>%</div>
                            <div class="stat-card-label">Taux Remboursement Moyen</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="chart-card">
                    <h3>Répartition des assurances par type</h3>
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
                        <input type="text" id="searchInput" placeholder="Rechercher par nom...">
                    </div>
                    <select id="typeFilter" class="form-control" style="width:auto;">
                        <option value="">Tous les types</option>
                        <?php foreach ($uniqueTypes as $t): ?>
                            <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
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
                            <th class="sortable" data-sort-key="nom">Nom</th>
                            <th class="sortable" data-sort-key="type">Type</th>
                            <th class="sortable" data-sort-key="prix">Prix (DT)</th>
                            <th class="sortable" data-sort-key="duree">Durée</th>
                            <th class="sortable" data-sort-key="taux">Remboursement</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="assurancesTableBody">
                        <?php if (count($paginated) === 0): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="icon">🛡️</div>
                                    <h3>Aucune assurance</h3>
                                    <p>Commencez par ajouter une assurance.</p>
                                    <a href="addAssurance.php" class="btn btn-primary btn-sm">+ Ajouter</a>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($paginated as $a): ?>
                        <tr class="assurance-row"
                            data-id="<?= (int)$a['id_assurance'] ?>"
                            data-nom="<?= strtolower(htmlspecialchars($a['nom_assurance'])) ?>"
                            data-type="<?= htmlspecialchars($a['TYPE']) ?>"
                            data-prix="<?= (float)$a['prix'] ?>"
                            data-duree="<?= (int)$a['duree'] ?>"
                            data-taux="<?= (float)$a['taux_remboursement'] ?>">
                            <td><?= $a['id_assurance'] ?></td>
                            <td><strong><?= htmlspecialchars($a['nom_assurance']) ?></strong></td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($a['TYPE']) ?></span></td>
                            <td><?= number_format($a['prix'], 2) ?> DT</td>
                            <td><?= $a['duree'] ?> mois</td>
                            <td><?= $a['taux_remboursement'] ?>%</td>
                            <td>
                                <div class="actions">
                                    <a href="contrat/contratsParAssurance.php?id=<?= $a['id_assurance'] ?>"
                                       class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-file-contract"></i> Contrats
                                    </a>
                                    <form method="POST" action="updateAssurance.php" style="display:inline">
                                        <input type="hidden" name="id" value="<?= $a['id_assurance'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm">
                                            <i class="fa-solid fa-pen"></i> Modifier
                                        </button>
                                    </form>
                                    <a href="deleteAssurance.php?id=<?= $a['id_assurance'] ?>"
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
                                    <p>Aucune assurance ne correspond aux filtres actuels.</p>
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
    var typeFilter   = document.getElementById('typeFilter');
    var resetFilters = document.getElementById('resetFilters');
    var resultsInfo  = document.getElementById('resultsInfo');
    var noResultsRow = document.getElementById('noResultsRow');
    var rows         = document.querySelectorAll('.assurance-row');

    function filtrerAssurances() {
        if (!searchInput || !typeFilter) return;

        var search = searchInput.value.toLowerCase().trim();
        var type = typeFilter.value;
        var visible = 0;

        rows.forEach(function(row) {
            var nom = row.dataset.nom;
            var rowType = row.dataset.type;
            var matchSearch = search === '' || nom.indexOf(search) !== -1;
            var matchType = type === '' || rowType === type;

            if (matchSearch && matchType) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        if (resultsInfo) {
            resultsInfo.textContent = visible + ' résultat(s) trouvé(s)';
        }
        if (noResultsRow) {
            noResultsRow.style.display = visible === 0 ? '' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filtrerAssurances);
    }
    if (typeFilter) {
        typeFilter.addEventListener('change', filtrerAssurances);
    }
    if (resetFilters) {
        resetFilters.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (typeFilter) typeFilter.value = '';
            filtrerAssurances();
        });
    }

    var sortableHeaders = document.querySelectorAll('th.sortable');
    var currentSortKey = '';
    var currentSortDir = 'asc';

    function trierAssurances(key, dir) {
        var tbody = document.getElementById('assurancesTableBody');
        if (!tbody) return;
        var noResults = document.getElementById('noResultsRow');
        var rowsArr = Array.from(tbody.querySelectorAll('.assurance-row'));

        rowsArr.sort(function(a, b) {
            var av = a.dataset[key] || '';
            var bv = b.dataset[key] || '';
            var aNum = parseFloat(av);
            var bNum = parseFloat(bv);
            var bothNumeric = !isNaN(aNum) && !isNaN(bNum);
            var cmp = bothNumeric ? (aNum - bNum) : av.localeCompare(bv, 'fr', { sensitivity: 'base' });
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
            trierAssurances(currentSortKey, currentSortDir);
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
</script>
</body>
</html>