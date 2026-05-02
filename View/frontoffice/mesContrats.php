<?php
session_start();
require_once __DIR__ . '/langue.php';
include '../../Controller/ContratController.php';

$contratC = new ContratController();
$i18n = i18n_boot('fr');
$lang = $i18n['lang'];
$isRtl = $i18n['isRtl'];

// TODO: remplacer 1 par $_SESSION['id_user'] après intégration auth
$list = $contratC->listActiveContrats(1);
$contrats = [];
foreach ($list as $c) { $contrats[] = $c; }

function daysBetween($start, $end) {
    $s = strtotime($start);
    $e = strtotime($end);
    if (!$s || !$e) return null;
    return (int)floor(($e - $s) / 86400);
}

function clampPercent($v) {
    if ($v < 0) return 0;
    if ($v > 100) return 100;
    return $v;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(i18n_t('my_contracts', $lang)) ?> - ASCLEPIA</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/frontoffice.css">
    <link rel="stylesheet" href="../../assets/css/assurance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body id="body">
    <nav class="navbar" id="navbar">
        <a href="#" class="navbar-brand">
            <div class="navbar-logo">🏥</div>
            <div class="navbar-name">ASCL<span>EPIA</span></div>
        </a>
        <div class="nav-links" id="navLinks">
            <a href="assurancefront.php?<?= htmlspecialchars(http_build_query(['lang' => $lang])) ?>" class="nav-link">
                <?= htmlspecialchars(i18n_t('insurances', $lang)) ?>
            </a>
            <a href="#" class="nav-link active">
                <?= htmlspecialchars(i18n_t('my_contracts', $lang)) ?>
            </a>
        </div>
        <div class="nav-actions">
            <div style="display:flex; gap:8px; align-items:center; margin-left:12px;">
                <a class="btn btn-outline-white btn-sm" href="<?= htmlspecialchars(i18n_lang_url('fr')) ?>">FR</a>
                <a class="btn btn-outline-white btn-sm" href="<?= htmlspecialchars(i18n_lang_url('en')) ?>">EN</a>
                <a class="btn btn-outline-white btn-sm" href="<?= htmlspecialchars(i18n_lang_url('ar')) ?>">AR</a>
            </div>
        </div>
        <div class="hamburger" onclick="document.getElementById('navLinks').classList.toggle('open')">
            <span></span><span></span><span></span>
        </div>
    </nav>

    <section style="background:var(--bg); padding: 60px 0;">
        <div class="container">
            <div class="wrapper">
                <h2 style="margin-bottom: 6px; color: var(--dark);">
                    <?= htmlspecialchars(i18n_t('active_contracts', $lang)) ?>
                </h2>
                <p style="color: var(--text-muted); margin-bottom: 18px;">
                    <?= htmlspecialchars(i18n_t('realtime_subtitle', $lang)) ?>
                </p>

                <?php if (empty($contrats)): ?>
                    <div class="card" style="padding:28px;">
                        <div style="color:var(--text-muted); text-align:center;">
                            <?= htmlspecialchars(i18n_t('no_contract', $lang)) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php foreach ($contrats as $c): ?>
                    <?php
                        $start = $c['date_d'];
                        $end   = !empty($c['date_f']) ? $c['date_f'] : null;
                        $today = date('Y-m-d');
                        $pct = 0;
                        if ($end) {
                            $totalDays = daysBetween($start, $end);
                            $doneDays  = daysBetween($start, $today);
                            if ($totalDays !== null && $totalDays > 0 && $doneDays !== null) {
                                $pct = clampPercent((int)round(($doneDays / $totalDays) * 100));
                            }
                        } else {
                            $pct = 10;
                        }
                    ?>
                    <div class="contrat-card" data-contrat-id="<?= (int)$c['id_contrat'] ?>">
                        <div class="contrat-head">
                            <div>
                                <p class="contrat-title">
                                    <?= htmlspecialchars($c['nom_assurance'] ?? ('Contrat #' . (int)$c['id_contrat'])) ?>
                                </p>
                                <div class="contrat-meta">
                                    <?= htmlspecialchars(i18n_t('type_label', $lang)) ?>
                                    <strong><?= htmlspecialchars($c['type_assurance'] ?? '—') ?></strong> •
                                    <?= htmlspecialchars(i18n_t('amount_label', $lang)) ?>
                                    <strong><?= number_format((float)$c['montant'], 2) ?> <?= htmlspecialchars(i18n_t('dt', $lang)) ?></strong>
                                </div>
                            </div>
                            <div>
                                <span class="status-pill active">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span class="status-text"><?= htmlspecialchars($c['statut']) ?></span>
                                </span>
                            </div>
                        </div>

                        <div class="progress-wrap">
                            <div class="contrat-progress-bar"><div style="width: <?= (int)$pct ?>%"></div></div>
                            <div class="progress-label">
                                <span><?= htmlspecialchars(i18n_t('start_label', $lang)) ?> <strong><?= htmlspecialchars($start) ?></strong></span>
                                <span><?= htmlspecialchars(i18n_t('end_label', $lang)) ?> <strong><?= htmlspecialchars($end ?: '—') ?></strong></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<script>
    async function refreshStatuses() {
        const cards = Array.from(document.querySelectorAll('[data-contrat-id]'));
        if (cards.length === 0) return;
        const ids = cards.map(el => el.dataset.contratId).join(',');
        const res = await fetch('contrats_status.php?ids=' + encodeURIComponent(ids), { cache: 'no-store' });
        if (!res.ok) return;
        const data = await res.json();
        for (const id of Object.keys(data)) {
            const card = document.querySelector('[data-contrat-id="' + id + '"]');
            if (!card) continue;
            const statusEl = card.querySelector('.status-text');
            if (statusEl) statusEl.textContent = data[id].statut || '';
        }
    }
    setInterval(refreshStatuses, 5000);
    refreshStatuses();
</script>
</body>
</html>