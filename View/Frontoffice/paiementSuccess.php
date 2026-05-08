<?php
session_start();
require_once __DIR__ . '/langue.php';
require_once __DIR__ . '/../../libs/stripe/stripe-php-20.1.0/init.php';
require_once __DIR__ . '/../../stripe_config.php';
require_once __DIR__ . '/../../Controller/ContratController.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$i18n  = i18n_boot('fr');
$lang  = $i18n['lang'];
$isRtl = $i18n['isRtl'];

$contratC         = new ContratController();
$id_contrat       = isset($_GET['id_contrat']) ? (int)$_GET['id_contrat'] : 0;
$payment_intent   = $_GET['payment_intent'] ?? '';

$success = false;
if (!empty($payment_intent)) {
    $intent = \Stripe\PaymentIntent::retrieve($payment_intent);
    if ($intent->status === 'succeeded') {
        $contratC->marquerPaye($id_contrat);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - ASCLEPIA</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/frontoffice.css">
    <link rel="stylesheet" href="../../assets/css/assurance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body id="body">
    <nav class="navbar">
        <a href="#" class="navbar-brand">
            <div class="navbar-logo">🏥</div>
            <div class="navbar-name">ASCL<span>EPIA</span></div>
        </a>
    </nav>

    <section style="background:var(--bg); padding:120px 0;">
        <div class="container">
            <div style="max-width:560px; margin:0 auto; text-align:center;">
                <?php if ($success): ?>
                    <div class="card" style="padding:60px 40px;">
    <div class="success-icon">✅</div>
    <h2 style="color:var(--dark); margin:16px 0 12px;">Paiement réussi !</h2>
    <p style="color:var(--text-muted); margin-bottom:32px;">Votre paiement a été accepté avec succès.</p>

    <?php

   $contratInfo = $contratC->showContrat($id_contrat);

$proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? (isset($_SERVER['HTTPS']) ? 'https' : 'http');
$host  = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];
$base_url = $proto . '://' . $host;

$downloadUrl = $base_url . '/projetweb/View/frontoffice/telechargerContrat.php?id_contrat=' . $id_contrat;
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($downloadUrl);
    ?>
    <div style="margin:24px 0;">
        <p style="color:var(--text-muted); font-size:0.88rem; margin-bottom:12px;">
            📱 Scannez ce QR code pour télécharger votre contrat sur mobile :
        </p>
        <img src="<?= htmlspecialchars($qrUrl) ?>" alt="QR Code contrat" 
             style="border:4px solid var(--border); border-radius:var(--radius); padding:8px; background:white;">
    </div>

    <div style="display:flex; gap:12px; justify-content:center;">
        <a href="telechargerContrat.php?id_contrat=<?= $id_contrat ?>" class="btn btn-primary">
            <i class="fa-solid fa-file-pdf"></i> Télécharger le contrat
        </a>
        <a href="mesContrats.php" class="btn btn-outline">
            <i class="fa-solid fa-file-contract"></i> Mes contrats
        </a>
    </div>
</div>
                <?php else: ?>
                    <div class="card" style="padding:60px 40px;">
                        <div style="font-size:3rem; margin-bottom:16px;">❌</div>
                        <h2 style="color:var(--dark);">Paiement échoué</h2>
                        <p style="color:var(--text-muted);">Une erreur est survenue lors du paiement.</p>
                        <a href="mesContrats.php" class="btn btn-outline" style="margin-top:24px;">Retour</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>
</html>