<?php
header('Content-Type: application/json');

include 'db/db_connect.php';


if (isset($_POST['numeroCompte']) ) {
   
    $telephone = $_POST['numeroCompte'];
 

    $conn = connecterBDD();

    $sql = "SELECT * FROM utilisateurs WHERE telephone = '$telephone'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {

        $row = $result->fetch_assoc();
        $telephone = $row['telephone'];
        $nom =  $row['nom'];
        $response = array(
            'success' => true,
            'telephone' => $telephone,
            'nom' => $nom
        );
        echo json_encode($response);
    } else {
        $response = array(
            'success' => false,
            'message' => 'Utilisateur inexistant.'
        );
        echo json_encode($response);
    }
    $conn->close();
} else {
    $response = array(
        'success' => false,
        'message' => 'Paramètres manquants.'
    );
    echo json_encode($response);
}
?>
