<?php
include 'db.php'; // Inclure le fichier de connexion

try {
    echo "Connexion à la base de données réussie !";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
