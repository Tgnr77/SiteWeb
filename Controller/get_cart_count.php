<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();
header('Content-Type: application/json');

$count = 0;

if (isset($_SESSION['utilisateur']['id'])) {
    // Compter tous les vols du panier pour l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT SUM(quantite) as total FROM panier WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['utilisateur']['id']]);
    $row = $stmt->fetch();
    $count = (int) ($row['total'] ?? 0);
} elseif (isset($_SESSION['panier'])) {
    // Compter tous les vols stockés temporairement en session
    foreach ($_SESSION['panier'] as $item) {
        $count += (int) $item['quantite'];
    }
}

echo json_encode(['count' => $count]);
