<?php
/**
 * Script d'installation de la base de données
 * Ce script crée automatiquement :
 * - La base de données dbbudget
 * - Les tables nécessaires (utilisateurs, comptes, transactions)
 * - Un compte de test
 */

// Paramètres de connexion
$servername = "localhost";
$username = "root";
$password = "";

try {
    // Connexion à MySQL sans sélectionner de base de données
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Erreur de connexion : " . $conn->connect_error);
    }

    // Création de la base de données
    $sql = "CREATE DATABASE IF NOT EXISTS dbbudget";
    if (!$conn->query($sql)) {
        throw new Exception("Erreur lors de la création de la base de données : " . $conn->error);
    }

    // Sélection de la base de données
    $conn->select_db("dbbudget");

    // Création de la table utilisateurs
    $sql = "CREATE TABLE IF NOT EXISTS utilisateurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        telephone VARCHAR(20) NOT NULL UNIQUE,
        nom VARCHAR(100) NOT NULL,
        code VARCHAR(20) NOT NULL
    )";
    if (!$conn->query($sql)) {
        throw new Exception("Erreur lors de la création de la table utilisateurs : " . $conn->error);
    }

    // Création de la table comptes
    $sql = "CREATE TABLE IF NOT EXISTS comptes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numero_compte VARCHAR(20) NOT NULL UNIQUE,
        solde DECIMAL(10,2) DEFAULT 0.00,
        FOREIGN KEY (numero_compte) REFERENCES utilisateurs(telephone)
    )";
    if (!$conn->query($sql)) {
        throw new Exception("Erreur lors de la création de la table comptes : " . $conn->error);
    }

    // Création de la table transactions
    $sql = "CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numero_compte VARCHAR(20) NOT NULL,
        type ENUM('revenu', 'depense') NOT NULL,
        montant DECIMAL(10,2) NOT NULL,
        description TEXT,
        date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (numero_compte) REFERENCES comptes(numero_compte)
    )";
    if (!$conn->query($sql)) {
        throw new Exception("Erreur lors de la création de la table transactions : " . $conn->error);
    }

    // Vérification si le compte de test existe déjà
    $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE telephone = ?");
    $testPhone = "123456";
    $stmt->bind_param("s", $testPhone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Insertion du compte de test
        $stmt = $conn->prepare("INSERT INTO utilisateurs (telephone, nom, code) VALUES (?, ?, ?)");
        $nom = "Test User";
        $code = "#9999#";
        $stmt->bind_param("sss", $testPhone, $nom, $code);
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de la création du compte de test : " . $stmt->error);
        }

        // Création du compte avec solde initial
        $stmt = $conn->prepare("INSERT INTO comptes (numero_compte, solde) VALUES (?, ?)");
        $soldeInitial = 100000.00;
        $stmt->bind_param("sd", $testPhone, $soldeInitial);
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de la création du solde initial : " . $stmt->error);
        }

        // Ajout de quelques transactions de test
        $transactions = [
            ['revenu', 50000.00, 'Salaire'],
            ['depense', 15000.00, 'Courses']
        ];

        $stmt = $conn->prepare("INSERT INTO transactions (numero_compte, type, montant, description) VALUES (?, ?, ?, ?)");
        foreach ($transactions as $t) {
            $stmt->bind_param("ssds", $testPhone, $t[0], $t[1], $t[2]);
            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de l'ajout des transactions de test : " . $stmt->error);
            }
        }
    }

    echo "Installation réussie ! La base de données est prête à être utilisée.<br>";
    echo "Compte de test :<br>";
    echo "- Numéro : 123456<br>";
    echo "- Code : #9999#<br>";
    echo "- Solde initial : 100 000 FCFA";

} catch (Exception $e) {
    echo "Erreur lors de l'installation : " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 