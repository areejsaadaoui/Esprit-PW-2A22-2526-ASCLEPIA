<?php

session_start();
require_once '../../Controller/ReponseController.php';
require_once '../../Model/Reponse.php';

$reponseC = new ReponseController();


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: showpost.php?error=missing_reponse_id');
    exit;
}

$id_rep = (int)$_GET['id'];

$reponseData = $reponseC->getReponseById($id_rep);
if (!$reponseData) {
    header('Location: showpost.php?error=reponse_not_found');
    exit;
}

$id_post = $reponseData['id_post'];


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveauTexte = trim($_POST['texte_rep'] ?? '');
    if (empty($nouveauTexte)) {
        $error = "Le texte de la réponse ne peut pas être vide.";
    } else {
        $result = $reponseC->modifreponse($id_rep, $nouveauTexte);
        if ($result) {
            $success = "Réponse modifiée avec succès !";
            
            $reponseData = $reponseC->getReponseById($id_rep);
            
            echo '<script>setTimeout(function(){ window.location.href = "showpost.php?id=' . $id_post . '"; }, 1500);</script>';
        } else {
            $error = "Erreur lors de la modification.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ASCLEPIA - Modifier une réponse</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 28px;
            padding: 30px;
            box-shadow: var(--shadow);
        }
        textarea {
            width: 100%;
            padding: 14px;
            border: 2px solid var(--border);
            border-radius: 16px;
            font-size: 1rem;
            font-family: inherit;
        }
        textarea:focus {
            border-color: var(--primary);
            outline: none;
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">⚕️</div>
            <div class="sidebar-title">ASC<span>LEPIA</span></div>
        </div>
        <div class="sidebar-user">
            <div class="user-avatar">AD</div>
            <div class="user-info"><div class="name">Administrateur</div><div class="role">Super Admin</div></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-item"><a href="dashboard.php"><i class="fas fa-tachometer-alt nav-icon"></i><span>Tableau de bord</span></a></div>
            <div class="nav-item"><a href="addpost.php"><i class="fas fa-plus-circle nav-icon"></i><span>Nouveau post</span></a></div>
            <div class="nav-item"><a href="showpost.php"><i class="fas fa-eye nav-icon"></i><span>Tous les posts</span></a></div>
            <div class="nav-item"><a href="chercherpost.php"><i class="fas fa-search nav-icon"></i><span>Rechercher</span></a></div>
            <div class="nav-section-label">Autres</div>
            <div class="nav-item"><a href="../Frontoffice/index.html"><i class="fas fa-globe nav-icon"></i><span>Voir le site</span></a></div>
        </nav>
        <div class="sidebar-footer">
            <a href="#" class="btn btn-outline-white btn-sm" style="width:100%; justify-content:center;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <div><div class="page-title">Modifier une réponse</div><div class="breadcrumb"><span>Accueil</span><span>/</span><span>Modifier réponse</span></div></div>
            </div>
        </div>

        <div class="page-content">
            <div class="form-container">
                <h2><i class="fas fa-edit"></i> Modifier la réponse</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="texte_rep">Texte de la réponse :</label>
                        <textarea name="texte_rep" id="texte_rep" rows="6" required><?= htmlspecialchars($reponseData['texte_rep']) ?></textarea>
                    </div>
                    <div style="display: flex; gap: 12px; margin-top: 20px;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                        <a href="showpost.php?id=<?= $id_post ?>" class="btn btn-outline">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    function toggleSidebar() { document.querySelector('.sidebar').classList.toggle('open'); }
</script>
</body>
</html>