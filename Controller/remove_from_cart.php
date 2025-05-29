<?php
require_once __DIR__ . '/../config/paths.php';
require_once MODEL_PATH . 'db.php';
session_start();

header('Content-Type: application/json');
// Vérifie si l'utilisateur est connecté ou en mode invité
try {
    if (isset($_SESSION['utilisateur']['id']) && isset($_POST['id_panier'])) {
        $id_panier = (int) $_POST['id_panier'];
        $user_id = $_SESSION['utilisateur']['id'];
// Vérification de l'existence du panier
        $stmt = $pdo->prepare("DELETE FROM panier WHERE id_panier = :id_panier AND id_utilisateur = :id_utilisateur");
        $stmt->execute([
            'id_panier' => $id_panier,
            'id_utilisateur' => $user_id
        ]);
// Si aucune ligne n'est affectée, l'ID du panier n'existe pas pour cet utilisateur
        echo json_encode(['success' => true]);
        exit;
    }
// Mode connecté : panier en base de données
    // Mode invité : panier en session
    elseif (isset($_POST['id_vol']) && isset($_SESSION['panier'][$_POST['id_vol']])) {
        unset($_SESSION['panier'][$_POST['id_vol']]);
        echo json_encode(['success' => true]);
        exit;
    }
// Si aucune condition n'est remplie, on renvoie une erreur
    echo json_encode(['success' => false, 'error' => 'Paramètres invalides.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
