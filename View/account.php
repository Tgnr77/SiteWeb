<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: ../View/login.html');
    exit;
}
$user_id = $_SESSION['utilisateur']['id'];


// Récupération des réservations
$stmt = $pdo->prepare("
    SELECT r.id_reservation, v.origine, v.destination, v.date_depart, v.date_arrivee, v.prix
    FROM reservations r
    JOIN vols v ON r.id_vol = v.id_vol
    WHERE r.id_utilisateur = ?
    ORDER BY v.date_depart DESC
");
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Compte - Zenith Airlines</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .tab-container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .tab-header {
      background-color: #CCBEAA;
      color: white;
      padding: 15px;
      font-size: 1.4em;
      font-weight: bold;
      text-align: center;
    }
    .reservation-list {
      padding: 20px;
    }
    .reservation-card {
      background: #f9f9f9;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px 20px;
      margin-bottom: 15px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .reservation-card h3 {
      margin-top: 0;
      color: #333;
    }
    .reservation-card p {
      margin: 5px 0;
      color: #555;
    }
    .reservation-card form {
      display: inline-block;
      margin-right: 10px;
    }
    .reservation-card button,
    .reservation-card a.button {
      padding: 8px 16px;
      background-color: #CCBEAA;
      color: white;
      border: none;
      border-radius: 20px;
      font-weight: bold;
      text-decoration: none;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .reservation-card button:hover,
    .reservation-card a.button:hover {
      background-color: #e9bd7f;
      transform: scale(1.03);
    }
    .no-reservation {
      text-align: center;
      padding: 20px;
      color: #999;
    }
  </style>
</head>
<body>
  <header>
    <div class="header-container">
      <div class="logo">
        <img src="zenith.webp" alt="Logo Zenith Airlines">
      </div>
      <nav class="main-nav">
      <a href="../View/index.html">Accueil</a>
      <a href="../View/vols.html">Vols à venir</a>
      <a href="../View/reserver.html">Réserver un siège</a>
      <a href="../View/contact.html">Nous contacter</a>

      </nav>
    </div>
  </header>

  <main>
    <div class="tab-container">
      <div class="tab-header">Mes Réservations</div>
      <div class="reservation-list">
        <?php if (count($reservations) > 0): ?>
          <?php foreach ($reservations as $res): ?>
            <div class="reservation-card">
              <h3><?= htmlspecialchars($res['origine']) ?> ➜ <?= htmlspecialchars($res['destination']) ?></h3>
              <p><strong>Départ :</strong> <?= date('d/m/Y H:i', strtotime($res['date_depart'])) ?></p>
              <p><strong>Arrivée :</strong> <?= date('d/m/Y H:i', strtotime($res['date_arrivee'])) ?></p>
              <p><strong>Prix :</strong> <?= $res['prix'] ?> €</p>
              <p><strong>Réservation #<?= $res['id_reservation'] ?></strong></p>

              <form action="cancel_reservation.php" method="POST" onsubmit="return confirm('Confirmer l\'annulation de cette réservation ?');">
                <input type="hidden" name="id_reservation" value="<?= $res['id_reservation'] ?>">
                <button type="submit">Annuler</button>
              </form>

              <a href="facture_pdf.php?id=<?= $res['id_reservation'] ?>" target="_blank" class="button">Télécharger la facture</a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no-reservation">Vous n'avez aucune réservation pour le moment.</div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 Zenith Airlines. Tous droits réservés.</p>
  </footer>
</body>
</html>
