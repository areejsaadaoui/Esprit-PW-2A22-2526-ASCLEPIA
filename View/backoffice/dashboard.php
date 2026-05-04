<?php
require_once '../../config/db.php';
require_once '../../controllers/ConsultationController.php';
require_once '../../controllers/OrdonnanceController.php';

$controller = new ConsultationController($pdo);
$ordonnanceController = new OrdonnanceController($pdo);

$consultations = $controller->getAllConsultations();
$ordonnances = $ordonnanceController->getAllOrdonnances();

// Stats consultations
$total = count($consultations);
$planifiees = count(array_filter($consultations, fn($c) => $c->getStatut() === 'planifiée'));
$terminees = count(array_filter($consultations, fn($c) => $c->getStatut() === 'terminée'));
$annulees = count(array_filter($consultations, fn($c) => $c->getStatut() === 'annulée'));

// Stats ordonnances
$totalOrd = count($ordonnances);

// Consultations par mois
$parMois = [];
foreach ($consultations as $c) {
    $mois = date('M Y', strtotime($c->getDateConsultation()));
    $parMois[$mois] = ($parMois[$mois] ?? 0) + 1;
}

// Prochaines consultations (planifiées futures)
$prochaines = array_filter($consultations, fn($c) =>
    $c->getStatut() === 'planifiée' &&
    strtotime($c->getDateConsultation()) > time()
);
usort($prochaines, fn($a, $b) =>
    strtotime($a->getDateConsultation()) - strtotime($b->getDateConsultation())
);
$prochaines = array_slice($prochaines, 0, 5);

// Dernières consultations terminées
$dernieres = array_filter($consultations, fn($c) => $c->getStatut() === 'terminée');
usort($dernieres, fn($a, $b) =>
    strtotime($b->getDateConsultation()) - strtotime($a->getDateConsultation())
);
$dernieres = array_slice($dernieres, 0, 5);

