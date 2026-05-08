<?php
session_start();
require_once __DIR__ . '/langue.php';
require_once __DIR__ . '/../../Controller/ContratController.php';
require_once __DIR__ . '/../../stripe_config.php';

$i18n  = i18n_boot('fr');
$lang  = $i18n['lang'];
$isRtl = $i18n['isRtl'];

$contratC   = new ContratController();
$id_contrat = isset($_GET['id_contrat']) ? (int)$_GET['id_contrat'] : 0;
$contrat    = $contratC->showContrat($id_contrat);

$proto    = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? (isset($_SERVER['HTTPS']) ? 'https' : 'http');
$host     = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];
$base_url = $proto . '://' . $host;


if (!$contrat) die('Contrat introuvable.');
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
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body id="body">
    <nav class="navbar">
        <a href="#" class="navbar-brand">
            <div class="navbar-logo">🏥</div>
            <div class="navbar-name">ASCL<span>EPIA</span></div>
        </a>
    </nav>

    <section style="background:var(--bg); padding:80px 0;">
        <div class="container">
            <div style="max-width:560px; margin:0 auto;">

                <div class="card" style="padding:32px; margin-bottom:24px;">
                    <h3 style="margin-bottom:16px; color:var(--dark);">
                        <i class="fa-solid fa-receipt" style="color:var(--primary)"></i> Récapitulatif
                    </h3>
                    <div class="contrat-details">
                        <div class="contrat-detail-item">
                            <div class="contrat-detail-label">Assurance</div>
                            <div class="contrat-detail-value"><?= htmlspecialchars($contrat['nom_assurance']) ?></div>
                        </div>
                        <div class="contrat-detail-item">
                            <div class="contrat-detail-label">Montant à payer</div>
                            <div class="contrat-detail-value price"><?= number_format((float)$contrat['montant'], 2) ?> DT</div>
                        </div>
                    </div>
                </div>

                <div class="card" style="padding:32px;">
                    <h3 style="margin-bottom:20px; color:var(--dark);">
                        <i class="fa-solid fa-credit-card" style="color:var(--primary)"></i> Informations de paiement
                    </h3>

                    <div id="payment-element" style="margin-bottom:24px;"></div>

                    <div id="payment-message" style="color:var(--danger); margin-bottom:16px; display:none;"></div>

                    <button id="btnPayer" class="btn btn-primary" style="width:100%; justify-content:center;">
                        <i class="fa-solid fa-lock"></i> Payer <?= number_format((float)$contrat['montant'], 2) ?> DT
                    </button>

                    <p style="text-align:center; color:var(--text-muted); font-size:0.8rem; margin-top:12px;">
                        <i class="fa-solid fa-shield-halved"></i> Paiement sécurisé par Stripe
                    </p>
                </div>

            </div>
        </div>
    </section>

<script>
    var stripe = Stripe('<?= STRIPE_PUBLIC_KEY ?>');
    var elements;
    var clientSecret;

    // Créer le PaymentIntent
    fetch('creerPaiement.php?id_contrat=<?= $id_contrat ?>', {
        method: 'POST',
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        clientSecret = data.clientSecret;
        elements = stripe.elements({ clientSecret: clientSecret });
        var paymentElement = elements.create('payment');
        paymentElement.mount('#payment-element');
    });

    document.getElementById('btnPayer').addEventListener('click', async function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Traitement...';

        var result = await stripe.confirmPayment({
            elements: elements,
            confirmParams: {
               return_url: '<?= $base_url ?>/projetweb/View/frontoffice/paiementSuccess.php?id_contrat=<?= $id_contrat ?>',
            },
        });

        if (result.error) {
            document.getElementById('payment-message').style.display = 'block';
            document.getElementById('payment-message').textContent = result.error.message;
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-lock"></i> Payer <?= number_format((float)$contrat['montant'], 2) ?> DT';
        }
    });
</script>
</body>
</html>