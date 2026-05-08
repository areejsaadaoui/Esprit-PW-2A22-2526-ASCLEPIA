<?php
// verify_code.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$dbname = 'fleliss';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion: ' . $e->getMessage()]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$code = trim($data['code'] ?? '');

if (empty($email) || empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Email et code requis']);
    exit();
}

// Vérifier si le code existe et n'est pas expiré
$stmt = $pdo->prepare("SELECT id_user, reset_code, reset_code_expiry FROM utilisateur WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Email non trouvé']);
    exit();
}

if (empty($user['reset_code'])) {
    echo json_encode(['success' => false, 'message' => 'Aucun code actif. Demandez un nouveau code.']);
    exit();
}

// Vérifier si le code est expiré
$expiry_time = strtotime($user['reset_code_expiry']);
$now = time();

if ($expiry_time < $now) {
    echo json_encode(['success' => false, 'message' => 'Code expiré (10 minutes). Renvoyez un nouveau code.']);
    exit();
}

// Vérifier si le code correspond
if ($user['reset_code'] == $code) {
    echo json_encode(['success' => true, 'message' => 'Code valide']);
} else {
    echo json_encode(['success' => false, 'message' => 'Code incorrect. Veuillez vérifier.']);
}
?>