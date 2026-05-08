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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_avis'])) {
    $id_avis = intval($_POST['id_avis']);
    $contenu = trim($_POST['contenu'] ?? '');
    $note = intval($_POST['note'] ?? 0);
    
    if ($note < 1 || $note > 5) {
        $response['error'] = 'Note invalide';
        echo json_encode($response);
        exit;
    }
    
    if (strlen($contenu) < 10) {
        $response['error'] = 'Minimum 10 caractères';
        echo json_encode($response);
        exit;
    }
    
    try {
        $conn = config::getConnexion();
        
        // Vérifier que l'avis appartient à l'utilisateur connecté
        $check = $conn->prepare("SELECT id_utilisateur FROM avis WHERE id_avis = :id");
        $check->execute([':id' => $id_avis]);
        $avis = $check->fetch(PDO::FETCH_ASSOC);
        
        if (!$avis || $avis['id_utilisateur'] != $id_utilisateur) {
            $response['error'] = 'Vous ne pouvez modifier que vos propres avis';
            echo json_encode($response);
            exit;
        }
        
        $sql = "UPDATE avis SET contenu = :contenu, note = :note WHERE id_avis = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':contenu' => $contenu,
            ':note' => $note,
            ':id' => $id_avis
        ]);
        
        $response['success'] = true;
    } catch (Exception $e) {
        $response['error'] = $e->getMessage();
    }
}

echo json_encode($response);
?>