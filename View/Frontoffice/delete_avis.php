<?php
session_start();
header('Content-Type: application/json');

require_once '../../config.php';

$response = ['success' => false, 'error' => ''];

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $response['error'] = 'Non connecté';
    echo json_encode($response);
    exit;
}

$id_utilisateur = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? '';
$isAdmin = ($userRole === 'admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_avis'])) {
    $id_avis = intval($_POST['id_avis']);
    
    try {
        $conn = config::getConnexion();
        
        // Récupérer l'avis
        $check = $conn->prepare("SELECT id_utilisateur, image FROM avis WHERE id_avis = :id");
        $check->execute([':id' => $id_avis]);
        $avis = $check->fetch(PDO::FETCH_ASSOC);
        
        if (!$avis) {
            $response['error'] = 'Avis introuvable';
            echo json_encode($response);
            exit;
        }
        
        // Vérifier les droits : admin OU propriétaire
        if (!$isAdmin && $avis['id_utilisateur'] != $id_utilisateur) {
            $response['error'] = 'Non autorisé - Vous ne pouvez supprimer que vos propres avis';
            echo json_encode($response);
            exit;
        }
        
        // Supprimer l'image si elle existe
        if (!empty($avis['image']) && file_exists(__DIR__ . '/' . $avis['image'])) {
            unlink(__DIR__ . '/' . $avis['image']);
        }
        
        $stmt = $conn->prepare("DELETE FROM avis WHERE id_avis = :id");
        $stmt->execute([':id' => $id_avis]);
        
        $response['success'] = true;
    } catch (Exception $e) {
        $response['error'] = $e->getMessage();
    }
}

echo json_encode($response);
?>