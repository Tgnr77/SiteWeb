<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();

$compteur = 0;
if (isset($_SESSION['utilisateur']['id'])) {
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM panier WHERE id_utilisateur = ?");
  $stmt->execute([$_SESSION['utilisateur']['id']]);
  $compteur = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vols à venir - Zenith Airlines</title>
  <link rel="stylesheet" href="styles.css">
  <script>
    let volsData = [];

    document.addEventListener('DOMContentLoaded', function () {
      const offersContainer = document.querySelector('.offers');
      offersContainer.innerHTML = '<p>Chargement des vols...</p>';

      // Récupère la liste des vols depuis le serveur
      fetch('../Controller/get_vols.php')
        .then(response => {
          if (!response.ok) throw new Error('Erreur lors de la récupération des données.');
          return response.json();
        })
        .then(data => {
          volsData = data;
          offersContainer.innerHTML = '';

          if (data.length > 0) {
            data.forEach(vol => {
              const volDiv = document.createElement('div');
              volDiv.classList.add('offer');
              // Affiche les informations du vol
              volDiv.innerHTML = `
                <div class="offer-details">
                  <h3>${vol.origine} - ${vol.destination}</h3>
                  <p>Départ : ${new Date(vol.date_depart).toLocaleString()}</p>
                  <p>Arrivée : ${new Date(vol.date_arrivee).toLocaleString()}</p>
                  <p>Durée : ${vol.duree}</p>
                  <p>Prix : ${vol.prix} €</p>
                  <div class="buttons">
                    <button class="add-to-cart" data-id="${vol.id_vol}">Ajouter au panier</button>
                    <button class="details-button" data-id="${vol.id_vol}">Détails</button>
                    <button onclick="window.location.href='../View/reserver_vol.php?id=${vol.id_vol}'" class="add-to-cart">Réserver</button>
                  </div>
                </div>
              `;
              offersContainer.appendChild(volDiv);
            });

            attachAddToCartEvents(); // Ajoute les événements pour le panier
            attachDetailsEvents();   // Ajoute les événements pour les détails
          } else {
            offersContainer.innerHTML = '<p>Aucun vol disponible pour le moment.</p>';
          }
        })
        .catch(error => {
          console.error('Erreur :', error);
          offersContainer.innerHTML = '<p>Erreur lors du chargement des vols. Veuillez réessayer plus tard.</p>';
        });

      // Chargement dynamique du compteur panier
      if (document.getElementById('panier-count')) {
        fetch('../Controller/get_cart_count.php')
          .then(response => response.json())
          .then(data => {
            if (data.count > 0) {
              document.getElementById('panier-count').textContent = `(${data.count})`;
            } else {
              document.getElementById('panier-count').textContent = '';
            }
          })
          .catch(() => {
            // Si erreur, utilise la valeur stockée en session
            const panierCount = sessionStorage.getItem('panier_count');
            if (panierCount > 0) {
              document.getElementById('panier-count').textContent = `(${panierCount})`;
            }
          });
      }
    });

    // Ajoute un vol au panier
    function attachAddToCartEvents() {
      document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function () {
          const volId = this.dataset.id;
          fetch('../Controller/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_vol=${volId}`
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                showToast('✔️ Vol ajouté au panier');
                if (data.success) {
  showToast('✔️ Vol ajouté au panier');

  // Requête serveur pour actualiser le compteur réel
  fetch('../Controller/get_cart_count.php')
    .then(res => res.json())
    .then(countData => {
      const span = document.getElementById('cart-count');
      if (span) span.textContent = `(${countData.count})`;
    });

  // (Optionnel) aussi garder localement si tu veux utiliser sessionStorage
  let count = parseInt(sessionStorage.getItem('panier_count') || '0');
  sessionStorage.setItem('panier_count', count + 1);
}
              } else {
                showToast('❌ ' + (data.error || 'Erreur inconnue'), true);
              }
            })
            .catch(error => {
              console.error('Erreur réseau :', error);
              showToast('❌ Impossible d\'ajouter au panier.', true);
            });
        });
      });
    }

    // Affiche la modale avec les détails du vol
    function attachDetailsEvents() {
      const detailButtons = document.querySelectorAll('.details-button');
      const modal = document.getElementById('modal');
      const closeBtn = modal.querySelector('.close');

      detailButtons.forEach(button => {
        button.addEventListener('click', function () {
          const volId = this.dataset.id;
          const vol = volsData.find(v => v.id_vol == volId);
          if (!vol) return;

          document.getElementById('modal-origine').textContent = `Origine : ${vol.origine}`;
          document.getElementById('modal-destination').textContent = `Destination : ${vol.destination}`;
          document.getElementById('modal-depart').textContent = `Départ : ${new Date(vol.date_depart).toLocaleString()}`;
          document.getElementById('modal-arrivee').textContent = `Arrivée : ${new Date(vol.date_arrivee).toLocaleString()}`;
          document.getElementById('modal-duree').textContent = `Durée : ${vol.duree}`;
          document.getElementById('modal-prix').textContent = `Prix : ${vol.prix} €`;
          document.getElementById('modal-compagnie').textContent = `Compagnie : ${vol.compagnie || 'Zenith Airlines'}`;
          document.getElementById('modal-passagers').textContent = `Nombre de passagers : ${vol.nb_passagers || 'Non spécifié'}`;

          modal.classList.remove('hidden');
          modal.classList.add('show');
        });
      });

      // Ferme la modale au clic sur la croix
      closeBtn.onclick = () => {
        modal.classList.add('hidden');
        modal.classList.remove('show');
      };

      // Ferme la modale au clic en dehors du contenu
      window.onclick = function (event) {
        if (event.target === modal) {
          modal.classList.add('hidden');
          modal.classList.remove('show');
        }
      };
    }

    // Affiche une notification toast
    function showToast(message, isError = false) {
      const toast = document.getElementById('toast');
      if (!toast) return;
      toast.textContent = message;
      toast.style.backgroundColor = isError ? '#e74c3c' : '#4CAF50';
      toast.style.opacity = '1';
      setTimeout(() => {
        toast.style.opacity = '0';
      }, 3000);
    }
  </script>
