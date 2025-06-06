<?php
include 'db/db_connect.php';

$conn = connecterBDD();

try {
    // Supprimer les catégories existantes
    $conn->query("DELETE FROM categories");
    
    // Insérer les deux catégories de base
    $categories = [
        ['Revenu', 'revenu'],
        ['Dépense', 'depense']
    ];
    
    $stmt = $conn->prepare("INSERT INTO categories (nom, type) VALUES (?, ?)");
    
    foreach ($categories as $categorie) {
        $stmt->bind_param("ss", $categorie[0], $categorie[1]);
        $stmt->execute();
    }
    
    echo "Catégories initialisées avec succès !";
    
} catch (Exception $e) {
    echo "Erreur lors de l'initialisation des catégories : " . $e->getMessage();
}

$stmt->close();
$conn->close();
?> 