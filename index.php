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
    <title>serbiGO - Votre solution de covoiturage au Maroc</title>
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

        nav ul li.auth a {
            background-color: var(--secondary-color);
            color: var(--background-white);
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            box-shadow: var(--shadow);
        }

        nav ul li.auth a:hover {
            background-color: #2a6ab0;
        }

        /* Full-Width Image Below Header */
        .full-width-image {
            width: 100%;
            height: 300px;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .full-width-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        /* Main Content Padding to Avoid Overlap */
        main {
            flex: 1 0 auto;
            padding-top: 20px;
        }

        /* Hero Section with Carousel */
        .hero {
            background-color: #f9f9f9;
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto 2rem;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 2rem;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            flex: 1;
            text-align: left;
        }

        .hero h1 {
            font-size: 2.8rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.2rem;
            color: var(--text-light);
            margin-bottom: 2rem;
        }

        .hero .button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--primary-color);
            color: var(--background-white);
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .hero .button:hover {
            background-color: #219653;
            transform: translateY(-2px);
        }

        /* Carousel Styles */
        .hero-image {
            flex: 1;
            position: relative;
            height: 400px;
            overflow: hidden;
        }

        .carousel {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .carousel-item {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .carousel-item.active {
            opacity: 1;
        }

        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: var(--border-radius);
        }

        .carousel-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .carousel-dot {
            width: 12px;
            height: 12px;
            background-color: var(--text-light);
            border-radius: 50%;
            cursor: pointer;
            transition: var(--transition);
        }

        .carousel-dot.active {
            background-color: var(--primary-color);
        }

        /* Features Section */
        .features {
            padding: 4rem 2rem;
            text-align: center;
            background-color: var(--background-white);
            margin: 0 auto 2rem;
            max-width: 1200px;
        }

        .features h2 {
            font-size: 2.2rem;
            color: var(--text-dark);
            margin-bottom: 2.5rem;
        }

        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .feature {
            background-color: #f9f9f9;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: transform var(--transition);
        }

        .feature:hover {
            transform: translateY(-5px);
        }

        .feature i {
            font-size: 2.8rem;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        .feature h3 {
            color: var(--text-dark);
            margin-bottom: 0.75rem;
        }

        .feature p {
            color: var(--text-light);
        }

        /* How It Works Section */
        .how-it-works {
            padding: 4rem 2rem;
            text-align: center;
            background-color: #e0f2f7;
            margin: 0 auto 2rem;
            max-width: 1000px;
        }

        .how-it-works h2 {
            font-size: 2.2rem;
            color: var(--text-dark);
            margin-bottom: 2.5rem;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .step {
            text-align: center;
        }

        .step i {
            font-size: 2.8rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .step h3 {
            color: var(--text-dark);
            margin-bottom: 0.75rem;
        }

        .step p {
            color: var(--text-light);
        }

        /* Call to Action Section */
        .call-to-action {
            padding: 4rem 2rem;
            text-align: center;
            background-color: var(--primary-color);
            color: var(--background-white);
            margin: 0 auto 2rem;
            max-width: 800px;
        }

        .call-to-action h2 {
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
        }

        .call-to-action p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .cta-buttons .button {
            background-color: var(--background-white);
            color: var(--primary-color);
            border: none;
            border-radius: var(--border-radius);
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .cta-buttons .button:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
        }

        .button.secondary {
            background-color: transparent;
            color: var(--background-white);
            border: 2px solid var(--background-white);
        }

        .button.secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Footer */
        footer {
            background-color: var(--text-dark);
            color: var(--background-white);
            padding: 2rem;
            text-align: center;
            margin-top: auto;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .footer-logo {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            display: flex;
            gap: 1.5rem;
        }

        .footer-links li a {
            color: var(--background-white);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links li a:hover {
            color: var(--primary-color);
        }

        .social-links a {
            color: var(--background-white);
            font-size: 1.5rem;
            margin: 0 0.75rem;
            transition: var(--transition);
        }

        .social-links a:hover {
            color: var(--accent-color);
        }

        .footer-bottom {
            margin-top: 1rem;
            font-size: 0.9rem;
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

            .hero {
                flex-direction: column;
                text-align: center;
            }

            .hero-content {
                text-align: center;
            }

            .hero-image {
                height: 300px;
            }

            .steps {
                grid-template-columns: 1fr;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .full-width-image {
    width: 100%;
    height: 300px;
    overflow: hidden;
    position: relative;
    z-index: 1;
    background-color: #f5e8c7; /* Matches the beige background of the image */
}

.full-width-image img {
    width: 100%;
    height: 100%;
    object-fit: contain; /* Changed from cover to contain to show the entire image */
    object-position: center;
    display: block;
}
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .hero .button {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
            }

            .full-width-image {
                height: 150px;
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
                <li><a href="index.php" class="active">Accueil</a></li>
                <?php if ($isLoggedIn): ?>
                    <?php if ($userRole === 'passager'): ?>
                        <li><a href="espace_passager.php">Espace Passager</a></li>
                    <?php elseif ($userRole === 'conducteur'): ?>
                        <li><a href="espace_conducteur.php">Espace Conducteur</a></li>
                    <?php endif; ?>
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

    <!-- Full-Width Image Section Below Header -->
    <div class="full-width-image">
        <img src="images.png"  alt="serbiGO ">>
    </div>

    <main>
        <div class="hero">
            <div class="hero-content">
                <h1>Partagez la route, simplifiez vos trajets au Maroc.</h1>
                <p>Connectez-vous avec des conducteurs et des passagers à travers le Maroc et voyagez à moindre coût.</p>
                <?php if ($isLoggedIn && $userRole === 'conducteur'): ?>
                    <a href="espace_conducteur.php" class="button primary"><i class="fas fa-car"></i> Proposer un trajet</a>
                <?php elseif ($isLoggedIn && $userRole === 'passager'): ?>
                    <a href="espace_passager.php" class="button primary"><i class="fas fa-search"></i> Rechercher un trajet</a>
                <?php else: ?>
                    <a href="inscription.php" class="button primary"><i class="fas fa-user-plus"></i> S'inscrire pour commencer</a>
                <?php endif; ?>
            </div>
            <div class="hero-image">
                <div class="carousel">
                    <div class="carousel-item active">
                        <img src="https://cdn.blablacar.com/k/a/images/daily_section-bc11e0e6d4fde5e6.svg" alt="Covoiturage au Maroc 1">
                    </div>
                    <div class="carousel-item">
                        <img src="https://pippipyalah.com/_next/image?url=%2Fstatic%2Fimages%2Fsafety-carpooling.jpg&w=1080&q=75" alt="Covoiturage au Maroc 2">
                    </div>
                    <div class="carousel-item">
                        <img src="https://cdn.blablacar.com/k/a/images/trust_safety_desktop-2bd0a711110148c4.webp" alt="Covoiturage au Maroc 3">
                    </div>
                    <div class="carousel-item">
                        <img src="https://cdn.blablacar.com/k/a/images/trust_safety_desktop-2bd0a711110148c4.webp" alt="Covoiturage au Maroc 4">
                    </div>
                    <div class="carousel-controls">
                        <span class="carousel-dot active"></span>
                        <span class="carousel-dot"></span>
                        <span class="carousel-dot"></span>
                    </div>
                </div>
            </div>
        </div>

        <section class="features">
            <h2>Pourquoi choisir serbiGO ?</h2>
            <div class="feature-list">
                <div class="feature">
                    <i class="fas fa-users"></i>
                    <h3>Large communauté sécurisée</h3>
                    <p>Connectez-vous avec des milliers d'utilisateurs au Maroc avec des profils vérifiés et un système de notation pour voyager en toute confiance.</p>
                </div>
                <div class="feature">
                    <i class="fas fa-money-bill"></i>
                    <h3>Économique</h3>
                    <p>Réduisez vos frais de déplacement en partageant les coûts, en dirhams marocains.</p>
                </div>
                <div class="feature">
                    <i class="fas fa-route"></i>
                    <h3>Flexibilité</h3>
                    <p>Trouver des trajets qui correspondent à votre itinéraire et à votre emploi du temps, de Casablanca à Ouarzazate.</p>
                </div>
            </div>
        </section>

        <section class="how-it-works">
            <h2>Comment ça marche ?</h2>
            <div class="steps">
                <div class="step">
                    <i class="fas fa-map-marked-alt"></i>
                    <h3>1. Se connecter</h3>
                    <p>Parcourez les offres disponibles et contactez les conducteurs ou passagers via la messagerie sécurisée.</p>
                </div>
                <div class="step">
                    <i class="fas fa-handshake"></i>
                    <h3>2. Rechercher un trajet</h3>
                    <p>Entrez votre point de départ et votre destination (ex. Rabat à Marrakech) et la date souhaitée.</p>
                </div>
                <div class="step">
                    <i class="fas fa-car"></i>
                    <h3>3. Voyager ensemble</h3>
                    <p>Profitez de votre trajet en covoiturage, partagez les frais et laissez un avis après le voyage.</p>
                </div>
            </div>
        </section>

        <section class="call-to-action">
            <h2>Prêt à partager la route au Maroc ?</h2>
            <p>Rejoignez la communauté serbiGO et commencez à voyager intelligemment dès aujourd'hui !</p>
            <div class="cta-buttons">
                <a href="inscription.php" class="button primary"><i class="fas fa-user-plus"></i> S'inscrire maintenant</a>
                <?php if ($isLoggedIn): ?>
                    <a href="historique.php" class="button secondary"><i class="fas fa-history"></i> Voir mon historique</a>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <i class="fas fa-caravan"></i> serbiGO
            </div>
            <ul class="footer-links">
                <li><a href="about.php">À propos</a></li>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const carouselItems = document.querySelectorAll('.carousel-item');
            const carouselDots = document.querySelectorAll('.carousel-dot');
            let currentIndex = 0;

            function showSlide(index) {
                carouselItems.forEach((item, i) => {
                    item.classList.toggle('active', i === index);
                    carouselDots[i].classList.toggle('active', i === index);
                });
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % carouselItems.length;
                showSlide(currentIndex);
            }

            setInterval(nextSlide, 5000);

            carouselDots.forEach((dot, i) => {
                dot.addEventListener('click', () => {
                    currentIndex = i;
                    showSlide(currentIndex);
                });
            });
        });
    </script>
</body>
</html>