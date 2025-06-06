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

$id_utilisateur = $_SESSION['user_id'];
$conn = connecterBDD();

try {
    // Calculer le solde total
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN c.type = 'revenu' THEN t.montant ELSE -t.montant END), 0) as solde,
            COALESCE(SUM(CASE WHEN c.type = 'revenu' THEN t.montant ELSE 0 END), 0) as revenus,
            COALESCE(SUM(CASE WHEN c.type = 'depense' THEN t.montant ELSE 0 END), 0) as depenses
        FROM transactions t
        JOIN categories c ON t.id_categorie = c.id
        WHERE t.id_utilisateur = ?
    ");
    
    $stmt->bind_param("i", $id_utilisateur);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'solde' => number_format($row['solde'], 2, '.', ''),
        'revenus' => number_format($row['revenus'], 2, '.', ''),
        'depenses' => number_format($row['depenses'], 2, '.', '')
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>