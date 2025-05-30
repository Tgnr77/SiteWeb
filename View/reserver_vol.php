<?php
require_once __DIR__ . '/../Config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();

// Vérifie si l'utilisateur est connecté, sinon redirige vers la page de connexion
if (!isset($_SESSION['utilisateur']['id'])) {
  header('Location: login.html');
  exit;
}

// Récupère les IDs des vols sélectionnés depuis la requête GET
$ids = $_GET['ids'] ?? [];

if (empty($ids) || !is_array($ids)) {
  die("Aucun vol sélectionné.");
}

// Filtre les IDs pour ne garder que les valeurs numériques
$ids = array_filter($ids, fn($id) => is_numeric($id));
if (empty($ids)) {
  die("Vols invalides.");
}

// Prépare la requête pour récupérer les informations des vols sélectionnés
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM vols WHERE id_vol IN ($placeholders)");
$stmt->execute($ids);
$vols = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pour chaque vol, récupère les sièges déjà réservés
$reservedSeatsPerVol = [];
foreach ($ids as $id_vol) {
  $stmt = $pdo->prepare("SELECT siege FROM reservations WHERE id_vol = ?");
  $stmt->execute([$id_vol]);
  $reservedSeatsPerVol[$id_vol] = array_column($stmt->fetchAll(), 'siege');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Réservation des vols sélectionnés</title>
  <link rel="stylesheet" href="styles.css">
  <style>
  /* CONTENEUR PRINCIPAL */
main.reservation-container {
  max-width: 800px;
  margin: 40px auto;
  padding: 30px;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  font-family: 'Arial', sans-serif;
  color: #333;
}

h1, h2, h3 {
  color: #444;
}

h2 {
  margin-top: 30px;
  margin-bottom: 10px;
  font-size: 1.4em;
}

label {
  display: inline-block;
  margin: 10px 0 5px;
  font-weight: bold;
}

input[type="radio"], select {
  margin-right: 10px;
}

select {
  padding: 6px 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
}

button.button {
  display: block;
  margin: 30px auto 0;
  background-color: #CCBEAA;
  color: white;
  font-weight: bold;
  border: none;
  padding: 12px 30px;
  border-radius: 30px;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

button.button:hover {
  background-color: #e9bd7f;
  transform: scale(1.05);
}

/* SIÈGES */
.seat-map {
  display: grid;
  grid-template-columns: repeat(9, auto);
  gap: 5px;
  margin: 20px 0;
  justify-content: center;
}

.seat {
  width: 35px;
  height: 35px;
  background: #ccc;
  text-align: center;
  line-height: 35px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9em;
  font-weight: bold;
  transition: background-color 0.3s;
}

.seat.selected {
  background: #CCBEAA;
  color: white;
}

.seat.disabled {
  background: #999;
  cursor: not-allowed;
}

.spacer {
  width: 10px;
}

.legend {
  display: flex;
  gap: 20px;
  margin-top: 10px;
  font-size: 0.9em;
  justify-content: center;
}

.legend span {
  display: flex;
  align-items: center;
  gap: 5px;
}

.prix-final {
  font-weight: bold;
  margin-top: 15px;
  text-align: right;
  font-size: 1.1em;
}

  </style>
</head>
<body>
  
  <header><h1>Réservation des vols</h1></header>
  <main class="reservation-container">
  <form action="formulaire_paiement.php" method="POST" id="reservation-form">
    
    <?php foreach ($vols as $vol): ?>
    <!-- Champs cachés pour transmettre les infos du vol au formulaire de paiement -->
    <input type="hidden" name="vols[<?= $vol['id_vol'] ?>][id_vol]" value="<?= $vol['id_vol'] ?>">
    <input type="hidden" name="prix_base[<?= $vol['id_vol'] ?>]" value="<?= $vol['prix'] ?>">

     <div>
  <h2><?= htmlspecialchars($vol['origine']) ?> → <?= htmlspecialchars($vol['destination']) ?></h2>
  <input type="hidden" name="vols[<?= $vol['id_vol'] ?>][id_vol]" value="<?= $vol['id_vol'] ?>">
  <input type="hidden" name="vols[<?= $vol['id_vol'] ?>][prix_base]" value="<?= $vol['prix'] ?>">

  <p><strong>Départ :</strong> <?= htmlspecialchars($vol['date_depart']) ?></p>
  <p><strong>Arrivée :</strong> <?= htmlspecialchars($vol['date_arrivee']) ?></p>
  <p><strong>Durée :</strong> <?= isset($vol['duree']) && $vol['duree'] ? htmlspecialchars($vol['duree']) : 'Non renseignée' ?></p>
  <p><strong>Prix de base :</strong> <?= $vol['prix'] ?> €</p>

  <h3>Formule</h3>
  <!-- Choix de la formule (économique ou premium) -->
  <label>
  <input type="radio" name="vols[<?= $vol['id_vol'] ?>][formule]" value="eco" checked> Économique
  </label>
  <label>
  <input type="radio" name="vols[<?= $vol['id_vol'] ?>][formule]" value="premium"> Premium (+70 €)
  </label>

  <h3>Bagage cabine</h3>
  <!-- Sélection du poids du bagage cabine -->
  <label for="poids_cabine_<?= $vol['id_vol'] ?>">Poids (kg) :</label>
  <select name="vols[<?= $vol['id_vol'] ?>][poids_cabine]" id="poids_cabine_<?= $vol['id_vol'] ?>">
  <?php for ($i = 0; $i <= 20; $i += 5): ?>
    <option value="<?= $i ?>"><?= $i ?> kg</option>
  <?php endfor; ?>
  </select>

  <!-- Section bagage en soute, affichée uniquement pour la formule premium -->
  <div id="soute-container-<?= $vol['id_vol'] ?>" style="display: none;">
  <h3>Bagage en soute</h3>
  <label for="poids_soute_<?= $vol['id_vol'] ?>">Poids (kg) :</label>
  <select name="vols[<?= $vol['id_vol'] ?>][poids_soute]" id="poids_soute_<?= $vol['id_vol'] ?>">
    <?php for ($i = 0; $i <= 35; $i += 5): ?>
    <option value="<?= $i ?>"><?= $i ?> kg</option>
    <?php endfor; ?>
  </select>
  </div>

  <!-- Sélection du siège, affichée uniquement pour la formule premium -->
  <div class="aircraft" id="siege-container-<?= $vol['id_vol'] ?>" style="display: none;">
  <h3>Choisissez votre siège</h3>
  <input type="hidden" name="vols[<?= $vol['id_vol'] ?>][siege]" id="selected-seat-<?= $vol['id_vol'] ?>" required>
  <div class="seat-map" id="seat-map-<?= $vol['id_vol'] ?>"></div>
  <div class="legend">
    <span style="display:inline-block;width:15px;height:15px;background:#CCBEAA;border-radius:3px;margin-right:5px;"></span> Sélectionné
    <span style="display:inline-block;width:15px;height:15px;background:#ccc;border-radius:3px;margin-right:5px;"></span> Disponible
    <span style="display:inline-block;width:15px;height:15px;background:#999;border-radius:3px;margin-right:5px;"></span> Occupé
  </div>
  </div>

  <!-- Affichage du prix total pour ce vol -->
  <div class="prix-final" id="prix-total-<?= $vol['id_vol'] ?>">
  Prix total : <?= $vol['prix'] ?> €
  </div>
  <hr>
</div>

    <?php endforeach; ?>

    <button type="submit" class="button">Procéder au paiement</button>
  </form>
  </main>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    // Injection des sièges réservés depuis PHP vers JS
    const reservedSeats = <?= json_encode($reservedSeatsPerVol) ?>;
    <?php foreach ($vols as $vol): ?>
    const id = <?= $vol['id_vol'] ?>;
    const basePrice = <?= $vol['prix'] ?>;
    // Récupération des éléments du formulaire pour ce vol
    const formuleRadios = document.getElementsByName(`vols[${id}][formule]`);
    const poidsCabine = document.getElementById(`poids_cabine_${id}`);
    const poidsSoute = document.getElementById(`poids_soute_${id}`);
    const souteContainer = document.getElementById(`soute-container-${id}`);
    const totalDisplay = document.getElementById(`prix-total-${id}`);
    const seatMap = document.getElementById(`seat-map-${id}`);
    const siegeContainer = document.getElementById(`siege-container-${id}`);
    const seatInput = document.getElementById(`selected-seat-${id}`);

    // Met à jour le prix total en fonction des options choisies
    function updatePrice() {
      let total = basePrice;
      const formule = [...formuleRadios].find(r => r.checked).value;
      const cabine = parseInt(poidsCabine.value);
      const soute = parseInt(poidsSoute.value || 0);

      if (formule === 'premium') {
      total += 70;
      souteContainer.style.display = 'block';
      siegeContainer.style.display = 'block';
      if (soute > 25) total += Math.ceil((soute - 25) / 5) * 40;
      } else {
      souteContainer.style.display = 'none';
      siegeContainer.style.display = 'none';
      if (cabine > 10) total += Math.min(((cabine - 10) / 5) * 30, 20);
      }

      totalDisplay.textContent = 'Prix total : ' + total.toFixed(2) + ' €';
    }

    // Génère la carte des sièges et gère la sélection
    seatMap.innerHTML = ''; // Nettoyage avant de regénérer
    function generateSeats() {
      const leftCols = ['A', 'B'];
      const middleCols = ['C', 'D', 'E'];
      const rightCols = ['F', 'G'];
      const reserved = reservedSeats[id] || [];
      for (let i = 1; i <= 10; i++) {
      [...leftCols, 'gap', ...middleCols, 'gap', ...rightCols].forEach(col => {
        if (col === 'gap') {
        const spacer = document.createElement('div');
        spacer.classList.add('spacer');
        seatMap.appendChild(spacer);
        } else {
        const seatCode = col + i;
        const seat = document.createElement('div');
        seat.classList.add('seat');
        seat.textContent = seatCode;
        // Vérifie si le siège est réservé
        if (reserved.includes(seatCode)) {
          seat.classList.add('disabled');
        } else {
          seat.addEventListener('click', () => {
          seatMap.querySelectorAll('.seat').forEach(s => s.classList.remove('selected'));
          seat.classList.add('selected');
          seatInput.value = seatCode;
          });
        }
        seatMap.appendChild(seat);
        }
      });
      }
    }

    // Ajoute les écouteurs d'événements pour mettre à jour le prix et l'affichage
    [...formuleRadios].forEach(r => r.addEventListener('change', updatePrice));
    poidsCabine.addEventListener('change', updatePrice);
    poidsSoute.addEventListener('change', updatePrice);

    generateSeats();
    updatePrice();
    <?php endforeach; ?>
  });
  </script>
  <!-- Script dupliqué, peut être supprimé car déjà présent ci-dessus -->
</body>
</html>
