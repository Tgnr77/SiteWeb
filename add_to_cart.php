<?php
include 'db.php';
session_start();
header('Content-Type: application/json');

try {
    if (!$pdo) {
        throw new Exception("Connexion à la base de données échouée.");
    }

    // Vérifier si un vol est envoyé en POST
    $id_vol = isset($_POST['id_vol']) ? (int) $_POST['id_vol'] : 0;

    if ($id_vol === 0) {
        echo json_encode(['error' => 'ID du vol invalide.']);
        exit;
    }

    if (isset($_SESSION['id_utilisateur'])) {
        $id_utilisateur = $_SESSION['id_utilisateur'];

        // Vérifier si le vol est déjà dans le panier
        $stmt = $pdo->prepare("SELECT * FROM panier WHERE id_utilisateur = :id_utilisateur AND id_vol = :id_vol");
        $stmt->execute([':id_utilisateur' => $id_utilisateur, ':id_vol' => $id_vol]);
        $panier_item = $stmt->fetch();

        if ($panier_item) {
            $stmt = $pdo->prepare("UPDATE panier SET quantite = quantite + 1, date_ajout = NOW() WHERE id_panier = :id_panier");
            $stmt->execute([':id_panier' => $panier_item['id_panier']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO panier (id_utilisateur, id_vol, quantite, date_ajout) VALUES (:id_utilisateur, :id_vol, 1, NOW())");
            $stmt->execute([':id_utilisateur' => $id_utilisateur, ':id_vol' => $id_vol]);
        }
    } else {
        // Stocker dans $_SESSION['panier'] pour les utilisateurs non connectés
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }

        if (isset($_SESSION['panier'][$id_vol])) {
            $_SESSION['panier'][$id_vol]['quantite']++;
        } else {
            $_SESSION['panier'][$id_vol] = [
                'id_vol'   => $id_vol,
                'quantite' => 1
            ];
        }
    }

    echo json_encode(['success' => 'Vol ajouté au panier.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Erreur serveur : ' . $e->getMessage()]);
}
?>
