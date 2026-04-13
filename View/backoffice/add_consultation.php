<?php
require_once '../../config/db.php';
require_once '../../models/Consultation.php';

$model = new Consultation($pdo);
$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = trim($_POST['date_consultation']);
    $diagnostique = trim($_POST['diagnostique']);
    $notes = trim($_POST['notes']);

    if (empty($date)) {
        $errors[] = "La date est obligatoire.";
    } elseif (strtotime($date) <= time()) {
        $errors[] = "La date de consultation doit être dans le futur.";
    } elseif ($model->existeDejaDate($date)) {
        $errors[] = "Une consultation existe déjà à cette date et heure exacte.";
    }
    if (empty($diagnostique) || strlen($diagnostique) < 10) {
        $errors[] = "Le diagnostique doit contenir au moins 10 caractères.";
    }
    if (empty($notes) || strlen($notes) < 5) {
        $errors[] = "Les notes doivent contenir au moins 5 caractères.";
    }

    if (empty($errors)) {
        $data = [
            'date_consultation' => $date,
            'diagnostique'      => $diagnostique,
            'notes'             => $notes
        ];
        if ($model->create($data)) {
            $success = "Consultation ajoutée avec succès !";
        } else {
            $errors[] = "Erreur lors de l'ajout.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Consultation - ASCLEPIA Admin</title>
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
                <a href="list_consultation.php">
                    <span class="nav-icon"><i class="fa-solid fa-calendar-check"></i></span>
                    Consultations
                </a>
            </div>
            <div class="nav-item">
                <a href="add_consultation.php" class="active">
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
                    <div class="page-title">Nouvelle Consultation</div>
                    <div class="breadcrumb">
                        <a href="list_consultation.php">Consultations</a>
                        <span>/</span>
                        <span>Ajouter</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-content">

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <ul style="margin:0;padding-left:16px">
                        <?php foreach ($errors as $e): ?>
                            <li><?= $e ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width:700px; margin:0 auto;">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-calendar-check" style="color:var(--primary)"></i>
                        Ajouter une consultation
                    </div>
                </div>

                <form action="" method="POST" id="formAdd" onsubmit="return validerFormulaire()">

                    <div class="form-group">
                        <label class="form-label">Date de consultation *</label>
                        <input type="datetime-local" name="date_consultation" id="date_consultation" class="form-control">
                        <span class="form-error" id="err_date">La date de consultation doit être dans le futur.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Diagnostique * <span class="text-muted" style="font-weight:400;font-size:0.8rem">(min. 10 caractères)</span></label>
                        <textarea name="diagnostique" id="diagnostique" class="form-control" placeholder="Entrez le diagnostique..." oninput="compter('diagnostique', 'count_diag', 10)"></textarea>
                        <span class="form-hint"><span id="count_diag">0</span> caractères</span>
                        <span class="form-error" id="err_diag">Le diagnostique doit contenir au moins 10 caractères.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes * <span class="text-muted" style="font-weight:400;font-size:0.8rem">(min. 5 caractères)</span></label>
                        <textarea name="notes" id="notes" class="form-control" placeholder="Entrez les notes..." oninput="compter('notes', 'count_notes', 5)"></textarea>
                        <span class="form-hint"><span id="count_notes">0</span> caractères</span>
                        <span class="form-error" id="err_notes">Les notes doivent contenir au moins 5 caractères.</span>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Ajouter
                        </button>
                        <a href="list_consultation.php" class="btn btn-outline">Annuler</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });

    function compter(champId, compteurId, minimum) {
        const nb = document.getElementById(champId).value.length;
        const el = document.getElementById(compteurId);
        el.textContent = nb;
        el.style.color = nb >= minimum ? 'green' : 'red';
    }

    function validerFormulaire() {
        let valide = true;
        document.querySelectorAll('.form-error').forEach(e => e.style.display = 'none');
        document.querySelectorAll('.form-control').forEach(e => e.classList.remove('is-invalid'));

        const date = document.getElementById('date_consultation').value;
        if (!date || new Date(date) <= new Date()) {
            document.getElementById('date_consultation').classList.add('is-invalid');
            document.getElementById('err_date').style.display = 'block';
            valide = false;
        }

        const diag = document.getElementById('diagnostique').value.trim();
        if (diag.length < 10) {
            document.getElementById('diagnostique').classList.add('is-invalid');
            document.getElementById('err_diag').style.display = 'block';
            valide = false;
        }

        const notes = document.getElementById('notes').value.trim();
        if (notes.length < 5) {
            document.getElementById('notes').classList.add('is-invalid');
            document.getElementById('err_notes').style.display = 'block';
            valide = false;
        }

        return valide;
    }

    const maintenant = new Date();
    const offset = maintenant.getTimezoneOffset() * 60000;
    document.getElementById('date_consultation').min = new Date(maintenant - offset).toISOString().slice(0, 16);
</script>
</body>
</html>