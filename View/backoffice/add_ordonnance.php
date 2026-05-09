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

$path_norm = str_replace('\\', '/', $_SERVER['PHP_SELF']);
$is_admin_user_dash = ($cur === 'dashboard.php' && stripos($path_norm, 'backoffice') === false && stripos($path_norm, '/back/') !== false);
$is_consult_dash = ($cur === 'dashboard.php' && stripos($path_norm, 'backoffice') !== false);
$sub_consult_open = in_array($cur, ['list_consultation.php', 'list_ordonnance.php'], true) || $is_consult_dash;
$sub_assur_open = in_array($cur, ['assurancelist.php', 'contratList.php'], true);
$sub_pharma_open = in_array($cur, ['listepharmacie.php', 'listemedicament.php'], true);
$sub_forum_open = in_array($cur, ['dashboardf.php', 'postlist.php', 'postList.php', 'addpost.php'], true);

require_once '../../config.php';
require_once '../../Controller/LanguageController.php';
require_once '../../Controller/OrdonnanceController.php';

$db_notif_ao = config::getConnexion();
$stmt_notif_ao = $db_notif_ao->query("SELECT nom_medicament AS nom, stock, id_medicament FROM medicament WHERE stock <= 5 ORDER BY stock ASC");
$alertes_stock_ao = $stmt_notif_ao->fetchAll();
$nb_alertes_ao = count($alertes_stock_ao);

$controller = new OrdonnanceController(config::getConnexion());
$success = '';
$errors = [];

