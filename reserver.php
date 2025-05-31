<?php
session_start();
require_once 'includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header("Location: connexion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_trajet']) && isset($_POST['nb_places'])) {
    $id_trajet = filter_input(INPUT_POST, 'id_trajet', FILTER_VALIDATE_INT);
    $nb_places = filter_input(INPUT_POST, 'nb_places', FILTER_VALIDATE_INT);

    if ($id_trajet !== false && $nb_places !== false && $nb_places > 0) {
        try {
            $pdo->beginTransaction();

            // Vérifier les places disponibles
            $stmt_check = $pdo->prepare("SELECT Nombre_places FROM trajets WHERE ID_T = ? AND Statut = 'ouvert' FOR UPDATE");
            $stmt_check->execute([$id_trajet]);
            $trajet = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($trajet && $trajet['Nombre_places'] >= $nb_places) {
                // Insérer la réservation avec Date_reservation
                $stmt_insert = $pdo->prepare("INSERT INTO reservations (ID_T, Email_passager, Statut_reservation, Nombre_places_reservees, Date_reservation) VALUES (?, ?, 'en_attente', ?, NOW())");
                $stmt_insert->execute([$id_trajet, $_SESSION['user_email'], $nb_places]);

                // Récupérer l'ID de la réservation insérée
                $id_reservation = $pdo->lastInsertId();

                // Mettre à jour les places restantes
                $new_places = $trajet['Nombre_places'] - $nb_places;
                $stmt_update = $pdo->prepare("UPDATE trajets SET Nombre_places = ? WHERE ID_T = ?");
                $stmt_update->execute([$new_places, $id_trajet]);

                // Créer une notification pour le conducteur
                $stmt_conducteur = $pdo->prepare("SELECT Email FROM trajets WHERE ID_T = ?");
                $stmt_conducteur->execute([$id_trajet]);
                $conducteur = $stmt_conducteur->fetchColumn();
                if ($conducteur) {
                    $stmt_notify = $pdo->prepare("INSERT INTO notifications (Email_destinataire, Type_notification, Titre, Message, ID_Reservation, Date_creation) VALUES (?, 'nouvelle_reservation', 'Nouvelle réservation', ?, ?, NOW())");
                    $stmt_notify->execute([$conducteur, "Un passager a réservé votre trajet.", $id_reservation]);
                }

                $pdo->commit();
                $success = "Réservation effectuée avec succès !";
            } else {
                $pdo->rollBack();
                $error = "Plus assez de places disponibles ou trajet invalide.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Erreur lors de la réservation : " . $e->getMessage();
        }
    } else {
        $error = "Données de réservation invalides.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - serbiGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Variables CSS pour la cohérence des couleurs et tailles */
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

        /* Réinitialisation et styles globaux */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-light);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Header et Navigation */
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

        /* Réservation Section */
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

        section p a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        section p a:hover {
            text-decoration: underline;
            color: #2a6ab0;
        }

        /* Responsive Design */
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

            section p {
                font-size: 0.9rem;
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
                <li><a href="espace_passager.php">Espace Passager</a></li>
                <li><a href="deconnexion.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Réservation</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <p><a href="espace_passager.php"><i class="fas fa-arrow-left"></i> Retour aux trajets</a></p>
        </section>
    </main>
</body>
</html>