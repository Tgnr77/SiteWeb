<?php
session_start();
$messages = $_SESSION['confirmation_messages'] ?? [];
unset($_SESSION['confirmation_messages']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Confirmation de réservation</title>
  <link rel="stylesheet" href="../View/styles.css"> <!-- Adapte le chemin si besoin -->
  <style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 0;
    }
    .confirmation-container {
      max-width: 700px;
      margin: 60px auto;
      background-color: #ffffff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    .confirmation-container h1 {
      color: #28a745;
      font-size: 2em;
      margin-bottom: 20px;
    }
    .confirmation-container .success-icon {
      font-size: 3em;
      color: #28a745;
      margin-bottom: 20px;
    }
    .message-block {
      margin-bottom: 30px;
      padding: 15px;
      background-color: #e6f9f0;
      border-left: 5px solid #28a745;
      text-align: left;
      border-radius: 6px;
    }
    .message-block p {
      margin: 0 0 10px 0;
      font-size: 1.1em;
      color: #333;
    }
    .facture-link {
      display: inline-block;
      margin-top: 5px;
      padding: 10px 18px;
      background-color: #007bff;
      color: white;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
      transition: background-color 0.3s;
    }
    .facture-link:hover {
      background-color: #0056b3;
    }
    .back-link {
      display: block;
      margin-top: 30px;
      color: #555;
      text-decoration: none;
    }
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="confirmation-container">
    <h1>Confirmation de réservation</h1>

    <?php foreach ($messages as $msg): ?>
      <div class="message-block">
        <p><?= htmlspecialchars($msg['text']) ?></p>
        <?php if (!empty($msg['id_reservation'])): ?>
          <a class="facture-link" href="facture.php?id_reservation=<?= $msg['id_reservation'] ?>" target="_blank">
            📄 Télécharger la facture
          </a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>

    <a class="back-link" href="../View/account.php">← Voir mes réservations</a>
  </div>

</body>
</html>
