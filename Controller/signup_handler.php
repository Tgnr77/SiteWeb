<?php
// Inclure le fichier de connexion à la base de données
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    // Vérifier si l'email existe déjà
    $checkEmail = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
    $checkEmail->execute(['email' => $email]);

    if ($checkEmail->rowCount() > 0) {
        echo "Cet email est déjà utilisé.";
        exit;
    }

    // Hacher le mot de passe
    $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_BCRYPT);

    // Insérer l'utilisateur dans la base de données
    $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) VALUES (:nom, :prenom, :email, :mot_de_passe)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => $mot_de_passe_hache,
        ]);
        echo "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>
