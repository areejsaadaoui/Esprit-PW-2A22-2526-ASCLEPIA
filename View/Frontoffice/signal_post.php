<?php
/**
 * ASCLEPIA — Signalement de post (version debug)
 */
require_once __DIR__ . '/../../Controller/PostController.php';
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode invalide']);
    exit;
}

$id_post = isset($_POST['id_post']) ? (int)$_POST['id_post'] : 0;
$action  = $_POST['action'] ?? 'signal';

if (!$id_post) {
    echo json_encode(['success' => false, 'error' => 'ID invalide']);
    exit;
}

$postC = new PostController();

// Test direct en BDD pour voir si ça marche
try {
    $db = config::getConnexion();
    
    if ($action === 'signal') {
        // Incrémentation directe en BDD
        $sql = "UPDATE post SET signalements = signalements + 1 WHERE id_post = :id_post";
        $req = $db->prepare($sql);
        $req->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        $ok = $req->execute();
        
        // Récupérer le nouveau nombre
        $sql2 = "SELECT signalements FROM post WHERE id_post = :id_post";
        $req2 = $db->prepare($sql2);
        $req2->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        $req2->execute();
        $newCount = $req2->fetchColumn();
        
        echo json_encode([
            'success' => $ok,
            'signalements' => $newCount,
            'action' => $action,
            'message' => 'Signalement ajouté !'
        ]);
        exit;
        
    } else {
        // Désincrémentation
        $sql = "UPDATE post SET signalements = GREATEST(signalements - 1, 0) WHERE id_post = :id_post";
        $req = $db->prepare($sql);
        $req->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        $ok = $req->execute();
        
        $sql2 = "SELECT signalements FROM post WHERE id_post = :id_post";
        $req2 = $db->prepare($sql2);
        $req2->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        $req2->execute();
        $newCount = $req2->fetchColumn();
        
        echo json_encode([
            'success' => $ok,
            'signalements' => $newCount,
            'action' => $action,
            'message' => 'Signalement retiré !'
        ]);
        exit;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}
?>