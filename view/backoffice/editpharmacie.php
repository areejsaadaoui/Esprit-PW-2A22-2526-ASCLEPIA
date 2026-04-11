<?php
require_once '../../controller/PharmacieC.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pharmacie = new pharmacie(
        $_POST['id_pharmacie'],
        $_POST['nom'],
        $_POST['adresse'],
        $_POST['telephone'],
        $_POST['email']
    );
    $pharmacieC = new pharmacieC();
    $pharmacieC->modifierPharmacie($pharmacie, $_POST['id_pharmacie']);
    header('Location: listpharmacie.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modifier Pharmacie</title>
</head>
<body>
    <form action="editpharmacie.php" method="POST">
        <input type="hidden" name="id_pharmacie" value="<?php echo $_GET['id_pharmacie']; ?>">
        <label>Nom:</label>
        <input type="text" name="nom" value="<?php echo $_GET['nom']; ?>" required>
        <label>Adresse:</label>
        <input type="text" name="adresse" value="<?php echo $_GET['adresse']; ?>" required>
        <label>Téléphone:</label>
        <input type="text" name="telephone" value="<?php echo $_GET['telephone']; ?>" required>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $_GET['email']; ?>" required>
        <button type="submit">Modifier</button>
    </form>
</body>
</html>