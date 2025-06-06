<?php 
include 'db/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non connecté'
    ]);
    exit;
}

if (!isset($_POST['montant']) || !isset($_POST['categorie']) || !isset($_POST['description'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Tous les champs sont requis'
    ]);
    exit;
}

$id_utilisateur = $_SESSION['user_id'];
$montant = floatval($_POST['montant']);
$id_categorie = intval($_POST['categorie']);
$description = $_POST['description'];

$conn = connecterBDD();

try {
    // Vérifier si la catégorie existe et obtenir son type
    $stmt = $conn->prepare("SELECT type FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id_categorie);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Catégorie invalide");
    }
    
    $categorie = $result->fetch_assoc();
    $type = $categorie['type'];
    
    // Insérer la transaction
    $stmt = $conn->prepare("
        INSERT INTO transactions (id_utilisateur, id_categorie, montant, description, date)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("iids", $id_utilisateur, $id_categorie, $montant, $description);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Transaction ajoutée avec succès'
        ]);
    } else {
        throw new Exception("Erreur lors de l'ajout de la transaction");
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
