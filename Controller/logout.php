<?php
session_start();

// Supprime toutes les variables de session
session_unset();

// Détruit la session
session_destroy();

// Supprime le cookie d'auth_token s'il existe
if (isset($_COOKIE['auth_token'])) {
    setcookie('auth_token', '', time() - 3600, '/', '', true, true);
}

// Redirige vers login.html avec paramètre de confirmation
header('Location: ../View/login.html?logout=1');
exit;