// Mots fréquents dans diagnostiques
$mots = [];
foreach ($consultations as $c) {
    $diag = strtolower($c->getDiagnostique());
    $words = preg_split('/\s+/', $diag);
    foreach ($words as $w) {
        $w = trim($w, '.,;:!?');
        if (strlen($w) > 4) {
            $mots[$w] = ($mots[$w] ?? 0) + 1;
        }
    }
}
arsort($mots);
$topMots = array_slice($mots, 0, 8, true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/dark.css">
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
                <div class="name">Ala</div>
                <div class="role">Médecin</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Navigation</div>
            <div class="nav-item">
                <a href="dashboard.php" class="active">
                    <span class="nav-icon"><i class="fa-solid fa-gauge"></i></span>
                    Dashboard
                </a>
            </div>
            <div class="nav-section-label">Consultation</div>
            <div class="nav-item">
                <a href="list_consultation.php">
                    <span class="nav-icon"><i class="fa-solid fa-calendar-check"></i></span>
                    Consultations
                </a>
            </div>
            <div class="nav-item">
                <a href="add_consultation.php">
                    <span class="nav-icon"><i class="fa-solid fa-plus"></i></span>
                    Ajouter
                </a>
            </div>
            <div class="nav-section-label">Ordonnance</div>
            <div class="nav-item">
                <a href="list_ordonnance.php">
                    <span class="nav-icon"><i class="fa-solid fa-file-prescription"></i></span>
                    Ordonnances
                </a>
            </div>
            <div class="nav-item">
                <a href="add_ordonnance.php">
                    <span class="nav-icon"><i class="fa-solid fa-plus"></i></span>
                    Ajouter
                </a>
            </div>
        </nav>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <div class="page-title">Dashboard</div>
                    <div class="breadcrumb">
                        <span>Bienvenue Dr. Ala —</span>
                        <span><?= date('l d F Y') ?></span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="add_consultation.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Nouvelle consultation
                </a>
                    <button class="dark-toggle" onclick="toggleDark()" id="darkBtn" title="Mode sombre">
        <i class="fa-solid fa-moon"></i>
    </button>
            </div>
        </div>

        <div class="page-content">

            <!-- STATS CARDS -->
            <div class="row mb-3">
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-card-icon blue"><i class="fa-solid fa-calendar-check"></i></div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= $total ?></div>
                            <div class="stat-card-label">Total consultations</div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-card-icon cyan"><i class="fa-solid fa-clock"></i></div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= $planifiees ?></div>
                            <div class="stat-card-label">Planifiées</div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-card-icon green"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= $terminees ?></div>
                            <div class="stat-card-label">Terminées</div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-card-icon purple"><i class="fa-solid fa-file-prescription"></i></div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= $totalOrd ?></div>
                            <div class="stat-card-label">Ordonnances</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRAPHIQUES -->
            <div class="row mb-3">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><i class="fa-solid fa-chart-pie"></i> Répartition des consultations</div>
                        </div>
                        <canvas id="chartStatut" height="220"></canvas>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><i class="fa-solid fa-chart-line"></i> Consultations par mois</div>
                        </div>
                        <canvas id="chartMois" height="220"></canvas>
                    </div>
                </div>
            </div>

            <!-- MOTS FREQUENTS -->
            <?php if (!empty($topMots)): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <div class="card-title"><i class="fa-solid fa-virus"></i> Mots fréquents dans les diagnostiques</div>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap; padding:16px;">
                    <?php foreach ($topMots as $mot => $nb): ?>
                    <?php
                    $size = min(1.2, 0.8 + ($nb * 0.1));
                    ?>
                    <span class="badge badge-primary" style="font-size:<?= $size ?>rem; padding:8px 16px;">
                        <?= htmlspecialchars($mot) ?> <strong>(<?= $nb ?>)</strong>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- PROCHAINES ET DERNIERES CONSULTATIONS -->
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><i class="fa-solid fa-clock"></i> Prochaines consultations</div>
                            <a href="list_consultation.php" class="btn btn-outline btn-sm">Voir tout</a>
                        </div>
                        <?php if (empty($prochaines)): ?>
                        <div style="padding:20px; text-align:center; color:var(--text-muted);">
                            Aucune consultation planifiée
                        </div>
                        <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prochaines as $c): ?>
                                <tr>
                                    <td><?= $c->getIdConsultation() ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($c->getDateConsultation())) ?></td>
                                    <td>
                                        <a href="edit_consultation.php?id=<?= $c->getIdConsultation() ?>" class="btn btn-outline btn-sm">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><i class="fa-solid fa-circle-check"></i> Dernières consultations terminées</div>
                            <a href="list_consultation.php" class="btn btn-outline btn-sm">Voir tout</a>
                        </div>
                        <?php if (empty($dernieres)): ?>
                        <div style="padding:20px; text-align:center; color:var(--text-muted);">
                            Aucune consultation terminée
                        </div>
                        <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Ordonnance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dernieres as $c): ?>
                                <?php $ord = $ordonnanceController->getOrdonnanceByConsultation($c->getIdConsultation()); ?>
                                <tr>
                                    <td><?= $c->getIdConsultation() ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($c->getDateConsultation())) ?></td>
                                    <td>
                                        <?php if ($ord): ?>
                                        <a href="ordonnance_pdf.php?id=<?= $ord['id_ordonnance'] ?>" class="btn btn-primary btn-sm" target="_blank">
                                            <i class="fa-solid fa-file-pdf"></i> PDF
                                        </a>
                                        <?php else: ?>
                                        <a href="add_ordonnance.php" class="btn btn-outline btn-sm">
                                            <i class="fa-solid fa-plus"></i> Ordonnance
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });

    // GRAPHIQUE STATUT
    new Chart(document.getElementById('chartStatut'), {
        type: 'doughnut',
        data: {
            labels: ['Planifiées', 'Terminées', 'Annulées'],
            datasets: [{
                data: [<?= $planifiees ?>, <?= $terminees ?>, <?= $annulees ?>],
                backgroundColor: ['#0ea5e9', '#10b981', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // GRAPHIQUE PAR MOIS
    new Chart(document.getElementById('chartMois'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($parMois)) ?>,
            datasets: [{
                label: 'Consultations',
                data: <?= json_encode(array_values($parMois)) ?>,
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14,165,233,0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
    // MODE SOMBRE
function toggleDark() {
    document.body.classList.toggle('dark-mode');
    const btn = document.getElementById('darkBtn');
    const isDark = document.body.classList.contains('dark-mode');
    btn.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
    localStorage.setItem('darkMode', isDark);
}

if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
    document.getElementById('darkBtn').innerHTML = '<i class="fa-solid fa-sun"></i>';
}
</script>
</body>
</html>