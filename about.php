<?php
session_start();
$isLoggedIn = isset($_SESSION['user_email']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos - serbiGO</title>
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
                <?php if ($isLoggedIn): ?>
                    <li><a href="<?php echo $userRole === 'passager' ? 'espace_passager.php' : 'espace_conducteur.php'; ?>">Espace <?php echo ucfirst($userRole); ?></a></li>
                    <li><a href="profil.php">Profil</a></li>
                    <li><a href="historique.php">Historique des trajets</a></li>
                    <li><a href="messages.php">Messagerie</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                    <?php if ($isAdmin): ?>
                        <li><a href="admin.php">Administration</a></li>
                    <?php endif; ?>
                    <li class="auth"><a href="deconnexion.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="connexion.php">Connexion</a></li>
                    <li><a href="inscription.php">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <section class="about">
        <h2>À propos de serbiGO</h2>
        <p>serbiGO est la plateforme de covoiturage leader au Maroc, conçue pour connecter les conducteurs et les passagers souhaitant partager leurs trajets à travers le pays. Notre mission est de rendre les déplacements plus économiques, écologiques et conviviaux.</p>
        <p>Que vous voyagiez de Casablanca à Marrakech ou de Rabat à Tanger, serbiGO vous offre une solution simple et sécurisée pour voyager en toute confiance.</p>
    </section>
    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <i class="fas fa-caravan"></i> serbiGO
            </div>
            <ul class="footer-links">
                <li><a href="about.php" class="active">À propos</a></li>
                <li><a href="faq.php">FAQ</a></li>
                <li><a href="privacy.php">Confidentialité</a></li>
                <li><a href="terms.php">Conditions d'utilisation</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if ($isAdmin): ?>
                    <li><a href="admin.php">Gestion des comptes</a></li>
                <?php endif; ?>
            </ul>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 serbiGO. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>