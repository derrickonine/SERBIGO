<?php
session_start();
require_once 'includes/config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header("Location: connexion.php");
    exit();
}

$email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];
$trajets = [];

// Récupérer les trajets selon le rôle
try {
    if ($user_role === 'conducteur') {
        // Trajets proposés par le conducteur (non archivés)
        $stmt_active = $pdo->prepare("SELECT ID_T, Ville_depart, Ville_arrivee, Date_trajet, Heure_depart, Statut, 'Conducteur' AS Role 
                                      FROM trajets 
                                      WHERE Email = ?");
        $stmt_active->execute([$email]);
        $trajets_active = $stmt_active->fetchAll(PDO::FETCH_ASSOC);

        // Trajets archivés
        $stmt_archived = $pdo->prepare("SELECT t.ID_T, t.Ville_depart, t.Ville_arrivee, t.Date_trajet, t.Heure_depart, h.Statut, h.Role 
                                        FROM historique_trajets h 
                                        JOIN trajets t ON h.ID_T = t.ID_T 
                                        WHERE h.Email = ? AND h.Role = 'Conducteur'");
        $stmt_archived->execute([$email]);
        $trajets_archived = $stmt_archived->fetchAll(PDO::FETCH_ASSOC);

        // Combiner les trajets
        $trajets = array_merge($trajets_active, $trajets_archived);
    } else {
        // Trajets réservés par le passager (non archivés)
        $stmt_active = $pdo->prepare("SELECT t.ID_T, t.Ville_depart, t.Ville_arrivee, t.Date_trajet, t.Heure_depart, r.Statut_reservation AS Statut, 'Passager' AS Role 
                                      FROM reservations r 
                                      JOIN trajets t ON r.ID_T = t.ID_T 
                                      WHERE r.Email_passager = ?");
        $stmt_active->execute([$email]);
        $trajets_active = $stmt_active->fetchAll(PDO::FETCH_ASSOC);

        // Trajets archivés
        $stmt_archived = $pdo->prepare("SELECT t.ID_T, t.Ville_depart, t.Ville_arrivee, t.Date_trajet, t.Heure_depart, h.Statut, h.Role 
                                        FROM historique_trajets h 
                                        JOIN trajets t ON h.ID_T = t.ID_T 
                                        WHERE h.Email = ? AND h.Role = 'Passager'");
        $stmt_archived->execute([$email]);
        $trajets_archived = $stmt_archived->fetchAll(PDO::FETCH_ASSOC);

        // Combiner les trajets
        $trajets = array_merge($trajets_active, $trajets_archived);
    }

    // Trier par date décroissante
    usort($trajets, function($a, $b) {
        return strtotime($b['Date_trajet']) - strtotime($a['Date_trajet']);
    });
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des trajets : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des trajets - serbiGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath d='M10 10 C 20 40, 40 20, 50 50 C 60 80, 80 60, 90 90' stroke='%2327ae60' stroke-width='0.5' fill='none'/%3E%3Cpath d='M20 90 C 40 60, 60 80, 70 50 C 80 20, 90 40, 90 10' stroke='%233686d6' stroke-width='0.5' fill='none'/%3E%3C/svg%3E"), var(--background-light);
            background-size: 200px 200px;
            background-repeat: repeat;
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
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

        .error {
            color: #e74c3c;
            margin-bottom: 1rem;
            font-weight: 500;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--primary-color);
            color: var(--background-white);
            font-weight: 600;
        }

        tr:hover {
            background-color: #f9f9f9;
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

            table {
                font-size: 0.9rem;
            }

            th, td {
                padding: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            section h2 {
                font-size: 1.5rem;
            }

            table {
                font-size: 0.8rem;
            }

            th, td {
                padding: 0.3rem;
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
                <li><a href="<?php echo $user_role === 'passager' ? 'espace_passager.php' : 'espace_conducteur.php'; ?>">Espace <?php echo ucfirst($user_role); ?></a></li>
                <li><a href="profil.php">Profil</a></li>
                <li><a href="historique.php" class="active">Historique des trajets</a></li>
                <li><a href="messages.php">Messagerie</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="deconnexion.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Historique de vos trajets</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if (empty($trajets)): ?>
                <p>Aucun trajet trouvé dans votre historique.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Ville de départ</th>
                        <th>Ville d'arrivée</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                    </tr>
                    <?php foreach ($trajets as $trajet): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($trajet['Ville_depart']); ?></td>
                            <td><?php echo htmlspecialchars($trajet['Ville_arrivee']); ?></td>
                            <td><?php echo htmlspecialchars($trajet['Date_trajet']); ?></td>
                            <td><?php echo htmlspecialchars($trajet['Heure_depart'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($trajet['Role']); ?></td>
                            <td><?php echo htmlspecialchars($trajet['Statut']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>