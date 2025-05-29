<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Vérifie si l'utilisateur est déjà connecté
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $rememberMe = isset($_POST['remember_me']);

    if (empty($email) || empty($mot_de_passe)) {
        echo "Veuillez remplir tous les champs.";
        exit;
    }
// Vérification de l'email
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
// Si l'utilisateur existe et que le mot de passe est correct
    if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        $_SESSION['utilisateur'] = [
            'id' => $utilisateur['id_utilisateur'],
            'nom' => $utilisateur['nom'],
            'prenom' => $utilisateur['prenom'],
            'email' => $utilisateur['email'],
        ];

        // ✅ Fusion du panier en session
        if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])) {
            foreach ($_SESSION['panier'] as $id_vol => $panier_item) {
                // Vérification de l'existence du vol dans la base de données
                $stmt = $pdo->prepare("
                    SELECT * FROM panier 
                    WHERE id_utilisateur = :id_utilisateur AND id_vol = :id_vol
                ");
                // Vérification de l'existence de l'article dans le panier
                $stmt->execute([
                    ':id_utilisateur' => $utilisateur['id_utilisateur'],
                    ':id_vol' => $id_vol
                ]);
                $existing = $stmt->fetch();
                    // Si l'article existe déjà, on met à jour la quantité
                if ($existing) {
                    $stmt = $pdo->prepare("
                        UPDATE panier 
                        SET quantite = quantite + :quantite, date_ajout = NOW()
                        WHERE id_panier = :id_panier
                    ");
                    // Mise à jour de la quantité de l'article dans le panier
                    $stmt->execute([
                        ':quantite' => $panier_item['quantite'],
                        ':id_panier' => $existing['id_panier']
                    ]);
                } else {
                    // Si l'article n'existe pas, on l'insère dans le panier
                    $stmt = $pdo->prepare("
                        INSERT INTO panier (id_utilisateur, id_vol, quantite, date_ajout)
                        VALUES (:id_utilisateur, :id_vol, :quantite, NOW())
                    ");
                    // Insertion de l'article dans le panier
                    $stmt->execute([
                        ':id_utilisateur' => $utilisateur['id_utilisateur'],
                        ':id_vol' => $id_vol,
                        ':quantite' => $panier_item['quantite']
                    ]);
                }
            }
            // Une fois le panier fusionné, on le vide de la session
            unset($_SESSION['panier']);
        }

        // Rester connecté
        if ($rememberMe) {
            $token = bin2hex(random_bytes(16));
            $expireTime = date('Y-m-d H:i:s', time() + (86400 * 30));
            setcookie('auth_token', $token, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
// Mise à jour du token et de la date d'expiration dans la base de données
            $stmt = $pdo->prepare("UPDATE utilisateurs SET token = :token, token_expire = :expire WHERE id_utilisateur = :id");
            $stmt->execute(['token' => $token, 'expire' => $expireTime, 'id' => $utilisateur['id_utilisateur']]);
        }
        // Redirection vers la page de compte ou une autre page après connexion
        $redirectUrl = $_POST['redirect_to'] ?? '../View/account.php';
        header("Location: $redirectUrl");
        exit;
    } else {
        echo "Les identifiants sont incorrects.";
    }
}
?>
