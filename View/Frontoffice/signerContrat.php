<?php
session_start();
require_once __DIR__ . '/langue.php';
require_once __DIR__ . '/../../Controller/ContratController.php';

$i18n  = i18n_boot('fr');
$lang  = $i18n['lang'];
$isRtl = $i18n['isRtl'];

$contratC   = new ContratController();
$id_contrat = isset($_GET['id_contrat']) ? (int)$_GET['id_contrat'] : 0;
$contrat    = $contratC->showContrat($id_contrat);
$success    = false;

if (isset($_POST['signature']) && !empty($_POST['signature']) && $id_contrat) {
    $contratC->saveSignature($id_contrat, $_POST['signature']);
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signer le contrat - ASCLEPIA</title>
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

    <section style="background:var(--bg); padding:80px 0;">
        <div class="container">
            <div class="signer-wrapper">

                <?php if ($success): ?>
    <div class="card">
        <div class="success-box">
            <div class="success-icon">✅</div>
            <h2>Contrat signé !</h2>
            <p>Votre signature a été enregistrée avec succès.</p>
            <div style="display:flex; gap:12px; justify-content:center; margin-top:24px;">
    <a href="paiement.php?id_contrat=<?= $id_contrat ?>" class="btn btn-primary">
        <i class="fa-solid fa-credit-card"></i> Payer le contrat
    </a>
    <a href="telechargerContrat.php?id_contrat=<?= $id_contrat ?>" class="btn btn-outline">
        <i class="fa-solid fa-file-pdf"></i> Télécharger PDF
    </a>
    <a href="mesContrats.php" class="btn btn-outline">
        <i class="fa-solid fa-file-contract"></i> Mes contrats
    </a>
</div>
        </div>
    </div>

                <?php elseif ($contrat): ?>
                    <div class="card">
                        <div class="contrat-header">
                            <div style="font-size:2rem;">🏥</div>
                            <h2>ASCLEPIA</h2>
                            <p>Contrat d'assurance santé</p>
                        </div>

                        <div class="contrat-details">
                            <div class="contrat-detail-item">
                                <div class="contrat-detail-label">Assurance</div>
                                <div class="contrat-detail-value"><?= htmlspecialchars($contrat['nom_assurance']) ?></div>
                            </div>
                            <div class="contrat-detail-item">
                                <div class="contrat-detail-label">Type</div>
                                <div class="contrat-detail-value"><?= htmlspecialchars($contrat['type_assurance']) ?></div>
                            </div>
                            <div class="contrat-detail-item">
                                <div class="contrat-detail-label">Date de début</div>
                                <div class="contrat-detail-value"><?= htmlspecialchars($contrat['date_d']) ?></div>
                            </div>
                            <div class="contrat-detail-item">
                                <div class="contrat-detail-label">Date de fin</div>
                                <div class="contrat-detail-value"><?= htmlspecialchars($contrat['date_f'] ?: '—') ?></div>
                            </div>
                            <div class="contrat-detail-item">
                                <div class="contrat-detail-label">Montant total</div>
                                <div class="contrat-detail-value price"><?= number_format((float)$contrat['montant'], 2) ?> DT</div>
                            </div>
                            <div class="contrat-detail-item">
                                <div class="contrat-detail-label">Statut</div>
                                <div class="contrat-detail-value status"><?= htmlspecialchars($contrat['statut']) ?></div>
                            </div>
                        </div>

                        <p class="contrat-terms">
                            En signant ce contrat, vous acceptez les conditions générales d'utilisation d'ASCLEPIA
                            et confirmez que les informations fournies sont exactes et complètes.
                        </p>
                    </div>

                    <div class="card signature-wrapper">
                        <h3>✍️ Votre signature</h3>
                        <p>Signez dans la zone ci-dessous avec votre souris ou votre doigt.</p>

                        <canvas id="signatureCanvas" class="signature-canvas" width="700" height="200"></canvas>

                        <div class="signature-actions">
                            <button type="button" onclick="clearCanvas()" class="btn btn-outline btn-sm">
                                <i class="fa-solid fa-eraser"></i> Effacer
                            </button>
                        </div>

                        <form method="POST" id="formSignature">
                            <input type="hidden" name="signature" id="signatureData">
                            <button type="button" onclick="soumettre()" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:24px;">
                                <i class="fa-solid fa-pen-nib"></i> Confirmer la signature
                            </button>
                        </form>
                    </div>

                <?php else: ?>
                    <div class="card">
                        <p>Contrat introuvable.</p>
                        <a href="assurancefront.php" class="btn btn-outline">Retour</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

<script>
    var canvas = document.getElementById('signatureCanvas');
    if (canvas) {
        var ctx     = canvas.getContext('2d');
        var drawing = false;

        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth   = 2.5;
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';

        function getPos(e) {
            var rect   = canvas.getBoundingClientRect();
            var scaleX = canvas.width  / rect.width;
            var scaleY = canvas.height / rect.height;
            if (e.touches) {
                return {
                    x: (e.touches[0].clientX - rect.left) * scaleX,
                    y: (e.touches[0].clientY - rect.top)  * scaleY
                };
            }
            return {
                x: (e.clientX - rect.left) * scaleX,
                y: (e.clientY - rect.top)  * scaleY
            };
        }

        canvas.addEventListener('mousedown',  function(e) { drawing = true; ctx.beginPath(); var p = getPos(e); ctx.moveTo(p.x, p.y); });
        canvas.addEventListener('mousemove',  function(e) { if (!drawing) return; var p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); });
        canvas.addEventListener('mouseup',    function()  { drawing = false; });
        canvas.addEventListener('mouseleave', function()  { drawing = false; });

        canvas.addEventListener('touchstart', function(e) { e.preventDefault(); drawing = true; ctx.beginPath(); var p = getPos(e); ctx.moveTo(p.x, p.y); }, { passive: false });
        canvas.addEventListener('touchmove',  function(e) { e.preventDefault(); if (!drawing) return; var p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); }, { passive: false });
        canvas.addEventListener('touchend',   function() { drawing = false; });
    }

    function clearCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    function soumettre() {
        var data = canvas.toDataURL('image/png');
        document.getElementById('signatureData').value = data;
        document.getElementById('formSignature').submit();
    }
</script>
</body>
</html>