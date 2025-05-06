<?php
// Inclure le fichier de connexion à la base de données
include 'db.php';

try {
    // Requête pour récupérer tous les vols triés par date de départ
    $query = $pdo->query("SELECT * FROM vols ORDER BY date_depart ASC");

    // Récupérer les résultats sous forme de tableau associatif
    $vols = $query->fetchAll(PDO::FETCH_ASSOC);

    // Retourner les résultats en JSON
    header('Content-Type: application/json');
    echo json_encode($vols);
} catch (PDOException $e) {
    // En cas d'erreur, retourner un message d'erreur au format JSON
    header('Content-Type: application/json', true, 500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
