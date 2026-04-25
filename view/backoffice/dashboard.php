<?php
require_once "../../Controller/PharmacieC.php";
require_once "../../Controller/MedicamentC.php";

$pharmacieC = new pharmacieC();
$medicamentC = new medicamentC();

$listePharmacies = $pharmacieC->listepharmacie();
$totalPharmacies = $listePharmacies->rowCount();

$listeMedicaments = $medicamentC->afficherMedicaments();
$totalMedicaments = $listeMedicaments->rowCount();

include "header_back.php";
?>

<div class="admin-container container">
    <div class="welcome-section mb-4">
        <h1 class="display-4 fw-bold">Tableau de Bord</h1>
        <p class="text-muted">Bienvenue dans l'interface d'administration d'ASCLEPIA. Voici un aperçu de votre activité.</p>
    </div>

    <!-- statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-4">
            <div class="stats-card">
                <div class="stats-icon bg-primary-soft">
                    <i class="fa-solid fa-hospital-user text-primary"></i>
                </div>
                <div class="stats-info">
                    <h3><?php echo $totalPharmacies; ?></h3>
                    <p>Pharmacies Partenaires</p>
                </div>
                <div class="stats-trend text-success">
                    <i class="fa-solid fa-arrow-up"></i> Actives
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="stats-card">
                <div class="stats-icon bg-success-soft">
                    <i class="fa-solid fa-pills text-success"></i>
                </div>
                <div class="stats-info">
                    <h3><?php echo $totalMedicaments; ?></h3>
                    <p>Médicaments Référencés</p>
                </div>
                <div class="stats-trend text-success">
                    <i class="fa-solid fa-check"></i> En Stock
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="stats-card">
                <div class="stats-icon bg-info-soft">
                    <i class="fa-solid fa-shield-halved text-info"></i>
                </div>
                <div class="stats-info">
                    <h3>5</h3>
                    <p>Assurances Connectées</p>
                </div>
                <div class="stats-trend text-info">
                    <i class="fa-solid fa-link"></i> Partenaires
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="crud-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="m-0"><i class="fa-solid fa-bolt me-2 text-warning"></i> Actions Rapides</h4>
                </div>
                <div class="quick-actions-grid">
                    <a href="addpharmacie.php" class="quick-action-btn">
                        <i class="fa-solid fa-plus-circle"></i>
                        <span>Ajouter une Pharmacie</span>
                    </a>
                    <a href="listepharmacie.php" class="quick-action-btn">
                        <i class="fa-solid fa-list-check"></i>
                        <span>Gérer les Pharmacies</span>
                    </a>
                    <a href="addmedicament.php" class="quick-action-btn">
                        <i class="fa-solid fa-folder-plus"></i>
                        <span>Ajouter un Médicament</span>
                    </a>
                    <a href="../frontoffice/index.php" target="_blank" class="quick-action-btn">
                        <i class="fa-solid fa-eye"></i>
                        <span>Voir le Site Public</span>
                    </a>
                    <a href="statistiques.php" class="quick-action-btn">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span>Voir les Statistiques</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="crud-card h-100">
                <h4 class="mb-4"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> État Système</h4>
                <div class="system-status">
                    <div class="status-item">
                        <span class="status-label">Base de données</span>
                        <span class="badge bg-success">Connecté</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Version Plateforme</span>
                        <span class="text-muted">v2.1.0</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Dernière Mise à Jour</span>
                        <span class="text-muted"><?php echo date('d/m/Y'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dashboard Specific Styles */
    .bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    .bg-info-soft { background: rgba(13, 202, 240, 0.1); }

    .stats-card {
        background: white;
        border-radius: var(--radius-lg);
        padding: 25px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }
    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .stats-info h3 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-main);
    }
    .stats-info p {
        margin: 0;
        color: var(--text-muted);
        font-size: 0.9rem;
    }
    .stats-trend {
        margin-left: auto;
        font-size: 0.8rem;
        font-weight: 600;
        background: rgba(0,0,0,0.03);
        padding: 5px 10px;
        border-radius: 20px;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }
    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: var(--radius-md);
        text-decoration: none;
        color: var(--text-main);
        font-weight: 600;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .quick-action-btn i {
        font-size: 1.5rem;
        color: var(--primary);
    }
    .quick-action-btn:hover {
        background: white;
        border-color: var(--primary);
        color: var(--primary);
        box-shadow: var(--shadow-sm);
    }
    .quick-action-btn.disabled {
        opacity: 0.5;
        pointer-events: none;
        filter: grayscale(1);
    }

    .system-status {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .status-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 10px;
        border-bottom: 1px dashed var(--border);
    }
    .status-item:last-child {
        border-bottom: none;
    }
    .status-label {
        font-weight: 500;
        color: var(--text-muted);
    }
</style>

<?php include "footer_back.php"; ?>
