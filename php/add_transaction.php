<?php 
include 'db/db_connect.php';

header('Content-Type: application/json');

function nettoyerTexte($texte) {
    // Convertir en minuscules
    $texte = strtolower($texte);
    // Supprimer les accents
    $texte = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texte);
    return $texte;
}

$numeroCompte = $_POST['numeroCompte'];
$montant = floatval($_POST['montant']);
$typeBrut = $_POST['type'];
$description = $_POST['description'];

$type = nettoyerTexte($typeBrut); // "revenu" ou "depense"

$conn = connecterBDD();

// 1. Récupérer l'utilisateur
$sqlUser = "SELECT id, solde FROM utilisateurs WHERE telephone = '$numeroCompte'";
$resultUser = $conn->query($sqlUser);

if ($resultUser->num_rows > 0) {
    $row = $resultUser->fetch_assoc();
    $idUtilisateur = $row['id'];
    $ancienSolde = floatval($row['solde']);

    // 2. Récupérer une catégorie correspondant au type
    $sqlCat = "SELECT id FROM categories WHERE LOWER(type) = '$type' LIMIT 1";
    $resultCat = $conn->query($sqlCat);

    if ($resultCat->num_rows > 0) {
        $cat = $resultCat->fetch_assoc();
        $idCategorie = $cat['id'];

        // 3. Calcul du nouveau solde
        $nouveauSolde = ($type === "revenu") ? $ancienSolde + $montant : $ancienSolde - $montant;

        if ($nouveauSolde < 0 && $type === "depense") {
            echo json_encode([
                'success' => false,
                'message' => "solde insuffisant pour une depense"
            ]);
            exit;
        }

        $conn->autocommit(FALSE);

        // 4. Mettre à jour le solde et insérer la transaction
        $sqlUpdate = "UPDATE utilisateurs SET solde = '$nouveauSolde' WHERE id = '$idUtilisateur'";
        $sqlInsert = "INSERT INTO transactions (id_utilisateur, id_categorie, montant, date, description) 
                      VALUES ('$idUtilisateur', '$idCategorie', '$montant', NOW(), '$description')";

        if ($conn->query($sqlUpdate) && $conn->query($sqlInsert)) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => "transaction enregistree avec succes",
                'nouveauSolde' => $nouveauSolde
            ]);
        } else {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => "erreur lors de l'enregistrement"
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => "categorie introuvable pour le type '$type'"
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => "utilisateur introuvable"
    ]);
}

$conn->close();
