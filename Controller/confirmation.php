<?php
session_start();
$messages = $_SESSION['confirmation_messages'] ?? [];
unset($_SESSION['confirmation_messages']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de réservation</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Confirmation</h1>
    </header>
    <main>
        <section class="booking">
            <?php if (empty($messages)): ?>
                <p>Aucune information de réservation trouvée.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($messages as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 20px;">
                <a href="account.php" class="button">Retour à mon compte</a>
                <a href="vols.html" class="button">Réserver un autre vol</a>
            </div>
        </section>
    </main>
</body>
</html>
