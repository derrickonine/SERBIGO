<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_email']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM comptes_inactifs WHERE Statut_suppression = 'en_attente'");
$stmt->execute();
$comptes_inactifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM avis_notes WHERE Statut = 'en_attente'");
$stmt->execute();
$avis_en_attente = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'supprimer_compte') {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        try {
            $stmt = $pdo->prepare("UPDATE comptes_inactifs SET Statut_suppression = 'supprime', date_suppression_effective = CURRENT_TIMESTAMP WHERE Email = ?");
            $stmt->execute([$email]);
            $success = "Compte supprimé avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'moderer_avis') {
        $id_note = filter_input(INPUT_POST, 'id_note', FILTER_SANITIZE_NUMBER_INT);
        $statut = filter_input(INPUT_POST, 'statut', FILTER_SANITIZE_STRING);
        try {
            $stmt = $pdo->prepare("UPDATE avis_notes SET Statut = ? WHERE id_note = ?");
            $stmt->execute([$statut, $id_note]);
            $success = "Avis modéré avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur lors de la modération : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - serbiGO</title>
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
                <li><a href="admin.php" class="active">Administration</a></li>
                <li class="auth"><a href="deconnexion.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <section class="administration">
        <h2>Tableau de bord administrateur</h2>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <h3>Comptes inactifs</h3>
        <table>
            <tr>
                <th>Email</th>
                <th>Dernière connexion</th>
                <th>Action</th>
            </tr>
            <?php foreach ($comptes_inactifs as $compte): ?>
                <tr>
                    <td><?php echo htmlspecialchars($compte['Email']); ?></td>
                    <td><?php echo $compte['derniere_connexion']; ?></td>
                    <td>
                        <form action="admin.php" method="POST">
                            <input type="hidden" name="email" value="<?php echo $compte['Email']; ?>">
                            <input type="hidden" name="action" value="supprimer_compte">
                            <button type="submit" class="button primary"><i class="fas fa-trash"></i> Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <h3>Avis en attente de modération</h3>
        <table>
            <tr>
                <th>Auteur</th>
                <th>Cible</th>
                <th>Note</th>
                <th>Avis</th>
                <th>Action</th>
            </tr>
            <?php foreach ($avis_en_attente as $avis): ?>
                <tr>
                    <td><?php echo htmlspecialchars($avis['Email_auteur']); ?></td>
                    <td><?php echo htmlspecialchars($avis['Email_cible']); ?></td>
                    <td><?php echo $avis['note']; ?></td>
                    <td><?php echo htmlspecialchars($avis['avis']); ?></td>
                    <td>
                        <form action="admin.php" method="POST">
                            <input type="hidden" name="id_note" value="<?php echo $avis['id_note']; ?>">
                            <input type="hidden" name="action" value="moderer_avis">
                            <select name="statut">
                                <option value="publie">Publier</option>
                                <option value="rejete">Rejeter</option>
                            </select>
                            <button type="submit" class="button primary"><i class="fas fa-check"></i> Valider</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>
</body>
</html>