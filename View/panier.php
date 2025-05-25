<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();

$items = [];
$total = 0;

if (isset($_SESSION['utilisateur']['id'])) {
    $user_id = $_SESSION['utilisateur']['id'];
    $stmt = $pdo->prepare("
        SELECT p.id_panier, v.origine, v.destination, v.date_depart, v.prix, p.quantite
        FROM panier p
        JOIN vols v ON p.id_vol = v.id_vol
        WHERE p.id_utilisateur = ?
    ");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $item) {
        $total += $item['prix'] * $item['quantite'];
    }
} elseif (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $id_vol => $data) {
        $stmt = $pdo->prepare("SELECT * FROM vols WHERE id_vol = ?");
        $stmt->execute([$id_vol]);
        $vol = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($vol) {
            $vol['quantite'] = $data['quantite'];
            $items[] = $vol;
            $total += $vol['prix'] * $vol['quantite'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon panier - Zenith Airlines</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header>
    <h1>Mon panier</h1>
    <nav>
      <a href="index.html">Accueil</a>
      <a href="vols.html">Vols</a>
      <a href="account.php">Mon compte</a>
    </nav>
  </header>
  <main>
    <?php if (count($items) > 0): ?>
      <?php foreach ($items as $item): ?>
        <div class="offer">
          <h3><?= htmlspecialchars($item['origine']) ?> ➜ <?= htmlspecialchars($item['destination']) ?></h3>
          <p>Départ : <?= date('d/m/Y H:i', strtotime($item['date_depart'])) ?></p>
          <p>Prix unitaire : <?= $item['prix'] ?> €</p>
          <p>Quantité : <?= $item['quantite'] ?></p>
          <p>Total : <?= $item['prix'] * $item['quantite'] ?> €</p>

          <?php if (isset($item['id_panier'])): ?>
            <form method="POST" action="../Controller/remove_from_cart.php" style="margin-top: 10px;">
              <input type="hidden" name="id_panier" value="<?= $item['id_panier'] ?>">
              <button type="submit">❌ Retirer</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <h3 style="text-align:right">Total global : <?= number_format($total, 2) ?> €</h3>
      <?php if (isset($_SESSION['utilisateur']['id'])): ?>
        <form method="POST" action="../Controller/valider_panier.php" style="text-align:center; margin-top:20px;">
          <button type="submit" class="button">✅ Valider et réserver</button>
        </form>
      <?php else: ?>
        <p style="text-align:center; color:red;">Connectez-vous pour valider votre panier.</p>
      <?php endif; ?>
    <?php else: ?>
      <p style="text-align:center; margin-top:50px;">Votre panier est vide.</p>
    <?php endif; ?>
    <?php if (isset($item['id_panier'])): ?>
  <form method="POST" action="../Controller/remove_from_cart.php" style="margin-top: 10px;">
  <?php if (isset($item['id_panier'])): ?>
    <input type="hidden" name="id_panier" value="<?= $item['id_panier'] ?>">
  <?php else: ?>
    <input type="hidden" name="id_vol" value="<?= $item['id_vol'] ?>">
  <?php endif; ?>
  <button type="submit">❌ Retirer</button>
</form>

<?php endif; ?>

  </main>
</body>
</html>
