<?php
session_start();
require_once 'includes/config.php';

$trajets = [];
$alternatifs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ville_depart = filter_input(INPUT_POST, 'ville_depart', FILTER_SANITIZE_SPECIAL_CHARS);
    $ville_arrivee = filter_input(INPUT_POST, 'ville_arrivee', FILTER_SANITIZE_SPECIAL_CHARS);
    $date_trajet = filter_input(INPUT_POST, 'date_trajet', FILTER_DEFAULT);
    $nb_places = filter_input(INPUT_POST, 'nb_places', FILTER_VALIDATE_INT);

    if ($nb_places === false || $nb_places <= 0) {
        $nb_places = 1; // Valeur par défaut si invalide
    }

    // Requête pour les trajets exacts
    $query = "SELECT * FROM trajets WHERE Ville_depart LIKE ? AND Ville_arrivee LIKE ? AND Date_trajet = ? AND Statut = 'ouvert' AND Nombre_places >= ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(["%$ville_depart%", "%$ville_arrivee%", $date_trajet, $nb_places]);
    $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si aucun trajet trouvé, proposer des alternatives (même villes, dates proches)
    if (empty($trajets)) {
        $query_alt = "SELECT * FROM trajets WHERE (Ville_depart LIKE ? OR Ville_arrivee LIKE ?) AND Statut = 'ouvert' AND Nombre_places >= ? AND Date_trajet BETWEEN DATE_SUB(?, INTERVAL 7 DAY) AND DATE_ADD(?, INTERVAL 7 DAY)";
        $stmt_alt = $pdo->prepare($query_alt);
        $stmt_alt->execute(["%$ville_depart%", "%$ville_arrivee%", $nb_places, $date_trajet, $date_trajet]);
        $alternatifs = $stmt_alt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Passager - serbiGO</title>
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

        /* Recherche Trajet Section */
        section {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: var(--background-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        section h2 {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        section h3 {
            font-size: 1.5rem;
            color: var(--text-dark);
            margin: 1.5rem 0 1rem;
        }

        .error {
            color: #e74c3c;
            margin-bottom: 1rem;
            font-weight: 500;
            text-align: center;
        }

        .success {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 500;
            text-align: center;
        }

        form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
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

        .trajet {
            background-color: #f9f9f9;
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .trajet:hover {
            transform: translateY(-5px);
        }

        .trajet p {
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .trajet form {
            margin-top: 0.5rem;
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

            section h3 {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 480px) {
            section h2 {
                font-size: 1.5rem;
            }

            section h3 {
                font-size: 1.2rem;
            }

            .input-group input {
                font-size: 0.9rem;
                padding: 0.5rem;
            }

            button {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
            }

            .trajet {
                padding: 1rem;
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
                <li><a href="espace_passager.php" class="active">Espace Passager</a></li>
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
            <h2>Rechercher un trajet</h2>
            <form method="POST">
                <div class="input-group">
                    <label for="ville_depart"><i class="fas fa-map-marker-alt"></i> Ville de départ :</label>
                    <input type="text" id="ville_depart" name="ville_depart" required value="<?php echo isset($ville_depart) ? htmlspecialchars($ville_depart) : ''; ?>">
                </div>
                <div class="input-group">
                    <label for="ville_arrivee"><i class="fas fa-map-marker-alt"></i> Ville d'arrivée :</label>
                    <input type="text" id="ville_arrivee" name="ville_arrivee" required value="<?php echo isset($ville_arrivee) ? htmlspecialchars($ville_arrivee) : ''; ?>">
                </div>
                <div class="input-group">
                    <label for="date_trajet"><i class="fas fa-calendar-alt"></i> Date du trajet :</label>
                    <input type="date" id="date_trajet" name="date_trajet" required value="<?php echo isset($date_trajet) ? htmlspecialchars($date_trajet) : ''; ?>">
                </div>
                <div class="input-group">
                    <label for="nb_places"><i class="fas fa-users"></i> Nombre de places :</label>
                    <input type="number" id="nb_places" name="nb_places" required value="<?php echo isset($nb_places) ? htmlspecialchars($nb_places) : 1; ?>">
                </div>
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
            </form>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <?php if (empty($trajets) && empty($alternatifs)): ?>
                    <p class="error">Pas de trajet disponible pour cette recherche.</p>
                <?php elseif (!empty($trajets)): ?>
                    <h3>Résultats exacts :</h3>
                    <?php foreach ($trajets as $trajet): ?>
                        <div class="trajet">
                            <p>De <?php echo htmlspecialchars($trajet['Ville_depart']); ?> à <?php echo htmlspecialchars($trajet['Ville_arrivee']); ?> - Date: <?php echo htmlspecialchars($trajet['Date_trajet']); ?> - Heure: <?php echo htmlspecialchars($trajet['Heure_depart']); ?> - Tarif: <?php echo htmlspecialchars($trajet['Tarif']); ?> MAD - Places: <?php echo htmlspecialchars($trajet['Nombre_places']); ?></p>
                            <form action="reserver.php" method="POST">
                                <input type="hidden" name="id_trajet" value="<?php echo htmlspecialchars($trajet['ID_T']); ?>">
                                <input type="hidden" name="nb_places" value="<?php echo htmlspecialchars($nb_places); ?>">
                                <button type="submit"><i class="fas fa-ticket-alt"></i> Réserver</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php elseif (!empty($alternatifs)): ?>
                    <p class="error">Aucun trajet exact trouvé. Voici des alternatives :</p>
                    <?php foreach ($alternatifs as $alt): ?>
                        <div class="trajet">
                            <p>De <?php echo htmlspecialchars($alt['Ville_depart']); ?> à <?php echo htmlspecialchars($alt['Ville_arrivee']); ?> - Date: <?php echo htmlspecialchars($alt['Date_trajet']); ?> - Heure: <?php echo htmlspecialchars($alt['Heure_depart']); ?> - Tarif: <?php echo htmlspecialchars($alt['Tarif']); ?> MAD - Places: <?php echo htmlspecialchars($alt['Nombre_places']); ?></p>
                            <form action="reserver.php" method="POST">
                                <input type="hidden" name="id_trajet" value="<?php echo htmlspecialchars($alt['ID_T']); ?>">
                                <input type="hidden" name="nb_places" value="<?php echo htmlspecialchars($nb_places); ?>">
                                <button type="submit"><i class="fas fa-ticket-alt"></i> Réserver</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>