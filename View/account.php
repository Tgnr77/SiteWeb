<?php
// Inclusion des chemins de configuration et de la connexion à la base de données
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';

// Démarrage de la session utilisateur
session_start();

// Vérifie si l'utilisateur est connecté, sinon redirige vers la page de connexion
if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: login.html');
    exit;
}

// Récupération des informations de l'utilisateur connecté
$user_id = $_SESSION['utilisateur']['id'];
$prenom = $_SESSION['utilisateur']['prenom'] ?? '';
$nom = $_SESSION['utilisateur']['nom'] ?? '';
$email = $_SESSION['utilisateur']['email'] ?? '';

// Requête pour récupérer toutes les réservations de l'utilisateur
$stmt = $pdo->prepare("
    SELECT r.id_reservation, v.origine, v.destination, v.date_depart, v.date_arrivee, v.prix
    FROM reservations r
    JOIN vols v ON r.id_vol = v.id_vol
    WHERE r.id_utilisateur = ?
    ORDER BY v.date_depart DESC
");
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul du nombre total de vols et du montant total dépensé
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
    // Script pour mettre à jour dynamiquement le nombre d’articles dans le panier
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
 body {
  font-family: 'Arial', sans-serif;
  background: #f7f8fa;
  margin: 0;
  padding: 0;
  color: #333;
}

header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 15px 30px;
  background-color: white;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.logo img {
  height: 50px;
}

nav a {
  margin-left: 20px;
  text-decoration: none;
  color: #333;
  font-weight: bold;
  transition: color 0.2s;
}

nav a:hover {
  color: #2b6777;
}

main {
  max-width: 1000px;
  margin: 40px auto;
  padding: 0 20px;
}

h1 {
  font-size: 2em;
  margin-bottom: 10px;
}

.section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  padding: 25px 30px;
  margin-bottom: 30px;
}

.section h2 {
  font-size: 1.4em;
  margin-bottom: 15px;
  color: #2b6777;
}

.reservation, .panier-item {
  background: #f9f9f9;
  padding: 15px 20px;
  border-radius: 8px;
  border: 1px solid #ddd;
  margin-bottom: 15px;
}

.reservation h3, .panier-item h4 {
  margin-top: 0;
  color: #444;
}

.button {
  display: inline-block;
  background-color: #2b6777;
  color: white;
  text-decoration: none;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: bold;
  transition: background-color 0.3s ease;
}

.button:hover {
  background-color: #215763;
}

.delete-button {
  background-color: #e74c3c;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
}

.delete-button:hover {
  background-color: #c0392b;
}

.footer {
  text-align: center;
  padding: 20px;
  font-size: 0.85em;
  color: #666;
  background: #f1f1f1;
}
body {
  background-color: #f7f8fa;
}

.section {
  background: white;
  border-radius: 12px;
  padding: 20px 25px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  margin-bottom: 30px;
}

</style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="zenith.webp" alt="Logo Zenith Airlines">
    </div>
    <nav>
          <!-- Navigation principale -->
      <a href="index.html">Accueil</a>
      <a href="vols.php">Vols à venir</a>
      <a href="panier.php">🛒 Voir le panier<span id="panier-count" style="margin-left: 5px; color: red;"></span></a>
      <a href="../Controller/logout.php">Se déconnecter</a>                                                                      
    </nav>
  </header>

  <main>
  <section class="dashboard">
      <!-- Affichage du prénom et nom -->
    <h1> Bienvenue, <span style="color:#2b6777; font-weight:bold;"><?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?></span></h1>
    <p> Email : <strong><?= htmlspecialchars($email) ?></strong></p>

    <div class="section">
      <h2> Mes statistiques</h2>
      <ul style="list-style:none; padding-left:0;">
        <li><strong>Total de vols :</strong> <?= $totalVols ?></li>
        <li><strong>Total dépensé :</strong> <?= number_format($totalDepense, 2) ?> €</li>
      </ul>
    </div>

    <div class="section">
      <h2>Mes réservations</h2>
      <?php if ($totalVols > 0): ?>
        <?php foreach ($reservations as $res): ?>
          <div class="reservation" style="border:1px solid #ccc; border-radius:8px; padding:15px; margin-bottom:15px; background:#f9f9f9;">
                      <!-- Détail d'une réservation -->
            <h3 style="margin:0 0 10px 0; color:#333;"><?= htmlspecialchars($res['origine']) ?> ➜ <?= htmlspecialchars($res['destination']) ?></h3>
            <p> Départ : <?= date('d/m/Y H:i', strtotime($res['date_depart'])) ?></p>
            <p> Arrivée : <?= date('d/m/Y H:i', strtotime($res['date_arrivee'])) ?></p>
            <p> Prix : <?= $res['prix'] ?> €</p>
            <p>ID réservation : <strong><?= $res['id_reservation'] ?></strong></p>
                      <!-- Lien vers la facture PDF -->
            <a href="../Controller/facture.php?id_reservation=<?= $res['id_reservation'] ?>" class="button" style="display:inline-block; margin-top:10px; padding:8px 16px; background:#2b6777; color:white; text-decoration:none; border-radius:20px;" target="_blank">📄 Télécharger la facture</a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Vous n'avez encore effectué aucune réservation.</p>
      <?php endif; ?>
    </div>

    <div class="section">
      <h2>🛒 Mon panier</h2>
      <?php
      // Requête pour récupérer les articles du panier de l'utilisateur
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
                        <!-- Formulaire pour retirer l'article du panier -->
          <form method="POST" action="../Controller/remove_from_cart.php">
            <input type="hidden" name="id_panier" value="<?= $item['id_panier'] ?>">
            <button type="submit" style="margin-top:5px; background:#e74c3c; color:white; border:none; padding:6px 12px; border-radius:6px;">❌ Retirer</button>
          </form>
        </div>
      <?php endforeach; ?>
            <!-- Total du panier -->
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
