<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Compte - Zenith Airlines</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Styles spécifiques pour la page d'authentification */
    .tab-container {
      max-width: 400px;
      margin: 40px auto;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .tabs {
      display: flex;
      justify-content: space-around;
      background-color: #f2f2f2;
      cursor: pointer;
    }
    .tabs div {
      flex: 1;
      padding: 12px;
      text-align: center;
      font-weight: bold;
      transition: background-color 0.3s;
    }
    .tabs .active {
      background-color: #CCBEAA;
      color: #fff;
    }
    .form-container {
      padding: 20px;
    }
    .form-container form {
      display: none;
    }
    .form-container form.active {
      display: block;
    }
    .form-container h2 {
      margin-bottom: 20px;
      color: #333;
      text-align: center;
    }
    .form-container label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #555;
    }
    .form-container input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      box-sizing: border-box;
    }
    .form-container button {
      width: 100%;
      padding: 10px;
      background-color: #CCBEAA;
      color: #fff;
      border: none;
      border-radius: 30px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.3s;
    }
    .form-container button:hover {
      background-color: #e9bd7f;
      transform: scale(1.02);
    }
  </style>
</head>
<body>
  <header>
    <div class="header-container">
      <div class="logo">
        <img src="zenith.webp" alt="Logo Zenith Airlines">
      </div>
      <nav class="main-nav">
        <a href="../View/index.html">Accueil</a>
        <a href="../View/vols.html">Vols à venir</a>
        <a href="../View/reserver.html">Réserver un siège</a>
        <a href="../View/contact.html">Nous contacter</a>
        <!-- Le lien vers "Mon Compte" n'est utile qu'une fois connecté -->
      </nav>
    </div>
  </header>
  
  <main>
    <div class="tab-container">
      <div class="tabs">
        <div id="login-tab" class="active">Se connecter</div>
        <div id="signup-tab">Créer un compte</div>
      </div>
      <div class="form-container">
        <!-- Formulaire de connexion -->
        <form id="login-form" class="active" action="login.php" method="POST">
          <h2>Connexion</h2>
          <label for="login-email">Adresse e-mail :</label>
          <input type="email" name="email" id="login-email" placeholder="Votre adresse e-mail" required>
          
          <label for="login-password">Mot de passe :</label>
          <input type="password" name="password" id="login-password" placeholder="Votre mot de passe" required>
          
          <button type="submit">Se connecter</button>
        </form>
        
        <!-- Formulaire de création de compte -->
        <form id="signup-form" action="signup_handler.php" method="POST">
          <h2>Créer un compte</h2>
          <label for="signup-nom">Nom :</label>
          <input type="text" name="nom" id="signup-nom" placeholder="Votre nom" required>
          
          <label for="signup-prenom">Prénom :</label>
          <input type="text" name="prenom" id="signup-prenom" placeholder="Votre prénom" required>
          
          <label for="signup-email">Adresse e-mail :</label>
          <input type="email" name="email" id="signup-email" placeholder="Votre adresse e-mail" required>
          
          <label for="signup-mot_de_passe">Mot de passe :</label>
          <input type="password" name="mot_de_passe" id="signup-mot_de_passe" placeholder="Choisissez un mot de passe" required>
          
          <label for="signup-mot_de_passe-confirm">Confirmez le mot de passe :</label>
          <input type="password" name="mot_de_passe_confirm" id="signup-mot_de_passe-confirm" placeholder="Confirmez votre mot de passe" required>
          
          <button type="submit">Créer un compte</button>
        </form>
      </div>
    </div>
  </main>
  
  <footer>
    <p>&copy; 2025 Zenith Airlines. Tous droits réservés.</p>
  </footer>
  
  <script>
    // Gestion de l'affichage des onglets
    document.getElementById('login-tab').addEventListener('click', function() {
      document.getElementById('login-form').classList.add('active');
      document.getElementById('signup-form').classList.remove('active');
      this.classList.add('active');
      document.getElementById('signup-tab').classList.remove('active');
    });
    document.getElementById('signup-tab').addEventListener('click', function() {
      document.getElementById('signup-form').classList.add('active');
      document.getElementById('login-form').classList.remove('active');
      this.classList.add('active');
      document.getElementById('login-tab').classList.remove('active');
    });
  </script>
</body>
</html>
