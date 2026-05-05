<?php
// send_welcome_email.php - Email de bienvenue APRÈS inscription
require_once __DIR__ . '/../assets/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../assets/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../assets/PHPMailer/src/SMTP.php';

function sendWelcomeEmail($email, $nom, $role) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Configuration SMTP (Gmail)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'contact.asclepia@gmail.com';     // ⚠️ REMPLACEZ PAR VOTRE EMAIL
        $mail->Password   = 'gjpa zkeo tnyy prvt';          // ⚠️ REMPLACEZ PAR VOTRE MOT DE PASSE
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Encodage UTF-8
        $mail->CharSet = 'UTF-8';
        
        // Expéditeur
        $mail->setFrom('no-reply@asclepia.tn', 'ASCLEPIA');
        $mail->addAddress($email, $nom);
        
        // Rôle en français
        $roleText = ($role == 'medecin') ? 'Médecin' : 'Patient';
        
        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = '🎉 Bienvenue sur ASCLEPIA - Votre compte a été créé';
        $mail->Body = "<html>
        <head><style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #0ea5e9, #3b82f6); padding: 20px; text-align: center; color: white; }
            .content { padding: 20px; }
            .button { background: #0ea5e9; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
        </style></head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏥 ASCLEPIA</h1>
                </div>
                <div class='content'>
                    <h2>Bienvenue " . htmlspecialchars($nom) . " ! 🎉</h2>
                    <p>Votre compte <strong>" . $roleText . "</strong> a été créé avec succès.</p>
                    <p><strong>Email de connexion :</strong> " . htmlspecialchars($email) . "</p>
                    <p><strong>Rôle :</strong> " . $roleText . "</p>
                    <br>
                    <a href='http://localhost/asclepia/View/front/login.html' class='button'>🔐 Se connecter</a>
                    <br><br>
                    <p>Cordialement,<br>L'équipe ASCLEPIA</p>
                </div>
            </div>
        </body>
        </html>";
        
        $mail->AltBody = "Bienvenue " . $nom . " sur ASCLEPIA !\n\n"
                       . "Votre compte " . $roleText . " a été créé avec succès.\n\n"
                       . "Email : " . $email . "\n"
                       . "Rôle : " . $roleText . "\n\n"
                       . "Connectez-vous : http://localhost/asclepia/View/front/login.html\n\n"
                       . "Cordialement,\nL'équipe ASCLEPIA";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Erreur envoi email: " . $mail->ErrorInfo);
        return false;
    }
}
?>