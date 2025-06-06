<?php
include 'db/db_connect.php';
header('Content-Type: application/json');

if (!isset($_POST['numeroCompte'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Numéro de compte manquant'
    ]);
    exit;
}

$numeroCompte = $_POST['numeroCompte'];
$conn = connecterBDD();

// Récupérer le solde actuel
$stmt = $conn->prepare("SELECT solde FROM comptes WHERE numero_compte = ?");
$stmt->bind_param("s", $numeroCompte);
$stmt->execute();
$result = $stmt->get_result();
$solde = $result->fetch_assoc()['solde'] ?? 0;

// Calculer les revenus
$stmt = $conn->prepare("SELECT COALESCE(SUM(montant), 0) as total FROM transactions WHERE numero_compte = ? AND type = 'revenu'");
$stmt->bind_param("s", $numeroCompte);
$stmt->execute();
$revenus = $stmt->get_result()->fetch_assoc()['total'];

// Calculer les dépenses
$stmt = $conn->prepare("SELECT COALESCE(SUM(montant), 0) as total FROM transactions WHERE numero_compte = ? AND type = 'depense'");
$stmt->bind_param("s", $numeroCompte);
$stmt->execute();
$depenses = $stmt->get_result()->fetch_assoc()['total'];

echo json_encode([
    'success' => true,
    'solde' => number_format($solde, 2, '.', ''),
    'revenus' => number_format($revenus, 2, '.', ''),
    'depenses' => number_format($depenses, 2, '.', '')
]);

$conn->close();
?>