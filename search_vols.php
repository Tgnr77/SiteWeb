<?php
include 'db.php';

try {
    // Récupérer les paramètres et supprimer les espaces inutiles
    $origine = isset($_GET['origine']) ? htmlspecialchars(trim($_GET['origine'])) : '';
    $destination = isset($_GET['destination']) ? htmlspecialchars(trim($_GET['destination'])) : '';
    $date_depart = isset($_GET['date_depart']) ? htmlspecialchars(trim($_GET['date_depart'])) : '';

    // Construire la requête SQL
    $sql = "SELECT * FROM vols WHERE origine LIKE :origine AND destination LIKE :destination";
    $params = [
        ':origine' => '%' . $origine . '%',
        ':destination' => '%' . $destination . '%',
    ];

    // Ajouter une condition pour la date uniquement si elle est spécifiée
    if (!empty($date_depart)) {
        $sql .= " AND DATE(date_depart) = :date_depart";
        $params[':date_depart'] = $date_depart;
    }

    // Ajouter une limite pour éviter des réponses trop volumineuses
    $sql .= " LIMIT 50";

    // Préparer et exécuter la requête
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Récupérer les résultats
    $vols = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retourner les résultats en JSON
    header('Content-Type: application/json');
    if (!empty($vols)) {
        echo json_encode($vols);
    } else {
        echo json_encode(['message' => 'Aucun vol trouvé pour ces critères.']);
    }
} catch (PDOException $e) {
    // Gérer les erreurs
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => $e->getMessage()]);
}
