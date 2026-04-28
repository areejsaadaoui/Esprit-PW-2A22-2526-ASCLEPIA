<?php
include '../../Controller/AssuranceController.php';
$assuranceC = new AssuranceController();
$list       = $assuranceC->listAssurances();

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

$count      = count($assurances);
$prixMoyen  = $count > 0 ? $totalPrix / $count : 0;
$tauxMoyen  = $count > 0 ? $totalTaux / $count : 0;

// Type le plus populaire
$typeCount  = array_count_values($types);
$typePop    = $count > 0 ? array_search(max($typeCount), $typeCount) : 'N/A';
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

            <!-- TABLE -->
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Prix (DT)</th>
                            <th>Durée</th>
                            <th>Remboursement</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($count === 0): ?>
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
                        <?php foreach ($assurances as $a): ?>
                        <tr>
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
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

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