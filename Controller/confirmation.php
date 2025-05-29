<?php
require_once __DIR__ . '/../Config/paths.php';
require_once MODEL_PATH . 'db.php';
require_once LIB_PATH . 'fpdf.php'; 
session_start();

if (!isset($_SESSION['utilisateur']['id'])) {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['utilisateur']['id'];

$vols         = $_POST['vols'] ?? [];
$formules     = $_POST['formule'] ?? [];
$poids_cabine = $_POST['poids_cabine'] ?? [];
$poids_soute  = $_POST['poids_soute'] ?? [];
$sieges       = $_POST['siege'] ?? [];
$prix_base    = $_POST['prix_base'] ?? [];

if (empty($vols)) {
    die("Aucun vol sélectionné pour le paiement.");
}

$messages = [];

foreach ($vols as $id_vol) {
    if (!is_numeric($id_vol)) continue;

    $formule = $formules[$id_vol] ?? 'eco';
    $cabine  = (int)($poids_cabine[$id_vol] ?? 0);
    $soute   = (int)($poids_soute[$id_vol] ?? 0);
    $siege   = $sieges[$id_vol] ?? null;
    $prix    = (float)($prix_base[$id_vol] ?? 0);

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
        $siege = null;
    }

    // Vérifier que le siège n’est pas déjà réservé
    if ($siege) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE id_vol = ? AND siege = ?");
        $stmt->execute([$id_vol, $siege]);
        if ($stmt->fetchColumn() > 0) {
            $messages[] = "❌ Le siège $siege pour le vol $id_vol est déjà réservé.";
            continue;
        }
    }

    // Insertion en base
    $stmt = $pdo->prepare("
        INSERT INTO reservations (
            id_utilisateur, id_vol, formule, poids_cabine, poids_soute, siege, prix_total, statut_paiement, date_reservation
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'validé', NOW())
    ");
    $stmt->execute([$user_id, $id_vol, $formule, $cabine, $soute, $siege, $prix]);

    // Génération de la facture PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Facture de Réservation', 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'ID Utilisateur : ' . $user_id, 0, 1);
    $pdf->Cell(0, 10, 'Vol ID : ' . $id_vol, 0, 1);
    $pdf->Cell(0, 10, 'Formule : ' . $formule, 0, 1);
    $pdf->Cell(0, 10, 'Bagage cabine : ' . $cabine . ' kg', 0, 1);
    if ($formule === 'premium') {
        $pdf->Cell(0, 10, 'Bagage soute : ' . $soute . ' kg', 0, 1);
        $pdf->Cell(0, 10, 'Siège : ' . ($siege ?: 'Aucun'), 0, 1);
    }
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Montant total : ' . number_format($prix, 2) . ' €', 0, 1);

    // Enregistrement du fichier
    $filename = __DIR__ . '/../Factures/facture_vol_' . $id_vol . '_user_' . $user_id . '.pdf';
    $pdf->Output('F', $filename);

    $messages[] = "✅ Vol $id_vol réservé. <a href='../Factures/" . basename($filename) . "' target='_blank'>Télécharger la facture</a>";
}

// Stockage pour confirmation.php
$_SESSION['confirmation_messages'] = $messages;
header('Location: ../confirmation.php');
exit;
