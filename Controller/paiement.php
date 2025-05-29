<?php
require_once __DIR__ . '/../Config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['utilisateur']['id'];

$vols = $_POST['vols'] ?? [];
$formules = $_POST['formule'] ?? [];
$poids_cabine = $_POST['poids_cabine'] ?? [];
$poids_soute = $_POST['poids_soute'] ?? [];
$sieges = $_POST['siege'] ?? [];
$prix_base = $_POST['prix_base'] ?? [];

if (empty($vols)) {
    die("Aucun vol sélectionné pour le paiement.");
}

$messages = [];

foreach ($vols as $id_vol) {
    if (!is_numeric($id_vol)) continue;

    $formule = $formules[$id_vol] ?? 'eco';
    $cabine = (int)($poids_cabine[$id_vol] ?? 0);
    $soute = (int)($poids_soute[$id_vol] ?? 0);
    $siege = $sieges[$id_vol] ?? null;
    $prix = (float)($prix_base[$id_vol] ?? 0);

    // Calcul du prix final
    if ($formule === 'premium') {
        $prix += 70;
        if ($soute > 25) {
            $prix += ceil(($soute - 25) / 5) * 40;
        }
    } else {
        if ($cabine > 10) {
            $prix += min((($cabine - 10) / 5) * 30, 20);
        }
        $siege = null; // Pas de sélection de siège en eco
    }

    // Vérifier si le siège est déjà réservé
    if ($siege) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE id_vol = ? AND siege = ?");
        $stmt->execute([$id_vol, $siege]);
        if ($stmt->fetchColumn() > 0) {
            $messages[] = "Le siège $siege pour le vol ID $id_vol est déjà réservé.";
            continue;
        }
    }

    // Insérer la réservation
    $stmt = $pdo->prepare("INSERT INTO reservations (id_utilisateur, id_vol, formule, poids_cabine, poids_soute, siege, prix_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $id_vol, $formule, $cabine, $soute, $siege, $prix]);

    $messages[] = "Vol ID $id_vol réservé avec succès. Prix total : $prix €.";
}

$_SESSION['confirmation_messages'] = $messages;

header('Location: ../confirmation.php');
exit;
