<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();

header('Content-Type: application/json');

try {
    if (isset($_SESSION['utilisateur']['id']) && isset($_POST['id_panier'])) {
        $id_panier = (int) $_POST['id_panier'];
        $user_id = $_SESSION['utilisateur']['id'];

        $stmt = $pdo->prepare("DELETE FROM panier WHERE id_panier = :id_panier AND id_utilisateur = :id_utilisateur");
        $stmt->execute([
            'id_panier' => $id_panier,
            'id_utilisateur' => $user_id
        ]);

        echo json_encode(['success' => true]);
        exit;
    }

    // Mode invité : panier en session
    elseif (isset($_POST['id_vol']) && isset($_SESSION['panier'][$_POST['id_vol']])) {
        unset($_SESSION['panier'][$_POST['id_vol']]);
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Paramètres invalides.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
