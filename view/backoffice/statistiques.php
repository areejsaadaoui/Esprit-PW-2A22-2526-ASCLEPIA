<?php
require_once '../../config.php';

$db = config::getConnexion();

// 1. Statistiques globales
$totalMeds = $db->query("SELECT COUNT(*) as total FROM medicament")->fetch()['total'];
$totalPharms = $db->query("SELECT COUNT(*) as total FROM pharmacie")->fetch()['total'];
$valeurTotale = $db->query("SELECT COALESCE(SUM(prix * stock), 0) as valeur FROM medicament")->fetch()['valeur'];
$prixMoyen = $db->query("SELECT COALESCE(AVG(prix), 0) as moyenne FROM medicament")->fetch()['moyenne'];

// 2. Valeur du stock par pharmacie (pour le bar chart)
$stockParPharmacie = $db->query("
    SELECT p.nom, COALESCE(SUM(m.prix * m.stock), 0) as valeur_stock, COUNT(m.id_medicament) as nb_meds
    FROM pharmacie p
    LEFT JOIN medicament m ON p.id_pharmacie = m.id_pharmacie
    GROUP BY p.id_pharmacie, p.nom
    ORDER BY valeur_stock DESC
")->fetchAll();

// 3. Répartition par catégorie (pour le donut chart)
$parCategorie = $db->query("
    SELECT categorie, COUNT(*) as nb, SUM(prix * stock) as valeur
    FROM medicament
    GROUP BY categorie
    ORDER BY nb DESC
")->fetchAll();

// 4. Top 5 médicaments les plus chers
$topCher = $db->query("
    SELECT m.nom, m.prix, m.stock, p.nom as nom_pharmacie
    FROM medicament m
    JOIN pharmacie p ON m.id_pharmacie = p.id_pharmacie
    ORDER BY m.prix DESC
    LIMIT 5
")->fetchAll();

// 5. Top 5 médicaments par valeur de stock (prix * stock)
$topValeur = $db->query("
    SELECT m.nom, m.prix, m.stock, (m.prix * m.stock) as valeur, p.nom as nom_pharmacie
    FROM medicament m
    JOIN pharmacie p ON m.id_pharmacie = p.id_pharmacie
    ORDER BY valeur DESC
    LIMIT 5
")->fetchAll();

// Préparer les données JSON pour Chart.js
$pharmLabels = array_column($stockParPharmacie, 'nom');
$pharmValues = array_column($stockParPharmacie, 'valeur_stock');
$pharmMedCounts = array_column($stockParPharmacie, 'nb_meds');

$catLabels = array_column($parCategorie, 'categorie');
$catValues = array_column($parCategorie, 'nb');
$catValeurs = array_column($parCategorie, 'valeur');

include 'header_back.php';
?>

<main class="admin-container">
    <div class="container">

        <!-- Header -->
        <div class="section-header" style="margin-bottom: 40px;">
            <div class="section-tag"><i class="fa-solid fa-chart-pie"></i> Analytique</div>
            <h2 class="section-title">Statistiques Pharmacie & Médicaments</h2>
            <p class="section-desc">Vue d'ensemble de la valeur du stock et de la répartition des produits dans votre réseau.</p>
        </div>

        <!-- KPI Cards -->
        <div class="stat-kpi-grid">
            <div class="stat-kpi-card kpi-primary">
                <div class="kpi-icon"><i class="fa-solid fa-pills"></i></div>
                <div class="kpi-value"><?= $totalMeds ?></div>
                <div class="kpi-label">Médicaments</div>
            </div>
            <div class="stat-kpi-card kpi-green">
                <div class="kpi-icon"><i class="fa-solid fa-hospital"></i></div>
                <div class="kpi-value"><?= $totalPharms ?></div>
                <div class="kpi-label">Pharmacies</div>
            </div>
            <div class="stat-kpi-card kpi-orange">
                <div class="kpi-icon"><i class="fa-solid fa-coins"></i></div>
                <div class="kpi-value"><?= number_format($valeurTotale, 3) ?> <small>DT</small></div>
                <div class="kpi-label">Valeur Totale du Stock</div>
            </div>
            <div class="stat-kpi-card kpi-purple">
                <div class="kpi-icon"><i class="fa-solid fa-calculator"></i></div>
                <div class="kpi-value"><?= number_format($prixMoyen, 3) ?> <small>DT</small></div>
                <div class="kpi-label">Prix Moyen</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="stat-charts-row">
            <!-- Bar Chart: stock par pharmacie -->
            <div class="stat-chart-card stat-chart-wide">
                <div class="stat-chart-header">
                    <h3><i class="fa-solid fa-chart-bar"></i> Valeur du Stock par Pharmacie</h3>
                    <span class="stat-chart-badge">Prix × Quantité</span>
                </div>
                <div class="stat-chart-body">
                    <canvas id="chartPharmacie"></canvas>
                </div>
            </div>

            <!-- Donut Chart: catégories -->
            <div class="stat-chart-card stat-chart-narrow">
                <div class="stat-chart-header">
                    <h3><i class="fa-solid fa-chart-pie"></i> Répartition par Catégorie</h3>
                    <span class="stat-chart-badge">Nombre de produits</span>
                </div>
                <div class="stat-chart-body" style="display: flex; align-items: center; justify-content: center;">
                    <canvas id="chartCategorie"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="stat-charts-row">
            <!-- Top 5 les plus chers -->
            <div class="stat-chart-card" style="flex: 1;">
                <div class="stat-chart-header">
                    <h3><i class="fa-solid fa-arrow-up-wide-short"></i> Top 5 — Médicaments les Plus Chers</h3>
                </div>
                <div class="stat-table-wrap">
                    <table class="stat-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Médicament</th>
                                <th>Pharmacie</th>
                                <th>Prix</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topCher as $i => $m): ?>
                            <tr>
                                <td><span class="stat-rank"><?= $i + 1 ?></span></td>
                                <td class="fw-600"><?= htmlspecialchars($m['nom']) ?></td>
                                <td><?= htmlspecialchars($m['nom_pharmacie']) ?></td>
                                <td class="text-primary fw-700"><?= number_format($m['prix'], 3) ?> DT</td>
                                <td><?= $m['stock'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($topCher)): ?>
                            <tr><td colspan="5" style="text-align:center; color:var(--text-muted);">Aucun médicament enregistré.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top 5 par valeur -->
            <div class="stat-chart-card" style="flex: 1;">
                <div class="stat-chart-header">
                    <h3><i class="fa-solid fa-sack-dollar"></i> Top 5 — Plus Grande Valeur en Stock</h3>
                </div>
                <div class="stat-table-wrap">
                    <table class="stat-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Médicament</th>
                                <th>Pharmacie</th>
                                <th>Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topValeur as $i => $m): ?>
                            <tr>
                                <td><span class="stat-rank"><?= $i + 1 ?></span></td>
                                <td class="fw-600"><?= htmlspecialchars($m['nom']) ?></td>
                                <td><?= htmlspecialchars($m['nom_pharmacie']) ?></td>
                                <td class="text-primary fw-700"><?= number_format($m['valeur'], 3) ?> DT</td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($topValeur)): ?>
                            <tr><td colspan="4" style="text-align:center; color:var(--text-muted);">Aucun médicament enregistré.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Retour au Dashboard</a>
        </div>

    </div>
</main>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<script>
// Couleurs premium
const colors = [
    '#6366f1', '#10b981', '#f59e0b', '#ec4899', '#0ea5e9',
    '#8b5cf6', '#14b8a6', '#f97316', '#ef4444', '#06b6d4'
];
const colorsBg = colors.map(c => c + '22');

// ---- Bar Chart: Valeur du stock par Pharmacie ----
const ctxBar = document.getElementById('chartPharmacie').getContext('2d');
new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: <?= json_encode($pharmLabels) ?>,
        datasets: [{
            label: 'Valeur du stock (DT)',
            data: <?= json_encode(array_map('floatval', $pharmValues)) ?>,
            backgroundColor: colors.slice(0, <?= count($pharmLabels) ?>),
            borderColor: colors.slice(0, <?= count($pharmLabels) ?>),
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
            barPercentage: 0.6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1a1a2e',
                titleFont: { size: 13, weight: '600' },
                bodyFont: { size: 12 },
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                    label: function(ctx) {
                        const nbMeds = <?= json_encode(array_map('intval', $pharmMedCounts)) ?>;
                        return [
                            'Valeur: ' + ctx.parsed.y.toFixed(3) + ' DT',
                            'Médicaments: ' + nbMeds[ctx.dataIndex]
                        ];
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: {
                    callback: function(v) { return v.toFixed(0) + ' DT'; },
                    font: { size: 11 }
                }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 11, weight: '600' } }
            }
        }
    }
});

