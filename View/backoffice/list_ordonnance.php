<?php
require_once '../../config/db.php';
require_once '../../models/Ordonnance.php';

$model = new Ordonnance($pdo);
$ordonnances = $model->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordonnances - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                <a href="list_consultation.php">
                    <span class="nav-icon"><i class="fa-solid fa-calendar-check"></i></span>
                    Consultations
                </a>
            </div>
            <div class="nav-section-label">Ordonnance</div>
            <div class="nav-item">
                <a href="list_ordonnance.php" class="active">
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
                    <div class="page-title">Ordonnances</div>
                    <div class="breadcrumb">
                        <a href="#">Dashboard</a>
                        <span>/</span>
                        <span>Ordonnances</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="add_ordonnance.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Nouvelle ordonnance
                </a>
            </div>
        </div>

        <div class="page-content">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date création</th>
                            <th>Consultation</th>
                            <th>Médicaments</th>
                            <th>Durée</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ordonnances)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="icon">📋</div>
                                    <h3>Aucune ordonnance</h3>
                                    <p>Commencez par ajouter une ordonnance.</p>
                                    <a href="add_ordonnance.php" class="btn btn-primary btn-sm">+ Ajouter</a>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($ordonnances as $o): ?>
                        <tr>
                            <td><strong>#<?= $o['id_ordonnance'] ?></strong></td>
                            <td><?= $o['date_creation'] ?></td>
                            <td>
                                <span class="badge badge-primary">
                                    Consultation #<?= $o['id_consultation'] ?>
                                </span>
                                <br>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($o['date_consultation'])) ?></small>
                            </td>
                            <td><?= htmlspecialchars(substr($o['medicaments'], 0, 50)) ?>...</td>
                            <td><?= $o['duree_traitement'] ?> jours</td>
                            <td>
                                <div class="actions">
                                    <a href="ordonnance_pdf.php?id=<?= $o['id_ordonnance'] ?>" class="btn btn-primary btn-sm" target="_blank">
                                        <i class="fa-solid fa-file-pdf"></i> PDF
                                    </a>
                                    <a href="edit_ordonnance.php?id=<?= $o['id_ordonnance'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fa-solid fa-pen"></i> Modifier
                                    </a>
                                    <a href="delete_ordonnance.php?id=<?= $o['id_ordonnance'] ?>" class="btn btn-danger btn-sm">
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