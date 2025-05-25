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
      <a href="logout.php">Se déconnecter</a>
    </nav>
  </header>

  <main>
    <section class="dashboard">
      <h1>Bienvenue, <?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?></h1>
      <p>Email : <?= htmlspecialchars($email) ?></p>
      <h2>📊 Mes statistiques</h2>
      <p>Total de vols : <?= $totalVols ?></p>
      <p>Total dépensé : <?= number_format($totalDepense, 2) ?> €</p>

      <h2>✈️ Mes réservations</h2>
      <?php if ($totalVols > 0): ?>
        <?php foreach ($reservations as $res): ?>
          <div class="reservation">
            <h3><?= htmlspecialchars($res['origine']) ?> ➜ <?= htmlspecialchars($res['destination']) ?></h3>
            <p>Départ : <?= date('d/m/Y H:i', strtotime($res['date_depart'])) ?></p>
            <p>Arrivée : <?= date('d/m/Y H:i', strtotime($res['date_arrivee'])) ?></p>
            <p>Prix : <?= $res['prix'] ?> €</p>
            <p>ID réservation : <?= $res['id_reservation'] ?></p>
            <a href="facture_pdf.php?id=<?= $res['id_reservation'] ?>" class="button" target="_blank">📄 Télécharger la facture</a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Vous n'avez encore effectué aucune réservation.</p>
      <?php endif; ?>
    </section>
    <section class="cart">
    <h2>🛒 Mon panier</h2>
    <?php
    $stmt = $pdo->prepare("SELECT p.id_panier, v.origine, v.destination, v.date_depart, v.prix, p.quantite
                            FROM panier p
                            JOIN vols v ON p.id_vol = v.id_vol
                            WHERE p.id_utilisateur = ?");
    $stmt->execute([$user_id]);
    $panier = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($panier) > 0):
      $total = 0;
      foreach ($panier as $item):
        $total += $item['prix'] * $item['quantite'];
    ?>
      <div class="panier-item">
        <h3><?= htmlspecialchars($item['origine']) ?> ➜ <?= htmlspecialchars($item['destination']) ?></h3>
        <p>Départ : <?= date('d/m/Y H:i', strtotime($item['date_depart'])) ?></p>
        <p>Prix unitaire : <?= $item['prix'] ?> €</p>
        <p>Quantité : <?= $item['quantite'] ?></p>
        <form method="POST" action="../Controller/remove_from_cart.php">
          <input type="hidden" name="id_panier" value="<?= $item['id_panier'] ?>">
          <button type="submit">❌ Retirer</button>
        </form>
      </div>
    <?php endforeach; ?>
      <p style="text-align:right; font-weight:bold;">Total : <?= number_format($total, 2) ?> €</p>
      <form method="POST" action="../Controller/valider_panier.php" style="text-align:center;">
        <button type="submit">✅ Valider et payer</button>
      </form>
    <?php else: ?>
      <p>Votre panier est vide.</p>
    <?php endif; ?>
  </section>
</main>
  <footer>
    <p>&copy; 2025 Zenith Airlines. Tous droits réservés.</p>
  </footer>
</body>
</html>
