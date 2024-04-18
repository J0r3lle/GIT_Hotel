<?php
// Inclure le fichier de configuration de connexion à la base de données
include 'config.php';

// La requête SQL pour récupérer toutes les chambres disponibles
$sql = "SELECT Id, Type FROM chambre WHERE dispo = TRUE";

// Exécuter la requête et récupérer les résultats
$result = $conn->query($sql);

// Vérifier s'il y a des résultats
if ($result->num_rows > 0) {
    // Parcourir les résultats et les afficher
    while($row = $result->fetch_assoc()) {
        // Afficher les informations de la chambre
        echo "Chambre ID: " . str_pad($row['Id'], 3, '0', STR_PAD_LEFT) . " - Type: " . $row['Type'];
        // Afficher un bouton de réservation pour chaque chambre
        echo " <a href='reservation.php?id=" . $row['Id'] . "'><button>Réserver</button></a><br>";
    }
} else {
    echo "Aucune chambre disponible.";
}

// Fermer la connexion à la base de données
$conn->close();
?>
