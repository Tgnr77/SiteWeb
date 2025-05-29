<?php
session_start(); // Démarre la session pour stocker les données utilisateur
if (!isset($_POST['vols']) || !is_array($_POST['vols'])) { // Vérifie si les vols ont été envoyés et sont sous forme de tableau
    die("Aucune sélection reçue."); // Arrête le script si aucune sélection n'a été reçue
}

$_SESSION['donnees_vols'] = $_POST['vols']; // Stocke les vols sélectionnés dans la session
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement - Zenith Airlines</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            max-width: 500px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        }
        label { display: block; margin-top: 15px; }
        input { width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; }
        .button { margin-top: 20px; width: 100%; background: #CCBEAA; color: white; border: none; padding: 12px; font-size: 1em; border-radius: 30px; cursor: pointer; }
        .button:hover { background-color: #e9bd7f; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Informations de paiement</h2>
        <form action="../Controller/paiement.php" method="POST"> <!-- Formulaire d'envoi des informations de paiement -->
            <label for="nom">Nom du titulaire :</label>
            <input type="text" id="nom" name="nom" required> <!-- Champ pour le nom du titulaire -->

            <label for="numero">Numéro de carte :</label>
            <input type="text" id="numero" name="numero" placeholder="1234 5678 9012 3456" required pattern="\d{16}"> <!-- Champ pour le numéro de carte -->

            <label for="expiration">Date d’expiration :</label>
            <input type="month" id="expiration" name="expiration" required> <!-- Champ pour la date d'expiration -->

            <label for="cvv">Cryptogramme visuel :</label>
            <input type="text" id="cvv" name="cvv" pattern="\d{3}" required> <!-- Champ pour le CVV -->

            <button type="submit" class="button">Valider le paiement</button> <!-- Bouton de validation -->
        </form>
    </div>
</body>
</html>
