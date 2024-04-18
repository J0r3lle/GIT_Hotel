<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password']; 

    // Modifie la requête pour récupérer l'utilisateur sans vérifier le mot de passe
    $sql = "SELECT * FROM users WHERE username = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;

                // Redirige selon le rôle/privilège(s) de l'utilisateur
                header("Location: " . ($username == 'admin' ? "admin.php" : "check_availability.php"));
                exit();
            } else {
                echo "Nom d'utilisateur ou mot de passe incorrect.";
            }
        } else {
            echo "Nom d'utilisateur ou mot de passe incorrect.";
        }
        $stmt->close();
    } else {
        echo "Erreur lors de la préparation de la requête: " . $conn->error;
    }
}

$conn->close();
?>
