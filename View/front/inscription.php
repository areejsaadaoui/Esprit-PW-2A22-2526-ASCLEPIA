<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../Model/user.php';
require_once __DIR__ . '/../../config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Aucune donnée']);
    exit();
}

$user = $data['user'];

try {
    $pdo = config::getConnexion();

    // Vérifier si l'email existe déjà
    $check = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ?");
    $check->execute([$user['email']]);

    if ($check->fetchColumn() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email existe déjà'
        ]);
        exit();
    }

    // Hash du mot de passe
    $hashed_password = password_hash($user['mot_de_passe'], PASSWORD_DEFAULT);

    // Insertion dans la table utilisateur
    $sql = "INSERT INTO utilisateur 
            (nom, email, mot_de_passe, adresse, role, date_naissance, telephone, description, date_creation)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $user['nom'],
        $user['email'],
        $hashed_password,
        $user['adresse'],
        $user['role'],
        $user['date_naissance'] ?? null,
        $user['telephone'] ?? null,
        $user['description'] ?? null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Utilisateur ajouté avec succès'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
