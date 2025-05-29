<?php
require_once __DIR__ . '/../Config/paths.php';
require_once MODEL_PATH . 'db.php';
require_once __DIR__ . '/../Lib/fpdf.php';
session_start();

if (!isset($_SESSION['utilisateur']['id'])) {
    die("Accès non autorisé.");
}

$id_reservation = $_GET['id_reservation'] ?? 0;
$user_id = $_SESSION['utilisateur']['id'];

// Récupération de la réservation
$stmt = $pdo->prepare("
    SELECT r.*, v.origine, v.destination, v.date_depart
    FROM reservations r
    JOIN vols v ON r.id_vol = v.id_vol
    WHERE r.id_reservation = ? AND r.id_utilisateur = ?
");
$stmt->execute([$id_reservation, $user_id]);
$resa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resa) {
    die("Réservation introuvable.");
}

// Configuration PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Logo
$pdf->Image('../View/zenith.jpg', 10, 10, 40); // adapte le chemin si nécessaire

// QR Code image statique (ex: générée depuis un outil en ligne)
//$pdf->Image('../assets/qrcode.png', 160, 10, 30); // optionnel

$pdf->Ln(30);
$pdf->Cell(0, 10, utf8_decode("Facture - Zenith Airlines"), 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);

// Numéro de facture
$numero_facture = date('Y') . '-' . str_pad($id_reservation, 4, '0', STR_PAD_LEFT);

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode("Facture n° : $numero_facture"), 0, 1, 'R');
$pdf->Ln(5);


// Infos client
$pdf->Cell(0, 10, utf8_decode('Nom : ' . $_SESSION['utilisateur']['prenom'] . ' ' . $_SESSION['utilisateur']['nom']), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Email : ' . $_SESSION['utilisateur']['email']), 0, 1);
$pdf->Ln(5);

// Vol
$pdf->Cell(0, 10, utf8_decode("Vol : {$resa['origine']} -> {$resa['destination']}"), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Départ : " . date('d/m/Y H:i', strtotime($resa['date_depart']))), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Siège : {$resa['siege']}"), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Formule : {$resa['formule']}"), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Bagage cabine : {$resa['poids_cabine']} kg"), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Bagage soute : {$resa['poids_soute']} kg"), 0, 1);
$pdf->Ln(5);

// Total
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode("Montant total : " . number_format($resa['prix_total'], 2) . " EUR"), 0, 1);

$pdf->Output('I', 'facture_zenith_' . $id_reservation . '.pdf');
exit;
