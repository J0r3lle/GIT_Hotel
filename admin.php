<?php
session_start();
include 'config.php';

// Assurez-vous que l'utilisateur est connecté et est l'administrateur.
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    echo "Accès refusé.";
    exit();
}

function searchUserAndReservations($conn, $searchTerm) {
    $searchResults = [];
    $userSql = "SELECT id, username FROM users WHERE username LIKE ?";
    $stmt = $conn->prepare($userSql);
    $searchTerm = "%" . $searchTerm . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $users = $stmt->get_result();

    while ($user = $users->fetch_assoc()) {
        $reservationsSql = "SELECT r.id, r.date_debut, r.date_fin, c.Type AS chambre_type FROM reservations r JOIN chambre c ON r.chambre_id = c.Id WHERE r.users_id = ?";
        $reservationsStmt = $conn->prepare($reservationsSql);
        $reservationsStmt->bind_param("i", $user['id']);
        $reservationsStmt->execute();
        $reservationsResult = $reservationsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $searchResults[] = [
            'user' => $user,
            'reservations' => $reservationsResult
        ];
    }
    return $searchResults;
}

// Vérifier si les données ont été envoyées pour la recherche
if (isset($_POST['search_client'])) {
    $searchTerm = $_POST['search_term'];
    $searchResults = searchUserAndReservations($conn, $searchTerm);
}

// Traiter la création d'un compte et d'une réservation
if (isset($_POST['create_account'])) {
    $username = $_POST['username'];
    // Hashe le mot de passe avant de l'insérer dans la base de données
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $numero_telephone = $_POST['numero_telephone'];
    $chambre_id = $_POST['chambre_id'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];

    $userSql = "INSERT INTO users (username, password, email, Numero_telephone) VALUES (?, ?, ?, ?)";
    if ($userStmt = $conn->prepare($userSql)) {
        $userStmt->bind_param("ssss", $username, $password, $email, $numero_telephone);
        $userStmt->execute();
        $user_id = $conn->insert_id;

        $reservationSql = "INSERT INTO reservations (users_id, chambre_id, date_debut, date_fin) VALUES (?, ?, ?, ?)";
        if ($reservationStmt = $conn->prepare($reservationSql)) {
            $reservationStmt->bind_param("iiss", $user_id, $chambre_id, $date_debut, $date_fin);
            $reservationStmt->execute();
            echo "Utilisateur et réservation créés avec succès.";
            $reservationStmt->close();
        }
        $userStmt->close();
    } else {
        echo "Erreur lors de la préparation de la requête : " . $conn->error;
    }
}

// Traiter la modification ou l'annulation d'une réservation
if (isset($_POST['modify_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $new_date_debut = $_POST['new_date_debut'] ?? null;
    $new_date_fin = $_POST['new_date_fin'] ?? null;

    // Mettre à jour ou supprimer la réservation
    if ($new_date_debut && $new_date_fin) {
        $updateSql = "UPDATE reservations SET date_debut = ?, date_fin = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ssi", $new_date_debut, $new_date_fin, $reservation_id);
        $updateStmt->execute();
    } else {
        $deleteSql = "DELETE FROM reservations WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $reservation_id);
        $deleteStmt->execute();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Page d'administration</title>
</head>
<body>
    <h1>Administration de l'hôtel</h1>

    <form method="post" action="">
        <label>Rechercher un utilisateur:</label>
        <input type="text" name="search_term">
        <button type="submit" name="search_client">Rechercher</button>
    </form>

    <?php if (!empty($searchResults)): ?>
        <?php foreach ($searchResults as $result): ?>
            <h3><?= htmlspecialchars($result['user']['username']) ?></h3>
            <?php foreach ($result['reservations'] as $reservation): ?>
                <p>Réservation ID: <?= htmlspecialchars($reservation['id']) ?>, Type de chambre: <?= htmlspecialchars($reservation['chambre_type']) ?>, Date de début: <?= htmlspecialchars($reservation['date_debut']) ?>, Date de fin: <?= htmlspecialchars($reservation['date_fin']) ?></p>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2>Créer un nouvel utilisateur et une réservation</h2>
    <form method="post" action="">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="numero_telephone" placeholder="Numéro de téléphone" required>
        <input type="number" name="chambre_id" placeholder="Numéro de la chambre" required>
        <input type="date" name="date_debut" placeholder="Date de début" required>
        <input type="date" name="date_fin" placeholder="Date de fin" required>
        <button type="submit" name="create_account">Créer et Réserver</button>
    </form>

    <h2>Modifier ou annuler une réservation</h2>
    <h5><i>Laisser la date vide pour supprimer</i></h5>
    <form method="post" action="">
        <input type="number" name="reservation_id" placeholder="ID de réservation" required>
        <input type="date" name="new_date_debut" placeholder="Nouvelle date de début">
        <input type="date" name="new_date_fin" placeholder="Nouvelle date de fin">
        <button type="submit" name="modify_reservation">Modifier/Annuler Réservation</button>
    </form>

</body>
</html>
