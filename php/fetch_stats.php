<?php
include 'db/db_connect.php';
header('Content-Type: application/json');

$numeroCompte = $_POST['numeroCompte'];

$conn = connecterBDD();

// Récupérer l'ID utilisateur
$stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE telephone = ?");
$stmt->bind_param("s", $numeroCompte);
$stmt->execute();
$idUtilisateur = $stmt->get_result()->fetch_assoc()['id'];

// Requêtes statistiques
$reqRevenus = $conn->prepare("SELECT SUM(montant) FROM transactions t JOIN categories c ON t.id_categorie = c.id WHERE t.id_utilisateur = ? AND c.type = 'revenu'");
$reqRevenus->bind_param("i", $idUtilisateur);
$reqRevenus->execute();
$revenus = $reqRevenus->get_result()->fetch_column() ?? 0;

$reqDepenses = $conn->prepare("SELECT SUM(montant) FROM transactions t JOIN categories c ON t.id_categorie = c.id WHERE t.id_utilisateur = ? AND c.type = 'depense'");
$reqDepenses->bind_param("i", $idUtilisateur);
$reqDepenses->execute();
$depenses = $reqDepenses->get_result()->fetch_column() ?? 0;

echo json_encode([
    'success' => true,
    'solde' => $revenus - $depenses,
    'revenus' => $revenus,
    'depenses' => $depenses
]);
?>