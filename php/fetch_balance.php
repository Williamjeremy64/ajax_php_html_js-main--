<?php
include 'db/db_connect.php';

// Vérification de la présence des données POST
if (isset($_POST['numeroCompte'])) {
    $numeroCompte = $_POST['numeroCompte'];

    
    $conn = connecterBDD();


    $sql = "SELECT solde FROM utilisateurs WHERE telephone = '$numeroCompte'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $solde = $row["solde"];
        $response = array(
            'success' => true,
            'solde' => $solde
        );
        echo json_encode($response);
    } else {
        $response = array(
            'success' => false,
            'message' => 'Aucun compte trouvé avec ce numéro.'
        );
        echo json_encode($response);
    }
    
    $conn->close();
} else {
    $response = array(
        'success' => false,
        'message' => 'Paramètre manquant : numéroCompte.'
    );
    echo json_encode($response);
}
?>