$consultations = $controller->getConsultationsTerminees();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_consultation = intval($_POST['id_consultation'] ?? 0);
    $medicaments = trim($_POST['medicaments'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $duree = intval($_POST['duree_traitement'] ?? 0);
    $signature = trim($_POST['signature'] ?? '');

    if (empty($id_consultation)) {
        $errors[] = "Veuillez choisir une consultation.";
    }
    if (empty($medicaments) || strlen($medicaments) < 5) {
        $errors[] = "Les médicaments sont obligatoires (min. 5 caractères).";
    }
    if (empty($instructions) || strlen($instructions) < 5) {
        $errors[] = "Les instructions sont obligatoires (min. 5 caractères).";
    }
    if ($duree <= 0) {
        $errors[] = "La durée du traitement doit être supérieure à 0.";
    }
    if (empty($signature)) {
        $errors[] = "La signature du médecin est obligatoire.";
    }

    if (empty($errors)) {
        // Sauvegarder la signature comme image
        $signatureDir = '../../assets/signatures/';
        if (!is_dir($signatureDir)) {
            mkdir($signatureDir, 0777, true);
        }
        $signatureFile = 'signature_' . time() . '.png';
        $signatureData = str_replace('data:image/png;base64,', '', $signature);
        $signatureData = base64_decode($signatureData);
        file_put_contents($signatureDir . $signatureFile, $signatureData);

        $data = [
            'id_consultation'  => $id_consultation,
            'medicaments'      => $medicaments,
            'instructions'     => $instructions,
            'duree_traitement' => $duree,
            'signature'        => $signatureFile
        ];
        if ($controller->createOrdonnance($data)) {
            $success = "Ordonnance ajoutée avec succès !";
            $consultations = $controller->getConsultationsTerminees();
        } else {
            $errors[] = "Erreur lors de l'ajout.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Ordonnance - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="../../assets/css/dark.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        #signatureCanvas {
            border: 2px dashed var(--border);
            border-radius: var(--radius);
            cursor: crosshair;
            background: white;
            display: block;
            width: 100%;
            touch-action: none;
        }
        #signatureCanvas:hover { border-color: var(--primary); }
        .signature-toolbar {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .color-btn {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: 2px solid transparent;
            cursor: pointer;
            transition: transform 0.15s;
        }
        .color-btn:hover, .color-btn.active { transform: scale(1.2); border-color: var(--primary); }
        .preview-signature {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 8px;
            margin-top: 8px;
            display: none;
        }
    </style>
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
                <a href="../back/dashboard.php" <?= $is_admin_user_dash ? 'class="active"' : '' ?>><i class="fas fa-tachometer-alt nav-icon"></i><span>Tableau de bord</span></a>
            </div>
            <div class="nav-section-label">Gestion</div>
            <div class="nav-item has-sub <?= $sub_assur_open ? 'open' : '' ?>">
                <a onclick="toggleSubMenu(this)" <?= $sub_assur_open ? 'class="active"' : '' ?>><i class="fa-solid fa-shield-halved nav-icon"></i><span>Assurances &amp; Contrats</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu <?= $sub_assur_open ? 'open' : '' ?>">
                    <a href="assurancelist.php" <?= isActive('assurancelist.php') ? 'class="active"' : '' ?>>Les assurances</a>
                    <a href="contrat/contratList.php" <?= isActive('contratList.php') ? 'class="active"' : '' ?>>Les contrats</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= $sub_consult_open ? 'open' : '' ?>">
                <a onclick="toggleSubMenu(this)" <?= (isActive('list_consultation.php', 'list_ordonnance.php') || $is_consult_dash) ? 'class="active"' : '' ?>><i class="fa-solid fa-file-contract nav-icon"></i><span>Ordonnances &amp; Consultations</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu <?= $sub_consult_open ? 'open' : '' ?>">
                    <a href="dashboard.php" <?= $is_consult_dash ? 'class="active"' : '' ?>>Toutes les consultations</a>
                    <a href="list_consultation.php" <?= isActive('list_consultation.php') ? 'class="active"' : '' ?>>Les consultations</a>
                    <a href="list_ordonnance.php" <?= isActive('list_ordonnance.php') ? 'class="active"' : '' ?>>Les ordonnances</a>
                </div>
            </div>
            <div class="nav-item has-sub <?= $sub_pharma_open ? 'open' : '' ?>">
                <a onclick="toggleSubMenu(this)" <?= $sub_pharma_open ? 'class="active"' : '' ?>><i class="fa-solid fa-prescription-bottle-medical nav-icon"></i><span>Pharmacies &amp; Médicaments</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu <?= $sub_pharma_open ? 'open' : '' ?>">
                    <a href="listepharmacie.php" <?= isActive('listepharmacie.php') ? 'class="active"' : '' ?>>Les pharmacies</a>
                    <a href="listemedicament.php" <?= isActive('listemedicament.php') ? 'class="active"' : '' ?>>Les médicaments</a>
                </div>
            </div>
            <div class="nav-item">
                <a href="statistiques.php" <?= isActive('statistiques.php') ? 'class="active"' : '' ?>><i class="fa-solid fa-chart-pie nav-icon"></i><span><?= tr('bo_stats') ?></span></a>
            </div>
            <div class="nav-item has-sub <?= $sub_forum_open ? 'open' : '' ?>">
                <a onclick="toggleSubMenu(this)" <?= $sub_forum_open ? 'class="active"' : '' ?>><i class="fas fa-comments nav-icon"></i><span>Forum</span><i class="fas fa-chevron-right nav-arrow"></i></a>
                <div class="sub-menu <?= $sub_forum_open ? 'open' : '' ?>">
                    <a href="dashboardf.php" <?= isActive('dashboardf.php') ? 'class="active"' : '' ?>>📊 Dashboard Forum</a>
                    <a href="../Frontoffice/postlist.php" <?= isActive('postlist.php', 'postList.php') ? 'class="active"' : '' ?>>📝 Tous les posts</a>
                    <a href="addpost.php" <?= isActive('addpost.php') ? 'class="active"' : '' ?>>Ajouter un post</a>
                    <a href="dashboard.php" <?= $is_consult_dash ? 'class="active"' : '' ?>>Gestion des posts</a>
                </div>
            </div>
            <div class="nav-section-label">Configuration</div>
            <div class="nav-item">
                <a href="../front/indexp.php" <?= isActive('indexp.php') ? 'class="active"' : '' ?>><i class="fas fa-globe nav-icon"></i><span>Voir le site</span></a>
            </div>
            <div class="nav-item">
                <a href="../back/loginadmin.html"><i class="fas fa-sign-out-alt nav-icon"></i><span>Déconnexion</span></a>
            </div>
        </nav>
        <div class="sidebar-toolbar" style="padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.06); margin-top: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <button type="button" class="dark-toggle" onclick="toggleDark()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: rgba(255,255,255,0.7);" title="Mode sombre"><i class="fa-solid fa-moon"></i></button>
                <div class="notification-container" style="position: relative;">
                    <button type="button" id="notifToggleAddOrd" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: rgba(255,255,255,0.7); position: relative;" title="Notifications de stock">
                        <i class="fa-solid fa-bell"></i>
                        <?php if ($nb_alertes_ao > 0): ?>
                            <span style="position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border-radius: 50%; font-size: 0.65rem; padding: 2px 5px; font-weight: bold;"><?= $nb_alertes_ao ?></span>
                        <?php endif; ?>
                    </button>
                    <div id="notifDropdownAddOrd" style="display: none; position: absolute; bottom: 100%; left: 0; margin-bottom: 10px; width: 280px; background: var(--bg-card, white); border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 1001; color: var(--text-main);">
                        <div style="padding: 12px 15px; border-bottom: 1px solid var(--border); font-weight: 600;">Alertes de stock (<?= $nb_alertes_ao ?>)</div>
                        <div style="max-height: 220px; overflow-y: auto;">
                            <?php if ($nb_alertes_ao > 0): ?>
                                <?php foreach ($alertes_stock_ao as $al): ?>
                                    <a href="editmedicament.php?id_medicament=<?= (int)$al['id_medicament'] ?>" style="display: block; padding: 12px 15px; border-bottom: 1px solid var(--border); text-decoration: none; color: inherit;">
                                        <span style="font-weight: 600;"><?= htmlspecialchars($al['nom']) ?></span>
                                        <span style="color: #f59e0b; font-size: 0.8rem;">Stock: <?= (int)$al['stock'] ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="padding: 16px; text-align: center; color: var(--text-muted);">Aucune alerte.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 5px; align-items: center; font-size: 0.9rem;">
                    <a href="?lang=fr" style="color: <?= ($_SESSION['lang'] ?? 'fr') === 'fr' ? 'var(--primary)' : 'rgba(255,255,255,0.45)' ?>; font-weight: 700; text-decoration: none;">FR</a>
                    <span style="color: rgba(255,255,255,0.35);">|</span>
                    <a href="?lang=en" style="color: <?= ($_SESSION['lang'] ?? 'fr') === 'en' ? 'var(--primary)' : 'rgba(255,255,255,0.45)' ?>; font-weight: 700; text-decoration: none;">EN</a>
                </div>
            </div>
        </div>
        <div class="sidebar-footer">
            <div class="sidebar-version">Version 1.0</div>
        </div>
        <script>
        (function(){
            var b = document.getElementById('notifToggleAddOrd'), d = document.getElementById('notifDropdownAddOrd');
            if (b && d) {
                b.addEventListener('click', function(e) { e.stopPropagation(); d.style.display = d.style.display === 'block' ? 'none' : 'block'; });
                document.addEventListener('click', function(e) { if (!d.contains(e.target) && e.target !== b) d.style.display = 'none'; });
            }
        })();
        </script>
        <?php endif; ?>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <div class="page-title">Nouvelle Ordonnance</div>
                    <div class="breadcrumb">
                        <a href="list_ordonnance.php">Ordonnances</a>
                        <span>/</span>
                        <span>Ajouter</span>
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

            <?php if (empty($consultations)): ?>
                <div class="alert alert-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Aucune consultation terminée disponible.
                </div>
            <?php else: ?>

            <div class="card" style="max-width:700px; margin:0 auto;">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-file-prescription" style="color:var(--primary)"></i>
                        Ajouter une ordonnance
                    </div>
                </div>

                <form action="" method="POST" id="formAdd" onsubmit="return validerFormulaire()">

                    <div class="form-group">
                        <label class="form-label">Consultation * <span class="text-muted" style="font-weight:400;font-size:0.8rem">(consultations terminées uniquement)</span></label>
                        <select name="id_consultation" id="id_consultation" class="form-control">
                            <option value="">-- Choisir une consultation --</option>
                            <?php foreach ($consultations as $c): ?>
                            <option value="<?= $c['id_consultation'] ?>">
                                #<?= $c['id_consultation'] ?> — <?= date('d/m/Y H:i', strtotime($c['date_consultation'])) ?> — <?= htmlspecialchars(substr($c['diagnostique'], 0, 40)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-error" id="err_consultation">Veuillez choisir une consultation.</span>
                    </div>

                    <div class="form-group" style="position:relative;">
                        <label class="form-label">Médicaments *
                            <span class="text-muted" style="font-weight:400;font-size:0.8rem">(min. 5 caractères)</span>
                            <span class="badge badge-primary" style="font-size:0.75rem; margin-left:8px;">
                                <i class="fa-solid fa-robot"></i> IA
                            </span>
                        </label>
                        <textarea name="medicaments" id="medicaments" class="form-control"
                            placeholder="Ex: Paracétamol 500mg, Ibuprofène 400mg..."
                            oninput="compter('medicaments', 'count_med', 5); suggererMedicament();"></textarea>
                        <span class="form-hint"><span id="count_med">0</span> caractères</span>
                        <span class="form-error" id="err_med">Les médicaments sont obligatoires (min. 5 caractères).</span>
                        <div id="suggestions_med" style="position:absolute; background:white; border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); z-index:1000; width:100%; display:none; margin-top:4px;"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instructions *
                            <span class="text-muted" style="font-weight:400;font-size:0.8rem">(min. 5 caractères)</span>
                        </label>
                        <textarea name="instructions" id="instructions" class="form-control"
                            placeholder="Ex: Prendre 1 comprimé 3 fois par jour après les repas..."
                            oninput="compter('instructions', 'count_inst', 5)"></textarea>
                        <span class="form-hint"><span id="count_inst">0</span> caractères</span>
                        <span class="form-error" id="err_inst">Les instructions sont obligatoires (min. 5 caractères).</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Durée du traitement *
                            <span class="text-muted" style="font-weight:400;font-size:0.8rem">(en jours)</span>
                        </label>
                        <input type="number" name="duree_traitement" id="duree_traitement" class="form-control"
                            placeholder="Ex: 7" min="1">
                        <span class="form-error" id="err_duree">La durée doit être supérieure à 0.</span>
                    </div>

                    <!-- SIGNATURE -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-signature" style="color:var(--primary)"></i>
                            Signature du médecin *
                        </label>
                        <p class="form-hint">Dessinez votre signature dans le cadre ci-dessous</p>

                        <canvas id="signatureCanvas" width="660" height="150"></canvas>

                        <div class="signature-toolbar">
                            <span style="font-size:0.82rem; color:var(--text-muted);">Couleur :</span>
                            <div class="color-btn active" style="background:#0f172a;" onclick="setCouleur('#0f172a', this)" title="Noir"></div>
                            <div class="color-btn" style="background:#0ea5e9;" onclick="setCouleur('#0ea5e9', this)" title="Bleu"></div>
                            <div class="color-btn" style="background:#ef4444;" onclick="setCouleur('#ef4444', this)" title="Rouge"></div>

                            <span style="font-size:0.82rem; color:var(--text-muted); margin-left:8px;">Épaisseur :</span>
                            <select id="epaisseur" class="form-control" style="width:80px; padding:4px 8px; font-size:0.82rem;" onchange="setEpaisseur(this.value)">
                                <option value="1">Fine</option>
                                <option value="2" selected>Normale</option>
                                <option value="4">Épaisse</option>
                            </select>

                            <button type="button" onclick="effacerSignature()" class="btn btn-outline btn-sm" style="margin-left:auto;">
                                <i class="fa-solid fa-eraser"></i> Effacer
                            </button>
                        </div>

                        <span class="form-error" id="err_signature">La signature est obligatoire.</span>
                        <input type="hidden" name="signature" id="signatureData">
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Ajouter
                        </button>
                        <a href="list_ordonnance.php" class="btn btn-outline">Annuler</a>
                    </div>

                </form>
            </div>
            <?php endif; ?>
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

    // ========================
    // SIGNATURE CANVAS
    // ========================
    const canvas = document.getElementById('signatureCanvas');
    const ctx = canvas.getContext('2d');
    let dessin = false;
    let couleur = '#0f172a';
    let epaisseur = 2;

    // Redimensionner canvas
    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        canvas.width = rect.width * ratio;
        canvas.height = 150 * ratio;
        ctx.scale(ratio, ratio);
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        if (e.touches) {
            return {
                x: (e.touches[0].clientX - rect.left),
                y: (e.touches[0].clientY - rect.top)
            };
        }
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }

    canvas.addEventListener('mousedown', (e) => {
        dessin = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    });

    canvas.addEventListener('mousemove', (e) => {
        if (!dessin) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.strokeStyle = couleur;
        ctx.lineWidth = epaisseur;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.stroke();
    });

    canvas.addEventListener('mouseup', () => { dessin = false; });
    canvas.addEventListener('mouseleave', () => { dessin = false; });

    // Support tactile
    canvas.addEventListener('touchstart', (e) => {
        e.preventDefault();
        dessin = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    });

    canvas.addEventListener('touchmove', (e) => {
        e.preventDefault();
        if (!dessin) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.strokeStyle = couleur;
        ctx.lineWidth = epaisseur;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.stroke();
    });

    canvas.addEventListener('touchend', () => { dessin = false; });

    function setCouleur(c, btn) {
        couleur = c;
        document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    function setEpaisseur(val) {
        epaisseur = parseInt(val);
    }

    function effacerSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('signatureData').value = '';
    }

    function signatureVide() {
        const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
        return !data.some(channel => channel !== 0);
    }

    // ========================
    // SUGGESTION IA
    // ========================
    let timer = null;

    function suggererMedicament() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const q = document.getElementById('medicaments').value.trim();
            const box = document.getElementById('suggestions_med');

            if (q.length < 2) { box.style.display = 'none'; return; }

            const dernierMot = q.split(/[,\n]/).pop().trim();
            if (dernierMot.length < 2) { box.style.display = 'none'; return; }

            box.innerHTML = "<div style='padding:10px'>Chargement...</div>";
            box.style.display = 'block';

            fetch('suggest_medicament.php?q=' + encodeURIComponent(dernierMot), {
                cache: 'no-store',
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (!Array.isArray(data) || data.length === 0) {
                    box.innerHTML = "<div style='padding:10px'>Aucune suggestion</div>";
                    return;
                }
                box.innerHTML = data.map(s =>
                    `<div onclick="choisirMedicament('${s.replace(/'/g, "\\'")}')"
                    style="padding:10px 16px; cursor:pointer; font-size:0.9rem; border-bottom:1px solid var(--border);"
                    onmouseover="this.style.background='var(--bg)'"
                    onmouseout="this.style.background='white'">
                    <i class="fa-solid fa-pills" style="color:var(--primary); margin-right:8px;"></i>
                    ${s}</div>`
                ).join('');
                box.style.display = 'block';
            })
            .catch(() => { box.innerHTML = "<div style='padding:10px'>Aucune suggestion</div>"; });
        }, 400);
    }

    function choisirMedicament(texte) {
        const med = document.getElementById('medicaments');
        const parts = med.value.split(/[,\n]/);
        parts[parts.length - 1] = texte;
        med.value = parts.join(', ') + ', ';
        document.getElementById('suggestions_med').style.display = 'none';
        compter('medicaments', 'count_med', 5);
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#medicaments') && !e.target.closest('#suggestions_med')) {
            document.getElementById('suggestions_med').style.display = 'none';
        }
    });

    // ========================
    // VALIDATION
    // ========================
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

        const consultation = document.getElementById('id_consultation').value;
        if (!consultation) {
            document.getElementById('id_consultation').classList.add('is-invalid');
            document.getElementById('err_consultation').style.display = 'block';
            valide = false;
        }

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

        // Vérifier signature
        if (signatureVide()) {
            document.getElementById('err_signature').style.display = 'block';
            canvas.style.borderColor = 'var(--danger)';
            valide = false;
        } else {
            document.getElementById('signatureData').value = canvas.toDataURL('image/png');
            canvas.style.borderColor = '';
        }

        return valide;
    }

    // ========================
    // MODE SOMBRE
    // ========================
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