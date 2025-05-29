<?php
session_start();
$messages = $_SESSION['confirmation_messages'] ?? [];
unset($_SESSION['confirmation_messages']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Confirmation de réservation</h1>
    <ul>
        <?php foreach ($messages as $message): ?>
    <li>
  <?= htmlspecialchars($message['text']) ?>
  <?php if (!empty($message['id_reservation'])): ?>
    — <a href="facture.php?id_reservation=<?= $message['id_reservation'] ?>" target="_blank">📄 Télécharger la facture</a>
  <?php endif; ?>
</li>
        <?php endforeach; ?>
    </ul>
    <div style="margin-top: 20px;">
        <a href="../View/account.php" class="button">Voir mes réservations</a>
    </div>
</body>
</html>
