<?php
session_start();
require_once '../../config.php';
require_once '../../Controller/ConsultationController.php';

$pdo        = config::getConnexion();
$controller = new ConsultationController($pdo);

// ✅ id_medecin from session
$id_medecin = $_SESSION['user_id'] ?? null;

// Get consultation ID from URL
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: list_consultation.php');
    exit();
}

// Load existing consultation
$consultation = $controller->getConsultationById($id);
if (!$consultation) {
    header('Location: list_consultation.php');
    exit();
}

$success = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date         = trim($_POST['date_consultation'] ?? '');
    $diagnostique = trim($_POST['diagnostique'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');
    $statut       = trim($_POST['statut'] ?? '');
    $id_patient   = intval($_POST['id_patient'] ?? 0);

    if ($id_patient === 0) {
        $errors[] = "Veuillez sélectionner un patient.";
    }

    if (empty($date)) {
        $errors[] = "La date est obligatoire.";
    } elseif ($controller->existsByDate($date, $id)) {
        $errors[] = "Une consultation existe déjà à cette date et heure exacte.";
    }

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
    } elseif ($statut === 'terminée' && !empty($date) && strtotime($date) > time()) {
        $errors[] = "Le statut 'terminée' est impossible pour une consultation future.";
    }

    if (empty($errors)) {
        $updated = Consultation::fromArray([
            'id_consultation'   => $id,
            'date_consultation' => $date,
            'diagnostique'      => $diagnostique,
            'notes'             => $notes,
            'statut'            => $statut,
            'id_patient'        => $id_patient,
            'id_medecin'        => $id_medecin,
        ]);

        if ($controller->updateConsultation($updated)) {
            $success = "Consultation modifiée avec succès !";
            // Reload updated data
            $consultation = $controller->getConsultationById($id);
        } else {
            $errors[] = "Erreur lors de la modification.";
        }
    }
}

