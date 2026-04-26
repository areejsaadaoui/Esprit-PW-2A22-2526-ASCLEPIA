<?php
require_once '../../config/db.php';
require_once '../../controllers/ConsultationController.php';

$controller = new ConsultationController($pdo);
$consultations = $controller->getAllConsultations();

// Statistiques
$total = count($consultations);
$planifiees = count(array_filter($consultations, fn($c) => $c->getStatut() === 'planifiée'));
$terminees = count(array_filter($consultations, fn($c) => $c->getStatut() === 'terminée'));
$annulees = count(array_filter($consultations, fn($c) => $c->getStatut() === 'annulée'));

// Maladies les plus fréquentes (mots du diagnostique)
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
$topMots = array_slice($mots, 0, 5, true);

// Consultations par jour
$parJour = [];
foreach ($consultations as $c) {
    $jour = date('d/m', strtotime($c->getDateConsultation()));
    $parJour[$jour] = ($parJour[$jour] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultations - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
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
            <div class="nav-section-label">Consultation</div>
            <div class="nav-item">
                <a href="list_consultation.php" class="active">
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
                    <div class="page-title">Consultations</div>
                    <div class="breadcrumb">
                        <a href="#">Dashboard</a>
                        <span>/</span>
                        <span>Consultations</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="add_consultation.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Nouvelle consultation
                </a>
            </div>
        </div>

        <div class="page-content">

            <!-- STATISTIQUES -->
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
                        <div class="stat-card-icon red"><i class="fa-solid fa-circle-xmark"></i></div>
                        <div class="stat-card-body">
                            <div class="stat-card-value"><?= $annulees ?></div>
                            <div class="stat-card-label">Annulées</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- GRAPHIQUES -->
            <div class="row mb-3">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><i class="fa-solid fa-chart-pie"></i> Répartition par statut</div>
                        </div>
                        <canvas id="chartStatut" height="200"></canvas>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><i class="fa-solid fa-chart-bar"></i> Consultations par jour</div>
                        </div>
                        <canvas id="chartJour" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- MOTS FREQUENTS -->
            <?php if (!empty($topMots)): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <div class="card-title"><i class="fa-solid fa-virus"></i> Mots les plus fréquents dans les diagnostiques</div>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap; padding:16px;">
                    <?php foreach ($topMots as $mot => $nb): ?>
                    <span class="badge badge-primary" style="font-size:0.9rem; padding:8px 16px;">
                        <?= htmlspecialchars($mot) ?> <strong>(<?= $nb ?>)</strong>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- RECHERCHE ET FILTRES -->
            <div class="card mb-3">
                <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; padding:16px;">

                    <div style="position:relative; flex:1; min-width:200px;">
                        <i class="fa-solid fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--gray-light);"></i>
                        <input type="text" id="recherche" class="form-control"
                            placeholder="Rechercher par diagnostique, notes..."
                            style="padding-left:36px;"
                            oninput="filtrer()">
                    </div>

                    <select id="filtreStatut" class="form-control" style="width:160px;" onchange="filtrer()">
                        <option value="">Tous les statuts</option>
                        <option value="planifiée">Planifiée</option>
                        <option value="terminée">Terminée</option>
                        <option value="annulée">Annulée</option>
                    </select>

                    <button onclick="trierDate('asc')" class="btn btn-outline btn-sm" id="btn-asc">
                        <i class="fa-solid fa-arrow-up"></i> Date ↑
                    </button>
                    <button onclick="trierDate('desc')" class="btn btn-outline btn-sm" id="btn-desc">
                        <i class="fa-solid fa-arrow-down"></i> Date ↓
                    </button>

                    <button onclick="resetFiltres()" class="btn btn-outline btn-sm">
                        <i class="fa-solid fa-rotate-left"></i> Réinitialiser
                    </button>

                </div>
            </div>

            <!-- TABLEAU -->
            <div class="table-wrapper">
                <table class="table" id="tableConsultations">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Diagnostique</th>
                            <th>Notes</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tbody">
                        <?php if (empty($consultations)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="icon">📋</div>
                                    <h3>Aucune consultation</h3>
                                    <p>Commencez par ajouter une consultation.</p>
                                    <a href="add_consultation.php" class="btn btn-primary btn-sm">+ Ajouter</a>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($consultations as $c): ?>
                        <tr class="consultation-row"
                            data-date="<?= $c->getDateConsultation() ?>"
                            data-statut="<?= $c->getStatut() ?>"
                            data-search="<?= strtolower(htmlspecialchars($c->getDiagnostique() . ' ' . $c->getNotes())) ?>">
                            <td><?= $c->getIdConsultation() ?></td>
                            <td><?= $c->getDateConsultation() ?></td>
                            <td><?= htmlspecialchars(substr($c->getDiagnostique(), 0, 50)) ?>...</td>
                            <td><?= htmlspecialchars(substr($c->getNotes(), 0, 40)) ?>...</td>
                            <td>
                                <?php
                                $statut = $c->getStatut() ?: 'planifiée';
                                $badgeClass = match($statut) {
                                    'planifiée' => 'badge-primary',
                                    'terminée'  => 'badge-success',
                                    'annulée'   => 'badge-danger',
                                    default     => 'badge-gray'
                                };
                                $icone = match($statut) {
                                    'planifiée' => 'fa-clock',
                                    'terminée'  => 'fa-circle-check',
                                    'annulée'   => 'fa-circle-xmark',
                                    default     => 'fa-circle'
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <i class="fa-solid <?= $icone ?>"></i>
                                    <?= ucfirst($statut) ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="edit_consultation.php?id=<?= $c->getIdConsultation() ?>" class="btn btn-outline btn-sm">
                                        <i class="fa-solid fa-pen"></i> Modifier
                                    </a>
                                    <a href="delete_consultation.php?id=<?= $c->getIdConsultation() ?>" class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div id="aucunResultat" style="display:none; text-align:center; padding:40px; color:var(--text-muted);">
                    <i class="fa-solid fa-magnifying-glass" style="font-size:2rem; margin-bottom:12px;"></i>
                    <p>Aucune consultation trouvée pour cette recherche.</p>
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
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // GRAPHIQUE PAR JOUR
    new Chart(document.getElementById('chartJour'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($parJour)) ?>,
            datasets: [{
                label: 'Consultations',
                data: <?= json_encode(array_values($parJour)) ?>,
                backgroundColor: '#0ea5e9',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // FILTRAGE ET RECHERCHE
    function filtrer() {
        const recherche = document.getElementById('recherche').value.toLowerCase();
        const statut = document.getElementById('filtreStatut').value.toLowerCase();
        const rows = document.querySelectorAll('.consultation-row');
        let visible = 0;

        rows.forEach(row => {
            const search = row.dataset.search;
            const rowStatut = row.dataset.statut;
            const matchRecherche = recherche === '' || search.includes(recherche);
            const matchStatut = statut === '' || rowStatut === statut;

            if (matchRecherche && matchStatut) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('aucunResultat').style.display = visible === 0 ? 'block' : 'none';
    }

    // TRI PAR DATE
    let triActuel = 'desc';
    function trierDate(ordre) {
        triActuel = ordre;
        const tbody = document.getElementById('tbody');
        const rows = Array.from(document.querySelectorAll('.consultation-row'));

        rows.sort((a, b) => {
            const dateA = new Date(a.dataset.date);
            const dateB = new Date(b.dataset.date);
            return ordre === 'asc' ? dateA - dateB : dateB - dateA;
        });

        rows.forEach(row => tbody.appendChild(row));

        document.getElementById('btn-asc').classList.toggle('btn-primary', ordre === 'asc');
        document.getElementById('btn-asc').classList.toggle('btn-outline', ordre !== 'asc');
        document.getElementById('btn-desc').classList.toggle('btn-primary', ordre === 'desc');
        document.getElementById('btn-desc').classList.toggle('btn-outline', ordre !== 'desc');
    }

    // RESET
    function resetFiltres() {
        document.getElementById('recherche').value = '';
        document.getElementById('filtreStatut').value = '';
        filtrer();
        trierDate('desc');
    }
</script>
</body>
</html>