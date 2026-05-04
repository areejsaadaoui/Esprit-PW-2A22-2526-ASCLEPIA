<?php
require_once '../../config/db.php';
require_once '../../controllers/ConsultationController.php';

$controller = new ConsultationController($pdo);
$consultations = $controller->getAllConsultations();

// Préparer les données pour le calendrier
$events = [];
foreach ($consultations as $c) {
    $color = match($c->getStatut()) {
        'planifiée' => '#0ea5e9',
        'terminée'  => '#10b981',
        'annulée'   => '#ef4444',
        default     => '#64748b'
    };
    $events[] = [
        'id'    => $c->getIdConsultation(),
        'title' => '#' . $c->getIdConsultation() . ' - ' . ($c->getDiagnostique() ? substr($c->getDiagnostique(), 0, 20) . '...' : 'Planifiée'),
        'start' => $c->getDateConsultation(),
        'color' => $color,
        'statut' => $c->getStatut(),
        'diag'  => $c->getDiagnostique(),
        'notes' => $c->getNotes(),
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="../../assets/css/dark.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
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
        .legende { display: flex; gap: 16px; flex-wrap: wrap; padding: 12px 0; }
        .legende-item { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; }
        .legende-color { width: 14px; height: 14px; border-radius: 50%; }

        /* Modal */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(15,23,42,0.6);
            backdrop-filter: blur(4px);
            z-index: 2000;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: all 0.25s;
        }
        .modal-overlay.open { opacity: 1; pointer-events: all; }
        .modal {
            background: var(--bg-card, white);
            border-radius: 20px;
            width: 100%; max-width: 500px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            transform: scale(0.95) translateY(20px);
            transition: all 0.25s;
            overflow: hidden;
        }
        .modal-overlay.open .modal { transform: scale(1) translateY(0); }
        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border, #e2e8f0);
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-title { font-size: 1rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .modal-close {
            width: 32px; height: 32px; border-radius: 50%;
            border: none; background: var(--bg, #f0f4f8);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            font-size: 1rem; color: var(--text-muted, #64748b);
        }
        .modal-close:hover { background: #ef4444; color: white; }
        .modal-body { padding: 20px 24px; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--border, #e2e8f0); display: flex; justify-content: flex-end; gap: 10px; }
        .info-row { display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-start; }
        .info-label { font-size: 0.82rem; color: var(--text-muted, #64748b); font-weight: 600; min-width: 100px; }
        .info-value { font-size: 0.9rem; color: var(--text, #0f172a); }
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
            <div class="user-avatar">A</div>
            <div class="user-info">
                <div class="name">Ala</div>
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
            <div class="nav-item">
                <a href="calendrier.php" class="active">
                    <span class="nav-icon"><i class="fa-solid fa-calendar"></i></span>
                    Calendrier
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

            <!-- LÉGENDE -->
            <div class="card mb-3">
                <div style="padding:16px;">
                    <div class="legende">
                        <div class="legende-item">
                            <div class="legende-color" style="background:#0ea5e9;"></div>
                            <span>Planifiée</span>
                        </div>
                        <div class="legende-item">
                            <div class="legende-color" style="background:#10b981;"></div>
                            <span>Terminée</span>
                        </div>
                        <div class="legende-item">
                            <div class="legende-color" style="background:#ef4444;"></div>
                            <span>Annulée</span>
                        </div>
                        <div style="margin-left:auto; font-size:0.85rem; color:var(--text-muted);">
                            <i class="fa-solid fa-hand-pointer"></i> Cliquez sur une consultation pour voir les détails
                        </div>
                    </div>
                </div>
            </div>

            <!-- CALENDRIER -->
            <div class="card">
                <div style="padding:20px;">
                    <div id="calendrier"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL DÉTAIL -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title" id="modalTitle">
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
                <div class="info-label"><i class="fa-solid fa-stethoscope"></i> Diagnostique</div>
                <div class="info-value" id="modalDiag"></div>
            </div>
            <div class="info-row">
                <div class="info-label"><i class="fa-solid fa-notes-medical"></i> Notes</div>
                <div class="info-value" id="modalNotes"></div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="#" id="modalEditBtn" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-pen"></i> Modifier
            </a>
            <button onclick="fermerModal()" class="btn btn-primary btn-sm">Fermer</button>
        </div>
    </div>
</div>

<script>
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });

    // MODE SOMBRE
    function toggleDark() {
        document.body.classList.toggle('dark-mode');
        const btn = document.getElementById('darkBtn');
        const isDark = document.body.classList.contains('dark-mode');
        btn.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
        localStorage.setItem('darkMode', isDark);
    }

    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        document.getElementById('darkBtn').innerHTML = '<i class="fa-solid fa-sun"></i>';
    }

    // ÉVÉNEMENTS
    const events = <?= json_encode($events) ?>;

    // CALENDRIER
    $(document).ready(function() {
        $('#calendrier').fullCalendar({
            locale: 'fr',
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            buttonText: {
                today: "Aujourd'hui",
                month: 'Mois',
                week: 'Semaine',
                day: 'Jour'
            },
            events: events,
            eventClick: function(event) {
                ouvrirModal(event);
            },
            dayClick: function(date) {
                window.location.href = 'add_consultation.php';
            },
            eventRender: function(event, element) {
                element.attr('title', event.title);
            },
            height: 650,
            editable: false,
            eventLimit: true,
            eventLimitText: 'voir plus'
        });
    });

    // MODAL
    function ouvrirModal(event) {
        const statuts = {
            'planifiée': '<span class="badge badge-primary"><i class="fa-solid fa-clock"></i> Planifiée</span>',
            'terminée': '<span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Terminée</span>',
            'annulée': '<span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> Annulée</span>'
        };

        document.getElementById('modalId').textContent = '#' + event.id;
        document.getElementById('modalDate').textContent = event.start.format('DD/MM/YYYY HH:mm');
        document.getElementById('modalStatut').innerHTML = statuts[event.statut] || event.statut;
        document.getElementById('modalDiag').textContent = event.diag || 'Non renseigné';
        document.getElementById('modalNotes').textContent = event.notes || 'Non renseigné';
        document.getElementById('modalEditBtn').href = 'edit_consultation.php?id=' + event.id;

        document.getElementById('modalOverlay').classList.add('open');
    }

    function fermerModal() {
        document.getElementById('modalOverlay').classList.remove('open');
    }

    document.getElementById('modalOverlay').addEventListener('click', function(e) {
        if (e.target === this) fermerModal();
    });
</script>
</body>
</html>