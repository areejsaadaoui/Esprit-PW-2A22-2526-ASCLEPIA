<?php 
require_once '../../Controller/MedicamentC.php';
require_once '../../Controller/PharmacieC.php';

$mc = new medicamentC();
$pc = new pharmacieC();
$pharmacies = $pc->listepharmacie();

if (isset($_GET['id_medicament'])) {
    $currentMed = $mc->recupererMedicament($_GET['id_medicament']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicament = new medicament(
        $_POST['id_medicament'],
        $_POST['nom'],
        (float)$_POST['prix'],
        (int)$_POST['stock'],
        $_POST['categorie'],
        $_POST['images'],
        (int)$_POST['id_pharmacie']
    );
    $mc->modifierMedicament($medicament, $_POST['id_medicament']);
    header('Location: listemedicament.php');
    exit();
}

include 'header_back.php';
?>

<main class="admin-container">
    <div class="container" style="max-width: 600px;">
        <div class="section-header">
            <div class="section-tag">Modification</div>
            <h2 class="section-title">Modifier le Médicament</h2>
            <p class="section-desc">Mettez à jour les informations du produit sélectionné.</p>
        </div>

        <div class="crud-card animate-fadeInUp">
            <?php if(isset($currentMed) && $currentMed): ?>
            <form action="editmedicament.php" method="POST" id="editMedForm">
                <input type="hidden" name="id_medicament" value="<?= $currentMed['id_medicament'] ?>">
                
                <div class="form-group">
                    <label for="nom">Nom du Médicament</label>
                    <input type="text" id="nom" name="nom" class="form-control" value="<?= $currentMed['nom'] ?>" required>
                    <div id="nom-error" class="error-message">Le nom doit contenir au moins 3 caractères.</div>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label for="categorie">Catégorie</label>
                        <select id="categorie" name="categorie" class="form-control" required>
                            <option value="Analgésique" <?= $currentMed['categorie'] == 'Analgésique' ? 'selected' : '' ?>>Analgésique</option>
                            <option value="Antibiotique" <?= $currentMed['categorie'] == 'Antibiotique' ? 'selected' : '' ?>>Antibiotique</option>
                            <option value="Anti-inflammatoire" <?= $currentMed['categorie'] == 'Anti-inflammatoire' ? 'selected' : '' ?>>Anti-inflammatoire</option>
                            <option value="Vitamine" <?= $currentMed['categorie'] == 'Vitamine' ? 'selected' : '' ?>>Vitamine</option>
                            <option value="Autre" <?= $currentMed['categorie'] == 'Autre' ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>
                    <div>
                        <label for="prix">Prix (DT)</label>
                        <input type="number" step="0.001" id="prix" name="prix" class="form-control" value="<?= $currentMed['prix'] ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_pharmacie">Pharmacie Associée</label>
                    <select id="id_pharmacie" name="id_pharmacie" class="form-control" required>
                        <?php foreach($pharmacies as $p): ?>
                            <option value="<?= $p['id_pharmacie'] ?>" <?= $currentMed['id_pharmacie'] == $p['id_pharmacie'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="stock">Quantité en Stock</label>
                    <input type="number" id="stock" name="stock" class="form-control" value="<?= $currentMed['stock'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="images">URL de l'image</label>
                    <input type="text" id="images" name="images" class="form-control" value="<?= $currentMed['images'] ?>">
                </div>

                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Enregistrer les Modifications
                    </button>
                    <a href="listemedicament.php" class="btn btn-outline">
                        Annuler
                    </a>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-danger">Médicament introuvable.</div>
                <a href="listemedicament.php" class="btn btn-primary">Retour à la liste</a>
            <?php endif; ?>
        </div>
    </div>  
</main>

<script>
    document.getElementById('editMedForm').addEventListener('submit', function(e) {
        const nom = document.getElementById('nom').value;
        if (nom.length < 3) {
            document.getElementById('nom-error').style.display = 'block';
            e.preventDefault();
        }
    });
</script>

<?php include 'footer_back.php'; ?>