// Current values (from DB or failed POST)
$cur_date   = $_POST['date_consultation'] ?? $consultation->getDateConsultation();
$cur_diag   = $_POST['diagnostique']      ?? $consultation->getDiagnostique();
$cur_notes  = $_POST['notes']             ?? $consultation->getNotes();
$cur_statut = $_POST['statut']            ?? $consultation->getStatut();
$cur_patient = intval($_POST['id_patient'] ?? $consultation->getIdPatient());

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Consultation - ASCLEPIA</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dark.css">
    <style>
        .patient-search-wrap { position: relative; }
        .patient-search-wrap .search-icon {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); pointer-events: none;
        }
        .patient-search-input {
            width: 100%; padding: 10px 14px 10px 38px;
            border: 1px solid var(--border); border-radius: var(--radius);
            font-size: 0.92rem; background: var(--card); color: var(--text);
            outline: none; transition: border-color .2s; box-sizing: border-box;
        }
        .patient-search-input:focus { border-color: var(--primary); }
        .patient-search-input.is-invalid { border-color: var(--danger); }
        .patient-dropdown {
            position: absolute; top: 100%; left: 0; right: 0; z-index: 100;
            background: var(--card); border: 1px solid var(--border);
            border-top: none; border-radius: 0 0 var(--radius) var(--radius);
            max-height: 220px; overflow-y: auto;
            display: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        .patient-dropdown.open { display: block; }
        .p-opt {
            padding: 10px 14px; cursor: pointer;
            display: flex; align-items: center; gap: 10px;
            border-bottom: 1px solid var(--border); font-size: 0.9rem;
            transition: background .15s;
        }
        .p-opt:last-child { border-bottom: none; }
        .p-opt:hover { background: rgba(14,165,233,0.08); }
        .p-opt .p-name { flex: 1; }
        .p-opt .p-id-badge {
            font-size: 0.72rem; color: var(--text-muted);
            background: var(--bg); padding: 2px 7px; border-radius: 20px;
        }
        .no-result { padding: 12px 14px; font-size: 0.88rem; color: var(--text-muted); text-align: center; }
        .selected-badge {
            display: none; align-items: center; gap: 8px;
            margin-top: 8px; padding: 8px 12px;
            background: rgba(14,165,233,0.08);
            border: 1px solid rgba(14,165,233,0.3);
            border-radius: var(--radius);
            font-size: 0.88rem; color: var(--primary);
        }
        .selected-badge.show { display: flex; }
        .selected-badge .clear-btn {
            margin-left: auto; background: none; border: none;
            color: var(--text-muted); cursor: pointer; font-size: 0.85rem; padding: 0 4px;
        }
        .selected-badge .clear-btn:hover { color: var(--danger); }
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
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_nom'] ?? 'M', 0, 1)) ?></div>
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Médecin') ?></div>
                <div class="role">Médecin</div>
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
            <div class="nav-section-label">Consultation</div>
            <div class="nav-item">
                <a href="list_consultation.php">
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
                    <div class="page-title">Modifier Consultation #<?= $id ?></div>
                    <div class="breadcrumb">
                        <a href="list_consultation.php">Consultations</a>
                        <span>/</span>
                        <span>Modifier</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <button class="dark-toggle" onclick="toggleDark()" id="darkBtn" title="Mode sombre">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </div>

        <div class="page-content">

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <ul style="margin:0;padding-left:16px">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width:700px; margin:0 auto;">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-pen-to-square" style="color:var(--primary)"></i>
                        Modifier la consultation
                    </div>
                </div>

                <form action="" method="POST" id="formEdit" onsubmit="return validerFormulaire()">

                    <!-- ✅ PATIENT SEARCHABLE PICKER -->
                    <div class="form-group">
                        <label class="form-label">Patient *</label>
                        <input type="hidden" name="id_patient" id="id_patient" value="<?= $cur_patient ?>">

                        <div class="patient-search-wrap">
                            <i class="fa-solid fa-user-injured search-icon"></i>
                            <input type="text"
                                   id="patientSearch"
                                   class="patient-search-input"
                                   placeholder="Rechercher un patient par nom..."
                                   autocomplete="off">
                            <div class="patient-dropdown" id="patientDropdown"></div>
                        </div>

                        <div class="selected-badge" id="selectedBadge">
                            <i class="fa-solid fa-user-check"></i>
                            <span id="selectedName"></span>
                            <button type="button" class="clear-btn" onclick="clearPatient()">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <span class="form-error" id="err_patient" style="display:none; color:var(--danger); font-size:0.8rem; margin-top:4px;">
                            Veuillez sélectionner un patient.
                        </span>
                    </div>

                    <!-- DATE -->
                    <div class="form-group">
                        <label class="form-label">Date de consultation *</label>
                        <input type="datetime-local" name="date_consultation" id="date_consultation"
                               class="form-control"
                               value="<?= htmlspecialchars(str_replace(' ', 'T', $cur_date)) ?>"
                               onchange="verifierDate()">
                        <span class="form-error" id="err_date">La date est obligatoire.</span>
                    </div>

                    <!-- STATUT -->
                    <div class="form-group">
                        <label class="form-label">Statut *</label>
                        <select name="statut" id="statut" class="form-control">
                            <option value="">-- Choisir un statut --</option>
                            <option value="planifiée"  <?= $cur_statut === 'planifiée'  ? 'selected' : '' ?>>Planifiée</option>
                            <option value="terminée"   <?= $cur_statut === 'terminée'   ? 'selected' : '' ?> id="opt_terminee">Terminée</option>
                            <option value="annulée"    <?= $cur_statut === 'annulée'    ? 'selected' : '' ?>>Annulée</option>
                        </select>
                        <span class="form-error" id="err_statut">Veuillez choisir un statut.</span>
                    </div>

                    <!-- DIAGNOSTIQUE -->
                    <div class="form-group" style="position:relative;">
                        <label class="form-label">
                            Diagnostique
                            <span class="text-muted" id="hint_diag" style="font-weight:400;font-size:0.8rem"></span>
                            <span class="badge badge-primary" style="font-size:0.75rem; margin-left:8px;">
                                <i class="fa-solid fa-robot"></i> IA
                            </span>
                        </label>
                        <textarea name="diagnostique" id="diagnostique" class="form-control"
                            oninput="compter('diagnostique','count_diag',10); suggererDiagnostique()"><?= htmlspecialchars($cur_diag ?? '') ?></textarea>
                        <div id="suggestions_diag" style="display:none; position:absolute; left:0; right:0; background:var(--card); border:1px solid var(--border); border-top:none; border-radius:0 0 var(--radius) var(--radius); z-index:50; max-height:180px; overflow-y:auto;"></div>
                        <span class="form-hint"><span id="count_diag">0</span> caractères</span>
                        <span class="form-error" id="err_diag">Le diagnostique doit contenir au moins 10 caractères.</span>
                    </div>

                    <!-- NOTES -->
                    <div class="form-group">
                        <label class="form-label">
                            Notes
                            <span class="text-muted" id="hint_notes" style="font-weight:400;font-size:0.8rem"></span>
                        </label>
                        <textarea name="notes" id="notes" class="form-control"
                            oninput="compter('notes','count_notes',5)"><?= htmlspecialchars($cur_notes ?? '') ?></textarea>
                        <span class="form-hint"><span id="count_notes">0</span> caractères</span>
                        <span class="form-error" id="err_notes">Les notes doivent contenir au moins 5 caractères.</span>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Enregistrer
                        </button>
                        <a href="list_consultation.php" class="btn btn-outline">Annuler</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ── AJAX Patient Search ──
const searchInput  = document.getElementById('patientSearch');
const dropdown     = document.getElementById('patientDropdown');
const hiddenInput  = document.getElementById('id_patient');
const badge        = document.getElementById('selectedBadge');
const selectedName = document.getElementById('selectedName');
let searchTimeout  = null;

// ✅ Pre-select the current patient on page load via AJAX
const preselectedId = parseInt(hiddenInput.value) || 0;
if (preselectedId) {
    fetch('search_patients.php?id=' + preselectedId)
        .then(r => r.json())
        .then(data => { if (data.length) selectPatient(data[0].id_user, data[0].nom); })
        .catch(() => {});
}

searchInput.addEventListener('input', function() {
    const q = this.value.trim();
    if (q.length < 2) {
        dropdown.classList.remove('open');
        dropdown.innerHTML = '';
        return;
    }
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dropdown.innerHTML = '<div class="no-result"><i class="fa-solid fa-spinner fa-spin"></i> Recherche...</div>';
        dropdown.classList.add('open');
        fetch('search_patients.php?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                dropdown.innerHTML = data.length
                    ? data.map(p =>
                        `<div class="p-opt" onclick="selectPatient(${p.id_user}, '${p.nom.replace(/'/g, "\\'")}')">
                            <i class="fa-solid fa-user" style="color:var(--primary);font-size:0.85rem;"></i>
                            <span class="p-name">${p.nom}</span>
                            <span class="p-id-badge">ID ${p.id_user}</span>
                        </div>`).join('')
                    : '<div class="no-result"><i class="fa-solid fa-face-sad-tear"></i> Aucun patient trouvé</div>';
                dropdown.classList.add('open');
            })
            .catch(() => { dropdown.innerHTML = '<div class="no-result">Erreur de recherche.</div>'; });
    }, 300);
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('.patient-search-wrap')) dropdown.classList.remove('open');
});

