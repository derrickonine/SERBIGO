<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: index.php');
    exit;
}

$email = $_SESSION['user_email'];
$stmt = $pdo->prepare("SELECT m.*, r.ID_T, t.Ville_depart, t.Ville_arrivee 
                       FROM messages m 
                       JOIN reservations r ON m.ID_Reservation = r.ID_Reservation 
                       JOIN trajets t ON r.ID_T = t.ID_T 
                       WHERE m.Email_expediteur = ? OR m.Email_destinataire = ? 
                       ORDER BY m.Date_envoi DESC");
$stmt->execute([$email, $email]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_reservation = filter_input(INPUT_POST, 'id_reservation', FILTER_SANITIZE_NUMBER_INT);
    $contenu = filter_input(INPUT_POST, 'contenu', FILTER_SANITIZE_STRING);
    $destinataire = filter_input(INPUT_POST, 'destinataire', FILTER_SANITIZE_EMAIL);

    try {
        $stmt = $pdo->prepare("INSERT INTO messages (ID_Reservation, Email_expediteur, Email_destinataire, Contenu_message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_reservation, $email, $destinataire, $contenu]);
        // Ajouter une notification
        $stmt = $pdo->prepare("INSERT INTO notifications (Email_destinataire, Type_notification, Titre, Message, ID_Message) VALUES (?, 'nouveau_message', 'Nouveau message', 'Vous avez reçu un message concernant une réservation.', ?)");
        $stmt->execute([$destinataire, $pdo->lastInsertId()]);
        $success = "Message envoyé avec succès !";
    } catch (PDOException $e) {
        $error = "Erreur lors de l’envoi du message : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - serbiGO</title>
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
                <li><a href="messages.php" class="active">Messagerie</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li class="auth"><a href="deconnexion.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <section class="messagerie">
        <h2>Vos messages</h2>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <h3>Conversations</h3>
        <table>
            <tr>
                <th>Trajet</th>
                <th>Interlocuteur</th>
                <th>Message</th>
                <th>Date</th>
            </tr>
            <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?php echo htmlspecialchars($message['Ville_depart'] . ' à ' . $message['Ville_arrivee']); ?></td>
                    <td><?php echo htmlspecialchars($message['Email_expediteur'] === $email ? $message['Email_destinataire'] : $message['Email_expediteur']); ?></td>
                    <td><?php echo htmlspecialchars($message['Contenu_message']); ?></td>
                    <td><?php echo $message['Date_envoi']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <h3>Envoyer un message</h3>
        <form action="messages.php" method="POST">
            <div class="input-group">
                <label for="id_reservation"><i class="fas fa-ticket-alt"></i> Réservation :</label>
                <select id="id_reservation" name="id_reservation" required>
                    <?php
                    $stmt = $pdo->prepare("SELECT r.ID_Reservation, t.Ville_depart, t.Ville_arrivee FROM reservations r JOIN trajets t ON r.ID_T = t.ID_T WHERE r.Email_passager = ? OR t.Email = ?");
                    $stmt->execute([$email, $email]);
                    while ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$res['ID_Reservation']}'>{$res['Ville_depart']} à {$res['Ville_arrivee']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="input-group">
                <label for="destinataire"><i class="fas fa-user"></i> Destinataire :</label>
                <input type="email" id="destinataire" name="destinataire" required>
            </div>
            <div class="input-group">
                <label for="contenu"><i class="fas fa-comment"></i> Message :</label>
                <textarea id="contenu" name="contenu" required></textarea>
            </div>
            <button type="submit" class="button primary"><i class="fas fa-paper-plane"></i> Envoyer</button>
        </form>
    </section>
</body>
</html>