<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();

// Si connecté → suppression en base
if (isset($_SESSION['utilisateur']['id']) && isset($_POST['id_panier'])) {
    $id_panier = (int) $_POST['id_panier'];
    $user_id = $_SESSION['utilisateur']['id'];

    $stmt = $pdo->prepare("DELETE FROM panier WHERE id_panier = :id_panier AND id_utilisateur = :id_utilisateur");
    $stmt->execute([
        'id_panier' => $id_panier,
        'id_utilisateur' => $user_id
    ]);
}

// Sinon → suppression dans la session (si panier anonyme)
elseif (isset($_POST['id_vol']) && isset($_SESSION['panier'][$_POST['id_vol']])) {
    unset($_SESSION['panier'][$_POST['id_vol']]);
}

header('Location: ../View/panier.php');
exit;
