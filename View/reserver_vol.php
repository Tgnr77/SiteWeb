<?php
require_once __DIR__ . '/../Config/paths.php';
require_once MODEL_PATH . 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Vol non spécifié.");
}

$id_vol = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM vols WHERE id_vol = ?");
$stmt->execute([$id_vol]);
$vol = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vol) {
    die("Vol introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Réserver - <?= htmlspecialchars($vol['origine']) ?> → <?= htmlspecialchars($vol['destination']) ?></title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 0;
    }
    .reservation-container {
      max-width: 850px;
      margin: 40px auto;
      padding: 30px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
    }
    h1, h2, h3 { text-align: center; color: #333; }
    label { display: block; margin: 10px 0 5px; }
    select, input[type="radio"] { margin-top: 5px; }
    .prix-final { font-weight: bold; font-size: 1.2em; text-align: right; margin-top: 15px; }
    .button { background-color: #CCBEAA; color: white; border: none; padding: 12px; width: 100%; border-radius: 30px; font-size: 1em; font-weight: bold; cursor: pointer; }
    .button:hover { background-color: #e9bd7f; }
    .aircraft {
      background: #f5f5f5;
      border-radius: 50px;
      padding: 30px 20px;
      margin: 30px auto;
      max-width: 600px;
      box-shadow: inset 0 0 20px rgba(0,0,0,0.05);
    }
    .seat-map {
      display: grid;
      grid-template-columns: repeat(9, 1fr);
      gap: 6px;
      justify-content: center;
    }
    .seat {
      width: 35px;
      height: 35px;
      background-color: #ccc;
      border-radius: 6px;
      text-align: center;
      line-height: 35px;
      font-weight: bold;
      cursor: pointer;
      user-select: none;
    }
    .seat:hover { background-color: #bbb; }
    .seat.selected { background-color: #CCBEAA; color: white; }
    .seat.disabled { background-color: #999; cursor: not-allowed; }
    .spacer { width: 35px; height: 35px; background: transparent; }
    .legend { text-align: center; margin-top: 10px; font-size: 0.9em; color: #666; }
    .row-label { text-align: center; font-weight: bold; margin-top: 10px; }
  </style>
</head>
<body>
  <header><h1>Réservation du vol</h1></header>

  <main class="reservation-container">
    <form action="../Controller/paiement.php" method="POST" id="reservation-form">
      <input type="hidden" name="id_vol" value="<?= $id_vol ?>">
      <input type="hidden" id="prix_base_hidden" name="prix_base" value="<?= $vol['prix'] ?>">

      <div>
        <h2>Informations du vol</h2>
        <p><strong>De :</strong> <?= htmlspecialchars($vol['origine']) ?></p>
        <p><strong>Vers :</strong> <?= htmlspecialchars($vol['destination']) ?></p>
        <p><strong>Date de départ :</strong> <?= htmlspecialchars($vol['date_depart']) ?></p>
        <p><strong>Arrivée :</strong> <?= htmlspecialchars($vol['date_arrivee']) ?></p>
        <p><strong>Durée :</strong> <?= isset($vol['duree']) && $vol['duree'] ? htmlspecialchars($vol['duree']) : 'Non renseignée' ?></p>
        <p><strong>Prix de base :</strong> <?= $vol['prix'] ?> €</p>
      </div>

      <div>
        <h3>Formule</h3>
        <label><input type="radio" name="formule" value="eco" checked> Économique : Bagage cabine / Siège non sélectionnable / Non remboursable</label>
        <label><input type="radio" name="formule" value="premium"> Premium (+70 €) : Bagage cabine + soute / Siège sélectionnable / Remboursable</label>
      </div>

      <div>
        <h3>Bagage cabine</h3>
        <label for="poids_cabine">Poids (kg) :</label>
        <select name="poids_cabine" id="poids_cabine">
          <?php for ($i = 0; $i <= 20; $i += 5): ?>
            <option value="<?= $i ?>"><?= $i ?> kg</option>
          <?php endfor; ?>
        </select>
      </div>

      <div id="soute-container" style="display: none;">
        <h3>Bagage en soute</h3>
        <label for="poids_soute">Poids (kg) :</label>
        <select name="poids_soute" id="poids_soute">
          <?php for ($i = 0; $i <= 35; $i += 5): ?>
            <option value="<?= $i ?>"><?= $i ?> kg</option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="aircraft" id="siege-container" style="display: none;">
        <h3>Choisissez votre siège</h3>
        <input type="hidden" name="siege" id="selected-seat" required>
        <div class="seat-map" id="seat-map"></div>
        <div class="legend">
          <span style="display:inline-block;width:15px;height:15px;background:#CCBEAA;border-radius:3px;margin-right:5px;"></span> Sélectionné
          <span style="display:inline-block;width:15px;height:15px;background:#ccc;border-radius:3px;margin-right:5px;"></span> Disponible
          <span style="display:inline-block;width:15px;height:15px;background:#999;border-radius:3px;margin-right:5px;"></span> Occupé
        </div>
      </div>

      <div class="prix-final">
        Prix total : <span id="prix-total"><?= $vol['prix'] ?> €</span>
      </div>

      <button type="submit" class="button">Procéder au paiement</button>
    </form>
  </main>

  <script>
    const basePrice = <?= $vol['prix'] ?>;
    const formuleRadios = document.querySelectorAll('input[name="formule"]');
    const poidsCabine = document.getElementById('poids_cabine');
    const poidsSoute = document.getElementById('poids_soute');
    const priceDisplay = document.getElementById('prix-total');
    const hiddenInput = document.getElementById('prix_base_hidden');
    const siegeContainer = document.getElementById('siege-container');
    const seatMap = document.getElementById('seat-map');
    const souteContainer = document.getElementById('soute-container');

    function updatePrice() {
      let total = basePrice;
      const formule = document.querySelector('input[name="formule"]:checked').value;
      const cabine = parseInt(poidsCabine.value);
      const soute = parseInt(poidsSoute ? poidsSoute.value : 0);

      if (formule === 'premium') {
        total += 70;
        siegeContainer.style.display = 'block';
        souteContainer.style.display = 'block';
        if (soute > 25) total += Math.ceil((soute - 25) / 5) * 40;
      } else {
        siegeContainer.style.display = 'none';
        souteContainer.style.display = 'none';
        if (cabine > 10) total += Math.min(((cabine - 10) / 5) * 30, 20);
      }

      priceDisplay.textContent = total.toFixed(2) + " €";
      hiddenInput.value = total.toFixed(2);
    }

    function generateSeats() {
      const leftCols = ['A', 'B'];
      const middleCols = ['C', 'D', 'E'];
      const rightCols = ['F', 'G'];
      const reserved = [];
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
            if (reserved.includes(seatCode)) {
              seat.classList.add('disabled');
            } else {
              seat.addEventListener('click', () => {
                document.querySelectorAll('.seat').forEach(s => s.classList.remove('selected'));
                seat.classList.add('selected');
                document.getElementById('selected-seat').value = seatCode;
              });
            }
            seatMap.appendChild(seat);
          }
        });
      }
    }

    formuleRadios.forEach(r => r.addEventListener('change', updatePrice));
    poidsCabine.addEventListener('change', updatePrice);
    if (poidsSoute) poidsSoute.addEventListener('change', updatePrice);

    document.addEventListener('DOMContentLoaded', () => {
      generateSeats();
      updatePrice();
    });
  </script>
</body>
</html>
