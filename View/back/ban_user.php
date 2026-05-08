<?php
// ban_user.php - Gestion du bannissement des utilisateurs
session_start();
header('Content-Type: application/json');

// Vérifier si l'admin est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

$host = 'localhost';
$dbname = 'fleliss';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? null;
    $action = $data['action'] ?? 'ban';
    $duration = $data['duration'] ?? 'hour';

    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
        exit();
    }

    try {
        if ($action === 'ban') {
            // Calculer la date de fin de bannissement
            $ban_until = ($duration === 'day') ? 
                date('Y-m-d H:i:s', strtotime('+1 day')) : 
                date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Bannir l'utilisateur
            $stmt = $pdo->prepare("UPDATE utilisateur SET is_banned = 1, ban_until = :ban_until WHERE id_user = :id");
            $stmt->execute([':ban_until' => $ban_until, ':id' => $user_id]);
            
            // Récupérer l'email de l'utilisateur
            $stmt = $pdo->prepare("SELECT email, nom FROM utilisateur WHERE id_user = :id");
            $stmt->execute([':id' => $user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                sendBanEmail($user['email'], $user['nom'], $ban_until, $duration);
            }
            
            echo json_encode(['success' => true, 'message' => 'Utilisateur banni avec succès', 'ban_until' => $ban_until]);
            
        } elseif ($action === 'unban') {
            // Lever le bannissement
            $stmt = $pdo->prepare("UPDATE utilisateur SET is_banned = 0, ban_until = NULL WHERE id_user = :id");
            $stmt->execute([':id' => $user_id]);
            
            // Récupérer l'email de l'utilisateur
            $stmt = $pdo->prepare("SELECT email, nom FROM utilisateur WHERE id_user = :id");
            $stmt->execute([':id' => $user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                sendUnbanEmail($user['email'], $user['nom']);
            }
            
            echo json_encode(['success' => true, 'message' => 'Bannissement levé avec succès']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
}

function sendBanEmail($email, $nom, $ban_until, $duration) {
    $to = $email;
    $subject = "🔒 Compte suspendu - ASCLEPIA";
    
    $date_ban = new DateTime($ban_until);
    $date_formatee = $date_ban->format('d/m/Y à H:i');
    $duration_text = ($duration === 'day') ? '24 heures' : '1 heure';
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 15px 15px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 15px 15px; }
            .warning { background: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 8px; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; border-top: 1px solid #e5e5e5; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>⚕️ ASCLEPIA</h2>
                <p>Plateforme Médicale</p>
            </div>
            <div class='content'>
                <h3>Bonjour $nom,</h3>
                <p>Nous vous informons que votre compte a été temporairement suspendu.</p>
                
                <div class='warning'>
                    <strong>⛔ Détails de la suspension :</strong><br><br>
                    • <strong>Date de fin :</strong> $date_formatee<br>
                    • <strong>Durée :</strong> $duration_text
                </div>
                
                <p>Passé ce délai, vous pourrez vous reconnecter normalement.</p>
                <p>Cordialement,<br><strong>L'équipe ASCLEPIA</strong></p>
            </div>
            <div class='footer'>
                <p>Cet email est un message automatique, merci de ne pas y répondre.</p>
                <p>&copy; 2025 ASCLEPIA - Tous droits réservés</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: ASCLEPIA <noreply@asclepia.com>" . "\r\n";
    
    mail($to, $subject, $message, $headers);
}

function sendUnbanEmail($email, $nom) {
    $to = $email;
    $subject = "✅ Compte réactivé - ASCLEPIA";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; border-radius: 15px 15px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 15px 15px; }
            .success { background: #d1fae5; border-left: 4px solid #10b981; padding: 20px; margin: 20px 0; border-radius: 8px; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; border-top: 1px solid #e5e5e5; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>⚕️ ASCLEPIA</h2>
                <p>Plateforme Médicale</p>
            </div>
            <div class='content'>
                <h3>Bonjour $nom,</h3>
                <div class='success'>
                    <strong>✅ Votre compte a été réactivé !</strong><br><br>
                    Vous pouvez maintenant vous reconnecter à votre espace personnel.
                </div>
                <p>Cordialement,<br><strong>L'équipe ASCLEPIA</strong></p>
            </div>
            <div class='footer'>
                <p>&copy; 2025 ASCLEPIA - Tous droits réservés</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: ASCLEPIA <noreply@asclepia.com>" . "\r\n";
    
    mail($to, $subject, $message, $headers);
}
?>