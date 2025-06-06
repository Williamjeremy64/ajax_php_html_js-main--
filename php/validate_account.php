<?php
header('Content-Type: application/json');
session_start();

include 'db/db_connect.php';

if (!isset($_POST['numeroCompte']) || !isset($_POST['code'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Numéro de téléphone et code requis'
    ]);
    exit;
}

$telephone = $_POST['numeroCompte'];
$code = $_POST['code'];

// Vérifier si le code est correct (#9999#)
if ($code !== '#9999#') {
    echo json_encode([
        'success' => false,
        'message' => 'Code incorrect'
    ]);
    exit;
}

$conn = connecterBDD();

try {
    $stmt = $conn->prepare("SELECT id, nom FROM utilisateurs WHERE telephone = ?");
    $stmt->bind_param("s", $telephone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        echo json_encode([
            'success' => true,
            'id' => $row['id'],
            'nom' => $row['nom']
        ]);
    } else {
        // Si l'utilisateur n'existe pas, on le crée
        $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, telephone) VALUES (?, ?)");
        $nom = "Utilisateur " . $telephone;
        $stmt->bind_param("ss", $nom, $telephone);
        
        if ($stmt->execute()) {
            $id = $conn->insert_id;
            $_SESSION['user_id'] = $id;
            echo json_encode([
                'success' => true,
                'id' => $id,
                'nom' => $nom
            ]);
        } else {
            throw new Exception("Erreur lors de la création du compte");
        }
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la vérification du compte: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>
