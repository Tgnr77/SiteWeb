<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $rememberMe = isset($_POST['remember_me']);

    if (empty($email) || empty($mot_de_passe)) {
        echo "Veuillez remplir tous les champs.";
        exit;
    }

    // Rechercher l'utilisateur dans la base de données
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification des identifiants
    if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        // Stocker les informations dans la session
        $_SESSION['utilisateur'] = [
            'id' => $utilisateur['id_utilisateur'],
            'nom' => $utilisateur['nom'],
            'prenom' => $utilisateur['prenom'],
            'email' => $utilisateur['email'],
        ];

        // Si "Rester connecté" est coché
        if ($rememberMe) {
            $token = bin2hex(random_bytes(16)); // Génère un token sécurisé
            $expireTime = date('Y-m-d H:i:s', time() + (86400 * 30)); // Expiration dans 30 jours
            setcookie('auth_token', $token, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => true, // Activez uniquement en HTTPS
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            // Enregistrer le token et son expiration dans la base de données
            $stmt = $pdo->prepare("UPDATE utilisateurs SET token = :token, token_expire = :expire WHERE id_utilisateur = :id");
            $stmt->execute(['token' => $token, 'expire' => $expireTime, 'id' => $utilisateur['id_utilisateur']]);
        }

        // Redirection
       $redirectUrl = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : '../View/account.php';
        header("Location: $redirectUrl");
        exit;
    } else {
        echo "Les identifiants sont incorrects.";
    }
}
if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $id_vol => $panier_item) {
        $stmt = $pdo->prepare("SELECT * FROM panier WHERE id_utilisateur = :id_utilisateur AND id_vol = :id_vol");
        $stmt->execute([':id_utilisateur' => $_SESSION['id_utilisateur'], ':id_vol' => $id_vol]);
        $existingItem = $stmt->fetch();

        if ($existingItem) {
            // Mettre à jour la quantité
            $stmt = $pdo->prepare("UPDATE panier SET quantite = quantite + :quantite WHERE id_panier = :id_panier");
            $stmt->execute([':quantite' => $panier_item['quantite'], ':id_panier' => $existingItem['id_panier']]);
        } else {
            // Ajouter le vol au panier de l'utilisateur
            $stmt = $pdo->prepare("INSERT INTO panier (id_utilisateur, id_vol, quantite, date_ajout) VALUES (:id_utilisateur, :id_vol, :quantite, NOW())");
            $stmt->execute([':id_utilisateur' => $_SESSION['id_utilisateur'], ':id_vol' => $id_vol, ':quantite' => $panier_item['quantite']]);
        }
    }
    // Vider le panier de session après fusion
    unset($_SESSION['panier']);
}

?>
