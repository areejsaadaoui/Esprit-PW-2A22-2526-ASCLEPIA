<?php
session_start();
header('Content-Type: application/json');

require_once '../../config.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID manquant']);
    exit;
}

$id_avis = intval($_GET['id']);

try {
    $conn = config::getConnexion();
    $sql = "SELECT a.id_avis, a.contenu, a.note, a.date_avis, a.image, a.id_utilisateur,
                   u.nom as auteur
            FROM avis a
            LEFT JOIN utilisateur u ON a.id_utilisateur = u.id_user
            WHERE a.id_avis = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id_avis]);
    $avis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'avis' => $avis]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>