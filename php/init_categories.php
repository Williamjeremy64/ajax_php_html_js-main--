<?php
include 'db/db_connect.php';

$conn = connecterBDD();

try {
    // Supprimer les catégories existantes
    $conn->query("DELETE FROM categories");
    
    // Insérer les catégories de base
    $categories = [
        ['Salaire', 'revenu'],
        ['Investissement', 'revenu'],
        ['Cadeau', 'revenu'],
        ['Autre revenu', 'revenu'],
        ['Alimentation', 'depense'],
        ['Transport', 'depense'],
        ['Logement', 'depense'],
        ['Loisirs', 'depense'],
        ['Santé', 'depense'],
        ['Éducation', 'depense'],
        ['Autre dépense', 'depense']
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