<?php
require_once '../../config/db.php';
require_once '../../models/Ordonnance.php';

$model = new Ordonnance($pdo);
$success = '';
$errors = [];

$consultations = $model->getConsultationsTerminees();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_consultation = intval($_POST['id_consultation'] ?? 0);
    $medicaments = trim($_POST['medicaments'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $duree = intval($_POST['duree_traitement'] ?? 0);

    if (empty($id_consultation)) {
        $errors[] = "Veuillez choisir une consultation.";
    }
    if (empty($medicaments) || strlen($medicaments) < 5) {
        $errors[] = "Les médicaments sont obligatoires (min. 5 caractères).";
    }
    if (empty($instructions) || strlen($instructions) < 5) {
        $errors[] = "Les instructions sont obligatoires (min. 5 caractères).";
    }
    if ($duree <= 0) {
        $errors[] = "La durée du traitement doit être supérieure à 0.";
    }

    if (empty($errors)) {
        $data = [
            'id_consultation'  => $id_consultation,
            'medicaments'      => $medicaments,
            'instructions'     => $instructions,
            'duree_traitement' => $duree
        ];
        if ($model->create($data)) {
            $success = "Ordonnance ajoutée avec succès !";
            $consultations = $model->getConsultationsTerminees();
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
    <title>Ajouter Ordonnance - ASCLEPIA Admin</title>
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
                <a href="list_ordonnance.php">
                    <span class="nav-icon"><i class="fa-solid fa-file-prescription"></i></span>
                    Ordonnances
                </a>
            </div>
            <div class="nav-item">
                <a href="add_ordonnance.php" class="active">
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
                    <div class="page-title">Nouvelle Ordonnance</div>
                    <div class="breadcrumb">
                        <a href="list_ordonnance.php">Ordonnances</a>
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

            <?php if (empty($consultations)): ?>
                <div class="alert alert-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Aucune consultation terminée disponible. Une ordonnance ne peut être créée que pour une consultation avec le statut <strong>terminée</strong> et sans ordonnance existante.
                </div>
            <?php else: ?>

            <div class="card" style="max-width:700px; margin:0 auto;">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-file-prescription" style="color:var(--primary)"></i>
                        Ajouter une ordonnance
                    </div>
                </div>

                <form action="" method="POST" id="formAdd" onsubmit="return validerFormulaire()">

                    <div class="form-group">
                        <label class="form-label">Consultation * <span class="text-muted" style="font-weight:400;font-size:0.8rem">(consultations terminées uniquement)</span></label>
                        <select name="id_consultation" id="id_consultation" class="form-control">
                            <option value="">-- Choisir une consultation --</option>
                            <?php foreach ($consultations as $c): ?>
                            <option value="<?= $c['id_consultation'] ?>">
                                #<?= $c['id_consultation'] ?> — <?= date('d/m/Y H:i', strtotime($c['date_consultation'])) ?> — <?= htmlspecialchars(substr($c['diagnostique'], 0, 40)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-error" id="err_consultation">Veuillez choisir une consultation.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Médicaments * <span class="text-muted" style="font-weight:400;font-size:0.8rem">(min. 5 caractères)</span></label>
                        <textarea name="medicaments" id="medicaments" class="form-control"
                            placeholder="Ex: Paracétamol 500mg, Ibuprofène 400mg..."
                            oninput="compter('medicaments', 'count_med', 5)"></textarea>
                        <span class="form-hint"><span id="count_med">0</span> caractères</span>
                        <span class="form-error" id="err_med">Les médicaments sont obligatoires (min. 5 caractères).</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instructions * <span class="text-muted" style="font-weight:400;font-size:0.8rem">(min. 5 caractères)</span></label>
                        <textarea name="instructions" id="instructions" class="form-control"
                            placeholder="Ex: Prendre 1 comprimé 3 fois par jour après les repas..."
                            oninput="compter('instructions', 'count_inst', 5)"></textarea>
                        <span class="form-hint"><span id="count_inst">0</span> caractères</span>
                        <span class="form-error" id="err_inst">Les instructions sont obligatoires (min. 5 caractères).</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Durée du traitement * <span class="text-muted" style="font-weight:400;font-size:0.8rem">(en jours)</span></label>
                        <input type="number" name="duree_traitement" id="duree_traitement" class="form-control"
                            placeholder="Ex: 7" min="1">
                        <span class="form-error" id="err_duree">La durée doit être supérieure à 0.</span>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Ajouter
                        </button>
                        <a href="list_ordonnance.php" class="btn btn-outline">Annuler</a>
                    </div>

                </form>
            </div>
            <?php endif; ?>
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

        const consultation = document.getElementById('id_consultation').value;
        if (!consultation) {
            document.getElementById('id_consultation').classList.add('is-invalid');
            document.getElementById('err_consultation').style.display = 'block';
            valide = false;
        }

        const med = document.getElementById('medicaments').value.trim();
        if (med.length < 5) {
            document.getElementById('medicaments').classList.add('is-invalid');
            document.getElementById('err_med').style.display = 'block';
            valide = false;
        }

        const inst = document.getElementById('instructions').value.trim();
        if (inst.length < 5) {
            document.getElementById('instructions').classList.add('is-invalid');
            document.getElementById('err_inst').style.display = 'block';
            valide = false;
        }

        const duree = parseInt(document.getElementById('duree_traitement').value);
        if (!duree || duree <= 0) {
            document.getElementById('duree_traitement').classList.add('is-invalid');
            document.getElementById('err_duree').style.display = 'block';
            valide = false;
        }

        return valide;
    }
</script>
</body>
</html>