// ---- Donut Chart: Répartition par catégorie ----
const ctxDonut = document.getElementById('chartCategorie').getContext('2d');
new Chart(ctxDonut, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($catLabels) ?>,
        datasets: [{
            data: <?= json_encode(array_map('intval', $catValues)) ?>,
            backgroundColor: colors.slice(0, <?= count($catLabels) ?>),
            borderWidth: 3,
            borderColor: '#fff',
            hoverOffset: 12
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 16,
                    usePointStyle: true,
                    pointStyleWidth: 10,
                    font: { size: 12, weight: '500' }
                }
            },
            tooltip: {
                backgroundColor: '#1a1a2e',
                padding: 12,
                cornerRadius: 8,
                callbacks: {
                    label: function(ctx) {
                        const valeurs = <?= json_encode(array_map('floatval', $catValeurs)) ?>;
                        return [
                            ctx.label + ': ' + ctx.parsed + ' produit(s)',
                            'Valeur: ' + valeurs[ctx.dataIndex].toFixed(3) + ' DT'
                        ];
                    }
                }
            }
        }
    }
});
</script>

<style>
/* ===== KPI Cards ===== */
.stat-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 36px;
}
.stat-kpi-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 28px 24px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(0,0,0,0.04);
    transition: transform .25s ease, box-shadow .25s ease;
    position: relative;
    overflow: hidden;
}
.stat-kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
}
.kpi-primary::before { background: linear-gradient(90deg, #6366f1, #8b5cf6); }
.kpi-green::before   { background: linear-gradient(90deg, #10b981, #059669); }
.kpi-orange::before  { background: linear-gradient(90deg, #f59e0b, #d97706); }
.kpi-purple::before  { background: linear-gradient(90deg, #ec4899, #db2777); }

.stat-kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}
.kpi-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    color: white;
}
.kpi-primary .kpi-icon { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
.kpi-green   .kpi-icon { background: linear-gradient(135deg, #10b981, #059669); }
.kpi-orange  .kpi-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }
.kpi-purple  .kpi-icon { background: linear-gradient(135deg, #ec4899, #db2777); }

.kpi-value {
    font-size: 1.7rem;
    font-weight: 800;
    color: var(--text-main, #1a1a2e);
    line-height: 1.2;
}
.kpi-value small {
    font-size: 0.7em;
    font-weight: 600;
    opacity: 0.6;
}
.kpi-label {
    font-size: 0.85rem;
    color: var(--text-muted, #64748b);
    font-weight: 500;
}

/* ===== Chart Cards ===== */
.stat-charts-row {
    display: flex;
    gap: 24px;
    margin-bottom: 28px;
}
.stat-chart-card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(0,0,0,0.04);
    overflow: hidden;
}
.stat-chart-wide  { flex: 1.6; }
.stat-chart-narrow { flex: 1; }

.stat-chart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 0;
}
.stat-chart-header h3 {
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
    color: var(--text-main, #1a1a2e);
    display: flex;
    align-items: center;
    gap: 8px;
}
.stat-chart-header h3 i {
    color: var(--primary, #6366f1);
}
.stat-chart-badge {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    background: rgba(99,102,241,0.1);
    color: var(--primary, #6366f1);
}
.stat-chart-body {
    padding: 20px 24px 24px;
    height: 320px;
}

/* ===== Tables ===== */
.stat-table-wrap {
    padding: 0 24px 24px;
}
.stat-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}
.stat-table thead th {
    text-align: left;
    padding: 12px 10px;
    font-weight: 600;
    color: var(--text-muted, #64748b);
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 2px solid rgba(0,0,0,0.06);
}
.stat-table tbody td {
    padding: 12px 10px;
    border-bottom: 1px solid rgba(0,0,0,0.04);
    color: var(--text-main, #1a1a2e);
}
.stat-table tbody tr:last-child td { border-bottom: none; }
.stat-table tbody tr:hover { background: rgba(99,102,241,0.03); }

.stat-rank {
    display: inline-flex;
    width: 26px; height: 26px;
    align-items: center; justify-content: center;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 700;
}
.fw-600 { font-weight: 600; }
.fw-700 { font-weight: 700; }
.text-primary { color: var(--primary, #6366f1); }

/* ===== Responsive ===== */
@media (max-width: 900px) {
    .stat-charts-row {
        flex-direction: column;
    }
    .stat-chart-wide,
    .stat-chart-narrow {
        flex: 1;
    }
}
</style>

<?php include 'footer_back.php'; ?>
