<?php
// Configuration des informations de connexion à la base de données
$host = 'localhost'; // Adresse du serveur (par défaut : localhost)
$dbname = 'zenith_airlines'; // Nom de la base de données
$username = 'root'; // Nom d'utilisateur MySQL (par défaut : root)
$password = ''; // Mot de passe (par défaut : vide)

try {
    // Création d'une connexion PDO à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configuration de l'attribut pour afficher les erreurs en mode Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si la connexion échoue, afficher un message d'erreur
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
