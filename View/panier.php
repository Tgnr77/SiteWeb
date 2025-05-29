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
    SELECT c.id_panier, c.id_vol, v.origine, v.destination, v.date_depart, v.date_arrivee, v.prix
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
    <style>
        .offer {
            position: relative;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            background: white;
        }

        .offer-details {
            padding-right: 40px;
        }

        .delete-form {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .delete-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            font-size: 20px;
            line-height: 1;
        }

        .delete-button:hover {
            transform: scale(1.2);
        }
    </style>
</head>
<body>
<header>
    <h1>Mon Panier</h1>
</header>
<main>
    <section class="offers">
        <?php if (empty($vols_panier)): ?>
            <p class="error-message">Votre panier est vide.</p>
    <div style="text-align:center; margin-top: 20px;">
        <a href="account.php" class="return-button">← Retour à mon compte</a>
    </div>
<?php else: ?>

            <div style="text-align:center; margin-bottom:15px;">
                <label>
                    <input type="checkbox" id="select-all"> Tout sélectionner
                </label>
            </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="button">Valider la sélection</button>
                </div>
            </form>

            <!-- Formulaires de suppression : séparés du form principal -->
            <?php foreach ($vols_panier as $vol): ?>
    <div class="offer">
        <!-- Formulaire de suppression placé à l’intérieur de l’offre -->
        <form action="../Controller/remove_from_cart.php" method="post"
              onsubmit="return confirm('Supprimer ce vol du panier ?');" class="delete-form">
            <input type="hidden" name="id_panier" value="<?= $vol['id_panier'] ?>">
            <button type="submit" class="delete-button" title="Supprimer ce vol">🗑️</button>
        </form>

        <!-- Détails du vol -->
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


        <?php endif; ?>
    </section>
</main>

<script>
    document.getElementById('select-all').addEventListener('change', function () {
        document.querySelectorAll('.vol-checkbox').forEach(cb => cb.checked = this.checked);
    });
</script>
</body>
</html>
