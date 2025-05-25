<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: account.php');
    exit;
}

$user_id = $_SESSION['utilisateur']['id'];

// Récupération du panier
$stmt = $pdo->prepare("
    SELECT p.id_panier, p.id_vol, p.quantite
    FROM panier p
    WHERE p.id_utilisateur = ?
");
$stmt->execute([$user_id]);
$panier = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Insertion dans les réservations
foreach ($panier as $item) {
    for ($i = 0; $i < $item['quantite']; $i++) {
        $stmtInsert = $pdo->prepare("
            INSERT INTO reservations (id_utilisateur, id_vol, date_reservation)
            VALUES (:id_utilisateur, :id_vol, NOW())
        ");
        $stmtInsert->execute([
            'id_utilisateur' => $user_id,
            'id_vol' => $item['id_vol']
        ]);
    }
}

// Suppression du panier
$stmt = $pdo->prepare("DELETE FROM panier WHERE id_utilisateur = ?");
$stmt->execute([$user_id]);

header('Location: account.php');
exit;
