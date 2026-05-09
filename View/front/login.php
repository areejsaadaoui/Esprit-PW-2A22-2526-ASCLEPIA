<?php
// login.php - Dans View/front/
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
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $remember = isset($_POST['remember']);
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    // Validation
    if (empty($email) || empty($mot_de_passe)) {
        header('Location: login.html?error=1');
        exit();
    }
    
    // ========== VÉRIFICATION reCAPTCHA ==========
    if (empty($recaptcha_response)) {
        header('Location: login.html?error=3');
        exit();
    }
    
    // Vérifier avec Google
    $secret_key = '6LcxldQsAAAAAEi2Ym74vAW4Em9Lam5Wd-PaFrHm';
    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $post_data = http_build_query([
        'secret' => $secret_key,
        'response' => $recaptcha_response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $post_data
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($verify_url, false, $context);
    $captcha_result = json_decode($result, true);
    
    if (!$captcha_result['success']) {
        header('Location: login.html?error=3');
        exit();
    }
    // =========================================

    // Chercher l'utilisateur avec les colonnes de bannissement
    $stmt = $pdo->prepare("SELECT id_user, nom, email, mot_de_passe, role, is_banned, ban_until FROM utilisateur WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: login.html?error=1');
        exit();
    }

    // ========== VÉRIFICATION DU BANNISSEMENT ==========
    if ($user['is_banned'] == 1) {
        $ban_until = $user['ban_until'];
        $current_time = date('Y-m-d H:i:s');
        
        // Vérifier si le bannissement n'a pas expiré
        if ($ban_until && $ban_until > $current_time) {
            // Bannissement toujours actif
            $ban_until_formatted = date('d/m/Y à H:i', strtotime($ban_until));
            $error_message = urlencode("Votre compte est suspendu jusqu'au {$ban_until_formatted}");
            header("Location: login.html?error=4&message={$error_message}");
            exit();
        } elseif ($ban_until && $ban_until <= $current_time) {
            // Bannissement expiré, réactiver automatiquement le compte
            $updateBan = $pdo->prepare("UPDATE utilisateur SET is_banned = 0, ban_until = NULL WHERE id_user = :id");
            $updateBan->execute([':id' => $user['id_user']]);
            // Continuer la connexion normalement
        }
    }
    // ===================================================

    if (!password_verify($mot_de_passe, $user['mot_de_passe'])) {
        header('Location: login.html?error=2');
        exit();
    }

    // === CRÉATION DE LA SESSION ===
    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['user_nom'] = $user['nom'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    // Option "Se souvenir de moi"
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), "/");
        // Optionnel : sauvegarder le token en base
        $updateToken = $pdo->prepare("UPDATE utilisateur SET remember_token = :token WHERE id_user = :id");
        $updateToken->execute([':token' => $token, ':id' => $user['id_user']]);
    }

    // === REDIRECTION SELON LE RÔLE ===
    // === REDIRECTION SELON LE RÔLE ===
if ($user['role'] === 'admin') {
    header('Location: ../back/dashboard.php');
} elseif ($user['role'] === 'medecin') {
    header('Location: indexd.php');
} else {
    header('Location: indexp.php');
}
    exit();
} else {
    header('Location: login.html');
    exit();
}
?>