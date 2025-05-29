<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['utilisateur']['id'];
$prenom = $_SESSION['utilisateur']['prenom'] ?? '';
$nom = $_SESSION['utilisateur']['nom'] ?? '';
$email = $_SESSION['utilisateur']['email'] ?? '';

$stmt = $pdo->prepare("
    SELECT r.id_reservation, v.origine, v.destination, v.date_depart, v.date_arrivee, v.prix
    FROM reservations r
    JOIN vols v ON r.id_vol = v.id_vol
    WHERE r.id_utilisateur = ?
    ORDER BY v.date_depart DESC
");
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalVols = count($reservations);
$totalDepense = array_reduce($reservations, fn($c, $i) => $c + $i['prix'], 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Compte - Zenith Airlines</title>
  <link rel="stylesheet" href="styles.css">
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const panierCountEl = document.getElementById('panier-count');
    if (panierCountEl) {
      fetch('../Controller/get_cart_count.php')
        .then(res => res.json())
        .then(data => {
          panierCountEl.textContent = data.count > 0 ? `(${data.count})` : '';
        })
        .catch(() => {
          panierCountEl.textContent = '';
        });
    }
  });
</script>
<style>
  @media (max-width: 768px) {
    .dashboard {
      padding: 10px;
    }
    .reservation,
    .panier-item {
      font-size: 15px;
      padding: 10px;
    }
    header nav a {
      display: inline-block;
      margin: 4px 6px;
      font-size: 15px;
    }
    h1, h2, h3, h4 {
      font-size: 1.2em;
    }
    .logo img {
      max-width: 100px;
    }
    .button,
    button {
      font-size: 14px;
      padding: 8px 12px;
    }
  }
</style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="zenith.webp" alt="Logo Zenith Airlines">
    </div>
    <nav>
      <a href="index.html">Accueil</a>
      <a href="vols.html">Vols à venir</a>
      <a href="reserver.html">Réserver un siège</a>
      <a href="panier.php">🛒 Voir le panier<span id="panier-count" style="margin-left: 5px; color: red;"></span></a>
      <a href="../Controller/logout.php">Se déconnecter</a>                                                                      
    </nav>
  </header>

  <main>
  <section class="dashboard">
    <h1>👋 Bienvenue, <span style="color:#2b6777; font-weight:bold;"><?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?></span></h1>
    <p>📧 Email : <strong><?= htmlspecialchars($email) ?></strong></p>

    <div class="stats" style="margin-top:30px;">
      <h2>📊 Mes statistiques</h2>
      <ul style="list-style:none; padding-left:0;">
        <li><strong>Total de vols :</strong> <?= $totalVols ?></li>
        <li><strong>Total dépensé :</strong> <?= number_format($totalDepense, 2) ?> €</li>
      </ul>
    </div>

    <div class="reservations" style="margin-top:30px;">
      <h2>✈️ Mes réservations</h2>
      <?php if ($totalVols > 0): ?>
        <?php foreach ($reservations as $res): ?>
          <div class="reservation" style="border:1px solid #ccc; border-radius:8px; padding:15px; margin-bottom:15px; background:#f9f9f9;">
            <h3 style="margin:0 0 10px 0; color:#333;"><?= htmlspecialchars($res['origine']) ?> ➜ <?= htmlspecialchars($res['destination']) ?></h3>
            <p>🕓 Départ : <?= date('d/m/Y H:i', strtotime($res['date_depart'])) ?></p>
            <p>🛬 Arrivée : <?= date('d/m/Y H:i', strtotime($res['date_arrivee'])) ?></p>
            <p>💶 Prix : <?= $res['prix'] ?> €</p>
            <p>ID réservation : <strong><?= $res['id_reservation'] ?></strong></p>
            <a href="../Controller/facture.php?id_reservation=<?= $res['id_reservation'] ?>" class="button" style="display:inline-block; margin-top:10px; padding:8px 16px; background:#2b6777; color:white; text-decoration:none; border-radius:20px;" target="_blank">📄 Télécharger la facture</a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Vous n'avez encore effectué aucune réservation.</p>
      <?php endif; ?>
    </div>

    <div class="panier" style="margin-top:40px;">
      <h2>🛒 Mon panier</h2>
      <?php
      $stmt = $pdo->prepare("SELECT p.id_panier, v.origine, v.destination, v.date_depart, v.prix, p.quantite FROM panier p JOIN vols v ON p.id_vol = v.id_vol WHERE p.id_utilisateur = ?");
      $stmt->execute([$user_id]);
      $panier = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (count($panier) > 0):
        $total = 0;
        foreach ($panier as $item):
          $total += $item['prix'] * $item['quantite'];
      ?>
        <div class="panier-item" style="border:1px dashed #aaa; border-radius:6px; padding:12px; margin-bottom:10px; background:#fffef5;">
          <h4><?= htmlspecialchars($item['origine']) ?> ➜ <?= htmlspecialchars($item['destination']) ?></h4>
          <p>🕓 Départ : <?= date('d/m/Y H:i', strtotime($item['date_depart'])) ?></p>
          <p>💳 Prix : <?= $item['prix'] ?> € &nbsp; | &nbsp; Quantité : <?= $item['quantite'] ?></p>
          <form method="POST" action="../Controller/remove_from_cart.php">
            <input type="hidden" name="id_panier" value="<?= $item['id_panier'] ?>">
            <button type="submit" style="margin-top:5px; background:#e74c3c; color:white; border:none; padding:6px 12px; border-radius:6px;">❌ Retirer</button>
          </form>
        </div>
      <?php endforeach; ?>
        <p style="text-align:right; font-weight:bold;">💰 Total panier : <?= number_format($total, 2) ?> €</p>
        <form method="POST" action="../Controller/valider_panier.php" style="text-align:center; margin-top:15px;">
          <button type="submit" style="padding:10px 20px; background:#27ae60; color:white; font-weight:bold; border:none; border-radius:20px;">✅ Valider et payer</button>
        </form>
      <?php else: ?>
        <p>Votre panier est vide.</p>
      <?php endif; ?>
    </div>
  </section>
</main>
  <footer>
    <p>&copy; 2025 Zenith Airlines. Tous droits réservés.</p>
  </footer>
</body>
</html>
