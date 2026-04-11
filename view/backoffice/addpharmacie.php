<?php 
require_once '../../controller/PharmacieC.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pharmacieC = new pharmacieC();
    $pharmacie = new pharmacie(
        null,
        $_POST['nom'],
        $_POST['adresse'],
        $_POST['telephone'],
        $_POST['email']
    );
    $pharmacieC->ajouterPharmacie($pharmacie);
    header('Location: listepharmacie.php');
    exit();
}

include 'header_back.php';
?>

<main class="admin-container">
    <div class="container" style="max-width: 600px;">
        <div class="section-header">
            <div class="section-tag">Formulaire</div>
            <h2 class="section-title">Ajouter une Pharmacie</h2>
            <p class="section-desc">Remplissez les informations ci-dessous pour enregistrer une nouvelle pharmacie.</p>
        </div>

        <div class="crud-card animate-fadeInUp">
            <form action="addpharmacie.php" method="POST">
                <div class="form-group">
                    <label for="nom">Nom de la Pharmacie</label>
                    <input type="text" id="nom" name="nom" class="form-control" placeholder="Ex: Pharmacie Centrale" required>
                    <div id="nom-error" class="error-message">Le nom doit contenir au moins 3 caractères.</div>
                </div>

                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="adresse" class="form-control" placeholder="Ex: Rue Habib Bourguiba, Tunis" required>
                    <div id="adresse-error" class="error-message">L'adresse doit contenir au moins 5 caractères.</div>
                </div>

                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="text" id="telephone" name="telephone" class="form-control" placeholder="Ex: +216 71 123 456" required>
                    <div id="telephone-error" class="error-message">Le téléphone doit contenir exactement 8 chiffres.</div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Ex: contact@pharmacie.tn" required>
                    <div id="email-error" class="error-message">Veuillez entrer une adresse email valide.</div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i> Enregistrer
                    </button>
                    <a href="listepharmacie.php" class="btn btn-outline">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>  
</main>

<?php include 'footer_back.php'; ?>