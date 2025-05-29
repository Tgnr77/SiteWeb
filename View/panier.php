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
        /* Styles CSS pour l'affichage du panier */
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
                                onclick="supprimerVol(<?= $vol['id_panier'] ?>)">🗑️</button>
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