function selectPatient(id, nom) {
    hiddenInput.value = id;
    selectedName.textContent = nom;
    badge.classList.add('show');
    searchInput.style.display = 'none';
    dropdown.classList.remove('open');
    document.getElementById('err_patient').style.display = 'none';
}

function clearPatient() {
    hiddenInput.value = 0;
    searchInput.value = '';
    searchInput.style.display = '';
    badge.classList.remove('show');
    dropdown.innerHTML = '';
    searchInput.focus();
}

document.querySelector('.sidebar-toggle').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('open');
});

function compter(champId, compteurId, minimum) {
    const nb = document.getElementById(champId).value.length;
    const el = document.getElementById(compteurId);
    el.textContent = nb;
    el.style.color = nb >= minimum ? 'green' : 'red';
}

// Init counters
compter('diagnostique', 'count_diag', 10);
compter('notes', 'count_notes', 5);

function verifierDate() {
    const dateVal      = document.getElementById('date_consultation').value;
    const diag         = document.getElementById('diagnostique');
    const notes        = document.getElementById('notes');
    const hintDiag     = document.getElementById('hint_diag');
    const hintNotes    = document.getElementById('hint_notes');
    const optTerminee  = document.getElementById('opt_terminee');
    const selectStatut = document.getElementById('statut');

    if (!dateVal) { diag.disabled = notes.disabled = false; return; }

    const isPast = new Date(dateVal) <= new Date();
    diag.disabled = notes.disabled = false; // always editable in edit mode
    optTerminee.disabled = !isPast;
    hintDiag.textContent  = isPast ? "(min. 10 caractères)" : "";
    hintNotes.textContent = isPast ? "(min. 5 caractères)"  : "";
    if (!isPast && selectStatut.value === 'terminée') selectStatut.value = '';
}

