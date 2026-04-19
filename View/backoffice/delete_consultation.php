<?php
require_once '../../config/db.php';
require_once '../../controllers/ConsultationController.php';

$controller = new ConsultationController($pdo);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$consultation = $controller->getConsultationById($id);

if (!$consultation) {
    die("Consultation introuvable.");
}

if (isset($_GET['confirm']) && $_GET['confirm'] === 'oui') {
    $controller->deleteConsultation($id);
    header('Location: list_consultation.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer Consultation - ASCLEPIA Admin</title>
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

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <div class="page-title">Supprimer Consultation</div>
                    <div class="breadcrumb">
                        <a href="list_consultation.php">Consultations</a>
                        <span>/</span>
                        <span>Supprimer</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <div class="card" style="max-width:600px; margin:0 auto;">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-trash" style="color:var(--danger)"></i>
                        Confirmer la suppression
                    </div>
                </div>

                <div class="alert alert-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Es-tu sûr de vouloir supprimer cette consultation ? Cette action est irréversible.
                </div>

                <div class="form-group">
                    <label class="form-label">Date</label>
                    <p><?= $consultation->getDateConsultation() ?></p>
                </div>

                <div class="form-group">
                    <label class="form-label">Diagnostique</label>
                    <p><?= htmlspecialchars($consultation->getDiagnostique()) ?></p>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <p><?= htmlspecialchars($consultation->getNotes()) ?></p>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <a href="delete_consultation.php?id=<?= $id ?>&confirm=oui" class="btn btn-danger">
                        <i class="fa-solid fa-trash"></i> Oui, supprimer
                    </a>
                    <a href="list_consultation.php" class="btn btn-outline">
                        <i class="fa-solid fa-xmark"></i> Annuler
                    </a>
                </div>
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