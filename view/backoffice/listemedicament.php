<?php
require_once '../../Controller/MedicamentC.php';

$mc = new medicamentC();
$liste = $mc->afficherMedicaments();

include 'header_back.php';
?>

<main class="admin-container">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">BackOffice</div>
            <h2 class="section-title">Gestion des Médicaments</h2>
            <p class="section-desc">Gérez le catalogue des médicaments disponibles sur la plateforme ASCLEPIA.</p>
        </div>

        <div class="crud-card animate-fadeInUp">
            <div class="d-flex justify-between align-center mb-4">
                <h3 style="margin: 0;">Médicaments Enregistrés</h3>
                <a href="addmedicament.php" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Ajouter un Médicament
                </a>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Pharmacie</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($liste as $m): ?>
                            <tr>
                                <td>
                                    <?php if(!empty($m['images'])): ?>
                                        <img src="<?= $m['images'] ?>" alt="<?= $m['nom'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa-solid fa-pills text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 600;"><?= $m['nom'] ?></td>
                                <td><span class="badge badge-primary"><?= $m['categorie'] ?></span></td>
                                <td><span class="badge badge-outline"><?= htmlspecialchars($m['nom_pharmacie']) ?></span></td>
                                <td style="font-weight: 700; color: var(--primary);"><?= number_format($m['prix'], 3) ?> DT</td>
                                <td>
                                    <?php if($m['stock'] > 10): ?>
                                        <span class="badge badge-success"><?= $m['stock'] ?> en stock</span>
                                    <?php elseif($m['stock'] > 0): ?>
                                        <span class="badge badge-warning"><?= $m['stock'] ?> faible</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Rupture</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-btns">
                                    <a href="editmedicament.php?id_medicament=<?= $m['id_medicament'] ?>" class="btn btn-outline btn-sm" title="Modifier">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="deletemedicament.php?id_medicament=<?= $m['id_medicament'] ?>" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: #fca5a5;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce médicament ?')" title="Supprimer">
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
