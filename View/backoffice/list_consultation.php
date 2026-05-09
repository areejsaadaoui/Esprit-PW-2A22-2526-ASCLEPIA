<?php
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

require_once '../../config.php';
require_once '../../Controller/ConsultationController.php';
require_once '../../Controller/OrdonnanceController.php';

$controller = new ConsultationController(config::getConnexion());
$ordonnanceController = new OrdonnanceController(config::getConnexion());
$consultations = $controller->getAllConsultations();

// Statistiques
$total = count($consultations);
$planifiees = count(array_filter($consultations, fn($c) => $c->getStatut() === 'planifiée'));
$terminees = count(array_filter($consultations, fn($c) => $c->getStatut() === 'terminée'));
$annulees = count(array_filter($consultations, fn($c) => $c->getStatut() === 'annulée'));

// Maladies les plus fréquentes
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

// PAGINATION
$parPage = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$totalPages = ceil($total / $parPage);
$page = max(1, min($page, $totalPages ?: 1));
$debut = ($page - 1) * $parPage;
$consultationsPage = array_slice($consultations, $debut, $parPage);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultations - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="../../assets/css/dark.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <style>
    @media print {
        .sidebar, .topbar, .card.mb-3,
        .actions, .btn, #chartStatut,
        #chartJour, .stat-card, .pagination-wrapper { display: none !important; }
        .main-content { margin-left: 0 !important; }
        .table-wrapper { box-shadow: none !important; }
        body::before {
            content: "ASCLEPIA - Liste des consultations";
            display: block;
            text-align: center;
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
    }
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
            <div class="user-avatar" id="adminAvatar">
                <?php echo strtoupper(substr($adminNom ?? 'A', 0, 2)); ?>
            </div>
            <div class="user-info">
                <div class="name" id="adminName">
                    <?php echo htmlspecialchars($adminNom ?? 'Administrateur'); ?>
                </div>
                <div class="role">Super Admin</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Navigation</div>
            <div class="nav-item">
                <a href="dashboard.php">
                    <span class="nav-icon"><i class="fa-solid fa-gauge"></i></span>
                    Dashboard
                </a>
            </div>
            <div class="nav-item">
                <a href="calendrier.php">
                    <span class="nav-icon"><i class="fa-solid fa-calendar"></i></span>
                    Calendrier
                </a>
            </div>
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
                        <a href="dashboard.php">Dashboard</a>
                        <span>/</span>
                        <span>Consultations</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <button class="dark-toggle" onclick="toggleDark()" id="darkBtn" title="Mode sombre">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <a href="export_excel.php" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </a>
                <button onclick="window.print()" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-print"></i> Imprimer
                </button>
                <a href="add_consultation.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Nouvelle consultation
                </a>
            </div>
            <div style="position:relative;">
    <button class="topbar-btn" onclick="toggleNotifs()" id="notifBtn" title="Notifications">
        <i class="fa-solid fa-bell"></i>
        <span class="topbar-notif" id="notifCount" style="display:none;">0</span>
    </button>
    <div id="notifPanel" style="
        display:none;
        position:absolute;
        right:0; top:48px;
        width:320px;
        background:var(--white);
        border:1px solid var(--border);
        border-radius:var(--radius-lg);
        box-shadow:var(--shadow-lg);
        z-index:500;
        overflow:hidden;">
        <div style="padding:14px 16px; border-bottom:1px solid var(--border); font-weight:700; font-size:0.9rem;">
            <i class="fa-solid fa-bell" style="color:var(--primary);"></i> Notifications
        </div>
        <div id="notifList" style="max-height:300px; overflow-y:auto;"></div>
    </div>
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
                    <select id="filtreOrdonnance" class="form-control" style="width:180px;" onchange="filtrer()">
                        <option value="">Toutes les ordonnances</option>
                        <option value="oui">Avec ordonnance</option>
                        <option value="non">Sans ordonnance</option>
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
                            <th>Ordonnance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tbody">
                        <?php if (empty($consultationsPage)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="icon">📋</div>
                                    <h3>Aucune consultation</h3>
                                    <p>Commencez par ajouter une consultation.</p>
                                    <a href="add_consultation.php" class="btn btn-primary btn-sm">+ Ajouter</a>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($consultationsPage as $c): ?>
                        <?php $ord = $ordonnanceController->getOrdonnanceByConsultation($c->getIdConsultation()); ?>
                        <tr class="consultation-row"
                            data-date="<?= $c->getDateConsultation() ?>"
                            data-statut="<?= $c->getStatut() ?>"
                            data-ordonnance="<?= $ord ? 'oui' : 'non' ?>"
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
                                <?php if ($ord): ?>
                                    <a href="ordonnance_pdf.php?id=<?= $ord['id_ordonnance'] ?>" class="btn btn-primary btn-sm" target="_blank">
                                        <i class="fa-solid fa-file-pdf"></i> PDF
                                    </a>
                                <?php elseif ($statut === 'terminée'): ?>
                                    <a href="add_ordonnance.php" class="btn btn-outline btn-sm">
                                        <i class="fa-solid fa-plus"></i> Créer
                                    </a>
                                <?php else: ?>
                                    <span class="badge badge-gray">
                                        <i class="fa-solid fa-minus"></i> N/A
                                    </span>
                                <?php endif; ?>
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

            <!-- PAGINATION -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper" style="display:flex; justify-content:center; align-items:center; gap:8px; margin-top:20px; flex-wrap:wrap;">
                <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Précédent
                </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-outline' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-outline btn-sm">
                    Suivant <i class="fa-solid fa-arrow-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <div style="text-align:center; margin-top:8px; font-size:0.85rem; color:var(--text-muted);">
                Page <?= $page ?> sur <?= $totalPages ?> — <?= $total ?> consultations au total
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });

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

    function filtrer() {
        const recherche = document.getElementById('recherche').value.toLowerCase();
        const statut = document.getElementById('filtreStatut').value.toLowerCase();
        const ordonnance = document.getElementById('filtreOrdonnance').value.toLowerCase();
        const rows = document.querySelectorAll('.consultation-row');
        let visible = 0;

        rows.forEach(row => {
            const search = row.dataset.search;
            const rowStatut = row.dataset.statut;
            const rowOrd = row.dataset.ordonnance;
            const matchRecherche = recherche === '' || search.includes(recherche);
            const matchStatut = statut === '' || rowStatut === statut;
            const matchOrd = ordonnance === '' || rowOrd === ordonnance;

            if (matchRecherche && matchStatut && matchOrd) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('aucunResultat').style.display = visible === 0 ? 'block' : 'none';
    }

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

    function resetFiltres() {
        document.getElementById('recherche').value = '';
        document.getElementById('filtreStatut').value = '';
        document.getElementById('filtreOrdonnance').value = '';
        filtrer();
        trierDate('desc');
    }
    // NOTIFICATIONS
function chargerNotifications() {
    fetch('notifications.php')
        .then(r => r.json())
        .then(data => {
            const count = data.count;
            const countEl = document.getElementById('notifCount');
            const listEl = document.getElementById('notifList');

            if (count > 0) {
                countEl.style.display = 'flex';
                countEl.textContent = count;
            } else {
                countEl.style.display = 'none';
            }

            if (data.notifications.length === 0) {
                listEl.innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-muted); font-size:0.85rem;">Aucune notification</div>';
            } else {
                listEl.innerHTML = data.notifications.map(n => `
                    <a href="${n.link}" style="display:flex; gap:12px; padding:12px 16px; border-bottom:1px solid var(--border); text-decoration:none; color:var(--text);" 
                       onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
                        <div style="width:36px; height:36px; border-radius:50%; background:${n.type === 'warning' ? '#fef3c7' : '#fee2e2'}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="fa-solid ${n.icon}" style="color:${n.type === 'warning' ? '#f59e0b' : '#ef4444'};"></i>
                        </div>
                        <div>
                            <div style="font-size:0.85rem; font-weight:500;">${n.message}</div>
                            <div style="font-size:0.78rem; color:var(--text-muted);">${n.time}</div>
                        </div>
                    </a>
                `).join('');
            }
        })
        .catch(() => {});
}

function toggleNotifs() {
    const panel = document.getElementById('notifPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#notifBtn') && !e.target.closest('#notifPanel')) {
        const panel = document.getElementById('notifPanel');
        if (panel) panel.style.display = 'none';
    }
});

// Charger au démarrage et toutes les 30 secondes
chargerNotifications();
setInterval(chargerNotifications, 30000);
</script>
</body>
<script src="../../assets/js/language-switcher.js"></script>
</html>