<?php
require_once __DIR__ . '/../Config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: login.html');
    exit;
}

if (!isset($_POST['vols']) || !is_array($_POST['vols'])) {
    die("Aucune donnée reçue.");
}

$user_id = $_SESSION['utilisateur']['id'];
$vols = $_POST['vols'];
$messages = [];

foreach ($vols as $vol_data) {
    $id_vol = (int) ($vol_data['id_vol'] ?? 0);
    $formule = $vol_data['formule'] ?? 'eco';
    $poids_cabine = (int) ($vol_data['poids_cabine'] ?? 0);
    $poids_soute = (int) ($vol_data['poids_soute'] ?? 0);
    $siege = trim($vol_data['siege'] ?? '');

    if ($id_vol <= 0) {
        $messages[] = "ID de vol invalide.";
        continue;
    }

    // Vérifier que le vol existe
    $stmt = $pdo->prepare("SELECT prix FROM vols WHERE id_vol = ?");
    $stmt->execute([$id_vol]);
    $prix_base = $stmt->fetchColumn();

    if ($prix_base === false) {
        $messages[] = "Le vol $id_vol n'existe pas.";
        continue;
    }

    // Vérifie si le siège est déjà pris
    $check = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE id_vol = ? AND siege = ?");
    $check->execute([$id_vol, $siege]);
    if ($check->fetchColumn() > 0) {
        $messages[] = "Le siège $siege pour le vol $id_vol est déjà réservé.";
        continue;
    }

    // Calcul du prix total
    $prix_total = $prix_base;
    if ($formule === 'premium') $prix_total += 50;
    $prix_total += max(0, $poids_soute * 10);

    // Simulation de paiement (toujours réussi ici)
    $paiement_ok = true;

    if ($paiement_ok) {
        $insert = $pdo->prepare("
            INSERT INTO reservations (id_utilisateur, id_vol, formule, poids_cabine, poids_soute, siege, prix_total)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->execute([$user_id, $id_vol, $formule, $poids_cabine, $poids_soute, $siege, $prix_total]);

        $messages[] = "Réservation confirmée pour le vol $id_vol (siège $siege).";
    } else {
        $messages[] = "Échec du paiement pour le vol $id_vol.";
    }
}

// Stocker les messages pour confirmation.php
$_SESSION['confirmation_messages'] = $messages;

header('Location: confirmation.php');
exit;
