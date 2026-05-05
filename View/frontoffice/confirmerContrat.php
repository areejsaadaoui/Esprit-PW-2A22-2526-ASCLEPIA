<?php
session_start();
require_once __DIR__ . '/langue.php';
require_once __DIR__ . '/../../Controller/ContratController.php';

$i18n  = i18n_boot('fr');
$lang  = $i18n['lang'];
$isRtl = $i18n['isRtl'];

$contratC = new ContratController();
$token    = $_GET['token'] ?? '';
$result     = '';
$id_contrat = 0;

if (!empty($token)) {
    $res        = $contratC->confirmerContratByToken($token);
    $result     = $res['status'];
    $id_contrat = $res['id_contrat'];
} else {
    $result = 'invalid';
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - ASCLEPIA</title>
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
                <?php if ($result === 'success'): ?>
                    <div class="card" style="padding:60px 40px;">
                        <div style="width:80px; height:80px; background:linear-gradient(135deg,#10b981,#059669); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; margin:0 auto 24px; box-shadow:0 8px 24px rgba(16,185,129,0.3);">✅</div>
                        <h2 style="color:var(--dark); margin-bottom:12px;">Contrat activé !</h2>
                        <p style="color:var(--text-muted); margin-bottom:32px;">Votre contrat est maintenant <strong style="color:#10b981;">Actif</strong>.</p>
                       <div style="display:flex; gap:12px; justify-content:center;">
    <a href="signerContrat.php?id_contrat=<?= $id_contrat ?>" class="btn btn-primary">
        <i class="fa-solid fa-pen-nib"></i> Signer le contrat
    </a>
    <a href="mesContrats.php" class="btn btn-outline">
        <i class="fa-solid fa-file-contract"></i> Mes contrats
    </a>
</div>
                    </div>
                <?php else: ?>
                    <div class="card" style="padding:60px 40px;">
                        <div style="width:80px; height:80px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; margin:0 auto 24px;">❌</div>
                        <h2 style="color:var(--dark); margin-bottom:12px;">Erreur</h2>
                        <p style="color:var(--text-muted);">
                            <?php if ($result === 'already_confirmed'): ?>
                                Ce contrat est déjà confirmé.
                            <?php else: ?>
                                Lien invalide ou expiré.
                            <?php endif; ?>
                        </p>
                        <a href="assurancefront.php" class="btn btn-outline" style="margin-top:24px;">Retour</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>
</html>
