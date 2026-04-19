<?php
require_once '../../config/db.php';
require_once '../../controllers/ConsultationController.php';

$controller = new ConsultationController($pdo);
$consultations = $controller->getAllConsultations();
?>"
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultations - ASCLEPIA Admin</title>
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
        </nav>
    </aside>

    <!-- MAIN -->
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
            <div class="table-wrapper">
                <table class="table">
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
                    <tbody>
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
                        <tr>
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
                                ?>"
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