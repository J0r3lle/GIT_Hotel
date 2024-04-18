<?php
session_start();
include 'config.php';

// Vérifie si l'utilisateur est connecté et a un ID
if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour effectuer une réservation.";
    exit();
}

// Récupère l'ID de l'utilisateur depuis la session
$user_id = $_SESSION['user_id'];

// Vérifie si les données ont été envoyées
if (isset($_GET['chambre_id']) && isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $chambre_id = $_GET['chambre_id'];
    $date_debut = $_GET['start_date'];
    $date_fin = $_GET['end_date'];

    // S'assure que les dates sont valides et que date_fin est après date_debut
    if (new DateTime($date_debut) < new DateTime($date_fin)) {
        // Prépare la requête SQL pour insérer la réservation avec l'ID de l'utilisateur
        $sql = "INSERT INTO reservations (users_id, chambre_id, date_debut, date_fin) VALUES (?, ?, ?, ?)";

        // Prépare et lie les paramètres
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iiss", $user_id, $chambre_id, $date_debut, $date_fin);

            // Exécute la requête préparée et vérifiez si elle réussit
            if ($stmt->execute()) {
                echo "Réservation enregistrée avec succès.";
            } else {
                echo "Erreur lors de l'enregistrement de la réservation: " . $stmt->error;
            }

            // Ferme la déclaration
            $stmt->close();
        } else {
            echo "Erreur lors de la préparation de la requête: " . $conn->error;
        }
    } else {
        echo "La date de fin doit être postérieure à la date de début.";
    }
} else {
    echo "Informations de réservation manquantes.";
}

// Ferme la connexion à la base de données
$conn->close();
?>
