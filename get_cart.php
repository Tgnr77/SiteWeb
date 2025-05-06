<?php
include 'db.php';
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Utilisateur non connecté.']);
    exit;
}

try {
    $id_utilisateur = $_SESSION['id_utilisateur'];

    $stmt = $pdo->prepare("
        SELECT p.id_panier, v.origine, v.destination, v.date_depart, v.prix, p.quantite
        FROM panier p
        JOIN vols v ON p.id_vol = v.id_vol
        WHERE p.id_utilisateur = :id_utilisateur
    ");
    $stmt->execute([':id_utilisateur' => $id_utilisateur]);
    $panier = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($panier);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
