<?php
session_start();
header('Content-Type: application/json');

require_once '../../config.php';

try {
    $conn = config::getConnexion();
    
    $sql = "SELECT a.id_avis, a.contenu, a.note, a.date_avis, a.image, a.id_utilisateur,
                   COALESCE(u.nom, 'Utilisateur') as auteur
            FROM avis a
            LEFT JOIN utilisateur u ON a.id_utilisateur = u.id_user
            ORDER BY a.date_avis DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'avis' => $avis]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur BDD: ' . $e->getMessage()]);
}
?>