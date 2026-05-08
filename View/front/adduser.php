<?php

header('Content-Type: application/json');

require_once '../../config.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "Aucune donnée"
    ]);
    exit();
}

$user = $data["user"];

try {
    $pdo = config::getConnexion();

    // Vérifier si l'email existe déjà
    $check = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email=?");
    $check->execute([$user["email"]]);

    if ($check->fetchColumn() > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Email existe déjà"
        ]);
        exit();
    }

    // Hacher le mot de passe
    $password = password_hash($user["mot_de_passe"], PASSWORD_DEFAULT);

    // Insérer l'utilisateur
    $sql = "INSERT INTO utilisateur
            (nom, email, mot_de_passe, adresse, role, date_naissance, telephone, description, date_creation)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $user["nom"],
        $user["email"],
        $password,
        $user["adresse"],
        $user["role"],
        $user["date_naissance"],
        $user["telephone"],
        $user["description"]
    ]);

    // ========== ENVOI DE L'EMAIL DE BIENVENUE (VRAI) ==========
    require_once __DIR__ . '/send_welcome_email.php';
    
    $nom = $user["nom"];
    $email = $user["email"];
    $role = $user["role"];
    
    $emailSent = sendWelcomeEmail($email, $nom, $role);
    
    if ($emailSent) {
        $message = "Utilisateur ajouté avec succès ! Un email de bienvenue a été envoyé.";
    } else {
        $message = "Utilisateur ajouté avec succès ! (Email non envoyé - erreur SMTP)";
    }
    // ==================================================

    echo json_encode([
        "success" => true,
        "message" => $message
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

?>