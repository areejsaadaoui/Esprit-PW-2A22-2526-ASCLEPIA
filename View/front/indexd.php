<?php
session_start();

// ── Auth ──────────────────────────────────────────────────
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'medecin') {
    header('Location: login.html');
    exit();
}

require_once '../../config.php';

$pdo        = config::getConnexion();
$medecinId  = (int)($_SESSION['user_id'] ?? 0);
$medecinNom = $_SESSION['user_nom']   ?? 'Docteur';
$medecinEmail = $_SESSION['user_email'] ?? '';
$today      = date('Y-m-d');
$heure      = (int)date('H');
$salutation = $heure < 12 ? 'Bonjour' : ($heure < 18 ? 'Bon après-midi' : 'Bonsoir');

// ── Stats ─────────────────────────────────────────────────
$q = fn(string $sql, array $p = []) => (function() use ($pdo, $sql, $p) {
    $s = $pdo->prepare($sql); $s->execute($p); return $s;
})();

$totalConsultations = $q("SELECT COUNT(*) FROM consultation WHERE id_medecin=?", [$medecinId])->fetchColumn();
$totalPlanifiees    = $q("SELECT COUNT(*) FROM consultation WHERE id_medecin=? AND statut='planifiée'",  [$medecinId])->fetchColumn();
$totalTerminees     = $q("SELECT COUNT(*) FROM consultation WHERE id_medecin=? AND statut='terminée'",   [$medecinId])->fetchColumn();
$totalOrdonnances   = $q(
    "SELECT COUNT(*) FROM ordonnance o JOIN consultation c ON o.id_consultation=c.id_consultation WHERE c.id_medecin=?",
    [$medecinId]
)->fetchColumn();

// ── Today's consultations ─────────────────────────────────
$todayList = $q(
    "SELECT * FROM consultation WHERE id_medecin=? AND DATE(date_consultation)=? ORDER BY date_consultation ASC",
    [$medecinId, $today]
)->fetchAll(PDO::FETCH_ASSOC);

// ── Upcoming consultations (planifiée, future) ────────────
$upcomingList = $q(
    "SELECT * FROM consultation WHERE id_medecin=? AND statut='planifiée' AND date_consultation >= NOW()
     ORDER BY date_consultation ASC LIMIT 8",
    [$medecinId]
)->fetchAll(PDO::FETCH_ASSOC);

// ── Recent ordonnances ────────────────────────────────────
$recentOrdos = $q(
    "SELECT o.*, c.date_consultation, c.diagnostique FROM ordonnance o
     JOIN consultation c ON o.id_consultation=c.id_consultation
     WHERE c.id_medecin=? ORDER BY o.date_creation DESC LIMIT 5",
    [$medecinId]
)->fetchAll(PDO::FETCH_ASSOC);

// ── Calendar events (JSON for mini-agenda) ────────────────
$allEvents = $q(
    "SELECT id_consultation, date_consultation, statut, diagnostique FROM consultation
     WHERE id_medecin=? AND MONTH(date_consultation)=MONTH(NOW()) AND YEAR(date_consultation)=YEAR(NOW())",
    [$medecinId]
)->fetchAll(PDO::FETCH_ASSOC);

$colorMap = ['planifiée'=>'#0ea5e9','terminée'=>'#10b981','annulée'=>'#ef4444'];
$calEvents = array_map(fn($c) => [
    'id'    => $c['id_consultation'],
    'start' => $c['date_consultation'],
    'color' => $colorMap[$c['statut']] ?? '#64748b',
    'title' => '#'.$c['id_consultation'].' '.substr($c['diagnostique']??'',0,18),
], $allEvents);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Espace Médecin – ASCLEPIA</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/backoffice.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/fr.js"></script>
<style>
/* ── HERO BANNER ───────────────────────────────────────── */
.hero-banner {
    background: var(--gradient-hero);
    border-radius: var(--radius-lg);
    padding: 28px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}
