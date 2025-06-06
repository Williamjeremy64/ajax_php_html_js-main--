<?php
include 'db/db_connect.php';
header('Content-Type: application/json');

if (!isset($_POST['type'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Type de catégorie manquant'
    ]);
    exit;
}

$type = $_POST['type'];
$conn = connecterBDD();

try {
    $stmt = $conn->prepare("
        SELECT id, nom, type
        FROM categories
        WHERE type = ?
        ORDER BY nom ASC
    ");
    
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id' => $row['id'],
            'nom' => $row['nom'],
            'type' => $row['type']
        ];
    }

    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des catégories: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?> 