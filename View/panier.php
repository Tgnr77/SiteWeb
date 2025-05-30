<?php
require_once __DIR__ . '/../Config/paths.php'; // Inclut le fichier de configuration des chemins
require_once MODEL_PATH . 'db.php'; // Inclut la connexion à la base de données
session_start(); // Démarre la session

if (!isset($_SESSION['utilisateur']['id'])) { // Vérifie si l'utilisateur est connecté
    header('Location: login.html'); // Redirige vers la page de connexion si non connecté
    exit;
}

$user_id = $_SESSION['utilisateur']['id']; // Récupère l'id de l'utilisateur connecté

// Prépare et exécute la requête pour récupérer les vols dans le panier de l'utilisateur
$stmt = $pdo->prepare("
    SELECT c.id_panier, c.id_vol, v.origine, v.destination, v.date_depart, v.date_arrivee, v.prix
    FROM panier c
    JOIN vols v ON c.id_vol = v.id_vol
    WHERE c.id_utilisateur = ?
");
$stmt->execute([$user_id]);
$vols_panier = $stmt->fetchAll(PDO::FETCH_ASSOC); // Récupère les résultats sous forme de tableau associatif
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier</title>
    <link rel="stylesheet" href="styles.css">
    <style>
       body {
  font-family: 'Arial', sans-serif;
  background: #f9f9f9;
  margin: 0;
  padding: 0;
}

header {
  text-align: center;
  padding: 30px 20px 10px;
  background: #fff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

h1 {
  margin: 0;
  font-size: 2em;
  color: #333;
}

.offers {
  max-width: 800px;
  margin: 30px auto;
  padding: 0 20px;
}

.offer {
  position: relative;
  border-radius: 12px;
  background-color: white;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  margin-bottom: 25px;
  transition: transform 0.2s;
}

.offer:hover {
  transform: translateY(-3px);
}

.offer h3 {
  font-size: 1.4em;
  color: #444;
  margin-bottom: 10px;
}

.offer p {
  margin: 5px 0;
  color: #555;
  font-size: 0.95em;
}

.offer input[type="checkbox"] {
  margin-right: 8px;
  transform: scale(1.2);
}

.delete-form {
  position: absolute;
  top: 12px;
  right: 12px;
}

.delete-button {
  background-color: #e74c3c;
  color: white;
  border: none;
  padding: 8px 14px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 0.9em;
  font-weight: bold;
  transition: background-color 0.3s, transform 0.2s;
}

.delete-button:hover {
  background-color: #c0392b;
  transform: scale(1.05);
}

label {
  display: inline-flex;
  align-items: center;
  margin-top: 10px;
  font-size: 0.95em;
  color: #333;
}

.error-message {
  text-align: center;
  background: #ffe6e6;
  color: #c0392b;
  font-weight: bold;
  padding: 12px 20px;
  border-radius: 8px;
  max-width: 400px;
  margin: 30px auto;
}

.return-button {
  display: inline-block;
  margin-top: 10px;
  text-decoration: none;
  font-weight: bold;
  color: #3b3b99;
  border-bottom: 1px dashed #3b3b99;
}

.return-button:hover {
  color: #1c1c6c;
}

.button {
  background-color: #CCBEAA;
  color: white;
  font-weight: bold;
  padding: 12px 30px;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}

.button:hover {
  background-color: #e9bd7f;
  transform: scale(1.05);
}

#select-all {
  transform: scale(1.2);
  margin-right: 8px;
}
.empty-cart-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 60px auto;
  max-width: 400px;
  padding: 40px 20px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.empty-cart-message {
  background-color: #ffecec;
  color: #c0392b;
  font-weight: bold;
  padding: 16px 24px;
  border-radius: 8px;
  text-align: center;
  font-size: 1.2em;
  margin-bottom: 20px;
  width: 100%;
  box-sizing: border-box;
}

.back-link {
  color: #3b3b99;
  font-weight: bold;
  text-decoration: none;
  border-bottom: 1px dashed #3b3b99;
  transition: color 0.2s;
}

.back-link:hover {
  color: #1c1c6c;
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
            <div class="empty-cart-container">
  <div class="empty-cart-message">
    🛒 Votre panier est vide.
  </div>
  <a href="account.php" class="back-link">← Retour à mon compte</a>
</div>
        <?php else: ?>
        <form action="reserver_vol.php" method="get">
            <div style="text-align:center; margin-bottom:15px;">
                <label>
                    <input type="checkbox" id="select-all"> Tout sélectionner
                </label>
            </div>

            <?php foreach ($vols_panier as $vol): ?>
                <div class="offer" data-panier-id="<?= $vol['id_panier'] ?>">
                    <div class="delete-form">
                        <!-- Bouton pour supprimer un vol du panier -->
                        <button type="button" class="delete-button" title="Supprimer ce vol"
                                onclick="supprimerVol(<?= $vol['id_panier'] ?>)">Supprimer</button>
                    </div>

                    <div class="offer-details">
                        <h3><?= htmlspecialchars($vol['origine']) ?> - <?= htmlspecialchars($vol['destination']) ?></h3>
                        <p><strong>Départ :</strong> <?= htmlspecialchars($vol['date_depart']) ?></p>
                        <p><strong>Arrivée :</strong> <?= htmlspecialchars($vol['date_arrivee']) ?></p>
                        <p><strong>Prix :</strong> <?= $vol['prix'] ?> €</p>
                        <label>
                            <!-- Case à cocher pour sélectionner le vol à réserver -->
                            <input type="checkbox" class="vol-checkbox" name="ids[]" value="<?= $vol['id_vol'] ?>">
                            Réserver ce vol
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="button">Valider la sélection</button>
            </div>
        </form>
        <?php endif; ?>
    </section>  
</main>

<script>
    // Gestion du bouton "Tout sélectionner"
    document.getElementById('select-all')?.addEventListener('change', function () {
        document.querySelectorAll('.vol-checkbox').forEach(cb => cb.checked = this.checked);
    });

    // Fonction pour supprimer un vol du panier via AJAX
    function supprimerVol(id_panier) {
        if (confirm("Voulez-vous vraiment supprimer ce vol du panier ?")) {
            fetch('../Controller/remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id_panier=' + encodeURIComponent(id_panier)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const element = document.querySelector(`[data-panier-id="${id_panier}"]`);
                    if (element) element.remove();

                    // Si le panier est vide après suppression, affiche le message correspondant
                    if (document.querySelectorAll('.offer').length === 0) {
                        document.querySelector('.offers').innerHTML = `
                            <p class="error-message">Votre panier est vide.</p>
                            <div style="text-align:center; margin-top: 20px;">
                                <a href="account.php" class="return-button">← Retour à mon compte</a>
                            </div>
                        `;
                    }
                } else {
                    alert("Erreur : " + (data.error || "Suppression échouée."));
                }
            })
            .catch(error => {
                console.error("Erreur réseau :", error);
                alert("Une erreur est survenue lors de la suppression : " + error.message);
            });
        }
    }
</script>
</body>
</html>
