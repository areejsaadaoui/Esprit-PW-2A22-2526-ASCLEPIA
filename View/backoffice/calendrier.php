<?php
session_start();
require_once '../../config.php';
require_once '../../Controller/ConsultationController.php';

// ── Session info ───────────────────────────────────────────────────────────
$user_id    = $_SESSION['user_id']     ?? null;
$user_role  = $_SESSION['user_role']   ?? 'medecin'; // 'medecin' | 'patient' | 'admin'
$user_nom   = $_SESSION['user_nom']    ?? 'Utilisateur';
$userAvatar = $_SESSION['user_avatar'] ?? 'default';
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin    = ($user_role === 'admin');

// ── Sidebar active-link helpers (used by medecin sidebar) ──────────────────
$cur = basename($_SERVER['PHP_SELF']);
function docActive(...$p){ global $cur; return in_array($cur, $p) ? 'class="active"' : ''; }
function docSub(...$p)   { global $cur; return in_array($cur, $p) ? 'open' : ''; }

// ── Fetch & filter consultations ───────────────────────────────────────────
$controller       = new ConsultationController(config::getConnexion());
$allConsultations = $controller->getAllConsultations();

$consultations = array_filter($allConsultations, function($c) use ($user_id, $user_role) {
    if ($user_role === 'patient') return $c->getIdPatient() === $user_id;
    if ($user_role === 'medecin') return $c->getIdMedecin() === $user_id;
    return true; // admin sees all
});

