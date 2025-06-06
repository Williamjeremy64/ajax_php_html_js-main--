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
    // Requête pour récupérer les 5 dernières transactions avec les catégories
    $stmt = $conn->prepare("
        SELECT 
            t.montant,
            t.description,
            t.date,
            c.nom as categorie,
            c.type
        FROM transactions t
        JOIN categories c ON t.id_categorie = c.id
        WHERE t.id_utilisateur = ?
        ORDER BY t.date DESC
        LIMIT 5
    ");
    
    $stmt->bind_param("i", $id_utilisateur);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        $transactions[] = [
            'montant' => number_format($row['montant'], 2, '.', ''),
            'type' => $row['type'],
            'categorie' => $row['categorie'],
            'description' => $row['description'],
            'date' => date('d/m/Y H:i', strtotime($row['date']))
        ];
    }

    echo json_encode([
        'success' => true,
        'transactions' => $transactions
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des transactions: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>
