<?php
session_start();
session_unset();
session_destroy();

// Supprimer le cookie en le définissant avec une date d'expiration passée
if (isset($_COOKIE['auth_token'])) {
    setcookie('auth_token', '', time() - 3600, "/");
}

header('Location: index.html'); // Redirige vers la page d'accueil
exit;
?>
