<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur veut changer la langue via l'URL (ex: ?lang=en)
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Langue par défaut
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'fr';
}

// Charger le fichier de langue correspondant
$langFile = __DIR__ . '/Lang/' . $_SESSION['lang'] . '.php';

if (file_exists($langFile)) {
    $translations = require $langFile;
} else {
    $translations = require __DIR__ . '/Lang/fr.php'; // fallback
}

// Fonction utilitaire pour récupérer une traduction
function tr($key) {
    global $translations;
    return isset($translations[$key]) ? $translations[$key] : $key;
}
?>
