<form action="check_availability.php" method="post">
    <label for="start_date">Date d'arrivée:</label>
    <input type="date" id="start_date" name="start_date" required>

    <label for="end_date">Date de départ:</label>
    <input type="date" id="end_date" name="end_date" required>

    <input type="submit" value="Vérifier la disponibilité">
</form>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'config.php'; 

    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Prépare la requête SQL pour vérifier la disponibilité des chambres
    $sql = "SELECT c.Id, c.Type 
        FROM chambre c
        LEFT JOIN reservations r ON c.Id = r.chambre_id 
                                  AND NOT (r.date_fin <= ? OR r.date_debut >= ?)
        WHERE r.chambre_id IS NULL";

    // Prépare et lit les paramètres
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $start_date, $end_date);

        // Exécutez la requête préparée et obtenez les résultats
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            // Afficher les chambres disponibles
            while($row = $result->fetch_assoc()) {
                echo "Chambre ID: " . $row['Id'] . " - Type: " . $row['Type'];
                echo " <a href='reserve.php?chambre_id=" . $row['Id'] . "&start_date=" . $start_date . "&end_date=" . $end_date . "'>Réserver</a><br>";
            }
        } else {
            echo "Erreur lors de la vérification de la disponibilité: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "Erreur lors de la préparation de la requête: " . $conn->error;
    }

    $conn->close();
}
?>