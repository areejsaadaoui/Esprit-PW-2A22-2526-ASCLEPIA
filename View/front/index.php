<?php
header('Content-Type: application/json');

$host     = 'localhost';
$dbname   = 'fleliss';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare(
        "SELECT nom, adresse, telephone, description
         FROM utilisateur
         WHERE role = 'medecin'
         ORDER BY nom ASC"
    );
    $stmt->execute();
    $medecins = $stmt->fetchAll();

    echo json_encode(['success' => true, 'medecins' => $medecins]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>