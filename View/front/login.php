<?php
session_start();

$host   = 'localhost';
$dbname = 'fleliss';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email        = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // Validation basique côté serveur
    if (empty($email) || empty($mot_de_passe)) {
        header('Location: login.html?error=1');
        exit();
    }

    // Chercher l'utilisateur par email
    $stmt = $pdo->prepare("SELECT id_user, nom, email, mot_de_passe, role
                           FROM utilisateur
                           WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cas 1 : email introuvable → inviter à s'inscrire
    if (!$user) {
        header('Location: login.html?error=1');
        exit();
    }

    // Cas 2 : mot de passe incorrect
    if (!password_verify($mot_de_passe, $user['mot_de_passe'])) {
        header('Location: login.html?error=2');
        exit();
    }

    // Connexion réussie
    $_SESSION['user_id']    = $user['id_user'];
    $_SESSION['user_nom']   = $user['nom'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];

    // Option "Se souvenir de moi"
    if (isset($_POST['remember'])) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), "/");
    }

    header('Location: indexp.html');
    exit();

} else {
    header('Location: login.html');
    exit();
}
?>