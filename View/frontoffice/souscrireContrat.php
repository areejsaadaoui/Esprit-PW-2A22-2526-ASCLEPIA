<?php
session_start();
require_once __DIR__ . '/langue.php';
include '../../Controller/ContratController.php';
require_once __DIR__ . '/../../Model/Contrat.php';

$contratC       = new ContratController();
$assurances     = $contratC->listAssurances();
$assurancesList = [];
foreach ($assurances as $a) { $assurancesList[] = $a; }

// Récupérer détails assurances pour JS
$allAssurances    = $contratC->listAssurancesDetails()->fetchAll();
$assuranceDetails = [];
foreach ($allAssurances as $a) { $assuranceDetails[$a['id_assurance']] = $a; }

$success = false;
$error   = '';

if (isset($_POST['id_assurance'], $_POST['date_d'], $_POST['montant'])) {
    if (!empty($_POST['id_assurance']) && !empty($_POST['date_d']) && !empty($_POST['montant'])) {

        $token = bin2hex(random_bytes(32));

        $contrat = new Contrat(
            null,
            $_POST['date_d'],
            !empty($_POST['date_f']) ? $_POST['date_f'] : null,
            (int)$_POST['id_assurance'],
            (float)$_POST['montant'],
            'En attente'
        );
        $contratC->addContratWithToken($contrat, $token);

       $email = trim($contratC->getUserEmail(1));
       // var_dump($email); die();

        require_once __DIR__ . '/../../libs/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../../libs/PHPMailer/src/SMTP.php';
        require_once __DIR__ . '/../../libs/PHPMailer/src/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->SMTPOptions = [
    'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ]
];
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'contact.asclepia@gmail.com';
            $mail->Password   = 'ahtb nbgq dhvq dfio';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('contact.asclepia@gmail.com', 'ASCLEPIA');
            $mail->addAddress($email);
            $mail->Subject = 'Confirmation de votre contrat ASCLEPIA';

            $lien = 'http://localhost/projetweb/View/frontoffice/confirmerContrat.php?token=' . $token;
            
            $mail->isHTML(true);
            $mail->Body = '
                <div style="font-family:Arial,sans-serif; max-width:600px; margin:0 auto;">
                    <div style="background:linear-gradient(135deg,#0ea5e9,#10b981); padding:30px; text-align:center; border-radius:12px 12px 0 0;">
                        <h1 style="color:white; margin:0;">🏥 ASCLEPIA</h1>
                    </div>
                    <div style="background:#f8fafc; padding:30px; border-radius:0 0 12px 12px;">
                        <h2 style="color:#0f172a;">Confirmez votre contrat</h2>
                        <p style="color:#64748b;">Votre souscription est en attente de confirmation. Cliquez sur le bouton ci-dessous pour activer votre contrat.</p>
                        <div style="text-align:center; margin:30px 0;">
                            <a href="' . $lien . '" style="background:linear-gradient(135deg,#0ea5e9,#10b981); color:white; padding:14px 32px; border-radius:999px; text-decoration:none; font-weight:700; font-size:1rem;">
                                ✅ Confirmer mon contrat
                            </a>
                        </div>
                        <p style="color:#94a3b8; font-size:0.85rem;">Si vous n\'avez pas effectué cette souscription, ignorez cet email.</p>
                    </div>
                </div>
            ';
            if ($mail->send()) {
                $success = true;
            } else {
                $error = "Erreur envoi mail : " . $mail->ErrorInfo;
            }
        } catch (Exception $e) {
            $error = "Erreur envoi mail : " . $mail->ErrorInfo;
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

// ID passé depuis assurancefront.php
$preselect = isset($_GET['id_assurance']) ? (int)$_GET['id_assurance'] : 0;
$i18n = i18n_boot('fr');
$lang = $i18n['lang'];
$isRtl = $i18n['isRtl'];

if (!empty($error)) {
    if ($error === "Veuillez remplir tous les champs obligatoires.") {
        $error = i18n_t('err_required', $lang);
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(i18n_t('souscrire_title', $lang)) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/frontoffice.css">
    <link rel="stylesheet" href="../../assets/css/assurance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .souscription-wrapper { max-width:680px; margin:0 auto; padding:40px 24px 80px; }
        .assurance-preview { background:rgba(14,165,233,0.06); border:1px solid rgba(14,165,233,0.2); border-radius:var(--radius-lg); padding:20px 24px; margin-bottom:24px; display:none; }
        .assurance-preview.visible { display:block; }
        .preview-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid rgba(14,165,233,0.1); font-size:0.9rem; }
        .preview-row:last-child { border-bottom:none; }
        .preview-label { color:var(--text-muted); font-weight:500; }
        .preview-value { font-weight:700; color:var(--dark); }
        .preview-value.price { color:var(--primary); font-size:1.1rem; }
        .success-box { text-align:center; padding:60px 24px; }
        .success-icon { width:80px; height:80px; background:linear-gradient(135deg,#10b981,#059669); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; margin:0 auto 24px; box-shadow:0 8px 24px rgba(16,185,129,0.3); }

        /* RTL adjustments (Arabic) */
        html[dir="rtl"] body { direction: rtl; text-align: right; }
        html[dir="rtl"] .nav-links { direction: rtl; }
    </style>
</head>
<body id="body">

    <nav class="navbar" id="navbar">
        <a href="#" class="navbar-brand">
            <div class="navbar-logo">🏥</div>
            <div class="navbar-name">ASCL<span>EPIA</span></div>
        </a>
        <div class="nav-links" id="navLinks">
            <a href="#" class="nav-link"><?= htmlspecialchars(i18n_t('home', $lang)) ?></a>
            <a href="assurancefront.php?<?= htmlspecialchars(http_build_query(['lang' => $lang])) ?>" class="nav-link"><?= htmlspecialchars(i18n_t('insurances', $lang)) ?></a>
            <a href="#" class="nav-link active"><?= htmlspecialchars(i18n_t('subscribe', $lang)) ?></a>
            <a href="#" class="nav-link"><?= htmlspecialchars(i18n_t('contact', $lang)) ?></a>
        </div>
        <div class="nav-actions">
            <a href="#" class="btn btn-outline-white btn-sm"><?= htmlspecialchars(i18n_t('login', $lang)) ?></a>
            <a href="#" class="btn btn-primary btn-sm"><?= htmlspecialchars(i18n_t('signup', $lang)) ?></a>
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

    <section class="hero" style="min-height:35vh; padding:100px 0 50px;">
        <div class="hero-glow hero-glow-1"></div>
        <div class="hero-glow hero-glow-2"></div>
        <div class="container">
            <div class="hero-content" style="max-width:100%; text-align:center;">
                <div class="hero-badge"><?= htmlspecialchars(i18n_t('badge_contract', $lang)) ?></div>
                <h1 class="hero-title"><?= htmlspecialchars(i18n_t('hero_subscribe_title', $lang)) ?></h1>
                <p class="hero-subtitle" style="margin:0 auto;"><?= htmlspecialchars(i18n_t('hero_subscribe_subtitle', $lang)) ?></p>
            </div>
        </div>
    </section>

    <section style="background:var(--bg); padding:60px 0;">
        <div class="container">
            <div class="souscription-wrapper">

                <?php if ($success): ?>
                <div class="card">
                    <div class="success-box">
                        <div class="success-icon">✅</div>
                        <h2 style="margin-bottom:12px; color:var(--dark);">📧 Email envoyé !</h2>
<p style="color:var(--text-muted); margin-bottom:32px;">
    Votre contrat est en attente de confirmation. Consultez votre email 
    <strong style="color:var(--primary)"><?= htmlspecialchars($email) ?></strong> 
    et cliquez sur le lien pour l'activer.
</p>
                        <div class="d-flex gap-2" style="justify-content:center;">
                            <a href="souscrireContrat.php?<?= htmlspecialchars(http_build_query(['lang' => $lang])) ?>" class="btn btn-primary">
                                <i class="fa-solid fa-plus"></i> <?= htmlspecialchars(i18n_t('new_contract', $lang)) ?>
                            </a>
                            <a href="assurancefront.php?<?= htmlspecialchars(http_build_query(['lang' => $lang])) ?>" class="btn btn-outline"><?= htmlspecialchars(i18n_t('see_insurances', $lang)) ?></a>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" style="margin-bottom:24px;">
                        <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fa-solid fa-file-contract" style="color:var(--primary)"></i>
                            <?= htmlspecialchars(i18n_t('form_title', $lang)) ?>
                        </div>
                    </div>

                    <form action="" method="POST" id="formSouscrire" onsubmit="return validerFormulaire()">

                        <div class="form-group">
                            <label class="form-label"><?= htmlspecialchars(i18n_t('choose_insurance', $lang)) ?> <span style="color:var(--danger)">*</span></label>
                            <select name="id_assurance" id="id_assurance" class="form-control" onchange="majPreview()">
                                <option value=""><?= htmlspecialchars(i18n_t('select_insurance', $lang)) ?></option>
                                <?php foreach ($assurancesList as $a): ?>
                                    <option value="<?= $a['id_assurance'] ?>"
                                        <?= $preselect == $a['id_assurance'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($a['nom_assurance']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="form-error" id="err_assurance"><?= htmlspecialchars(i18n_t('err_choose_insurance', $lang)) ?></span>
                        </div>

                        <div class="assurance-preview" id="assurancePreview">
                            <p style="font-weight:700; color:var(--dark); margin-bottom:12px;">
                                <i class="fa-solid fa-circle-info" style="color:var(--primary)"></i>
                                <?= htmlspecialchars(i18n_t('details_title', $lang)) ?>
                            </p>
                            <div class="preview-row">
                                <span class="preview-label"><?= htmlspecialchars(i18n_t('type', $lang)) ?></span>
                                <span class="preview-value" id="prev_type">—</span>
                            </div>
                            <div class="preview-row">
                                <span class="preview-label"><?= htmlspecialchars(i18n_t('duration', $lang)) ?></span>
                                <span class="preview-value" id="prev_duree">—</span>
                            </div>
                            <div class="preview-row">
                                <span class="preview-label"><?= htmlspecialchars(i18n_t('refund', $lang)) ?></span>
                                <span class="preview-value" id="prev_taux">—</span>
                            </div>
                            <div class="preview-row">
                                <span class="preview-label"><?= htmlspecialchars(i18n_t('monthly_price', $lang)) ?></span>
                                <span class="preview-value price" id="prev_prix">—</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label"><?= htmlspecialchars(i18n_t('start_date', $lang)) ?> <span style="color:var(--danger)">*</span></label>
                                    <input type="date" name="date_d" id="date_d" class="form-control" onchange="calcMontant()">
                                    <span class="form-error" id="err_date_d"><?= htmlspecialchars(i18n_t('err_start_date', $lang)) ?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label"><?= htmlspecialchars(i18n_t('end_date', $lang)) ?> <span style="color:var(--text-muted); font-size:0.78rem;"><?= htmlspecialchars(i18n_t('optional', $lang)) ?></span></label>
                                    <input type="date" name="date_f" id="date_f" class="form-control" onchange="calcMontant()">
                                    <span class="form-error" id="err_date_f"><?= htmlspecialchars(i18n_t('err_end_date', $lang)) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><?= htmlspecialchars(i18n_t('total_amount', $lang)) ?> <span style="color:var(--danger)">*</span></label>
                            <input type="number" name="montant" id="montant" class="form-control" placeholder="<?= htmlspecialchars(i18n_t('auto_calc', $lang)) ?>">
                            <div class="form-hint"><?= htmlspecialchars(i18n_t('hint_calc', $lang)) ?></div>
                            <span class="form-error" id="err_montant"><?= htmlspecialchars(i18n_t('err_amount', $lang)) ?></span>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-lg" style="flex:1; justify-content:center;">
                                <i class="fa-solid fa-check"></i> <?= htmlspecialchars(i18n_t('confirm', $lang)) ?>
                            </button>
                            <a href="assurancefront.php?<?= htmlspecialchars(http_build_query(['lang' => $lang])) ?>" class="btn btn-outline"><?= htmlspecialchars(i18n_t('cancel', $lang)) ?></a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

    <button class="dark-toggle" id="darkToggle" onclick="toggleDark()" title="<?= htmlspecialchars(i18n_t('dark_mode', $lang)) ?>">
        🌙
    </button>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p><?= htmlspecialchars(i18n_t('footer_rights', $lang)) ?></p>
                <p><?= htmlspecialchars(i18n_t('footer_made_by', $lang)) ?></p>
            </div>
        </div>
    </footer>

<script>
    var assurances = <?= json_encode($assuranceDetails) ?>;
    var i18n = <?= json_encode([
        'months' => i18n_t('months', $lang),
        'per_month' => i18n_t('per_month_long', $lang),
    ], JSON_UNESCAPED_UNICODE) ?>;

    window.addEventListener('scroll', function() {
        document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
    });

    // ---- MODE SOMBRE (lié à assurancefront.php via localStorage) ----
    var darkMode = localStorage.getItem('darkMode') === 'true';

    function appliquerDark() {
        if (darkMode) {
            document.getElementById('body').classList.add('dark-mode');
            document.getElementById('darkToggle').textContent = '☀️';
        } else {
            document.getElementById('body').classList.remove('dark-mode');
            document.getElementById('darkToggle').textContent = '🌙';
        }
    }

    function toggleDark() {
        darkMode = !darkMode;
        localStorage.setItem('darkMode', darkMode);
        appliquerDark();
    }

    appliquerDark();

    // Pré-sélectionner si id passé en URL
    window.addEventListener('load', function() {
        if (document.getElementById('id_assurance').value !== '') {
            majPreview();
        }
    });

    function majPreview() {
        var id      = document.getElementById('id_assurance').value;
        var preview = document.getElementById('assurancePreview');
        if (id && assurances[id]) {
            var a = assurances[id];
            document.getElementById('prev_type').textContent  = a.TYPE;
            document.getElementById('prev_duree').textContent = a.duree + ' ' + (i18n.months || 'mois');
            document.getElementById('prev_taux').textContent  = a.taux_remboursement + '%';
            document.getElementById('prev_prix').textContent  = parseFloat(a.prix).toFixed(2) + ' ' + (i18n.per_month || 'DT / mois');
            preview.classList.add('visible');
            calcMontant();
        } else {
            preview.classList.remove('visible');
            document.getElementById('montant').value = '';
        }
    }

    function calcMontant() {
        var id    = document.getElementById('id_assurance').value;
        var dateD = document.getElementById('date_d').value;
        var dateF = document.getElementById('date_f').value;
        if (!id || !assurances[id] || !dateD) return;
        var prix        = parseFloat(assurances[id].prix);
        var dureeDefaut = parseInt(assurances[id].duree);
        if (dateF && dateF > dateD) {
            var d1   = new Date(dateD);
            var d2   = new Date(dateF);
            var mois = (d2.getFullYear() - d1.getFullYear()) * 12 + (d2.getMonth() - d1.getMonth());
            document.getElementById('montant').value = (prix * mois).toFixed(2);
        } else {
            document.getElementById('montant').value = (prix * dureeDefaut).toFixed(2);
        }
    }

    function validerChamp(id, condition) {
        var input  = document.getElementById(id);
        var valide = condition(input.value);
        input.classList.toggle('is-invalid', !valide);
        return valide;
    }

    function validerFormulaire() {
        var ok = true;
        if (!validerChamp('id_assurance', function(v) { return v !== ''; })) ok = false;
        if (!validerChamp('date_d',       function(v) { return v !== ''; })) ok = false;
        if (!validerChamp('montant',      function(v) { return v !== '' && parseFloat(v) > 0; })) ok = false;
        var dateD = document.getElementById('date_d').value;
        var dateF = document.getElementById('date_f').value;
        if (dateF !== '' && dateF <= dateD) {
            document.getElementById('date_f').classList.add('is-invalid');
            ok = false;
        } else {
            document.getElementById('date_f').classList.remove('is-invalid');
        }
        return ok;
    }

    document.getElementById('id_assurance').addEventListener('change', function() { validerChamp('id_assurance', function(v) { return v !== ''; }); });
    document.getElementById('date_d').addEventListener('change', function() { validerChamp('date_d', function(v) { return v !== ''; }); });
    document.getElementById('montant').addEventListener('input', function() { validerChamp('montant', function(v) { return v !== '' && parseFloat(v) > 0; }); });
</script>
</body>
</html>