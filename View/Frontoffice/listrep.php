<?php
session_start();
require_once '../../Controller/ReponseController.php';

$reponseC = new ReponseController();
$id_post = isset($_GET['id_post']) ? (int)$_GET['id_post'] : null;

if ($id_post) {
    // Récupère les réponses d’un post spécifique
    $reponses = $reponseC->listrep($id_post);
    $titrePage = "Réponses du post #$id_post";

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ASCLEPIA - <?= $titrePage ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/frontoffice.css">
    <style>
        .table-container {
            background: white;
            border-radius: 28px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-top: 30px;
        }
        .badge-post {
            background: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        .btn-back {
            background: var(--gray);
            color: white;
            padding: 8px 16px;
            border-radius: 30px;
            text-decoration: none;
        }
        .btn-back:hover {
            background: var(--dark);
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.html" class="navbar-brand">
        <div class="navbar-logo"></div>
        <div class="navbar-name">ASC<span>LEPIA</span></div>
    </a>
    <div class="nav-links">
        <a href="index.html">Accueil</a>
        <a href="postlist.php">Forum</a>
        <a href="listrep.php">Réponses</a>
    </div>
    <div class="nav-actions">
        <a href="login.html" class="btn btn-outline-white btn-sm">Se connecter</a>
        <a href="login.html" class="btn btn-primary btn-sm">S'inscrire</a>
    </div>
</nav>

<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <div class="section-tag"><i class="fa-solid fa-reply-all"></i> Communauté</div>
            <h2 class="section-title"><?= $titrePage ?></h2>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Réponse</th>
                        <th>Contenu</th>
                        <th>Utilisateur</th>
                        <th>Post lié</th>
                        <th>Date</th>
                        <th>Action</th>
                    </thead>
                <tbody>
                    <?php if (!empty($reponses)): ?>
                        <?php foreach ($reponses as $rep): ?>
                            <tr>
                                <td><?= htmlspecialchars($rep['id_rep']) ?></td>
                                <td><?= htmlspecialchars(substr($rep['texte_rep'], 0, 80)) ?>…</td>
                                <td>
                                    <i class="fas fa-user"></i> 
                                    <?= htmlspecialchars($rep['auteur'] ?? 'Anonyme') ?>
                                    <small>(#<?= $rep['id_utilisateur'] ?>)</small>
                                </td>
                                <td><span class="badge-post">Post #<?= $rep['id_post'] ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($rep['date_rep'])) ?></td>
                                <td>
                                    <a href="showpost.php?id=<?= $rep['id_post'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Voir le post
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;">Aucune réponse trouvée</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="postlist.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour au forum
            </a>
        </div>
    </div>
</section>

</body>
</html>