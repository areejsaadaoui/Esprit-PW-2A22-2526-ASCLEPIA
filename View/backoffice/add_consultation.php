<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'medecin'])) {
    header('Location: loginadmin.html');
    exit();
}

$user_role = $_SESSION['user_role'];
$adminNom  = $_SESSION['user_nom'] ?? 'Utilisateur';

$cur = basename($_SERVER['PHP_SELF']);
if (!function_exists('docActive')) {
    function docActive(...$p){ global $cur; return in_array($cur, $p) ? 'class="active"' : ''; }
    function docSub(...$p)   { global $cur; return in_array($cur, $p) ? 'open' : ''; }
}
if (!function_exists('isActive')) {
    function isActive(...$pages)   { global $cur; return in_array($cur, $pages); }
    function isSubActive(...$pages){ global $cur; return in_array($cur, $pages) ? 'open' : ''; }
}

require_once '../../config.php';
require_once '../../Controller/ConsultationController.php';

$pdo        = config::getConnexion();
$controller = new ConsultationController($pdo);

// ✅ id_medecin comes from session automatically
$id_medecin = $_SESSION['user_id'] ?? null;

$success = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date         = trim($_POST['date_consultation'] ?? '');
    $diagnostique = trim($_POST['diagnostique'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');
    $statut       = trim($_POST['statut'] ?? '');
    $id_patient   = intval($_POST['id_patient'] ?? 0);

    // Validate patient selection
    if ($id_patient === 0) {
        $errors[] = "Veuillez sélectionner un patient.";
    }

    if (empty($date)) {
        $errors[] = "La date est obligatoire.";
    } elseif (strtotime($date) <= time()) {
        $errors[] = "La date de consultation doit être dans le futur.";
    } elseif ($controller->existsByDate($date)) {
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
        $consultation = Consultation::fromArray([
            'date_consultation' => $date,
            'diagnostique'      => $diagnostique,
            'notes'             => $notes,
            'statut'            => $statut,
            'id_patient'        => $id_patient,
            'id_medecin'        => $id_medecin,
        ]);

        if ($controller->createConsultation($consultation)) {
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
    <title>Ajouter Consultation - ASCLEPIA</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dark.css">
    <style>
        /* ── Searchable patient picker ── */
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
        .p-opt:hover, .p-opt.focused { background: rgba(14,165,233,0.08); }
        .p-opt .p-name { flex: 1; }
        .p-opt .p-id-badge {
            font-size: 0.72rem; color: var(--text-muted);
            background: var(--bg); padding: 2px 7px; border-radius: 20px;
        }
        .no-result {
            padding: 12px 14px; font-size: 0.88rem;
            color: var(--text-muted); text-align: center;
        }

        /* Selected badge shown below search */
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
            color: var(--text-muted); cursor: pointer; font-size: 0.85rem;
            padding: 0 4px; transition: color .15s;
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
            <div class="user-avatar"><?= strtoupper(substr($adminNom, 0, 2)) ?></div>
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($adminNom) ?></div>
                <div class="role"><?= $user_role === 'admin' ? 'Super Admin' : 'Médecin' ?></div>
            </div>
        </div>

        <?php if ($user_role === 'medecin'): ?>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Principal</div>
            <div class="nav-item">
                <a href="../front/indexd.php" <?= docActive('indexd.php') ?>>
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>
            <div class="nav-section-label">Activité</div>
            <div class="nav-item has-sub <?= docSub('list_consultation.php','add_consultation.php','edit_consultation.php','delete_consultation.php','calendrier.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= docActive('list_consultation.php','add_consultation.php','edit_consultation.php','delete_consultation.php') ?>>
                    <i class="fa-solid fa-stethoscope nav-icon"></i>
                    <span>Consultations</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= docSub('list_consultation.php','add_consultation.php','edit_consultation.php','delete_consultation.php') ?>">
                    <a href="list_consultation.php" <?= docActive('list_consultation.php') ?>><i class="fa-solid fa-list"></i> Toutes les consultations</a>
                    <a href="add_consultation.php" <?= docActive('add_consultation.php') ?>><i class="fa-solid fa-plus"></i> Nouvelle consultation</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= docSub('list_ordonnance.php','add_ordonnance.php','edit_ordonnance.php','delete_ordonnance.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= docActive('list_ordonnance.php','add_ordonnance.php','edit_ordonnance.php','delete_ordonnance.php') ?>>
                    <i class="fa-solid fa-file-prescription nav-icon"></i>
                    <span>Ordonnances</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= docSub('list_ordonnance.php','add_ordonnance.php','edit_ordonnance.php','delete_ordonnance.php') ?>">
                    <a href="list_ordonnance.php" <?= docActive('list_ordonnance.php') ?>><i class="fa-solid fa-list"></i> Toutes les ordonnances</a>
                    <a href="add_ordonnance.php" <?= docActive('add_ordonnance.php') ?>><i class="fa-solid fa-plus"></i> Nouvelle ordonnance</a>
                </div>
            </div>
            <div class="nav-item">
                <a href="calendrier.php" <?= docActive('calendrier.php') ?>><i class="fa-solid fa-calendar-days nav-icon"></i><span>Calendrier</span></a>
            </div>
            <div class="nav-section-label">Autre</div>
            <div class="nav-item">
                <a href="../front/indexp.php"><i class="fas fa-globe nav-icon"></i><span>Espace patient</span></a>
            </div>
            <div class="nav-item">
                <a href="../front/login.php"><i class="fas fa-sign-out-alt nav-icon"></i><span>Déconnexion</span></a>
            </div>
        </nav>
        <?php else: ?>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Principal</div>
            <div class="nav-item">
                <a href="../back/dashboard.php" <?= isActive('dashboard.php') ? 'class="active"' : '' ?>><i class="fas fa-tachometer-alt nav-icon"></i><span>Tableau de bord</span></a>
            </div>
            <div class="nav-section-label">Gestion</div>
            <div class="nav-item has-sub <?= isSubActive('assurancelist.php','contratList.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= isActive('assurancelist.php','contratList.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-shield-halved nav-icon"></i><span>Assurances &amp; Contrats</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu">
                    <a href="../backoffice/assurancelist.php" <?= isActive('assurancelist.php') ? 'class="active"' : '' ?>>Les assurances</a>
                    <a href="contrat/contratList.php" <?= isActive('contratList.php') ? 'class="active"' : '' ?>>Les contrats</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= isSubActive('list_consultation.php','list_ordonnance.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= isActive('list_consultation.php','list_ordonnance.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-file-contract nav-icon"></i><span>Ordonnances &amp; Consultations</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu">
                    <a href="dashboard.php" <?= isActive('dashboard.php') ? 'class="active"' : '' ?>>Vue d’ensemble consultations</a>
                    <a href="list_consultation.php" <?= isActive('list_consultation.php') ? 'class="active"' : '' ?>>Les consultations</a>
                    <a href="list_ordonnance.php" <?= isActive('list_ordonnance.php') ? 'class="active"' : '' ?>>Les ordonnances</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= isSubActive('listepharmacie.php','listemedicament.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= isActive('listepharmacie.php','listemedicament.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-prescription-bottle-medical nav-icon"></i><span>Pharmacies &amp; Médicaments</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu">
                    <a href="listepharmacie.php" <?= isActive('listepharmacie.php') ? 'class="active"' : '' ?>>Les pharmacies</a>
                    <a href="listemedicament.php" <?= isActive('listemedicament.php') ? 'class="active"' : '' ?>>Les médicaments</a>
                </div>
            </div>
            <div class="nav-item">
                <a href="calendrier.php" <?= isActive('calendrier.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-calendar-days nav-icon"></i><span>Calendrier</span></a>
            </div>
            <div class="nav-item has-sub <?= isSubActive('postlist.php','postList.php','addpost.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= isActive('postlist.php','postList.php','addpost.php') ? 'class="active"' : '' ?>><i class="fas fa-comments nav-icon"></i><span>Forum</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu">
                    <a href="../Frontoffice/postlist.php" <?= isActive('postlist.php','postList.php') ? 'class="active"' : '' ?>>Tous les posts</a>
                    <a href="addpost.php" <?= isActive('addpost.php') ? 'class="active"' : '' ?>>Ajouter un post</a>
                    <a href="dashboard.php" <?= isActive('dashboard.php') ? 'class="active"' : '' ?>>Gestion des posts</a>
                </div>
            </div>
            <div class="nav-section-label">Configuration</div>
            <div class="nav-item">
                <a href="../front/indexp.php"><i class="fas fa-globe nav-icon"></i><span>Voir le site</span></a>
            </div>
            <div class="nav-item">
                <a href="../back/loginadmin.html"><i class="fas fa-sign-out-alt nav-icon"></i><span>Déconnexion</span></a>
            </div>
        </nav>
        <?php endif; ?>
    </aside>

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
                        <i class="fa-solid fa-calendar-check" style="color:var(--primary)"></i>
                        Ajouter une consultation
                    </div>
                </div>

                <form action="" method="POST" id="formAdd" onsubmit="return validerFormulaire()">

                    <!-- ✅ PATIENT SEARCHABLE PICKER -->
                    <div class="form-group">
                        <label class="form-label">Patient *</label>

                        <!-- Hidden field submitted with the form -->
                        <input type="hidden" name="id_patient" id="id_patient"
                               value="<?= intval($_POST['id_patient'] ?? 0) ?>">

                        <div class="patient-search-wrap">
                            <i class="fa-solid fa-user-injured search-icon"></i>
                            <input type="text"
                                   id="patientSearch"
                                   class="patient-search-input"
                                   placeholder="Rechercher un patient par nom..."
                                   autocomplete="off">
                            <div class="patient-dropdown" id="patientDropdown"></div>
                        </div>

                        <!-- Shown once a patient is selected -->
                        <div class="selected-badge" id="selectedBadge">
                            <i class="fa-solid fa-user-check"></i>
                            <span id="selectedName"></span>
                            <button type="button" class="clear-btn" onclick="clearPatient()" title="Changer de patient">
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
                               class="form-control" onchange="verifierDate()">
                        <span class="form-error" id="err_date">La date de consultation doit être dans le futur.</span>
                    </div>

                    <!-- STATUT -->
                    <div class="form-group">
                        <label class="form-label">Statut *</label>
                        <select name="statut" id="statut" class="form-control">
                            <option value="">-- Choisir un statut --</option>
                            <option value="planifiée">Planifiée</option>
                            <option value="terminée" id="opt_terminee">Terminée</option>
                            <option value="annulée">Annulée</option>
                        </select>
                        <span class="form-error" id="err_statut">Veuillez choisir un statut.</span>
                    </div>

                    <!-- DIAGNOSTIQUE -->
                    <div class="form-group" style="position:relative;">
                        <label class="form-label">
                            Diagnostique
                            <span class="text-muted" id="hint_diag" style="font-weight:400;font-size:0.8rem">(disponible après la consultation)</span>
                            <span class="badge badge-primary" style="font-size:0.75rem; margin-left:8px;">
                                <i class="fa-solid fa-robot"></i> IA
                            </span>
                        </label>
                        <textarea name="diagnostique" id="diagnostique" class="form-control"
                            placeholder="Disponible après la date de consultation..."
                            disabled
                            oninput="compter('diagnostique', 'count_diag', 10); suggererDiagnostique()"></textarea>
                        <div id="suggestions_diag" style="display:none; position:absolute; left:0; right:0; background:var(--card); border:1px solid var(--border); border-top:none; border-radius:0 0 var(--radius) var(--radius); z-index:50; max-height:180px; overflow-y:auto;"></div>
                        <span class="form-hint"><span id="count_diag">0</span> caractères</span>
                        <span class="form-error" id="err_diag">Le diagnostique doit contenir au moins 10 caractères.</span>
                    </div>

                    <!-- NOTES -->
                    <div class="form-group">
                        <label class="form-label">
                            Notes
                            <span class="text-muted" id="hint_notes" style="font-weight:400;font-size:0.8rem">(disponible après la consultation)</span>
                        </label>
                        <textarea name="notes" id="notes" class="form-control"
                            placeholder="Disponible après la date de consultation..."
                            disabled
                            oninput="compter('notes', 'count_notes', 5)"></textarea>
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
// ── AJAX Patient Search ──
const searchInput  = document.getElementById('patientSearch');
const dropdown     = document.getElementById('patientDropdown');
const hiddenInput  = document.getElementById('id_patient');
const badge        = document.getElementById('selectedBadge');
const selectedName = document.getElementById('selectedName');
let searchTimeout  = null;

// Pre-select if returning after a validation error
const preselectedId = parseInt(hiddenInput.value) || 0;
if (preselectedId) {
    fetch('search_patients.php?q=id:' + preselectedId)
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

// ── Sidebar toggle ──
document.querySelector('.sidebar-toggle').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('open');
});

function toggleSubMenu(el) {
    var navItem = el.closest('.nav-item');
    var isOpen  = navItem.classList.contains('open');
    document.querySelectorAll('.nav-item.has-sub.open').forEach(function(item) {
        item.classList.remove('open');
        var sub = item.querySelector('.sub-menu');
        if (sub) sub.classList.remove('open');
    });
    if (!isOpen) {
        navItem.classList.add('open');
        var sub = navItem.querySelector('.sub-menu');
        if (sub) sub.classList.add('open');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.nav-item.has-sub.open').forEach(function (item) {
        var sub = item.querySelector('.sub-menu');
        if (sub) sub.classList.add('open');
    });
});

// ── Character counter ──
function compter(champId, compteurId, minimum) {
    const nb = document.getElementById(champId).value.length;
    const el = document.getElementById(compteurId);
    el.textContent = nb;
    el.style.color = nb >= minimum ? 'green' : 'red';
}

// ── Date logic: enables/disables diagnostique & notes ──
function verifierDate() {
    const dateVal     = document.getElementById('date_consultation').value;
    const diag        = document.getElementById('diagnostique');
    const notes       = document.getElementById('notes');
    const hintDiag    = document.getElementById('hint_diag');
    const hintNotes   = document.getElementById('hint_notes');
    const optTerminee = document.getElementById('opt_terminee');
    const selectStatut = document.getElementById('statut');

    if (!dateVal) {
        diag.disabled = notes.disabled = optTerminee.disabled = true;
        return;
    }

    const isPast = new Date(dateVal) <= new Date();

    if (isPast) {
        diag.disabled = notes.disabled = false;
        optTerminee.disabled = false;
        diag.placeholder  = "Entrez le diagnostique...";
        notes.placeholder = "Entrez les notes...";
        hintDiag.textContent  = "(min. 10 caractères)";
        hintNotes.textContent = "(min. 5 caractères)";
    } else {
        diag.disabled = notes.disabled = true;
        diag.value = notes.value = '';
        optTerminee.disabled = true;
        diag.placeholder  = "Disponible après la date de consultation...";
        notes.placeholder = "Disponible après la date de consultation...";
        hintDiag.textContent  = "(disponible après la consultation)";
        hintNotes.textContent = "(disponible après la consultation)";
        document.getElementById('count_diag').textContent  = '0';
        document.getElementById('count_notes').textContent = '0';
        document.getElementById('suggestions_diag').style.display = 'none';
        if (selectStatut.value === 'terminée') selectStatut.value = '';
    }
}

// ── Dark mode ──
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

// ── AI Diagnostic suggestions ──
function suggererDiagnostique() {
    const q   = document.getElementById('diagnostique').value.trim();
    const box = document.getElementById('suggestions_diag');
    if (q.length < 2) { box.style.display = 'none'; return; }
    fetch('suggest_diagnostic.php?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            if (!data.length) { box.style.display = 'none'; return; }
            box.innerHTML = data.map(s =>
                `<div onclick="choisirSuggestion('${s.replace(/'/g, "\\'").replace(/\n/g, ' ')}')"
                    style="padding:10px 16px;cursor:pointer;font-size:0.9rem;border-bottom:1px solid var(--border);"
                    onmouseover="this.style.background='var(--bg)'"
                    onmouseout="this.style.background=''">
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

// ── Form validation ──
function validerFormulaire() {
    let valide = true;
    document.querySelectorAll('.form-error').forEach(e => e.style.display = 'none');
    document.querySelectorAll('.form-control').forEach(e => e.classList.remove('is-invalid'));

    // Patient
    if (!hiddenInput.value || parseInt(hiddenInput.value) === 0) {
        document.getElementById('err_patient').style.display = 'block';
        searchInput.classList.add('is-invalid');
        valide = false;
    }

    // Date
    const date = document.getElementById('date_consultation').value;
    if (!date || new Date(date) <= new Date()) {
        document.getElementById('date_consultation').classList.add('is-invalid');
        document.getElementById('err_date').style.display = 'block';
        valide = false;
    }

    // Statut
    if (!document.getElementById('statut').value) {
        document.getElementById('statut').classList.add('is-invalid');
        document.getElementById('err_statut').style.display = 'block';
        valide = false;
    }

    // Diag & notes only if date is in the past
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

// Set min date to now
const now = new Date();
document.getElementById('date_consultation').min =
    new Date(now - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);

verifierDate();
</script>
<script src="../../assets/js/language-switcher.js"></script>
</body>
</html>