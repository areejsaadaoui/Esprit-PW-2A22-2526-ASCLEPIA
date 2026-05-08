<?php
session_start();
header('Content-Type: application/json');

require_once '../../config.php';

$response = ['success' => false, 'error' => ''];

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    $response['error'] = 'Vous devez être connecté pour donner un avis';
    echo json_encode($response);
    exit;
}

$id_utilisateur = $_SESSION['user_id'];
$userNom = $_SESSION['user_nom'] ?? 'Utilisateur';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenu = trim($_POST['contenu'] ?? '');
    $note = intval($_POST['note'] ?? 0);
    $date_avis = date('Y-m-d H:i:s');
    $image = '';
    
    if ($note < 1 || $note > 5) {
        $response['error'] = 'Note invalide (1-5)';
        echo json_encode($response);
        exit;
    }
    
    if (strlen($contenu) < 10) {
        $response['error'] = 'L\'avis doit contenir au moins 10 caractères';
        echo json_encode($response);
        exit;
    }
    
    // Gestion de l'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/avis/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = 'uploads/avis/' . $fileName;
        }
    }
    
    try {
        $conn = config::getConnexion();
        $sql = "INSERT INTO avis (contenu, note, date_avis, image, id_utilisateur) 
                VALUES (:contenu, :note, :date_avis, :image, :id_utilisateur)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':contenu' => $contenu,
            ':note' => $note,
            ':date_avis' => $date_avis,
            ':image' => $image,
            ':id_utilisateur' => $id_utilisateur
        ]);
        
        $response['success'] = true;
    } catch (Exception $e) {
        $response['error'] = $e->getMessage();
    }
}

echo json_encode($response);
?>