function toggleDark() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    document.getElementById('darkBtn').innerHTML = isDark
        ? '<i class="fa-solid fa-sun"></i>'
        : '<i class="fa-solid fa-moon"></i>';
    localStorage.setItem('darkMode', isDark);
}
if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
    document.getElementById('darkBtn').innerHTML = '<i class="fa-solid fa-sun"></i>';
}

function suggererDiagnostique() {
    const q   = document.getElementById('diagnostique').value.trim();
    const box = document.getElementById('suggestions_diag');
    if (q.length < 2) { box.style.display = 'none'; return; }
    fetch('suggest_diagnostic.php?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            if (!data.length) { box.style.display = 'none'; return; }
            box.innerHTML = data.map(s =>
                `<div onclick="choisirSuggestion('${s.replace(/'/g,"\\'")}')">
                    <i class="fa-solid fa-robot" style="color:var(--primary);margin-right:8px;"></i>${s}
                </div>`
            ).join('');
            box.style.display = 'block';
        })
        .catch(() => box.style.display = 'none');
}

function choisirSuggestion(texte) {
    document.getElementById('diagnostique').value = texte;
    document.getElementById('suggestions_diag').style.display = 'none';
    compter('diagnostique', 'count_diag', 10);
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#diagnostique') && !e.target.closest('#suggestions_diag')) {
        document.getElementById('suggestions_diag').style.display = 'none';
    }
});

function validerFormulaire() {
    let valide = true;
    document.querySelectorAll('.form-error').forEach(e => e.style.display = 'none');
    document.querySelectorAll('.form-control').forEach(e => e.classList.remove('is-invalid'));

    if (!hiddenInput.value || parseInt(hiddenInput.value) === 0) {
        document.getElementById('err_patient').style.display = 'block';
        searchInput.classList.add('is-invalid');
        valide = false;
    }

    const date = document.getElementById('date_consultation').value;
    if (!date) {
        document.getElementById('date_consultation').classList.add('is-invalid');
        document.getElementById('err_date').style.display = 'block';
        valide = false;
    }

    if (!document.getElementById('statut').value) {
        document.getElementById('statut').classList.add('is-invalid');
        document.getElementById('err_statut').style.display = 'block';
        valide = false;
    }

    if (date && new Date(date) <= new Date()) {
        if (document.getElementById('diagnostique').value.trim().length < 10) {
            document.getElementById('diagnostique').classList.add('is-invalid');
            document.getElementById('err_diag').style.display = 'block';
            valide = false;
        }
        if (document.getElementById('notes').value.trim().length < 5) {
            document.getElementById('notes').classList.add('is-invalid');
            document.getElementById('err_notes').style.display = 'block';
            valide = false;
        }
    }

    return valide;
}

verifierDate();
</script>
<script src="../../assets/js/language-switcher.js"></script>
</body>
</html>