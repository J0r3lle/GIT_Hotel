<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "hotel_db"; 

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Échec de la connexion: " . $conn->connect_error);
}
?>
