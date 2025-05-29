<?php
require_once __DIR__ . '/../Config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: login.html');
    exit;
}

$vols = $_SESSION['donnees_vols'] ?? [];
unset($_SESSION['donnees_vols']);

if (empty($vols)) {
    die("Aucune donnée reçue (vols manquants).");
}

$numero = $_POST['numero'] ?? '';
$cvv = $_POST['cvv'] ?? '';
$expiration = $_POST['expiration'] ?? '';
$nom = trim($_POST['nom'] ?? '');

if (empty($nom) || strlen($numero) !== 16 || strlen($cvv) !== 3) {
    die("Paiement refusé. Vérifiez les informations saisies.");
}

$user_id = $_SESSION['utilisateur']['id'];
$vols = $_SESSION['donnees_vols'] ?? [];
unset($_SESSION['donnees_vols']);
$messages = [];

foreach ($vols as $vol_data) {
    $id_vol = (int) ($vol_data['id_vol'] ?? 0);
    $formule = $vol_data['formule'] ?? 'eco';
    $poids_cabine = (int) ($vol_data['poids_cabine'] ?? 0);
    $poids_soute = (int) ($vol_data['poids_soute'] ?? 0);
    $siege = trim($vol_data['siege'] ?? '');

    if ($id_vol <= 0) {
        $messages[] = ['text' => "ID de vol invalide."];
        continue;
    }

    // Vérifier que le vol existe
    $stmt = $pdo->prepare("SELECT prix FROM vols WHERE id_vol = ?");
    $stmt->execute([$id_vol]);
    $prix_base = $stmt->fetchColumn();

    if ($prix_base === false) {
        $messages[] = ['text' => "Le vol $id_vol n'existe pas."];
        continue;
    }

    // Vérifie si le siège est déjà pris
    $check = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE id_vol = ? AND siege = ?");
    $check->execute([$id_vol, $siege]);
    if ($check->fetchColumn() > 0) {
        $messages[] = ['text' => "Le siège $siege pour le vol $id_vol est déjà réservé."];
        continue;
    }

    // Calcul du prix total
    $prix_total = $prix_base;
    if ($formule === 'premium') $prix_total += 50;
    $prix_total += max(0, $poids_soute * 10);

    // Paiement simulé
    $paiement_ok = true;

    if ($paiement_ok) {
        $insert = $pdo->prepare("
            INSERT INTO reservations (id_utilisateur, id_vol, formule, poids_cabine, poids_soute, siege, prix_total)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->execute([$user_id, $id_vol, $formule, $poids_cabine, $poids_soute, $siege, $prix_total]);

        // Récupération de l'ID de la réservation
        $id_reservation = $pdo->lastInsertId();

        // Suppression du panier
        $delete = $pdo->prepare("DELETE FROM panier WHERE id_utilisateur = ? AND id_vol = ?");
        $delete->execute([$user_id, $id_vol]);

        if ($delete->rowCount() === 0) {
            error_log("⚠️ Aucun vol supprimé du panier : user $user_id, vol $id_vol");
        }

        // Message enrichi
        $messages[] = [
            'text' => "Réservation confirmée pour le vol $id_vol (siège $siege).",
            'id_reservation' => $id_reservation
        ];
    } else {
        $messages[] = ['text' => "Échec du paiement pour le vol $id_vol."];
    }
}

// Stocker les messages pour confirmation.php
$_SESSION['confirmation_messages'] = $messages;

header('Location: confirmation.php');
exit;
