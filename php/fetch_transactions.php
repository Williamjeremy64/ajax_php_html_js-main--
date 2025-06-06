<?php
include 'db/db_connect.php';
header('Content-Type: application/json');

if (!isset($_POST['numeroCompte'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Paramètre manquant : numeroCompte.'
    ]);
    exit;
}

    $numeroCompte = $_POST['numeroCompte'];
    $conn = connecterBDD();

try {
    // Requête pour récupérer les 5 dernières transactions
    $stmt = $conn->prepare("
        SELECT 
            montant,
            type,
            description,
            DATE_FORMAT(date_transaction, '%d/%m/%Y %H:%i') as date
        FROM transactions 
        WHERE numero_compte = ?
        ORDER BY date_transaction DESC
        LIMIT 5
    ");

    $stmt->bind_param("s", $numeroCompte);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $transactions = [];
    while ($row = $result->fetch_assoc()) {
        // Debug: afficher les données brutes
        error_log("Transaction row: " . print_r($row, true));
        
        $transactions[] = [
            'montant' => number_format($row['montant'], 2, '.', ''),
            'type' => $row['type'],
            'description' => $row['description'] ?? 'Sans description',
            'date' => $row['date']
        ];
        }

    echo json_encode([
            'success' => true,
            'transactions' => $transactions
    ]);

} catch (Exception $e) {
    error_log("Erreur dans fetch_transactions.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des transactions: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>
