<?php
// Inclure le fichier de connexion à la base de données
include 'db.php';

// Vérifier que la méthode de requête est POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données envoyées depuis le formulaire ou une requête AJAX
    $origine = $_POST['origine']; // Ville de départ
    $destination = $_POST['destination']; // Ville d'arrivée
    $date_depart = $_POST['date_depart']; // Date et heure du départ
    $date_arrivee = $_POST['date_arrivee']; // Date et heure de l'arrivée
    $prix = $_POST['prix']; // Prix du billet
    $statut = $_POST['statut']; // Statut du vol
    $compagnie = $_POST['compagnie']; // Nom de la compagnie

    try {
        // Préparer une requête SQL pour insérer un nouveau vol dans la base de données
        $stmt = $pdo->prepare("
            INSERT INTO vols (origine, destination, date_depart, date_arrivee, prix, statut, compagnie)
            VALUES (:origine, :destination, :date_depart, :date_arrivee, :prix, :statut, :compagnie)
        ");

        // Exécuter la requête avec les données fournies
        $stmt->execute([
            ':origine' => $origine,
            ':destination' => $destination,
            ':date_depart' => $date_depart,
            ':date_arrivee' => $date_arrivee,
            ':prix' => $prix,
            ':statut' => $statut,
            ':compagnie' => $compagnie,
        ]);

        // Retourner un message de succès au format JSON
        echo json_encode(["success" => "Vol ajouté avec succès"]);
    } catch (PDOException $e) {
        // En cas d'erreur, retourner un message d'erreur en JSON
        echo json_encode(["error" => $e->getMessage()]);
    }
}
?>
