<?php
session_start();

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Vérifie si l'utilisateur est administrateur
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirige vers la page de connexion si non connecté
 * @param string $redirectTo Page de redirection
 */
function requireLogin($redirectTo = 'login.html') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit();
    }
}

/**
 * Redirige vers l'accueil si non admin
 * @param string $redirectTo Page de redirection
 */
function requireAdmin($redirectTo = 'index.html') {
    if (!isAdmin()) {
        header('Location: ' . $redirectTo);
        exit();
    }
}

/**
 * Retourne les infos de l'utilisateur connecté
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nom' => $_SESSION['user_nom'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role' => $_SESSION['user_role'] ?? null
    ];
}

/**
 * Déconnecte l'utilisateur
 */
function logout() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}
?>