</head>
<body>
  <header>
    
    <div class="logo">
      <img src="zenith.webp" alt="Logo Zenith Airlines">
    </div>
    <nav>
      <a href="../View/index.html">Accueil</a>
      <a href="../View/account.php">Mon compte</a>
      <a href="panier.php">🛒 Voir le panier <span id="cart-count">(<?= $compteur ?>)</span></a>
      <a href="../View/contact.html">Nous contacter</a>
    </nav>
  </header>
  <main>
    <section>
      <h2>Vols à venir</h2>
      <p>Découvrez nos prochaines destinations :</p>
      <div class="offers"></div>
    </section>
  </main>
  <footer>
    <p>&copy; 2025 Zenith Airlines. Tous droits réservés.</p>
  </footer>

  <!-- MODALE DÉTAILS VOL -->
  <div id="modal" class="modal hidden">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Détails du vol</h2>
      <p id="modal-origine"></p>
      <p id="modal-destination"></p>
      <p id="modal-depart"></p>
      <p id="modal-arrivee"></p>
      <p id="modal-duree"></p>
      <p id="modal-prix"></p>
      <p id="modal-compagnie"></p>
      <p id="modal-passagers"></p>
    </div>
  </div>

  <!-- Toast Notification -->
  <div id="toast" style="
    position: fixed;
    bottom: 30px;
    right: 30px;
    background-color: #4CAF50;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    font-weight: bold;
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.5s ease;
    pointer-events: none;
  ">
    ✔️ Vol ajouté au panier
  </div>
</body>
</html>