.hero-banner::before {
    content:'';
    position:absolute; inset:0;
    background: url("data:image/svg+xml,%3Csvg width='400' height='200' viewBox='0 0 400 200' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='360' cy='40' r='120' fill='rgba(255,255,255,0.04)'/%3E%3Ccircle cx='60' cy='180' r='80' fill='rgba(255,255,255,0.03)'/%3E%3C/svg%3E") no-repeat right center;
    pointer-events:none;
}
.hero-text { position:relative; z-index:1; }
.hero-greeting {
    font-size: 0.88rem;
    color: rgba(255,255,255,0.6);
    font-weight: 500;
    margin-bottom: 6px;
    display:flex; align-items:center; gap:8px;
}
.hero-name {
    font-size: clamp(1.4rem, 3vw, 1.9rem);
    font-weight: 800;
    color: #fff;
    margin-bottom: 8px;
}
.hero-name span { color: #7dd3fc; }
.hero-sub {
    font-size: 0.88rem;
    color: rgba(255,255,255,0.55);
}
.hero-actions { display:flex; gap:10px; flex-shrink:0; position:relative; z-index:1; flex-wrap:wrap; }
.hero-badge {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: var(--radius-full);
    padding: 6px 14px;
    font-size: 0.8rem;
    color: rgba(255,255,255,0.85);
    display:flex; align-items:center; gap:6px;
}
.hero-badge i { color: #7dd3fc; }
.hero-icon {
    font-size: 4rem;
    opacity: 0.15;
    position: absolute;
    right: 180px; top: 50%;
    transform: translateY(-50%);
}

/* ── QUICK ACTIONS ─────────────────────────────────────── */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}
.quick-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px;
    display:flex; align-items:center; gap:16px;
    text-decoration: none;
    color: var(--dark);
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    position:relative; overflow:hidden;
}
.quick-card::after {
    content:'';
    position:absolute; bottom:0; left:0; right:0;
    height: 3px;
    background: var(--gradient-primary);
    transform: scaleX(0);
    transition: var(--transition);
}
.quick-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow);
    color: var(--dark);
    border-color: var(--primary);
}
.quick-card:hover::after { transform: scaleX(1); }
.quick-card:hover .quick-icon { transform: scale(1.1) rotate(-5deg); }
.quick-icon {
    width: 48px; height: 48px;
    border-radius: var(--radius);
    display:flex; align-items:center; justify-content:center;
    font-size: 1.3rem;
    flex-shrink:0;
    transition: var(--transition);
}
.quick-icon.blue   { background:#eff6ff; color:#2563eb; }
.quick-icon.green  { background:#f0fdf4; color:#16a34a; }
.quick-icon.purple { background:#faf5ff; color:#9333ea; }
.quick-icon.orange { background:#fff7ed; color:#ea580c; }
.quick-label { font-size: 0.82rem; color:var(--text-muted); }
.quick-title { font-size: 0.95rem; font-weight: 700; }

/* ── GRID LAYOUT ───────────────────────────────────────── */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
    align-items: start;
}
@media (max-width: 1100px) {
    .dashboard-grid { grid-template-columns: 1fr; }
}

/* ── SECTION CARD ──────────────────────────────────────── */
.section-card {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.section-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid var(--border);
    display:flex; align-items:center; justify-content:space-between;
}
.section-card-title {
    font-size: 0.97rem; font-weight: 700;
    display:flex; align-items:center; gap:10px; color:var(--dark);
}
.section-card-title i { color: var(--primary); }
.section-card-body { padding: 20px 24px; }

/* ── TABLE ─────────────────────────────────────────────── */
.dash-table { width:100%; border-collapse:collapse; font-size:0.88rem; }
.dash-table thead th {
    padding: 10px 14px;
    background: var(--dark);
    color: rgba(255,255,255,0.8);
    font-size:0.78rem; font-weight:600;
    text-transform:uppercase; letter-spacing:0.05em;
    white-space:nowrap;
}
.dash-table tbody tr {
    border-bottom: 1px solid var(--border);
    transition: var(--transition-fast);
}
.dash-table tbody tr:last-child { border-bottom:none; }
.dash-table tbody tr:hover { background: rgba(14,165,233,0.04); }
.dash-table td { padding: 12px 14px; color:var(--text); vertical-align:middle; }
.time-pill {
    background: var(--primary-light);
    color: var(--primary-dark);
    font-size:0.78rem; font-weight:600;
    padding:3px 10px; border-radius:var(--radius-full);
    white-space:nowrap;
}

/* ── TODAY ROW ─────────────────────────────────────────── */
.today-item {
    display:flex; align-items:center; gap:14px;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
}
.today-item:last-child { border-bottom:none; }
.today-time {
    font-size:0.8rem; font-weight:700; color:var(--primary);
    min-width:52px; text-align:center;
    background: var(--primary-light);
    padding:4px 8px; border-radius:var(--radius-sm);
}
.today-info { flex:1; }
.today-title { font-size:0.88rem; font-weight:600; color:var(--dark); }
.today-sub { font-size:0.78rem; color:var(--text-muted); margin-top:2px; }

/* ── ORDO LIST ─────────────────────────────────────────── */
.ordo-item {
    display:flex; align-items:center; gap:14px;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
}
.ordo-item:last-child { border-bottom:none; }
.ordo-icon {
    width:38px; height:38px; border-radius:var(--radius-sm);
    background:#faf5ff; color:#9333ea;
    display:flex; align-items:center; justify-content:center;
    font-size:1rem; flex-shrink:0;
}
.ordo-title { font-size:0.88rem; font-weight:600; color:var(--dark); }
.ordo-sub { font-size:0.78rem; color:var(--text-muted); margin-top:2px; }

/* ── MINI CALENDAR ─────────────────────────────────────── */
#miniCalendar .fc-toolbar { padding:0; margin-bottom:10px; }
#miniCalendar .fc-toolbar-title, 
#miniCalendar .fc-center h2 { font-size:0.9rem !important; font-weight:700; }
#miniCalendar .fc-button { padding:3px 8px; font-size:0.75rem; }
#miniCalendar .fc-event { font-size:0.7rem; padding:1px 4px; }
#miniCalendar .fc-today { background: rgba(14,165,233,0.08) !important; }
#miniCalendar .fc-day-number { font-size:0.78rem; padding:4px !important; }
#miniCalendar .fc-head th { font-size:0.72rem; }

/* ── EMPTY STATE ───────────────────────────────────────── */
.dash-empty {
    text-align:center; padding:32px 20px; color:var(--text-muted);
}
.dash-empty i { font-size:2.2rem; opacity:0.25; margin-bottom:10px; display:block; }
.dash-empty p { font-size:0.85rem; }

/* ── STAT ROW ──────────────────────────────────────────── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width:900px) { .stats-row { grid-template-columns: repeat(2,1fr); } }
@media (max-width:480px) { .stats-row { grid-template-columns: 1fr; } }

/* ── DARK TOGGLE ───────────────────────────────────────── */
.dark-toggle {
    width:38px; height:38px; border-radius:50%;
    border:1px solid var(--border); background:var(--white);
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:var(--gray); font-size:1rem;
    transition:var(--transition-fast);
}
.dark-toggle:hover { background:var(--dark); color:#facc15; border-color:var(--dark); }

/* ── LEGENDE ───────────────────────────────────────────── */
.cal-legend {
    display:flex; gap:12px; flex-wrap:wrap;
    padding: 12px 24px;
    border-top: 1px solid var(--border);
}
.cal-legend-item { display:flex; align-items:center; gap:6px; font-size:0.76rem; color:var(--text-muted); }
.cal-legend-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
</style>
</head>
<body>
<div class="admin-wrapper">

<!-- ════════════════════════ SIDEBAR ════════════════════════ -->
<aside class="sidebar">
    <a href="#" class="sidebar-brand">
        <div class="sidebar-logo">🏥</div>
        <div class="sidebar-title">ASCL<span>EPIA</span></div>
    </a>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($medecinNom, 0, 2)) ?></div>
        <div class="user-info">
            <div class="name"><?= htmlspecialchars($medecinNom) ?></div>
            <div class="role">Médecin</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php
        $cur = basename($_SERVER['PHP_SELF']);
        function docActive(...$p){ global $cur; return in_array($cur,$p)?'class="active"':''; }
        function docSub(...$p){ global $cur; return in_array($cur,$p)?'open':''; }
        ?>

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
            <a href="../backoffice/calendrier.php" <?= docActive('../backoffice/calendrier.php') ?>>
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

    <div class="sidebar-footer">
        <div class="sidebar-version">Version 1.0 – Médecin</div>
    </div>
</aside>

<!-- ════════════════════════ MAIN ════════════════════════ -->
<div class="main-content">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
            <div>
                <div class="page-title">Espace Médecin</div>
                <div class="breadcrumb">
                    <span><?= date('l d F Y') ?></span>
                </div>
            </div>
        </div>
        <div class="topbar-right">
            <button class="dark-toggle" onclick="toggleDark()" id="darkBtn" title="Mode sombre">
                <i class="fa-solid fa-moon"></i>
            </button>
            <a href="../backoffice/add_consultation.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> Nouvelle consultation
            </a>
        </div>
    </div>

    <div class="page-content">

        <!-- ── HERO BANNER ──────────────────────────────────────── -->
        <div class="hero-banner">
            <div class="hero-text">
                <div class="hero-greeting">
                    <i class="fa-solid fa-hand-wave"></i>
                    <?= $salutation ?>, Docteur
                </div>
                <div class="hero-name">
                    Dr. <span><?= htmlspecialchars($medecinNom) ?></span>
                </div>
                <div class="hero-sub">
                    <?php if (count($todayList) > 0): ?>
                        Vous avez <strong style="color:#7dd3fc"><?= count($todayList) ?> consultation(s)</strong> aujourd'hui.
                    <?php else: ?>
                        Aucune consultation planifiée pour aujourd'hui.
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-actions">
                <div class="hero-badge">
                    <i class="fa-regular fa-clock"></i>
                    <?= date('H:i') ?>
                </div>
                <div class="hero-badge">
                    <i class="fa-solid fa-calendar-day"></i>
                    <?= count($upcomingList) ?> à venir
                </div>
            </div>
            <i class="fa-solid fa-user-doctor hero-icon"></i>
        </div>

        <!-- ── STAT CARDS ──────────────────────────────────────── -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-card-icon blue"><i class="fa-solid fa-stethoscope"></i></div>
                <div class="stat-card-body">
                    <div class="stat-card-value"><?= $totalConsultations ?></div>
                    <div class="stat-card-label">Total consultations</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon cyan"><i class="fa-solid fa-clock"></i></div>
                <div class="stat-card-body">
                    <div class="stat-card-value"><?= $totalPlanifiees ?></div>
                    <div class="stat-card-label">Planifiées</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon green"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-card-body">
                    <div class="stat-card-value"><?= $totalTerminees ?></div>
                    <div class="stat-card-label">Terminées</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon purple"><i class="fa-solid fa-file-prescription"></i></div>
                <div class="stat-card-body">
                    <div class="stat-card-value"><?= $totalOrdonnances ?></div>
                    <div class="stat-card-label">Ordonnances</div>
                </div>
            </div>
        </div>

        <!-- ── QUICK ACTIONS ──────────────────────────────────── -->
        <div class="quick-actions">
            <a href="../backoffice/add_consultation.php" class="quick-card">
                <div class="quick-icon blue"><i class="fa-solid fa-plus"></i></div>
                <div>
                    <div class="quick-label">Créer</div>
                    <div class="quick-title">Nouvelle consultation</div>
                </div>
            </a>
            <a href="../backoffice/add_ordonnance.php" class="quick-card">
                <div class="quick-icon green"><i class="fa-solid fa-file-medical"></i></div>
                <div>
                    <div class="quick-label">Rédiger</div>
                    <div class="quick-title">Nouvelle ordonnance</div>
                </div>
            </a>
            <a href="../backoffice/calendrier.php" class="quick-card">
                <div class="quick-icon purple"><i class="fa-solid fa-calendar-days"></i></div>
                <div>
                    <div class="quick-label">Voir</div>
                    <div class="quick-title">Mon calendrier</div>
                </div>
            </a>
            <a href="../backoffice/list_consultation.php" class="quick-card">
                <div class="quick-icon orange"><i class="fa-solid fa-list-ul"></i></div>
                <div>
                    <div class="quick-label">Gérer</div>
                    <div class="quick-title">Mes consultations</div>
                </div>
            </a>
        </div>

        <!-- ── MAIN GRID ───────────────────────────────────────── -->
        <div class="dashboard-grid">

            <!-- LEFT COLUMN -->
            <div style="display:flex; flex-direction:column; gap:24px;">

                <!-- Upcoming consultations -->
                <div class="section-card">
                    <div class="section-card-header">
                        <div class="section-card-title">
                            <i class="fa-solid fa-calendar-clock"></i>
                            Consultations à venir
                        </div>
                        <a href="../backoffice/list_consultation.php" class="btn btn-outline btn-sm">Voir tout</a>
                    </div>
                    <div style="overflow-x:auto;">
                        <?php if (empty($upcomingList)): ?>
                            <div class="dash-empty">
                                <i class="fa-solid fa-calendar-xmark"></i>
                                <p>Aucune consultation planifiée.</p>
                            </div>
                        <?php else: ?>
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date &amp; Heure</th>
                                    <th>Diagnostique</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingList as $c): ?>
                                <tr>
                                    <td><strong>#<?= $c['id_consultation'] ?></strong></td>
                                    <td>
                                        <span class="time-pill">
                                            <i class="fa-regular fa-clock"></i>
                                            <?= date('d/m/Y', strtotime($c['date_consultation'])) ?>
                                            <?= date('H:i', strtotime($c['date_consultation'])) ?>
                                        </span>
                                    </td>
                                    <td style="max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <?= htmlspecialchars($c['diagnostique'] ?: '—') ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <i class="fa-solid fa-clock"></i> Planifiée
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../backoffice/edit_consultation.php?id=<?= $c['id_consultation'] ?>"
                                           class="btn btn-outline btn-sm">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Today's schedule -->
                <div class="section-card">
                    <div class="section-card-header">
                        <div class="section-card-title">
                            <i class="fa-solid fa-sun"></i>
                            Programme du jour — <?= date('d/m/Y') ?>
                        </div>
                        <span class="badge badge-primary"><?= count($todayList) ?> consultation(s)</span>
                    </div>
                    <div class="section-card-body">
                        <?php if (empty($todayList)): ?>
                            <div class="dash-empty">
                                <i class="fa-solid fa-mug-hot"></i>
                                <p>Journée libre — aucune consultation aujourd'hui.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($todayList as $c):
                                $badgeClass = match($c['statut']) {
                                    'planifiée' => 'badge-primary',
                                    'terminée'  => 'badge-success',
                                    'annulée'   => 'badge-danger',
                                    default     => 'badge-gray'
                                };
                            ?>
                            <div class="today-item">
                                <div class="today-time"><?= date('H:i', strtotime($c['date_consultation'])) ?></div>
                                <div class="today-info">
                                    <div class="today-title">
                                        Consultation #<?= $c['id_consultation'] ?>
                                        <?php if ($c['diagnostique']): ?>
                                            — <?= htmlspecialchars(substr($c['diagnostique'], 0, 35)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="today-sub">
                                        <?php if ($c['notes']): ?>
                                            <i class="fa-solid fa-note-sticky"></i>
                                            <?= htmlspecialchars(substr($c['notes'], 0, 50)) ?>
                                        <?php else: ?>
                                            Pas de notes
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($c['statut']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /LEFT COLUMN -->

            <!-- RIGHT COLUMN -->
            <div style="display:flex; flex-direction:column; gap:24px;">

                <!-- Mini Calendar -->
                <div class="section-card">
                    <div class="section-card-header">
                        <div class="section-card-title">
                            <i class="fa-solid fa-calendar"></i>
                            Calendrier du mois
                        </div>
                        <a href="../backoffice/calendrier.php" class="btn btn-outline btn-sm">Plein écran</a>
                    </div>
                    <div style="padding:16px;">
                        <div id="miniCalendar"></div>
                    </div>
                    <div class="cal-legend">
                        <div class="cal-legend-item">
                            <div class="cal-legend-dot" style="background:#0ea5e9;"></div> Planifiée
                        </div>
                        <div class="cal-legend-item">
                            <div class="cal-legend-dot" style="background:#10b981;"></div> Terminée
                        </div>
                        <div class="cal-legend-item">
                            <div class="cal-legend-dot" style="background:#ef4444;"></div> Annulée
                        </div>
                    </div>
                </div>

                <!-- Recent ordonnances -->
                <div class="section-card">
                    <div class="section-card-header">
                        <div class="section-card-title">
                            <i class="fa-solid fa-file-prescription"></i>
                            Dernières ordonnances
                        </div>
                        <a href="../backoffice/list_ordonnance.php" class="btn btn-outline btn-sm">Voir tout</a>
                    </div>
                    <div class="section-card-body">
                        <?php if (empty($recentOrdos)): ?>
                            <div class="dash-empty">
                                <i class="fa-solid fa-file-circle-xmark"></i>
                                <p>Aucune ordonnance rédigée.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentOrdos as $o): ?>
                            <div class="ordo-item">
                                <div class="ordo-icon"><i class="fa-solid fa-file-prescription"></i></div>
                                <div style="flex:1; min-width:0;">
                                    <div class="ordo-title">
                                        Ordonnance #<?= $o['id_ordonnance'] ?>
                                    </div>
                                    <div class="ordo-sub">
                                        <i class="fa-regular fa-calendar"></i>
                                        <?= date('d/m/Y', strtotime($o['date_creation'])) ?>
                                        &nbsp;·&nbsp;
                                        <?= htmlspecialchars(substr($o['medicaments'], 0, 28)) ?>…
                                    </div>
                                </div>
                                <a href="../backoffice/edit_ordonnance.php?id=<?= $o['id_ordonnance'] ?>"
                                   class="btn btn-outline btn-sm" title="Modifier">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /RIGHT COLUMN -->
        </div><!-- /dashboard-grid -->

    </div><!-- /page-content -->
</div><!-- /main-content -->
</div><!-- /admin-wrapper -->

<script>
// ── Sidebar toggle ────────────────────────────────────────
document.querySelector('.sidebar-toggle').addEventListener('click', function () {
    document.querySelector('.sidebar').classList.toggle('open');
});

// ── Sub-menus ─────────────────────────────────────────────
function toggleSubMenu(el) {
    var navItem = el.closest('.nav-item');
    var isOpen  = navItem.classList.contains('open');

    document.querySelectorAll('.nav-item.has-sub.open').forEach(function (item) {
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

// Auto-open active sub-menu on page load
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.nav-item.has-sub.open').forEach(function (item) {
        var sub = item.querySelector('.sub-menu');
        if (sub) sub.classList.add('open');
    });
});

// ── Dark mode ─────────────────────────────────────────────
function toggleDark() {
    document.body.classList.toggle('dark-mode');
    var isDark = document.body.classList.contains('dark-mode');
    document.getElementById('darkBtn').innerHTML = isDark
        ? '<i class="fa-solid fa-sun"></i>'
        : '<i class="fa-solid fa-moon"></i>';
    localStorage.setItem('darkMode', isDark);
}
if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
    document.getElementById('darkBtn').innerHTML = '<i class="fa-solid fa-sun"></i>';
}

// ── Mini Calendar ─────────────────────────────────────────
var calEvents = <?= json_encode($calEvents) ?>;

$(document).ready(function () {
    $('#miniCalendar').fullCalendar({
        locale:      'fr',
        defaultView: 'month',
        header: {
            left:   'prev,next',
            center: 'title',
            right:  ''
        },
        events:      calEvents,
        height:      260,
        editable:    false,
        eventLimit:  2,
        eventLimitText: '+',
        eventClick: function (event) {
            window.location.href = '../backoffice/edit_consultation.php?id=' + event.id;
        },
        dayClick: function () {
            window.location.href = '../backoffice/add_consultation.php';
        },
        eventRender: function (event, element) {
            element.css({ 'font-size': '0.68rem', 'padding': '1px 4px' });
        }
    });
});
</script>
<script src="../assets/js/language-switcher.js"></script>
</body>
</html>