<?php
require_once '../../config/db.php';
require_once '../../models/Consultation.php';

$model = new Consultation($pdo);
$success = '';
$errors = [];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$consultation = $model->getById($id);

if (!$consultation) {
    die("Consultation introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$date = isset($_POST['date_consultation']) ? trim($_POST['date_consultation']) : '';
$diagnostique = isset($_POST['diagnostique']) ? trim($_POST['diagnostique']) : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
$statut = isset($_POST['statut']) ? trim($_POST['statut']) : '';

    if (empty($date)) {
        $errors[] = "La date est obligatoire.";
    } elseif ($model->existeDejaDate($date, $id)) {
        $errors[] = "Une consultation existe déjà à cette date et heure exacte.";
    }

    // Diagnostique et notes obligatoires seulement si date passée
    $dateEstPassee = !empty($date) && strtotime($date) <= time();
    if ($dateEstPassee) {
        if (empty($diagnostique) || strlen($diagnostique) < 10) {
            $errors[] = "Le diagnostique doit contenir au moins 10 caractères.";
        }
        if (empty($notes) || strlen($notes) < 5) {
            $errors[] = "Les notes doivent contenir au moins 5 caractères.";
        }
    }

    if (empty($statut)) {
        $errors[] = "Le statut est obligatoire.";
    }

    if (empty($errors)) {
        $data = [
            'date_consultation' => $date,
            'diagnostique'      => $diagnostique,
            'notes'             => $notes,
            'statut'            => $statut
        ];
        if ($model->update($id, $data)) {
            $success = "Consultation modifiée avec succès !";
            $consultation = $model->getById($id);
        } else {
            $errors[] = "Erreur lors de la modification.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Consultation - ASCLEPIA Admin</title>
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
                    <div class="page-title">Modifier Consultation</div>
                    <div class="breadcrumb">
                        <a href="list_consultation.php">Consultations</a>
                        <span>/</span>
                        <span>Modifier</span>
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
                        <i class="fa-solid fa-pen" style="color:var(--primary)"></i>
                        Modifier la consultation #<?= $id ?>
                    </div>
                </div>

                <form action="" method="POST" id="formEdit" onsubmit="return validerFormulaire()">

                    <div class="form-group">
                        <label class="form-label">Date de consultation *</label>
                        <input type="datetime-local" name="date_consultation" id="date_consultation" class="form-control"
                            value="<?= date('Y-m-d\TH:i', strtotime($consultation['date_consultation'])) ?>"
                            onchange="verifierDate()">
                        <span class="form-error" id="err_date">La date est obligatoire.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Statut *</label>
                        <select name="statut" id="statut" class="form-control">
                            <option value="planifiée" <?= ($consultation['statut'] ?? '') === 'planifiée' ? 'selected' : '' ?>>Planifiée</option>
                            <option value="terminée" <?= ($consultation['statut'] ?? '') === 'terminée' ? 'selected' : '' ?>>Terminée</option>
                            <option value="annulée" <?= ($consultation['statut'] ?? '') === 'annulée' ? 'selected' : '' ?>>Annulée</option>
                        </select>
                        <span class="form-error" id="err_statut">Veuillez choisir un statut.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Diagnostique
                            <span class="text-muted" style="font-weight:400;font-size:0.8rem" id="hint_diag">
                                (disponible après la consultation)
                            </span>
                        </label>
                        <textarea name="diagnostique" id="diagnostique" class="form-control"
                            oninput="compter('diagnostique', 'count_diag', 10)"><?= htmlspecialchars($consultation['diagnostique']) ?></textarea>
                        <span class="form-hint"><span id="count_diag"><?= strlen($consultation['diagnostique']) ?></span> caractères</span>
                        <span class="form-error" id="err_diag">Le diagnostique doit contenir au moins 10 caractères.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes
                            <span class="text-muted" style="font-weight:400;font-size:0.8rem" id="hint_notes">
                                (disponible après la consultation)
                            </span>
                        </label>
                        <textarea name="notes" id="notes" class="form-control"
                            oninput="compter('notes', 'count_notes', 5)"><?= htmlspecialchars($consultation['notes']) ?></textarea>
                        <span class="form-hint"><span id="count_notes"><?= strlen($consultation['notes']) ?></span> caractères</span>
                        <span class="form-error" id="err_notes">Les notes doivent contenir au moins 5 caractères.</span>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Mettre à jour
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

    function verifierDate() {
        const dateVal = document.getElementById('date_consultation').value;
        const diag = document.getElementById('diagnostique');
        const notes = document.getElementById('notes');
        const hintDiag = document.getElementById('hint_diag');
        const hintNotes = document.getElementById('hint_notes');

        if (!dateVal) return;

        const dateChoisie = new Date(dateVal);
        const maintenant = new Date();

        if (dateChoisie <= maintenant) {
            // Date passée → activer les champs
            diag.disabled = false;
            notes.disabled = false;
            diag.placeholder = "Entrez le diagnostique...";
            notes.placeholder = "Entrez les notes...";
            hintDiag.textContent = "(min. 10 caractères)";
            hintNotes.textContent = "(min. 5 caractères)";
        } else {
            // Date future → désactiver les champs
            diag.disabled = true;
            diag.value = '';
            notes.disabled = true;
            notes.value = '';
            diag.placeholder = "Disponible après la date de consultation...";
            notes.placeholder = "Disponible après la date de consultation...";
            hintDiag.textContent = "(disponible après la consultation)";
            hintNotes.textContent = "(disponible après la consultation)";
            document.getElementById('count_diag').textContent = '0';
            document.getElementById('count_notes').textContent = '0';
        }
    }

    function validerFormulaire() {
        let valide = true;
        document.querySelectorAll('.form-error').forEach(e => e.style.display = 'none');
        document.querySelectorAll('.form-control').forEach(e => e.classList.remove('is-invalid'));

        const date = document.getElementById('date_consultation').value;
        if (!date) {
            document.getElementById('date_consultation').classList.add('is-invalid');
            document.getElementById('err_date').style.display = 'block';
            valide = false;
        }

        const statut = document.getElementById('statut').value;
        if (!statut) {
            document.getElementById('statut').classList.add('is-invalid');
            document.getElementById('err_statut').style.display = 'block';
            valide = false;
        }

        // Valider diagnostique et notes seulement si date passée
        const dateChoisie = new Date(date);
        const maintenant = new Date();
        if (date && dateChoisie <= maintenant) {
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
        }

        return valide;
    }

    // Vérifier au chargement de la page
    verifierDate();
</script>
</body>
</html>