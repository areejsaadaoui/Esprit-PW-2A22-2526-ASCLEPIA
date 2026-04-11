


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacie</title>
    <link rel="stylesheet" href="style.css" >
    <link rel="stylesheet" href="backoffice.css" >
    <link rel="stylesheet" href="frontoffice.css" >
</head>
<body>
    <div class="container">
        <h1>Ajouter une Pharmacie</h1>
        <form action="addpharmacie.php" method="POST">
            <label for="nom">Nom de la Pharmacie:</label>
            <input type="text" id="nom" name="nom" required>

            <label for="adresse">Adresse:</label>
            <input type="text" id="adresse" name="adresse" required>

            <label for="telephone">Téléphone:</label>
            <input type="text" id="telephone" name="telephone" required>

             <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <button type="submit">Ajouter</button>
        </form>
    </div>  
</body>
</html>


<?php 

//require_once '../config.php';
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
    header('Location: listpharmacie.php');
    exit();
}