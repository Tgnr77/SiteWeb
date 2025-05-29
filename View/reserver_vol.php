<?php
require_once __DIR__ . '/../Config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();


if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: login.html');
    exit;
}

$ids = $_GET['ids'] ?? [];

if (empty($ids) || !is_array($ids)) {
    die("Aucun vol sélectionné.");
}

$ids = array_filter($ids, fn($id) => is_numeric($id));
if (empty($ids)) {
    die("Vols invalides.");
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM vols WHERE id_vol IN ($placeholders)");
$stmt->execute($ids);
$vols = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
  
  <header><h1>Réservation des vols</h1></header>
  <main class="reservation-container">
    <form action="../Controller/paiement.php" method="POST" id="reservation-form">
      
      <?php foreach ($vols as $vol): ?>
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
  <label>
    <input type="radio" name="vols[<?= $vol['id_vol'] ?>][formule]" value="eco" checked> Économique
  </label>
  <label>
    <input type="radio" name="vols[<?= $vol['id_vol'] ?>][formule]" value="premium"> Premium (+70 €)
  </label>

  <h3>Bagage cabine</h3>
  <label for="poids_cabine_<?= $vol['id_vol'] ?>">Poids (kg) :</label>
  <select name="vols[<?= $vol['id_vol'] ?>][poids_cabine]" id="poids_cabine_<?= $vol['id_vol'] ?>">
    <?php for ($i = 0; $i <= 20; $i += 5): ?>
      <option value="<?= $i ?>"><?= $i ?> kg</option>
    <?php endfor; ?>
  </select>

  <div id="soute-container-<?= $vol['id_vol'] ?>" style="display: none;">
    <h3>Bagage en soute</h3>
    <label for="poids_soute_<?= $vol['id_vol'] ?>">Poids (kg) :</label>
    <select name="vols[<?= $vol['id_vol'] ?>][poids_soute]" id="poids_soute_<?= $vol['id_vol'] ?>">
      <?php for ($i = 0; $i <= 35; $i += 5): ?>
        <option value="<?= $i ?>"><?= $i ?> kg</option>
      <?php endfor; ?>
    </select>
  </div>

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
      const reservedSeats = <?= json_encode($reservedSeatsPerVol) ?>;
      <?php foreach ($vols as $vol): ?>
        const id = <?= $vol['id_vol'] ?>;
        const basePrice = <?= $vol['prix'] ?>;
        const formuleRadios = document.getElementsByName(`formule[${id}]`);
        const poidsCabine = document.getElementById(`poids_cabine_${id}`);
        const poidsSoute = document.getElementById(`poids_soute_${id}`);
        const souteContainer = document.getElementById(`soute-container-${id}`);
        const totalDisplay = document.getElementById(`prix-total-${id}`);
        const seatMap = document.getElementById(`seat-map-${id}`);
        const siegeContainer = document.getElementById(`siege-container-${id}`);
        const seatInput = document.getElementById(`selected-seat-${id}`);

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

        [...formuleRadios].forEach(r => r.addEventListener('change', updatePrice));
        poidsCabine.addEventListener('change', updatePrice);
        poidsSoute.addEventListener('change', updatePrice);

        generateSeats();
        updatePrice();
      <?php endforeach; ?>
    });
  </script>
  <script>
document.addEventListener('DOMContentLoaded', function () {
  <?php foreach ($vols as $vol): ?>
    const id = <?= $vol['id_vol'] ?>;
    const basePrice = <?= $vol['prix'] ?>;

    const formuleRadios = document.getElementsByName(`vols[${id}][formule]`);
    const poidsCabine = document.getElementById(`poids_cabine_${id}`);
    const poidsSoute = document.getElementById(`poids_soute_${id}`);
    const souteContainer = document.getElementById(`soute-container-${id}`);
    const totalDisplay = document.getElementById(`prix-total-${id}`);
    const seatMap = document.getElementById(`seat-map-${id}`);
    const siegeContainer = document.getElementById(`siege-container-${id}`);
    const seatInput = document.getElementById(`selected-seat-${id}`);

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
        seatInput.value = ''; // efface le siège si formule éco
        if (cabine > 10) total += Math.min(((cabine - 10) / 5) * 30, 20);
      }

      totalDisplay.textContent = 'Prix total : ' + total.toFixed(2) + ' €';
    }

    function generateSeats() {
      const leftCols = ['A', 'B'];
      const middleCols = ['C', 'D', 'E'];
      const rightCols = ['F', 'G'];

      // 🧠 Tu peux récupérer dynamiquement les sièges déjà réservés ici si besoin
      const reserved = []; // tu peux remplir ça avec un fetch('...')

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

    [...formuleRadios].forEach(r => r.addEventListener('change', updatePrice));
    poidsCabine.addEventListener('change', updatePrice);
    poidsSoute.addEventListener('change', updatePrice);

    generateSeats();
    updatePrice();
  <?php endforeach; ?>
});
</script>

</body>
</html>
