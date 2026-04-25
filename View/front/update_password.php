<?php
// update_password.php - Dans View/front/
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'fleliss';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$new_password = $data['new_password'] ?? '';

if (empty($email) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit();
}

// Vérifier la complexité
if (strlen($new_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères']);
    exit();
}

if (!preg_match('/[0-9]/', $new_password) || !preg_match('/[a-zA-Z]/', $new_password) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
    echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir des chiffres, des lettres et des symboles']);
    exit();
}

$stmt = $pdo->prepare("SELECT id_user FROM utilisateur WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
    exit();
}

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$updateStmt = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = :password WHERE email = :email");
$updateStmt->execute([':password' => $hashed_password, ':email' => $email]);

echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour avec succès']);
?>