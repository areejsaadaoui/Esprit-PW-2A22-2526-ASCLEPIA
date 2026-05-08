<?php
include '../../Controller/PharmacieC.php';

$pc = new pharmacieC();
$liste = $pc->listepharmacie();

include 'header_back.php';
?>

<main class="admin-container">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">BackOffice</div>
            <h2 class="section-title">Liste des pharmacies</h2>
            <p class="section-desc">Gérez les pharmacies partenaires de la plateforme ASCLEPIA.</p>
        </div>

        <div class="crud-card animate-fadeInUp">
            <div class="d-flex justify-between align-center mb-4">
                <h3 style="margin: 0;">Pharmacies Enregistrées</h3>
                <a href="addpharmacie.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Nouveau
                </a>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Adresse</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($liste as $p): ?>
                            <tr>
                                <td>#<?= $p['id_pharmacie'] ?></td>
                                <td style="font-weight: 600;"><?= $p['nom'] ?></td>
                                <td><?= $p['adresse'] ?></td>
                                <td><?= $p['telephone'] ?></td>
                                <td><?= $p['email'] ?></td>
                                <td class="action-btns">
                                    <a href="editpharmacie.php?id_pharmacie=<?= $p['id_pharmacie'] ?>&nom=<?= urlencode($p['nom']) ?>&adresse=<?= urlencode($p['adresse']) ?>&telephone=<?= urlencode($p['telephone']) ?>&email=<?= urlencode($p['email']) ?>" class="btn btn-outline btn-sm" title="Modifier">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="deletepharmacie.php?id_pharmacie=<?= $p['id_pharmacie'] ?>" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: #fca5a5;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette pharmacie ?')" title="Supprimer">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include 'footer_back.php'; ?>