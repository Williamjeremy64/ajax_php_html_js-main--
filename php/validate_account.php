<?php
header('Content-Type: application/json');

include 'db/db_connect.php';

if (isset($_POST['numeroCompte']) && isset($_POST['code'])) {
    $telephone = $_POST['numeroCompte'];
    $code = $_POST['code'];

    $conn = connecterBDD();

    $sql = "SELECT * FROM utilisateurs WHERE telephone = ? AND code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $telephone, $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response = array(
            'success' => true,
            'telephone' => $row['telephone'],
            'nom' => $row['nom']
        );
        echo json_encode($response);
    } else {
        $response = array(
            'success' => false,
            'message' => 'Code ou numéro de compte incorrect.'
        );
        echo json_encode($response);
    }
    $stmt->close();
    $conn->close();
} else {
    $response = array(
        'success' => false,
        'message' => 'Paramètres manquants.'
    );
    echo json_encode($response);
}
?>
