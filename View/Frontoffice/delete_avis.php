<?php
require_once __DIR__ . '/../../Controller/AvisController.php';

header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $id_avis = (int)($_POST['id_avis'] ?? 0);

    if ($id_avis <= 0) {
        throw new Exception('ID avis invalide');
    }

    $controller = new AvisController();
    $result = $controller->deleteAvis($id_avis);

    if ($result) {
        $response['success'] = true;
    } else {
        throw new Exception('Erreur lors de la suppression');
    }

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
