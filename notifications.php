<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: index.php');
    exit;
}

$email = $_SESSION['user_email'];
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE Email_destinataire = ? ORDER BY Date_creation DESC");
$stmt->execute([$email]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - serbiGO</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <img src="serbigo.jpg" height="150" width="150" alt="Logo serbiGO"/>
            </div>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="<?php echo $_SESSION['user_role'] === 'passager' ? 'espace_passager.php' : 'espace_conducteur.php'; ?>">Espace <?php echo ucfirst($_SESSION['user_role']); ?></a></li>
                <li><a href="profil.php">Profil</a></li>
                <li><a href="historique.php">Historique des trajets</a></li>
                <li><a href="messages.php">Messagerie</a></li>
                <li><a href="notifications.php" class="active">Notifications</a></li>
                <li class="auth"><a href="deconnexion.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <section class="notifications">
        <h2>Vos notifications</h2>
        <table>
            <tr>
                <th>Type</th>
                <th>Titre</th>
                <th>Message</th>
                <th>Date</th>
            </tr>
            <?php foreach ($notifications as $notification): ?>
                <tr>
                    <td><?php echo htmlspecialchars($notification['Type_notification']); ?></td>
                    <td><?php echo htmlspecialchars($notification['Titre']); ?></td>
                    <td><?php echo htmlspecialchars($notification['Message']); ?></td>
                    <td><?php echo $notification['Date_creation']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>
</body>
</html>