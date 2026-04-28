<?php
require_once '../../../Controller/ContratController.php';
require_once '../../../Controller/AssuranceController.php';

$contratC   = new ContratController();
$assuranceC = new AssuranceController();

$id_assurance = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$assurance = $assuranceC->showAssurance($id_assurance);
$contrats = $contratC->getContratsByAssurance($id_assurance);
$count    = count($contrats);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrats - <?= htmlspecialchars($assurance['nom_assurance'] ?? '') ?></title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <link rel="stylesheet" href="../../../assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="admin-wrapper">

    <aside class="sidebar">
        <a href="#" class="sidebar-brand">
            <div class="sidebar-logo">🏥</div>
            <div class="sidebar-title">ASCL<span>EPIA</span></div>
        </a>
        <div class="sidebar-user">
            <div class="user-avatar">A</div>
            <div class="user-info">
                <div class="name">Administrateur</div>
                <div class="role">Super Admin</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Gestion</div>
            <div class="nav-item">
                <a href="../assurancelist.php">
                    <span class="nav-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    Assurances
                </a>
            </div>
            <div class="nav-item">
                <a href="contratList.php" class="active">
                    <span class="nav-icon"><i class="fa-solid fa-file-contract"></i></span>
                    Contrats
                </a>
            </div>
        </nav>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <div class="page-title">Contrats de l'assurance</div>
                    <div class="breadcrumb">
                        <a href="../assurancelist.php">Assurances</a>
                        <span>/</span>
                        <span><?= htmlspecialchars($assurance['nom_assurance'] ?? '') ?></span>
                        <span>/</span>
                        <span>Contrats</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="addContrat.php?id_assurance=<?= $id_assurance ?>" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Nouveau contrat
                </a>
                <a href="../assurancelist.php" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>

        <div class="page-content">

            <!-- INFO ASSURANCE -->
            <div class="card mb-4" style="background: linear-gradient(135deg, #0f172a, #0c4a6e); border:none;">
                <div class="d-flex align-center gap-3">
                    <div style="background:rgba(255,255,255,0.1); border-radius:var(--radius); width:60px; height:60px; display:flex; align-items:center; justify-content:center; font-size:1.8rem; flex-shrink:0;">
                        🛡️
                    </div>
                    <div>
                        <h2 style="color:white; font-size:1.3rem; margin-bottom:6px;">
                            <?= htmlspecialchars($assurance['nom_assurance'] ?? '') ?>
                        </h2>
                        <div class="d-flex gap-2" style="flex-wrap:wrap;">
                            <span class="badge badge-primary"><?= htmlspecialchars($assurance['TYPE'] ?? '') ?></span>
                            <span style="color:rgba(255,255,255,0.7); font-size:0.85rem;">
                                <i class="fa-solid fa-money-bill"></i> <?= number_format($assurance['prix'] ?? 0, 2) ?> DT / mois
                            </span>
                            <span style="color:rgba(255,255,255,0.7); font-size:0.85rem;">
                                <i class="fa-solid fa-percent"></i> <?= $assurance['taux_remboursement'] ?? 0 ?>% remboursement
                            </span>
                            <span style="color:rgba(255,255,255,0.7); font-size:0.85rem;">
                                <i class="fa-solid fa-clock"></i> <?= $assurance['duree'] ?? 0 ?> mois
                            </span>
                        </div>
                    </div>
                    <div style="margin-left:auto; text-align:right;">
                        <div style="font-size:2rem; font-weight:800; color:white;"><?= $count ?></div>
                        <div style="color:rgba(255,255,255,0.6); font-size:0.82rem;">contrat(s)</div>
                    </div>
                </div>
            </div>

            <!-- TABLE CONTRATS -->
            <?php if ($count === 0): ?>
                <div class="empty-state">
                    <div class="icon">📄</div>
                    <h3>Aucun contrat pour cette assurance</h3>
                    <p>Personne n'a encore souscrit à cette assurance.</p>
                    <a href="addContrat.php?id_assurance=<?= $id_assurance ?>" class="btn btn-primary btn-sm">+ Ajouter un contrat</a>
                </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Assurance</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Montant (DT)</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contrats as $c): ?>
                        <tr>
                            <td><?= $c['id_contrat'] ?></td>
                            <td><strong><?= htmlspecialchars($c['nom_assurance']) ?></strong></td>
                            <td><?= $c['date_d'] ?></td>
                            <td><?= $c['date_f'] ?? '—' ?></td>
                            <td><?= number_format($c['montant'], 2) ?> DT</td>
                            <td>
                                <?php
                                $badgeClass = 'badge-gray';
                                if ($c['statut'] === 'Actif')   $badgeClass = 'badge-success';
                                if ($c['statut'] === 'Expiré')  $badgeClass = 'badge-warning';
                                if ($c['statut'] === 'Annulé')  $badgeClass = 'badge-danger';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($c['statut']) ?></span>
                            </td>
                            <td>
                                <div class="actions">
                                    <form method="POST" action="updateContrat.php" style="display:inline">
                                        <input type="hidden" name="id" value="<?= $c['id_contrat'] ?>">
                                        <input type="hidden" name="id_assurance_retour" value="<?= $id_assurance ?>">
                                        <button type="submit" class="btn btn-outline btn-sm">
                                            <i class="fa-solid fa-pen"></i> Modifier
                                        </button>
                                    </form>
                                    <a href="deleteContrat.php?id=<?= $c['id_contrat'] ?>&id_assurance=<?= $id_assurance ?>"
                                       onclick="return confirm('Confirmer la suppression ?')"
                                       class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });
</script>
</body>
</html>