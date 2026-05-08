<?php
// logout.php - Déconnexion de l'administrateur

// Démarrer la session
session_start();

// Vider toutes les variables de session
$_SESSION = array();

// Supprimer le cookie de session si existant
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),           // Nom du cookie de session
        '',                       // Valeur vide
        time() - 42000,          // Date dans le passé (suppression)
        $params["path"],         // Chemin
        $params["domain"],       // Domaine
        $params["secure"],       // Sécurisé ?
        $params["httponly"]      // HTTP only ?
    );
}

// Détruire complètement la session
session_destroy();

// Supprimer également le cookie remember_token s'il existe
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

if ($userRole === 'admin') {
    header('Location: loginadmin.html');
} else {
    header('Location: ../front/login.html');
}
exit();
?>