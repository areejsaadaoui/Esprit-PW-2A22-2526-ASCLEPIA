<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'medecin'])) {
    header('Location: loginadmin.html');
    exit();
}

$user_role = $_SESSION['user_role'];
$adminNom   = $_SESSION['user_nom'] ?? 'Utilisateur';

$cur = basename($_SERVER['PHP_SELF']);
if (!function_exists('docActive')) {
    function docActive(...$p){ global $cur; return in_array($cur, $p) ? 'class="active"' : ''; }
    function docSub(...$p)   { global $cur; return in_array($cur, $p) ? 'open' : ''; }
}
if (!function_exists('isActive')) {
    function isActive(...$pages)   { global $cur; return in_array($cur, $pages); }
    function isSubActive(...$pages){ global $cur; return in_array($cur, $pages) ? 'open' : ''; }
}

require_once '../../config.php';
require_once '../../Controller/OrdonnanceController.php';

$controller = new OrdonnanceController(config::getConnexion());
$success = '';
$errors = [];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ordonnance = $controller->getOrdonnanceById($id);

if (!$ordonnance) {
    die("Ordonnance introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicaments = trim($_POST['medicaments'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $duree = intval($_POST['duree_traitement'] ?? 0);

    if (empty($medicaments) || strlen($medicaments) < 5) {
        $errors[] = "Les médicaments sont obligatoires (min. 5 caractères).";
    }
    if (empty($instructions) || strlen($instructions) < 5) {
        $errors[] = "Les instructions sont obligatoires (min. 5 caractères).";
    }
    if ($duree <= 0) {
        $errors[] = "La durée du traitement doit être supérieure à 0.";
    }

    if (empty($errors)) {
        $data = [
            'medicaments'      => $medicaments,
            'instructions'     => $instructions,
            'duree_traitement' => $duree
        ];
        if ($controller->updateOrdonnance($id, $data)) {
            $success = "Ordonnance modifiée avec succès !";
            $ordonnance = $controller->getOrdonnanceById($id);
        } else {
            $errors[] = "Erreur lors de la modification.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Ordonnance - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/dark.css">
</head>
<body>
<div class="admin-wrapper">

    <aside class="sidebar">
        <a href="#" class="sidebar-brand">
            <div class="sidebar-logo">🏥</div>
            <div class="sidebar-title">ASCL<span>EPIA</span></div>
        </a>
        <div class="sidebar-user">
            <div class="user-avatar"><?= strtoupper(substr($adminNom, 0, 2)) ?></div>
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($adminNom) ?></div>
                <div class="role"><?= $user_role === 'admin' ? 'Super Admin' : 'Médecin' ?></div>
            </div>
        </div>

        <?php if ($user_role === 'medecin'): ?>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Principal</div>
            <div class="nav-item">
                <a href="../front/indexd.php" <?= docActive('indexd.php') ?>>
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>
            <div class="nav-section-label">Activité</div>
            <div class="nav-item has-sub <?= docSub('list_consultation.php','add_consultation.php','edit_consultation.php','delete_consultation.php','calendrier.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= docActive('list_consultation.php','add_consultation.php','edit_consultation.php','delete_consultation.php') ?>>
                    <i class="fa-solid fa-stethoscope nav-icon"></i>
                    <span>Consultations</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= docSub('list_consultation.php','add_consultation.php','edit_consultation.php','delete_consultation.php') ?>">
                    <a href="list_consultation.php" <?= docActive('list_consultation.php') ?>><i class="fa-solid fa-list"></i> Toutes les consultations</a>
                    <a href="add_consultation.php" <?= docActive('add_consultation.php') ?>><i class="fa-solid fa-plus"></i> Nouvelle consultation</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= docSub('list_ordonnance.php','add_ordonnance.php','edit_ordonnance.php','delete_ordonnance.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= docActive('list_ordonnance.php','add_ordonnance.php','edit_ordonnance.php','delete_ordonnance.php') ?>>
                    <i class="fa-solid fa-file-prescription nav-icon"></i>
                    <span>Ordonnances</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu <?= docSub('list_ordonnance.php','add_ordonnance.php','edit_ordonnance.php','delete_ordonnance.php') ?>">
                    <a href="list_ordonnance.php" <?= docActive('list_ordonnance.php') ?>><i class="fa-solid fa-list"></i> Toutes les ordonnances</a>
                    <a href="add_ordonnance.php" <?= docActive('add_ordonnance.php') ?>><i class="fa-solid fa-plus"></i> Nouvelle ordonnance</a>
                </div>
            </div>
            <div class="nav-item">
                <a href="calendrier.php" <?= docActive('calendrier.php') ?>><i class="fa-solid fa-calendar-days nav-icon"></i><span>Calendrier</span></a>
            </div>
            <div class="nav-section-label">Autre</div>
            <div class="nav-item">
                <a href="../front/indexp.php"><i class="fas fa-globe nav-icon"></i><span>Espace patient</span></a>
            </div>
            <div class="nav-item">
                <a href="../front/login.php"><i class="fas fa-sign-out-alt nav-icon"></i><span>Déconnexion</span></a>
            </div>
        </nav>
        <?php else: ?>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Principal</div>
            <div class="nav-item">
                <a href="../back/dashboard.php" <?= isActive('dashboard.php') ? 'class="active"' : '' ?>><i class="fas fa-tachometer-alt nav-icon"></i><span>Tableau de bord</span></a>
            </div>
            <div class="nav-section-label">Gestion</div>
            <div class="nav-item has-sub <?= isSubActive('assurancelist.php','contratList.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= isActive('assurancelist.php','contratList.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-shield-halved nav-icon"></i><span>Assurances &amp; Contrats</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu">
                    <a href="../backoffice/assurancelist.php" <?= isActive('assurancelist.php') ? 'class="active"' : '' ?>>Les assurances</a>
                    <a href="contrat/contratList.php" <?= isActive('contratList.php') ? 'class="active"' : '' ?>>Les contrats</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= isSubActive('list_consultation.php','list_ordonnance.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= isActive('list_consultation.php','list_ordonnance.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-file-contract nav-icon"></i><span>Ordonnances &amp; Consultations</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu">
                    <a href="dashboard.php" <?= isActive('dashboard.php') ? 'class="active"' : '' ?>>Vue d’ensemble consultations</a>
                    <a href="list_consultation.php" <?= isActive('list_consultation.php') ? 'class="active"' : '' ?>>Les consultations</a>
                    <a href="list_ordonnance.php" <?= isActive('list_ordonnance.php') ? 'class="active"' : '' ?>>Les ordonnances</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= isSubActive('listepharmacie.php','listemedicament.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= isActive('listepharmacie.php','listemedicament.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-prescription-bottle-medical nav-icon"></i><span>Pharmacies &amp; Médicaments</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu">
                    <a href="listepharmacie.php" <?= isActive('listepharmacie.php') ? 'class="active"' : '' ?>>Les pharmacies</a>
                    <a href="listemedicament.php" <?= isActive('listemedicament.php') ? 'class="active"' : '' ?>>Les médicaments</a>
                </div>
            </div>
            <div class="nav-item">
                <a href="calendrier.php" <?= isActive('calendrier.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-calendar-days nav-icon"></i><span>Calendrier</span></a>
            </div>
            <div class="nav-item has-sub <?= isSubActive('postlist.php','postList.php','addpost.php') ?>">
                <a onclick="toggleSubMenu(this)" <?= isActive('postlist.php','postList.php','addpost.php') ? 'class="active"' : '' ?>><i class="fas fa-comments nav-icon"></i><span>Forum</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu">
                    <a href="../Frontoffice/postlist.php" <?= isActive('postlist.php','postList.php') ? 'class="active"' : '' ?>>Tous les posts</a>
                    <a href="addpost.php" <?= isActive('addpost.php') ? 'class="active"' : '' ?>>Ajouter un post</a>
                    <a href="dashboard.php" <?= isActive('dashboard.php') ? 'class="active"' : '' ?>>Gestion des posts</a>
                </div>
            </div>
            <div class="nav-section-label">Configuration</div>
            <div class="nav-item">
                <a href="../front/indexp.php"><i class="fas fa-globe nav-icon"></i><span>Voir le site</span></a>
            </div>
            <div class="nav-item">
                <a href="../back/loginadmin.html"><i class="fas fa-sign-out-alt nav-icon"></i><span>Déconnexion</span></a>
            </div>
        </nav>
        <?php endif; ?>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <div class="page-title">Modifier Ordonnance</div>
                    <div class="breadcrumb">
                        <a href="list_ordonnance.php">Ordonnances</a>
                        <span>/</span>
                        <span>Modifier</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
    <button class="dark-toggle" onclick="toggleDark()" id="darkBtn" title="Mode sombre">
        <i class="fa-solid fa-moon"></i>
    </button>
</div>
        </div>

        <div class="page-content">

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <ul style="margin:0;padding-left:16px">
                        <?php foreach ($errors as $e): ?>
                            <li><?= $e ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width:700px; margin:0 auto;">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-pen" style="color:var(--primary)"></i>
                        Modifier l'ordonnance #<?= $id ?>
                    </div>
                </div>

                <div class="alert alert-info" style="margin:0 0 20px;">
                    <i class="fa-solid fa-circle-info"></i>
                    Consultation #<?= $ordonnance['id_consultation'] ?> —
                    <?= date('d/m/Y H:i', strtotime($ordonnance['date_consultation'])) ?>
                </div>

                <form action="" method="POST" id="formEdit" onsubmit="return validerFormulaire()">

                    <div class="form-group">
                        <label class="form-label">Médicaments * <span class="text-muted" style="font-weight:400;font-size:0.8rem">(min. 5 caractères)</span></label>
                        <textarea name="medicaments" id="medicaments" class="form-control"
                            oninput="compter('medicaments', 'count_med', 5)"><?= htmlspecialchars($ordonnance['medicaments']) ?></textarea>
                        <span class="form-hint"><span id="count_med"><?= strlen($ordonnance['medicaments']) ?></span> caractères</span>
                        <span class="form-error" id="err_med">Les médicaments sont obligatoires (min. 5 caractères).</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instructions * <span class="text-muted" style="font-weight:400;font-size:0.8rem">(min. 5 caractères)</span></label>
                        <textarea name="instructions" id="instructions" class="form-control"
                            oninput="compter('instructions', 'count_inst', 5)"><?= htmlspecialchars($ordonnance['instructions']) ?></textarea>
                        <span class="form-hint"><span id="count_inst"><?= strlen($ordonnance['instructions']) ?></span> caractères</span>
                        <span class="form-error" id="err_inst">Les instructions sont obligatoires (min. 5 caractères).</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Durée du traitement * <span class="text-muted" style="font-weight:400;font-size:0.8rem">(en jours)</span></label>
                        <input type="number" name="duree_traitement" id="duree_traitement" class="form-control"
                            value="<?= $ordonnance['duree_traitement'] ?>" min="1">
                        <span class="form-error" id="err_duree">La durée doit être supérieure à 0.</span>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Mettre à jour
                        </button>
                        <a href="list_ordonnance.php" class="btn btn-outline">Annuler</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });

    function toggleSubMenu(el) {
        var navItem = el.closest('.nav-item');
        var isOpen  = navItem.classList.contains('open');
        document.querySelectorAll('.nav-item.has-sub.open').forEach(function(item) {
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

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.nav-item.has-sub.open').forEach(function (item) {
            var sub = item.querySelector('.sub-menu');
            if (sub) sub.classList.add('open');
        });
    });

    function compter(champId, compteurId, minimum) {
        const nb = document.getElementById(champId).value.length;
        const el = document.getElementById(compteurId);
        el.textContent = nb;
        el.style.color = nb >= minimum ? 'green' : 'red';
    }

    function validerFormulaire() {
        let valide = true;
        document.querySelectorAll('.form-error').forEach(e => e.style.display = 'none');
        document.querySelectorAll('.form-control').forEach(e => e.classList.remove('is-invalid'));

        const med = document.getElementById('medicaments').value.trim();
        if (med.length < 5) {
            document.getElementById('medicaments').classList.add('is-invalid');
            document.getElementById('err_med').style.display = 'block';
            valide = false;
        }

        const inst = document.getElementById('instructions').value.trim();
        if (inst.length < 5) {
            document.getElementById('instructions').classList.add('is-invalid');
            document.getElementById('err_inst').style.display = 'block';
            valide = false;
        }

        const duree = parseInt(document.getElementById('duree_traitement').value);
        if (!duree || duree <= 0) {
            document.getElementById('duree_traitement').classList.add('is-invalid');
            document.getElementById('err_duree').style.display = 'block';
            valide = false;
        }

        return valide;
    }
    // MODE SOMBRE
function toggleDark() {
    document.body.classList.toggle('dark-mode');
    const btn = document.getElementById('darkBtn');
    const isDark = document.body.classList.contains('dark-mode');
    btn.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
    localStorage.setItem('darkMode', isDark);
}

if (localStorage.getItem('darkMode') === 'true') {
    document.body.classList.add('dark-mode');
    document.getElementById('darkBtn').innerHTML = '<i class="fa-solid fa-sun"></i>';
}
</script>
</body>
<script src="../../assets/js/language-switcher.js"></script>
</html>