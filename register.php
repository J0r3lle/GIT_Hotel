<?php
include 'config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifie si les données du formulaire ont été envoyées
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupère les données du formulaire
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);
    $email = $conn->real_escape_string($_POST['email']);
    
    // Vérifie si le nom d'utilisateur existe déjà
    $checkUserSql = "SELECT username FROM users WHERE username = ?";
    $checkUserStmt = $conn->prepare($checkUserSql);
    $checkUserStmt->bind_param("s", $username);
    $checkUserStmt->execute();
    $checkUserResult = $checkUserStmt->get_result();
    if ($checkUserResult->num_rows > 0) {
        echo "Le nom d'utilisateur est déjà pris, veuillez en choisir un autre.<a href='register.html'>Cliquez-ici.</a>";
        $checkUserStmt->close();
    } else {
        // Hache le mot de passe avant de l'insérer dans la base de données
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prépare la requête d'insertion pour la base de données
        $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
        
        // Prépare et lie les paramètres
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $username, $hashedPassword, $email);
            
            if ($stmt->execute()) {
                echo "Inscription réussie. Vous pouvez maintenant vous <a href='login.php'>connecter.</a>";
            } else {
                echo "Erreur lors de l'inscription : " . $stmt->error;
            }
            
            $stmt->close();
        } else {
            echo "Erreur lors de la préparation de la requête : " . $conn->error;
        }
    }
    
    $conn->close();
}
?>

