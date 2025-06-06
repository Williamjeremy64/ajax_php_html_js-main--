<?php
/**
 * Configuration de la connexion à la base de données
 * 
 * Ce fichier gère la connexion à la base de données MySQL
 * Il utilise les paramètres suivants :
 * - Serveur : localhost
 * - Utilisateur : root
 * - Mot de passe : (vide)
 * - Base de données : dbbudget
 */

function connecterBDD() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "dbbudget"; 

    // Création de la connexion
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Vérification de la connexion
    if ($conn->connect_error) {
        die("La connexion à la base de données a échoué : " . $conn->connect_error);
    }

    // Connexion réussie, on retourne l'objet mysqli
    return $conn;
}
?>
