<?php 
include 'db/db_connect.php';

header('Content-Type: application/json');

if (!isset($_POST['numeroCompte']) || !isset($_POST['montant']) || !isset($_POST['type']) || !isset($_POST['description'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Tous les champs sont requis'
    ]);
    exit;
}

$numeroCompte = $_POST['numeroCompte'];
$montant = floatval($_POST['montant']);
$type = strtolower($_POST['type']); // "revenu" ou "depense"
$description = $_POST['description'];

if (!in_array($type, ['revenu', 'depense'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Type de transaction invalide'
    ]);
    exit;
}

$conn = connecterBDD();

try {
    $conn->begin_transaction();

    // 1. Récupérer le solde actuel
    $stmt = $conn->prepare("SELECT solde FROM comptes WHERE numero_compte = ?");
    $stmt->bind_param("s", $numeroCompte);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Compte introuvable");
    }

    $row = $result->fetch_assoc();
    $ancienSolde = floatval($row['solde']);

    // 2. Calculer le nouveau solde
        $nouveauSolde = ($type === "revenu") ? $ancienSolde + $montant : $ancienSolde - $montant;

        if ($nouveauSolde < 0 && $type === "depense") {
        throw new Exception("Solde insuffisant pour effectuer cette dépense");
    }

    // 3. Mettre à jour le solde
    $stmt = $conn->prepare("UPDATE comptes SET solde = ? WHERE numero_compte = ?");
    $stmt->bind_param("ds", $nouveauSolde, $numeroCompte);
    if (!$stmt->execute()) {
        throw new Exception("Erreur lors de la mise à jour du solde");
    }

    // 4. Insérer la transaction
    $stmt = $conn->prepare("INSERT INTO transactions (numero_compte, type, montant, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $numeroCompte, $type, $montant, $description);
    if (!$stmt->execute()) {
        throw new Exception("Erreur lors de l'enregistrement de la transaction");
    }

            $conn->commit();

            echo json_encode([
                'success' => true,
        'message' => "Transaction enregistrée avec succès",
        'nouveauSolde' => number_format($nouveauSolde, 2, '.', '')
            ]);

} catch (Exception $e) {
            $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
