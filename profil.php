<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_email'])) {
    header("Location: connexion.php");
    exit();
}

$email = isset($_GET['email']) ? filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL) : $_SESSION['user_email'];
$current_user_email = $_SESSION['user_email'];

// Vérifier si l'utilisateur a le droit de voir ce profil
$can_view_profile = ($email === $current_user_email); // L'utilisateur peut toujours voir son propre profil
if (!$can_view_profile) {
    $stmt_check = $pdo->prepare("SELECT r.ID_Reservation 
                                 FROM reservations r 
                                 JOIN trajets t ON r.ID_T = t.ID_T 
                                 WHERE (r.Email_passager = ? AND t.Email = ?) 
                                    OR (r.Email_passager = ? AND t.Email = ?)");
    $stmt_check->execute([$current_user_email, $email, $email, $current_user_email]);
    $can_view_profile = $stmt_check->fetch(PDO::FETCH_ASSOC);
}

if (!$can_view_profile) {
    die("Accès non autorisé : vous devez avoir une réservation avec cet utilisateur.");
}

// Récupérer les informations de l'utilisateur
$stmt_user = $pdo->prepare("SELECT Nom, Prenom, Role, Telephone, Date_inscription, photo_profil 
                            FROM utilisateurs 
                            WHERE Email = ?");
$stmt_user->execute([$email]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur non trouvé.");
}

// Calculer la moyenne des notes
$stmt_avg = $pdo->prepare("SELECT AVG(note) as avg_note 
                           FROM avis_notes 
                           WHERE Email_cible = ? AND Statut = 'publie'");
$stmt_avg->execute([$email]);
$avg_note = $stmt_avg->fetch(PDO::FETCH_ASSOC)['avg_note'] ?? 0;

// Récupérer l'historique des trajets
$stmt_history = $pdo->prepare("SELECT t.Ville_depart, t.Ville_arrivee, t.Date_trajet, h.Role 
                               FROM historique_trajets h 
                               JOIN trajets t ON h.ID_T = t.ID_T 
                               WHERE h.Email = ? 
                               ORDER BY t.Date_trajet DESC");
$stmt_history->execute([$email]);
$history = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

// Gérer le téléversement de la photo de profil (uniquement pour l'utilisateur connecté)
if ($email === $current_user_email && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo_profil'])) {
    $upload_dir = 'uploads/';
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB

    $file = $_FILES['photo_profil'];
    if ($file['error'] === UPLOAD_ERR_OK && in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $destination = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $stmt_update = $pdo->prepare("UPDATE utilisateurs SET photo_profil = ? WHERE Email = ?");
            $stmt_update->execute([$destination, $current_user_email]);
            $user['photo_profil'] = $destination;
            $success = "Photo de profil mise à jour avec succès !";
        } else {
            $error = "Erreur lors du téléversement de la photo.";
        }
    } else {
        $error = "Fichier invalide ou trop volumineux (max 2MB, formats : JPEG, PNG, GIF).";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - serbiGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #27ae60;
            --secondary-color: #3686d6;
            --accent-color: #e037ad;
            --background-light: #f4f7f6;
            --background-white: #fff;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --border-color: #d0d8de;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-light);
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath d='M10 10 C 20 40, 40 20, 50 50 C 60 80, 80 60, 90 90' stroke='%2327ae60' stroke-width='0.5' fill='none'/%3E%3Cpath d='M20 90 C 40 60, 60 80, 70 50 C 80 20, 90 40, 90 10' stroke='%233686d6' stroke-width='0.5' fill='none'/%3E%3C/svg%3E");
            background-size: 200px 200px;
            background-repeat: repeat;
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: var(--background-white);
            box-shadow: var(--shadow);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            text-align: center;
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo img {
            height: 100px;
            width: auto;
            transition: var(--transition);
        }

        .logo img:hover {
            transform: scale(1.05);
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        nav ul li a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        nav ul li a:hover,
        nav ul li a.active {
            background-color: var(--primary-color);
            color: var(--background-white);
        }

        section {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: var(--background-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
        }

        section h2 {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 2px solid var(--border-color);
        }

        .error {
            color: #e74c3c;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .success {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .input-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .input-group label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(39, 174, 96, 0.3);
        }

        button, .button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--primary-color);
            color: var(--background-white);
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: var(--shadow);
            transition: var(--transition);
            text-decoration: none;
        }

        button:hover, .button:hover {
            background-color: #219653;
            transform: translateY(-2px);
        }

        ul {
            list-style: none;
            margin-top: 1rem;
        }

        ul li {
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                gap: 1rem;
            }

            nav ul {
                flex-direction: column;
                gap: 0.5rem;
            }

            section {
                margin: 1rem;
                padding: 1.5rem;
            }

            section h2 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            section h2 {
                font-size: 1.5rem;
            }

            .profile-pic {
                width: 100px;
                height: 100px;
            }

            button, .button {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
            }
        }
    </style>
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
                <li><a href="profil.php" class="active">Profil</a></li>
                <li><a href="historique.php">Historique des trajets</a></li>
                <li><a href="messages.php">Messagerie</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="deconnexion.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Profil de <?php echo htmlspecialchars($user['Prenom'] . ' ' . $user['Nom']); ?></h2>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <img src="<?php echo $user['photo_profil'] ? htmlspecialchars($user['photo_profil']) : 'default_profile.jpg'; ?>" alt="Photo de profil" class="profile-pic">
            <p><strong>Rôle :</strong> <?php echo ucfirst($user['Role']); ?></p>
            <p><strong>Téléphone :</strong> <?php echo $user['Telephone'] ? htmlspecialchars($user['Telephone']) : 'Non renseigné'; ?></p>
            <p><strong>Date d'inscription :</strong> <?php echo $user['Date_inscription']; ?></p>
            <p><strong>Note moyenne :</strong> <?php echo number_format($avg_note, 1); ?>/5</p>

            <?php if ($email === $current_user_email): ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="input-group">
                        <label for="photo_profil"><i class="fas fa-camera"></i> Changer la photo de profil :</label>
                        <input type="file" id="photo_profil" name="photo_profil" accept="image/jpeg,image/png,image/gif">
                    </div>
                    <button type="submit"><i class="fas fa-upload"></i> Mettre à jour</button>
                </form>
            <?php endif; ?>

            <h3>Historique des trajets</h3>
            <ul>
                <?php foreach ($history as $trajet): ?>
                    <li>
                        <?php echo htmlspecialchars($trajet['Role']); ?> : 
                        <?php echo htmlspecialchars($trajet['Ville_depart'] . ' → ' . $trajet['Ville_arrivee']); ?> 
                        (<?php echo $trajet['Date_trajet']; ?>)
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if ($can_view_profile && $email !== $current_user_email): ?>
                <?php
                // Récupérer les réservations communes pour permettre l'envoi de messages
                $stmt_reservations = $pdo->prepare("SELECT r.ID_Reservation, t.Ville_depart, t.Ville_arrivee 
                                                    FROM reservations r 
                                                    JOIN trajets t ON r.ID_T = t.ID_T 
                                                    WHERE (r.Email_passager = ? AND t.Email = ?) 
                                                       OR (r.Email_passager = ? AND t.Email = ?)");
                $stmt_reservations->execute([$current_user_email, $email, $email, $current_user_email]);
                $reservations = $stmt_reservations->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <?php if ($reservations): ?>
                    <h3>Envoyer un message</h3>
                    <form action="messages.php" method="POST">
                        <div class="input-group">
                            <label for="id_reservation"><i class="fas fa-ticket-alt"></i> Réservation :</label>
                            <select id="id_reservation" name="id_reservation" required>
                                <?php foreach ($reservations as $res): ?>
                                    <option value="<?php echo $res['ID_Reservation']; ?>">
                                        <?php echo htmlspecialchars($res['Ville_depart'] . ' à ' . $res['Ville_arrivee']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" name="destinataire" value="<?php echo htmlspecialchars($email); ?>">
                        <div class="input-group">
                            <label for="contenu"><i class="fas fa-comment"></i> Message :</label>
                            <textarea id="contenu" name="contenu" required></textarea>
                        </div>
                        <button type="submit"><i class="fas fa-paper-plane"></i> Envoyer</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>