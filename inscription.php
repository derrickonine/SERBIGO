<?php
session_start();
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (Email, Nom, Prenom, Mot_de_passe, Role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$email, $nom, $prenom, $password, $role]);
        
        // Connexion automatique après inscription
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $role;
        
        // Redirection selon le rôle
        if ($role === 'conducteur') {
            header('Location: espace_conducteur.php');
        } else {
            header('Location: espace_passager.php');
        }
        exit;
    } catch (PDOException $e) {
        $error = "Erreur lors de l'inscription : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - serbiGO</title>
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

        /* Inscription Section */
        .inscription {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: var(--background-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
        }

        .inscription h2 {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
        }

        .error {
            color: #e74c3c;
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

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(39, 174, 96, 0.3);
        }

        .button.primary {
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

        .button.primary:hover {
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

            .inscription {
                margin: 1rem;
                padding: 1.5rem;
            }

            .inscription h2 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .inscription h2 {
                font-size: 1.5rem;
            }

            .input-group input,
            .input-group select {
                font-size: 0.9rem;
                padding: 0.5rem;
            }

            .button.primary {
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
                <li><a href="connexion.php">Connexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="inscription">
            <h2>Inscription à serbiGO</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form action="inscription.php" method="POST">
                <div class="input-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email :</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="nom"><i class="fas fa-user"></i> Nom :</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="input-group">
                    <label for="prenom"><i class="fas fa-user"></i> Prénom :</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
                <div class="input-group">
                    <label for="password"><i class="fas fa-lock"></i> Mot de passe :</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="role"><i class="fas fa-car"></i> Vous êtes :</label>
                    <select id="role" name="role" required>
                        <option value="passager">Passager</option>
                        <option value="conducteur">Conducteur</option>
                    </select>
                </div>
                <button type="submit" class="button primary"><i class="fas fa-user-plus"></i> S'inscrire</button>
            </form>
        </section>
    </main>
</body>
</html>