// ── Build FullCalendar events ──────────────────────────────────────────────
$events = [];
foreach ($consultations as $c) {
    $color = match($c->getStatut()) {
        'planifiée' => '#0ea5e9',
        'terminée'  => '#10b981',
        'annulée'   => '#ef4444',
        default     => '#64748b'
    };
    $label = match($user_role) {
        'patient' => 'Dr. #'        . $c->getIdMedecin(),
        'admin'   => 'P#'           . $c->getIdPatient() . ' / M#' . $c->getIdMedecin(),
        default   => 'Patient #'    . $c->getIdPatient(),
    };
    $diag = $c->getDiagnostique();
    $events[] = [
        'id'         => $c->getIdConsultation(),
        'title'      => $label . ($diag ? ' – ' . substr($diag, 0, 20) . '…' : ''),
        'start'      => $c->getDateConsultation(),
        'color'      => $color,
        'statut'     => $c->getStatut(),
        'diag'       => $diag,
        'notes'      => $c->getNotes(),
        'id_patient' => $c->getIdPatient(),
        'id_medecin' => $c->getIdMedecin(),
    ];
}
$eventCount = count($events);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier - ASCLEPIA</title>

    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dark.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">

    <?php if ($user_role === 'patient'): ?>
        <link rel="stylesheet" href="../../assets/css/frontoffice.css">
    <?php else: ?>
        <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/fr.js"></script>

    <style>
        .fc-toolbar { flex-wrap: wrap; gap: 8px; }
        .fc-event { cursor: pointer; border-radius: 6px !important; border: none !important; padding: 2px 6px; font-size: 0.82rem; }
        .fc-day-grid-event { margin: 2px 4px; }
        .fc-today { background: rgba(14,165,233,0.08) !important; }
        .fc-toolbar-title { font-size: 1.1rem !important; font-weight: 700; }

        .role-banner {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 16px; border-radius: 10px;
            font-size: 0.88rem; font-weight: 600; margin-bottom: 12px;
        }
        .role-banner.medecin { background: rgba(14,165,233,0.1);  color: #0ea5e9; border: 1px solid rgba(14,165,233,0.25); }
        .role-banner.patient { background: rgba(16,185,129,0.1);  color: #10b981; border: 1px solid rgba(16,185,129,0.25); }
        .role-banner.admin   { background: rgba(139,92,246,0.1);  color: #8b5cf6; border: 1px solid rgba(139,92,246,0.25); }

        .legende { display: flex; gap: 16px; flex-wrap: wrap; padding: 12px 0; }
        .legende-item { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; }
        .legende-color { width: 14px; height: 14px; border-radius: 50%; }

        .patient-page { max-width: 1100px; margin: 30px auto; padding: 0 20px; }

        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(15,23,42,0.6); backdrop-filter: blur(4px);
            z-index: 2000; display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: all 0.25s;
        }
        .modal-overlay.open { opacity: 1; pointer-events: all; }
        .modal {
            background: var(--bg-card, white); border-radius: 20px;
            width: 100%; max-width: 500px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            transform: scale(0.95) translateY(20px); transition: all 0.25s; overflow: hidden;
        }
        .modal-overlay.open .modal { transform: scale(1) translateY(0); }
        .modal-header {
            padding: 20px 24px; border-bottom: 1px solid var(--border, #e2e8f0);
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-title { font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .modal-close {
            width: 32px; height: 32px; border-radius: 50%; border: none;
            background: var(--bg, #f0f4f8); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; color: var(--text-muted, #64748b);
        }
        .modal-close:hover { background: #ef4444; color: white; }
        .modal-body  { padding: 20px 24px; }
        .modal-footer {
            padding: 16px 24px; border-top: 1px solid var(--border, #e2e8f0);
            display: flex; justify-content: flex-end; gap: 10px;
        }
        .info-row { display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-start; }
        .info-label { font-size: 0.82rem; color: var(--text-muted, #64748b); font-weight: 600; min-width: 110px; }
        .info-value { font-size: 0.9rem; color: var(--text, #0f172a); }
    </style>
</head>
<body>

<?php if ($user_role === 'patient'): ?>
<!-- ════════════════════════════════════════════════════════════════════════
     PATIENT LAYOUT — top navbar + full-width content
════════════════════════════════════════════════════════════════════════ -->

<nav class="navbar" id="navbar">
    <a href="#" class="navbar-brand">
        <div class="navbar-logo">🏥</div>
        <div class="navbar-name">ASCL<span>EPIA</span></div>
    </a>
    <div class="nav-links" id="navLinks">
        <a href="../front/indexp.php"       class="nav-link">Accueil</a>
        <a href="consultation_patient.php"  class="nav-link active">Mes Consultations</a>
        <a href="ordonnance_patient.php"    class="nav-link">Mes Ordonnances</a>
        <a href="#"                         class="nav-link">Contact</a>
    </div>
    <div class="nav-actions">
        <?php if ($isLoggedIn): ?>
            <div class="nav-user-info">
                <div class="avatar-css small avatar-<?= htmlspecialchars($userAvatar) ?>"></div>
                <span><?= htmlspecialchars($user_nom) ?></span>
            </div>
            <?php if ($isAdmin): ?>
                <a href="../back/dashboard.php" class="btn btn-outline-white btn-sm">
                    <i class="fa-solid fa-gauge"></i> Admin
                </a>
            <?php endif; ?>
            <a href="../back/logout.php" class="btn btn-outline-white btn-sm">
                <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
            </a>
        <?php else: ?>
            <a href="login.html"     class="btn btn-outline-white btn-sm">Connexion</a>
            <a href="loginuser.html" class="btn btn-primary btn-sm">S'inscrire</a>
        <?php endif; ?>
    </div>
    <div class="hamburger" onclick="document.getElementById('navLinks').classList.toggle('open')">
        <span></span><span></span><span></span>
    </div>
</nav>

<div class="patient-page">
    <div class="role-banner patient">
        <i class="fa-solid fa-user"></i>
        Vos rendez-vous personnels — <?= $eventCount ?> consultation(s) trouvée(s)
    </div>

    <div class="card mb-3">
        <div style="padding:16px;">
            <div class="legende">
                <div class="legende-item"><div class="legende-color" style="background:#0ea5e9;"></div><span>Planifiée</span></div>
                <div class="legende-item"><div class="legende-color" style="background:#10b981;"></div><span>Terminée</span></div>
                <div class="legende-item"><div class="legende-color" style="background:#ef4444;"></div><span>Annulée</span></div>
                <div style="margin-left:auto;font-size:0.85rem;color:var(--text-muted);">
                    <i class="fa-solid fa-hand-pointer"></i> Cliquez pour voir les détails
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div style="padding:20px;">
            <div id="calendrier"></div>
        </div>
    </div>
</div>


<?php else: ?>
<!-- ════════════════════════════════════════════════════════════════════════
     DOCTOR / ADMIN LAYOUT — sidebar + main content
════════════════════════════════════════════════════════════════════════ -->

<div class="admin-wrapper">

    <aside class="sidebar">
        <a href="#" class="sidebar-brand">
            <div class="sidebar-logo">🏥</div>
            <div class="sidebar-title">ASCL<span>EPIA</span></div>
        </a>
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($user_nom, 0, 1)) ?></div>
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($user_nom) ?></div>
                <div class="role">Médecin</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Principal</div>
            <div class="nav-item">
                <a href="indexd.php" <?= docActive('indexd.php') ?>>
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>

            <div class="nav-section-label">Activité</div>

            <div class="nav-item has-sub <?= docSub('../backoffice/list_consultation.php','../backoffice/add_consultation.php','../backoffice/edit_consultation.php','../backoffice/calendrier.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= docActive('../backoffice/list_consultation.php','../backoffice/add_consultation.php','../backoffice/edit_consultation.php') ?>>
                    <i class="fa-solid fa-stethoscope nav-icon"></i>
                    <span>Consultations</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= docSub('../backoffice/list_consultation.php','../backoffice/add_consultation.php','../backoffice/edit_consultation.php') ?>">
                    <a href="../backoffice/list_consultation.php" <?= docActive('../backoffice/list_consultation.php') ?>>
                        <i class="fa-solid fa-list"></i> Toutes les consultations
                    </a>
                    <a href="../backoffice/add_consultation.php" <?= docActive('../backoffice/add_consultation.php') ?>>
                        <i class="fa-solid fa-plus"></i> Nouvelle consultation
                    </a>
                </div>
            </div>

            <div class="nav-item has-sub <?= docSub('../backoffice/list_ordonnance.php','../backoffice/add_ordonnance.php','../backoffice/edit_ordonnance.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= docActive('../backoffice/list_ordonnance.php','../backoffice/add_ordonnance.php','../backoffice/edit_ordonnance.php') ?>>
                    <i class="fa-solid fa-file-prescription nav-icon"></i>
                    <span>Ordonnances</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= docSub('../backoffice/list_ordonnance.php','../backoffice/add_ordonnance.php','../backoffice/edit_ordonnance.php') ?>">
                    <a href="../backoffice/list_ordonnance.php" <?= docActive('../backoffice/list_ordonnance.php') ?>>
                        <i class="fa-solid fa-list"></i> Toutes les ordonnances
                    </a>
                    <a href="../backoffice/add_ordonnance.php" <?= docActive('../backoffice/add_ordonnance.php') ?>>
                        <i class="fa-solid fa-plus"></i> Nouvelle ordonnance
                    </a>
                </div>
            </div>

            <div class="nav-item">
                <a href="../backoffice/calendrier.php" <?= docActive('../backoffice/calendrier.php','calendrier.php') ?>>
                    <i class="fa-solid fa-calendar-days nav-icon"></i>
                    <span>Calendrier</span>
                </a>
            </div>

            <div class="nav-section-label">Autre</div>
            <div class="nav-item">
                <a href="indexp.php">
                    <i class="fas fa-globe nav-icon"></i>
                    <span>Espace patient</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="login.php">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </nav>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <div class="page-title">Calendrier des consultations</div>
                    <div class="breadcrumb">
                        <a href="dashboard.php">Dashboard</a>
                        <span>/</span>
                        <span>Calendrier</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <button class="dark-toggle" onclick="toggleDark()" id="darkBtn" title="Mode sombre">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <a href="add_consultation.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Nouvelle consultation
                </a>
            </div>
        </div>

        <div class="page-content">
            <div class="role-banner <?= $user_role === 'admin' ? 'admin' : 'medecin' ?>">
                <?php if ($user_role === 'admin'): ?>
                    <i class="fa-solid fa-shield-halved"></i>
                    Vue administrateur — toutes les consultations (<?= $eventCount ?>)
                <?php else: ?>
                    <i class="fa-solid fa-stethoscope"></i>
                    Votre agenda médical — <?= $eventCount ?> consultation(s) assignée(s)
                <?php endif; ?>
            </div>

            <div class="card mb-3">
                <div style="padding:16px;">
                    <div class="legende">
                        <div class="legende-item"><div class="legende-color" style="background:#0ea5e9;"></div><span>Planifiée</span></div>
                        <div class="legende-item"><div class="legende-color" style="background:#10b981;"></div><span>Terminée</span></div>
                        <div class="legende-item"><div class="legende-color" style="background:#ef4444;"></div><span>Annulée</span></div>
                        <div style="margin-left:auto;font-size:0.85rem;color:var(--text-muted);">
                            <i class="fa-solid fa-hand-pointer"></i> Cliquez sur une consultation pour voir les détails
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div style="padding:20px;">
                    <div id="calendrier"></div>
                </div>
            </div>
        </div>
    </div>
</div><!-- /admin-wrapper -->

<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════════════════
     MODAL — shared by both layouts
════════════════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fa-solid fa-calendar-check" style="color:var(--primary)"></i>
                Détail de la consultation
            </div>
            <button class="modal-close" onclick="fermerModal()">✕</button>
        </div>
        <div class="modal-body">
            <div class="info-row">
                <div class="info-label"><i class="fa-solid fa-hashtag"></i> ID</div>
                <div class="info-value" id="modalId"></div>
            </div>
            <div class="info-row">
                <div class="info-label"><i class="fa-regular fa-clock"></i> Date</div>
                <div class="info-value" id="modalDate"></div>
            </div>
            <div class="info-row">
                <div class="info-label"><i class="fa-solid fa-circle"></i> Statut</div>
                <div class="info-value" id="modalStatut"></div>
            </div>
            <div class="info-row">
                <div class="info-label" id="labelContexte"></div>
                <div class="info-value"  id="modalContexte"></div>
            </div>
            <div class="info-row">
                <div class="info-label"><i class="fa-solid fa-stethoscope"></i> Diagnostique</div>
                <div class="info-value" id="modalDiag"></div>
            </div>
            <div class="info-row">
                <div class="info-label"><i class="fa-solid fa-notes-medical"></i> Notes</div>
                <div class="info-value" id="modalNotes"></div>
            </div>
        </div>
        <div class="modal-footer">
            <?php if ($user_role !== 'patient'): ?>
            <a href="#" id="modalEditBtn" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-pen"></i> Modifier
            </a>
            <?php endif; ?>
            <button onclick="fermerModal()" class="btn btn-primary btn-sm">Fermer</button>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════════════
     SCRIPTS — shared
════════════════════════════════════════════════════════════════════════ -->
<script>
    const userRole = <?= json_encode($user_role) ?>;
    const events   = <?= json_encode(array_values($events)) ?>;

    // ── FullCalendar ──────────────────────────────────────────────────────
    $(document).ready(function () {
        $('#calendrier').fullCalendar({
            locale: 'fr',
            header: {
                left:   'prev,next today',
                center: 'title',
                right:  'month,agendaWeek,agendaDay'
            },
            buttonText: {
                today: "Aujourd'hui",
                month: 'Mois',
                week:  'Semaine',
                day:   'Jour'
            },
            events: events,
            eventClick: function (event) { ouvrirModal(event); },
            dayClick: function () {
                if (userRole === 'medecin' || userRole === 'admin') {
                    window.location.href = 'add_consultation.php';
                }
            },
            eventRender: function (event, element) {
                element.attr('title', event.title);
            },
            height: 650,
            editable: false,
            eventLimit: true,
            eventLimitText: 'voir plus'
        });
    });

    // ── Modal ─────────────────────────────────────────────────────────────
    function ouvrirModal(event) {
        const statuts = {
            'planifiée': '<span class="badge badge-primary"><i class="fa-solid fa-clock"></i> Planifiée</span>',
            'terminée':  '<span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Terminée</span>',
            'annulée':   '<span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> Annulée</span>'
        };

        document.getElementById('modalId').textContent    = '#' + event.id;
        document.getElementById('modalDate').textContent  = event.start.format('DD/MM/YYYY HH:mm');
        document.getElementById('modalStatut').innerHTML  = statuts[event.statut] || event.statut;
        document.getElementById('modalDiag').textContent  = event.diag  || 'Non renseigné';
        document.getElementById('modalNotes').textContent = event.notes || 'Non renseigné';

        const ctxLabel = document.getElementById('labelContexte');
        const ctxValue = document.getElementById('modalContexte');
        if (userRole === 'patient') {
            ctxLabel.innerHTML   = '<i class="fa-solid fa-user-doctor"></i> Médecin';
            ctxValue.textContent = 'Dr. #' + (event.id_medecin || '—');
        } else if (userRole === 'medecin') {
            ctxLabel.innerHTML   = '<i class="fa-solid fa-user"></i> Patient';
            ctxValue.textContent = 'Patient #' + (event.id_patient || '—');
        } else {
            ctxLabel.innerHTML   = '<i class="fa-solid fa-link"></i> Participants';
            ctxValue.textContent = 'Patient #' + event.id_patient + ' / Dr. #' + event.id_medecin;
        }

        const editBtn = document.getElementById('modalEditBtn');
        if (editBtn) editBtn.href = 'edit_consultation.php?id=' + event.id;

        document.getElementById('modalOverlay').classList.add('open');
    }

    function fermerModal() {
        document.getElementById('modalOverlay').classList.remove('open');
    }

    document.getElementById('modalOverlay').addEventListener('click', function (e) {
        if (e.target === this) fermerModal();
    });

    // ── Dark mode (backoffice only) ───────────────────────────────────────
    function toggleDark() {
        document.body.classList.toggle('dark-mode');
        const isDark = document.body.classList.contains('dark-mode');
        const btn = document.getElementById('darkBtn');
        if (btn) btn.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
        localStorage.setItem('darkMode', isDark);
    }
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        const btn = document.getElementById('darkBtn');
        if (btn) btn.innerHTML = '<i class="fa-solid fa-sun"></i>';
    }

    // ── Sidebar toggle (backoffice only) ──────────────────────────────────
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            document.querySelector('.sidebar').classList.toggle('open');
        });
    }

    // ── Submenu toggle (backoffice only) ──────────────────────────────────
    function toggleSubMenu(el) {
        el.closest('.nav-item').classList.toggle('open');
    }
</script>
<script src="../../assets/js/language-switcher.js"></script>
</body>
</html>