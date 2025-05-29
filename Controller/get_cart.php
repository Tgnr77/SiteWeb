<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';

session_start();
// Vérifie si l'utilisateur est connecté, sinon renvoie une erreur 401
if (!isset($_SESSION['id_utilisateur'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Utilisateur non connecté.']);
    exit;
}

try {
    $id_utilisateur = $_SESSION['id_utilisateur'];
// Préparation de la requête pour récupérer les articles du panier de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT p.id_panier, v.origine, v.destination, v.date_depart, v.prix, p.quantite
        FROM panier p
        JOIN vols v ON p.id_vol = v.id_vol
        WHERE p.id_utilisateur = :id_utilisateur
    ");
    $stmt->execute([':id_utilisateur' => $id_utilisateur]);
    $panier = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Si le panier est vide, on renvoie un tableau vide
    echo json_encode($panier);
} catch (PDOException $e) {
    http_response_code(500);
    // En cas d'erreur, on renvoie un message d'erreur
    echo json_encode(['error' => $e->getMessage()]);
}
