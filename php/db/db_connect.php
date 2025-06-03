<?php
function connecterBDD() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "dbbudget"; 

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Vérifier la connexion
    if ($conn->connect_error) {
        die("La connexion à la base de données a échoué : " . $conn->connect_error);
    }

    // Connexion réussie, on retourne l'objet mysqli
    return $conn;
}
?>
