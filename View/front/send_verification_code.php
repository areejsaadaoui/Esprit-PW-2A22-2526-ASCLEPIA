<?php
// send_verification_code.php - Dans View/front/
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclusion de PHPMailer (chemins corrects)
require_once __DIR__ . '/../assets/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../assets/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../assets/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Configuration de la base de données
$host = 'localhost';
$dbname = 'fleliss';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données: ' . $e->getMessage()]);
    exit();
}

// Récupération des données
$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email requis']);
    exit();
}

// Vérifier si l'email existe dans la base
$stmt = $pdo->prepare("SELECT id_user, nom FROM utilisateur WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Aucun compte trouvé avec cette adresse email']);
    exit();
}

// Générer un code à 6 chiffres
$code = sprintf("%06d", mt_rand(1, 999999));
$expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Sauvegarder le code dans la base de données
$updateStmt = $pdo->prepare("UPDATE utilisateur SET reset_code = :code, reset_code_expiry = :expiry WHERE email = :email");
$updateStmt->execute([
    ':code' => $code,
    ':expiry' => $expiry,
    ':email' => $email
]);

// Configuration de l'envoi d'email avec PHPMailer
$mail = new PHPMailer(true);

try {
    // Configuration SMTP (pour Gmail)
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'contact.asclepia@gmail.com';     // ⚠️ REMPLACE PAR TON EMAIL GMAIL
    $mail->Password   = 'gjpa zkeo tnyy prvt';           // ⚠️ REMPLACE PAR TON MOT DE PASSE D'APPLICATION
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    
    // Encodage UTF-8 pour éviter les problèmes d'accents
    $mail->CharSet = 'UTF-8';
    
    // Expéditeur
    $mail->setFrom('no-reply@asclepia.tn', 'ASCLEPIA');
    
    // Destinataire
    $mail->addAddress($email, $user['nom']);
    
    // Contenu de l'email (texte brut)
    $mail->isHTML(false);
    $mail->Subject = '=?UTF-8?B?' . base64_encode('🔐 Code de réinitialisation - ASCLEPIA') . '?=';
    $mail->Body    = "Bonjour " . $user['nom'] . ",\n\n"
                   . "Vous avez demandé la réinitialisation de votre mot de passe sur ASCLEPIA.\n\n"
                   . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
                   . "     Votre code de vérification : " . $code . "\n"
                   . "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n"
                   . "Ce code est valable pendant 10 minutes.\n"
                   . "Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.\n\n"
                   . "Cordialement,\n"
                   . "L'équipe ASCLEPIA";
    
    // Envoi de l'email
    $mail->send();
    
    // Réponse en cas de succès
    echo json_encode([
        'success' => true, 
        'message' => 'Un code de vérification a été envoyé à ' . $email
    ]);
    
} catch (Exception $e) {
    // En cas d'erreur d'envoi, on retourne quand même le code pour les tests en local
    echo json_encode([
        'success' => true, 
        'message' => '⚠️ Email non envoyé (erreur SMTP). Code de test: ' . $code,
        'test_code' => $code,
        'debug_error' => $mail->ErrorInfo
    ]);
}
?>