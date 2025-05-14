<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();


// Si l'utilisateur n'est pas connecté, vérifier le cookie
if (!isset($_SESSION['utilisateur']) && isset($_COOKIE['auth_token'])) {
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE token = :token");
    $stmt->execute(['token' => $_COOKIE['auth_token']]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($utilisateur) {
        // Démarrer une session avec les informations de l'utilisateur
        $_SESSION['utilisateur'] = [
            'id' => $utilisateur['id_utilisateur'],
            'nom' => $utilisateur['nom'],
            'prenom' => $utilisateur['prenom'],
            'email' => $utilisateur['email'],
        ];
    }
}

// Rediriger vers la page de connexion si aucun utilisateur n'est connecté
if (!isset($_SESSION['utilisateur'])) {
    header('Location: login.html');
    exit;
}

$utilisateur = $_SESSION['utilisateur'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - Zenith Airlines</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="zenith.webp" alt="Logo Zenith Airlines">
        </div>
        <nav>
            <a href="index.html">Accueil</a>
            <a href="logout.php">Se déconnecter</a>
        </nav>
    </header>
    
    <main>
        <section class="user-dashboard">
            <h1>Bienvenue, <?php echo htmlspecialchars($utilisateur['prenom']); ?> !</h1>
            <p>Voici les informations associées à votre compte :</p>
            <ul>
                <li><strong>Nom :</strong> <?php echo htmlspecialchars($utilisateur['nom']); ?></li>
                <li><strong>Prénom :</strong> <?php echo htmlspecialchars($utilisateur['prenom']); ?></li>
                <li><strong>Email :</strong> <?php echo htmlspecialchars($utilisateur['email']); ?></li>
            </ul>
        </section>

        <section class="user-actions">
            <h2>Vos options :</h2>
            <a href="vols.html" class="button">Réserver un vol</a>
            <a href="logout.php" class="button">Se déconnecter</a>
        </section>
    </main>
    
    <footer>
        <p>&copy; 2025 Zenith Airlines. Tous droits réservés.</p>
    </footer>
</body>
</html>
