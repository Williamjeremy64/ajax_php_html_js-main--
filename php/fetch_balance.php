<?php
include 'db/db_connect.php';
header('Content-Type: application/json');

// Vérification de la présence des données POST
if (isset($_POST['numeroCompte'])) {
    $numeroCompte = $_POST['numeroCompte'];
    $conn = connecterBDD();

    // Utilisation d'une requête préparée pour la sécurité
    $stmt = $conn->prepare("SELECT solde FROM comptes WHERE numero_compte = ?");
    $stmt->bind_param("s", $numeroCompte);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = array(
            'success' => true,
            'solde' => number_format($row["solde"], 2, '.', '')
        );
        echo json_encode($response);
    } else {
        $response = array(
            'success' => false,
            'message' => 'Aucun compte trouvé avec ce numéro.'
        );
        echo json_encode($response);
    }
    
    $stmt->close();
    $conn->close();
} else {
    $response = array(
        'success' => false,
        'message' => 'Paramètre manquant : numéroCompte.'
    );
    echo json_encode($response);
}
?>