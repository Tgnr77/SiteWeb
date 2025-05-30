<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['utilisateur']['id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = $_SESSION['utilisateur']['id'];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM panier WHERE id_utilisateur = ?");
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn();

    echo json_encode(['count' => (int)$count]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0, 'error' => $e->getMessage()]);
}
