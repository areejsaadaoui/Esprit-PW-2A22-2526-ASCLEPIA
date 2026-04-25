<?php 
require_once '../../Controller/MedicamentC.php';
require_once '../../Controller/PharmacieC.php';

$pc = new pharmacieC();
$pharmacies = $pc->listepharmacie();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mc = new medicamentC();
    $medicament = new medicament(
        null,
        $_POST['nom'],
        (float)$_POST['prix'],
        (int)$_POST['stock'],
        $_POST['categorie'],
        $_POST['images'],
        (int)$_POST['id_pharmacie']
    );
    $mc->ajouterMedicament($medicament);
    header('Location: listemedicament.php');
    exit();
}

include 'header_back.php';
?>

<main class="admin-container">
    <div class="container" style="max-width: 600px;">
        <div class="section-header">
            <div class="section-tag">Formulaire</div>
            <h2 class="section-title">Ajouter un Médicament</h2>
            <p class="section-desc">Remplissez les informations ci-dessous pour ajouter un nouveau produit au catalogue.</p>
        </div>

        <div class="crud-card animate-fadeInUp">
            <form action="addmedicament.php" method="POST" id="medForm" novalidate>
                <div class="form-group">
                    <label for="nom">Nom du Médicament</label>
                    <input type="text" id="nom" name="nom" class="form-control" placeholder="Ex: Doliprane 1000mg">
                    <div id="nom-error" class="error-message">Le nom doit contenir au moins 3 caractères.</div>
                </div>

                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label for="categorie">Catégorie</label>
                        <select id="categorie" name="categorie" class="form-control">
                            <option value="">Choisir...</option>
                            <option value="Analgésique">Analgésique</option>
                            <option value="Antibiotique">Antibiotique</option>
                            <option value="Anti-inflammatoire">Anti-inflammatoire</option>
                            <option value="Vitamine">Vitamine</option>
                            <option value="Autre">Autre</option>
                        </select>
                        <div id="categorie-error" class="error-message">Veuillez sélectionner une catégorie.</div>
                    </div>
                    <div>
                        <label for="prix">Prix (DT)</label>
                        <input type="number" step="0.001" id="prix" name="prix" class="form-control" placeholder="0.000">
                        <div id="prix-error" class="error-message">Le prix doit être supérieur à 0.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="stock">Quantité en Stock</label>
                    <input type="number" id="stock" name="stock" class="form-control" placeholder="Ex: 50">
                    <div id="stock-error" class="error-message">Le stock doit être un nombre entier supérieur ou égal à 0.</div>
                </div>

                <div class="form-group">
                    <label for="id_pharmacie">Pharmacie Associée</label>
                    <select id="id_pharmacie" name="id_pharmacie" class="form-control">
                        <option value="">Sélectionner une pharmacie...</option>
                        <?php foreach($pharmacies as $p): ?>
                            <option value="<?= $p['id_pharmacie'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="id-pharmacie-error" class="error-message">Veuillez sélectionner une pharmacie.</div>
                </div>

                <div class="form-group">
                    <label for="images">URL de l'image (Optionnel)</label>
                    <input type="text" id="images" name="images" class="form-control" placeholder="Ex: https://.../image.jpg">
                    <p class="form-hint">Laissez vide pour utiliser une icône par défaut.</p>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i> Enregistrer le Produit
                    </button>
                    <a href="listemedicament.php" class="btn btn-outline">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>  
</main>

<script src="script.js"></script>

<?php include 'footer_back.php'; ?>
