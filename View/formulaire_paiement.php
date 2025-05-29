<?php
session_start();
if (!isset($_POST['vols']) || !is_array($_POST['vols'])) {
    die("Aucune sélection reçue.");
}

$_SESSION['donnees_vols'] = $_POST['vols'];
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
        <form action="../Controller/paiement.php" method="POST">
            <label for="nom">Nom du titulaire :</label>
            <input type="text" id="nom" name="nom" required>

            <label for="numero">Numéro de carte :</label>
            <input type="text" id="numero" name="numero" placeholder="1234 5678 9012 3456" required pattern="\d{16}">

            <label for="expiration">Date d’expiration :</label>
            <input type="month" id="expiration" name="expiration" required>

            <label for="cvv">Cryptogramme visuel :</label>
            <input type="text" id="cvv" name="cvv" pattern="\d{3}" required>

            <button type="submit" class="button">Valider le paiement</button>
        </form>
    </div>
</body>
</html>
