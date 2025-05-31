<?php
session_start();
require_once 'includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header("Location: connexion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ville_depart = filter_input(INPUT_POST, 'ville_depart', FILTER_SANITIZE_SPECIAL_CHARS);
    $ville_arrivee = filter_input(INPUT_POST, 'ville_arrivee', FILTER_SANITIZE_SPECIAL_CHARS);
    $date_trajet = filter_input(INPUT_POST, 'date_trajet', FILTER_DEFAULT);
    $heure_depart = filter_input(INPUT_POST, 'heure_depart', FILTER_DEFAULT);
    $tarif = filter_input(INPUT_POST, 'tarif', FILTER_VALIDATE_FLOAT);
    $nb_places = filter_input(INPUT_POST, 'nb_places', FILTER_VALIDATE_INT);

    if ($nb_places === false || $nb_places <= 0) {
        $nb_places = 1; // Valeur par défaut si invalide
    }

    // Validation supplémentaire
    if (!$ville_depart || !$ville_arrivee || !$date_trajet || !$heure_depart || $tarif === false || $tarif <= 0) {
        $error = "Veuillez remplir correctement tous les champs.";
    } else {
        try {
            // Ajouter explicitement Date_creation avec NOW()
            $stmt = $pdo->prepare("INSERT INTO trajets (Email, Ville_depart, Ville_arrivee, Date_trajet, Heure_depart, Tarif, Nombre_places, Statut, Date_creation) VALUES (?, ?, ?, ?, ?, ?, ?, 'ouvert', NOW())");
            $stmt->execute([$_SESSION['user_email'], $ville_depart, $ville_arrivee, $date_trajet, $heure_depart, $tarif, $nb_places]);
            $success = "Trajet ajouté avec succès !";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Conducteur - serbiGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath d='M10 10 C 20 40, 40 20, 50 50 C 60 80, 80 60, 90 90' stroke='%2327ae60' stroke-width='0.5' fill='none'/%3E%3Cpath d='M20 90 C 40 60, 60 80, 70 50 C 80 20, 90 40, 90 10' stroke='%233686d6' stroke-width='0.5' fill='none'/%3E%3C/svg%3E"), var(--background-light);
    background-size: 200px 200px;
    background-repeat: repeat;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: var(--text-dark);
    line-height: 1.6;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    overflow-x: hidden;
}
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

        /* Proposer Trajet Section */
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

        form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .input-group {
            display: flex;
            flex-direction: column;
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

        button {
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
        }

        button:hover {
            background-color: #219653;
            transform: translateY(-2px);
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

            .input-group input {
                font-size: 0.9rem;
                padding: 0.5rem;
            }

            button {
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
                <li><a href="espace_conducteur.php" class="active">Espace Conducteur</a></li>
                <li><a href="profil.php">Profil</a></li>
                <li><a href="historique_des_trajets.php">Historique des trajets</a></li>
                <li><a href="messages.php">Messagerie</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="deconnexion.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Proposer un trajet</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="input-group">
                    <label for="ville_depart"><i class="fas fa-map-marker-alt"></i> Ville de départ :</label>
                    <input type="text" id="ville_depart" name="ville_depart" required>
                </div>
                <div class="input-group">
                    <label for="ville_arrivee"><i class="fas fa-map-marker-alt"></i> Ville d'arrivée :</label>
                    <input type="text" id="ville_arrivee" name="ville_arrivee" required>
                </div>
                <div class="input-group">
                    <label for="date_trajet"><i class="fas fa-calendar-alt"></i> Date du trajet :</label>
                    <input type="date" id="date_trajet" name="date_trajet" required>
                </div>
                <div class="input-group">
                    <label for="heure_depart"><i class="fas fa-clock"></i> Heure de départ :</label>
                    <input type="time" id="heure_depart" name="heure_depart" required>
                </div>
                <div class="input-group">
                    <label for="tarif"><i class="fas fa-money-bill"></i> Tarif :</label>
                    <input type="number" step="0.01" id="tarif" name="tarif" required>
                </div>
                <div class="input-group">
                    <label for="nb_places"><i class="fas fa-users"></i> Nombre de places :</label>
                    <input type="number" id="nb_places" name="nb_places" value="1" required>
                </div>
                <button type="submit"><i class="fas fa-car"></i> Proposer</button>
            </form>
        </section>
    </main>
</body>
</html>