<?php
include '../../controller/AssuranceController.php';
$assuranceC = new AssuranceController();
$list       = $assuranceC->listAssurances();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Assurances</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; padding: 40px 20px; }
        h1 { text-align: center; color: #1a3c5e; margin-bottom: 10px; font-size: 2rem; }
        .subtitle { text-align: center; color: #6b7c93; margin-bottom: 40px; }
        .cards-container { display: flex; flex-wrap: wrap; gap: 24px; justify-content: center; max-width: 1200px; margin: 0 auto; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 280px; padding: 28px 24px; transition: transform 0.2s, box-shadow 0.2s; display: flex; flex-direction: column; gap: 12px; }
        .card:hover { transform: translateY(-6px); box-shadow: 0 10px 30px rgba(0,0,0,0.14); }
        .card-badge { display: inline-block; background: #e8f0fe; color: #1a73e8; font-size: 0.75rem; font-weight: 600; padding: 4px 12px; border-radius: 20px; width: fit-content; }
        .card h2 { font-size: 1.2rem; color: #1a3c5e; }
        .card p { font-size: 0.9rem; color: #6b7c93; line-height: 1.5; }
        .card-info { display: flex; flex-direction: column; gap: 6px; border-top: 1px solid #f0f0f0; padding-top: 12px; }
        .card-info span { font-size: 0.85rem; color: #444; }
        .card-info span strong { color: #1a3c5e; }
        .card-price { font-size: 1.5rem; font-weight: 700; color: #1a73e8; margin-top: 4px; }
        .card-price small { font-size: 0.85rem; font-weight: 400; color: #6b7c93; }
        .btn-subscribe { margin-top: auto; background: #1a73e8; color: white; border: none; border-radius: 8px; padding: 10px 0; width: 100%; font-size: 0.95rem; cursor: pointer; transition: background 0.2s; }
        .btn-subscribe:hover { background: #1558b0; }
    </style>
</head>
<body>
    <h1>Nos Offres d'Assurance</h1>
    <p class="subtitle">Choisissez la formule adaptée à vos besoins</p>
    <div class="cards-container">
        <?php foreach ($list as $a): ?>
        <div class="card">
            <span class="card-badge"><?= htmlspecialchars($a['TYPE']) ?></span>
            <h2><?= htmlspecialchars($a['nom_assurance']) ?></h2>
            <p><?= htmlspecialchars($a['description']) ?></p>
            <div class="card-info">
                <span>⏱ Durée : <strong><?= $a['duree'] ?> mois</strong></span>
                <span>🛡 Remboursement : <strong><?= $a['taux_remboursement'] ?>%</strong></span>
            </div>
            <div class="card-price">
                <?= number_format($a['prix'], 2) ?> DT
                <small>/ mois</small>
            </div>
            <button class="btn-subscribe">Souscrire</button>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>