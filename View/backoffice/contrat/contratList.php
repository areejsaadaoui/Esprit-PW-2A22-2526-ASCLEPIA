<?php
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

$count        = count($contrats);
$montantMoyen = $count > 0 ? $totalMontant / $count : 0;
$statutCount  = array_count_values($statuts);
$statutPop    = $count > 0 ? array_search(max($statutCount), $statutCount) : 'N/A';

$search       = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
$filterStatut = isset($_GET['statut']) ? $_GET['statut'] : '';

$filtered = array_filter($contrats, function($c) use ($search, $filterStatut) {
    $matchSearch = $search === '' || strpos(strtolower($c['nom_assurance']), $search) !== false;
    $matchStatut = $filterStatut === '' || $c['statut'] === $filterStatut;
    return $matchSearch && $matchStatut;
});
$filtered = array_values($filtered);

$perPage     = 5;
$totalItems  = count($filtered);
$totalPages  = max(1, ceil($totalItems / $perPage));
$currentPage = isset($_GET['page']) ? max(1, min((int)$_GET['page'], $totalPages)) : 1;
$offset      = ($currentPage - 1) * $perPage;
$paginated   = array_slice($filtered, $offset, $perPage);
$queryParams = http_build_query(['search' => $search, 'statut' => $filterStatut]);
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
    </style>
</head>
<body>
<div class="admin-wrapper">

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
                <a href="../assurancelist.php">
                    <span class="nav-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    Assurances
                </a>
            </div>
            <div class="nav-item">
                <a href="#" class="active">
                    <span class="nav-icon"><i class="fa-solid fa-file-contract"></i></span>
                    Contrats
                </a>
            </div>
        </nav>
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

            <!-- FILTRES -->
            <div class="filters-bar">
                <form method="GET" action="" class="d-flex align-center gap-2" style="flex:1; flex-wrap:wrap;">
                    <div class="search-bar">
                        <span class="icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" placeholder="Rechercher par assurance..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <select name="statut" class="form-control" style="width:auto;">
                        <option value="">Tous les statuts</option>
                        <?php foreach (['Actif', 'Expiré', 'Annulé'] as $s): ?>
                            <option value="<?= $s ?>" <?= $filterStatut === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-filter"></i> Filtrer
                    </button>
                    <?php if ($search || $filterStatut): ?>
                    <a href="contratList.php" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-xmark"></i> Réinitialiser
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="results-info"><?= $totalItems ?> résultat(s) trouvé(s)</div>

            <!-- TABLE -->
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Assurance</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Montant (DT)</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
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
                        <tr>
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
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?<?= $queryParams ?>&page=<?= $currentPage - 1 ?>"><i class="fa-solid fa-chevron-left"></i></a>
                <?php else: ?>
                    <span class="disabled"><i class="fa-solid fa-chevron-left"></i></span>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $currentPage): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= $queryParams ?>&page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?<?= $queryParams ?>&page=<?= $currentPage + 1 ?>"><i class="fa-solid fa-chevron-right"></i></a>
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
<script>
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });
</script>
</body>
</html>