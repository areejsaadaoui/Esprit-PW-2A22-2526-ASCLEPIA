<?php
// update_password.php - Version avec vérification du code
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
$code = trim($data['code'] ?? '');      // ← NOUVEAU : code de vérification
$new_password = $data['new_password'] ?? '';

if (empty($email) || empty($code) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes (email, code et mot de passe requis)']);
    exit();
}

// Vérifier la complexité du mot de passe
if (strlen($new_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères']);
    exit();
}

if (!preg_match('/[0-9]/', $new_password) || !preg_match('/[a-zA-Z]/', $new_password)) {
    echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir des chiffres et des lettres']);
    exit();
}

// Vérifier que l'email existe ET que le code est valide ET non expiré
$stmt = $pdo->prepare("SELECT id_user FROM utilisateur WHERE email = :email AND reset_code = :code AND reset_code_expiry > NOW()");
$stmt->execute([
    ':email' => $email,
    ':code' => $code
]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Code invalide ou expiré (10 minutes). Veuillez recommencer.']);
    exit();
}

// Mettre à jour le mot de passe et supprimer le code (usage unique)
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$updateStmt = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = :password, reset_code = NULL, reset_code_expiry = NULL WHERE email = :email");
$updateStmt->execute([
    ':password' => $hashed_password,
    ':email' => $email
]);

echo json_encode(['success' => true, 'message' => 'Mot de passe mis à jour avec succès']);
?>