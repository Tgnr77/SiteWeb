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

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

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
                $stmt = $pdo->prepare("
                    SELECT * FROM panier 
                    WHERE id_utilisateur = :id_utilisateur AND id_vol = :id_vol
                ");
                $stmt->execute([
                    ':id_utilisateur' => $utilisateur['id_utilisateur'],
                    ':id_vol' => $id_vol
                ]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $stmt = $pdo->prepare("
                        UPDATE panier 
                        SET quantite = quantite + :quantite, date_ajout = NOW()
                        WHERE id_panier = :id_panier
                    ");
                    $stmt->execute([
                        ':quantite' => $panier_item['quantite'],
                        ':id_panier' => $existing['id_panier']
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO panier (id_utilisateur, id_vol, quantite, date_ajout)
                        VALUES (:id_utilisateur, :id_vol, :quantite, NOW())
                    ");
                    $stmt->execute([
                        ':id_utilisateur' => $utilisateur['id_utilisateur'],
                        ':id_vol' => $id_vol,
                        ':quantite' => $panier_item['quantite']
                    ]);
                }
            }

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

            $stmt = $pdo->prepare("UPDATE utilisateurs SET token = :token, token_expire = :expire WHERE id_utilisateur = :id");
            $stmt->execute(['token' => $token, 'expire' => $expireTime, 'id' => $utilisateur['id_utilisateur']]);
        }

        $redirectUrl = $_POST['redirect_to'] ?? '../View/account.php';
        header("Location: $redirectUrl");
        exit;
    } else {
        echo "Les identifiants sont incorrects.";
    }
}
?>
