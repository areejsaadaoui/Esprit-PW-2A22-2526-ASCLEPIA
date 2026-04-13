<?php
session_start();
header('Content-Type: application/json');

// Configuration de la base de données
$host = 'localhost';
$dbname = 'fleliss';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit();
}

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis']);
    exit();
}

$email = trim($data['email']);
$password = $data['password'];

// Validation basique
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs']);
    exit();
}

// Rechercher l'utilisateur par email
$sql = "SELECT id_user, nom, email, mot_de_passe, role FROM utilisateur WHERE email = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur existe
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Aucun compte trouvé avec cet email']);
    exit();
}

// Vérifier le mot de passe (supporte hash bcrypt ET mot de passe en clair)
$passwordValid = false;

// Si le mot de passe stocké commence par $2y$, c'est un hash bcrypt
if (strpos($user['mot_de_passe'], '$2y$') === 0) {
    // Format hashé - utiliser password_verify
    $passwordValid = password_verify($password, $user['mot_de_passe']);
} else {
    // Format clair - comparaison directe
    $passwordValid = ($password === $user['mot_de_passe']);
    
    // Si le mot de passe est valide en clair, on le hache automatiquement pour la prochaine fois
    if ($passwordValid) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateSql = "UPDATE utilisateur SET mot_de_passe = :new_password WHERE id_user = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            ':new_password' => $hashedPassword,
            ':id' => $user['id_user']
        ]);
    }
}

if (!$passwordValid) {
    echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
    exit();
}

// Vérifier le rôle (admin uniquement)
if ($user['role'] !== 'admin') {
    echo json_encode(['success' => true, 'is_admin' => false, 'message' => 'Accès non autorisé. Rôle administrateur requis.']);
    exit();
}

// Connexion réussie - Stocker en session
$_SESSION['user_id'] = $user['id_user'];
$_SESSION['user_nom'] = $user['nom'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['logged_in'] = true;

// Retourner succès
echo json_encode([
    'success' => true, 
    'is_admin' => true, 
    'message' => 'Connexion réussie',
    'user' => [
        'id' => $user['id_user'],
        'nom' => $user['nom'],
        'email' => $user['email'],
        'role' => $user['role']
    ]
]);
exit();
?>