<?php
require_once __DIR__ . '/../../Controller/AvisController.php';

header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $note = (int)($_POST['note'] ?? 0);
    $contenu = trim($_POST['contenu'] ?? '');
    $id_utilisateur = 1; // Utilisateur par défaut

    // Validation
    if ($note < 1 || $note > 5) {
        throw new Exception('Note invalide');
    }
    if (strlen($contenu) < 10) {
        throw new Exception('Contenu trop court (min 10 caractères)');
    }
    if (strlen($contenu) > 2000) {
        throw new Exception('Contenu trop long (max 2000 caractères)');
    }

    // Gestion de l'image
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . '/uploads/avis/';
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;

        // Validation image
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            throw new Exception('Format image non autorisé');
        }
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            throw new Exception('Image trop lourde (max 5MB)');
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            throw new Exception('Erreur upload image');
        }
        $imagePath = 'uploads/avis/' . $fileName;
    }

    $controller = new AvisController();
    $result = $controller->addAvis($contenu, $imagePath, $id_utilisateur, $note);

    if ($result) {
        $response['success'] = true;
    } else {
        throw new Exception('Erreur lors de l\'ajout');
    }

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>