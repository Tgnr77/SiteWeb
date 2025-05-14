<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';

session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Utilisateur non connecté.']);
    exit;
}

try {
    $id_utilisateur = $_SESSION['id_utilisateur'];
    $id_panier = isset($_POST['id_panier']) ? (int)$_POST['id_panier'] : 0;

    if ($id_panier === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID du panier invalide.']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM panier WHERE id_panier = :id_panier AND id_utilisateur = :id_utilisateur");
    $stmt->execute([':id_panier' => $id_panier, ':id_utilisateur' => $id_utilisateur]);

    echo json_encode(['success' => 'Article supprimé du panier.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
