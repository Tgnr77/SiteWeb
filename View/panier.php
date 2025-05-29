<?php
require_once __DIR__ . '/../Config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['utilisateur']['id'];

$stmt = $pdo->prepare("
    SELECT c.id_vol, v.origine, v.destination, v.date_depart, v.date_arrivee, v.prix
    FROM panier c
    JOIN vols v ON c.id_vol = v.id_vol
    WHERE c.id_utilisateur = ?
");
$stmt->execute([$user_id]);
$vols_panier = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <h1>Mon Panier</h1>
</header>
<main>
    <form action="reserver_vol.php" method="get">
        <section class="offers">
            <?php if (empty($vols_panier)): ?>
                <p class="error-message">Votre panier est vide.</p>
            <?php else: ?>
                <div style="text-align:center; margin-bottom:15px;">
                    <label>
                        <input type="checkbox" id="select-all"> Tout sélectionner
                    </label>
                </div>
                <?php foreach ($vols_panier as $vol): ?>
                    <div class="offer">
                        <div class="offer-details">
                            <h3><?= htmlspecialchars($vol['origine']) ?> - <?= htmlspecialchars($vol['destination']) ?></h3>
                            <p><strong>Départ :</strong> <?= htmlspecialchars($vol['date_depart']) ?></p>
                            <p><strong>Arrivée :</strong> <?= htmlspecialchars($vol['date_arrivee']) ?></p>
                            <p><strong>Prix :</strong> <?= $vol['prix'] ?> €</p>
                            <label>
                                <input type="checkbox" class="vol-checkbox" name="ids[]" value="<?= $vol['id_vol'] ?>">
                                Réserver ce vol
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="button">Valider la sélection</button>
                </div>
            <?php endif; ?>
        </section>
    </form>
</main>

<script>
  // Cocher/Décocher tous les vols
  document.getElementById('select-all').addEventListener('change', function () {
    document.querySelectorAll('.vol-checkbox').forEach(cb => cb.checked = this.checked);
  });
</script>
</body>
</html>
