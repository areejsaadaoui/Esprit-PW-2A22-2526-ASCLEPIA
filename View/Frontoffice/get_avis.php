<?php
require_once __DIR__ . '/../../Controller/AvisController.php';

header('Content-Type: application/json');

$response = ['success' => false, 'avis' => null];

try {
    $id_avis = (int)($_GET['id'] ?? 0);

    if ($id_avis <= 0) {
        throw new Exception('ID avis invalide');
    }

    $controller = new AvisController();
    $avis = $controller->getAvisById($id_avis);

    if ($avis) {
        $response['success'] = true;
        $response['avis'] = $avis;
    } else {
        throw new Exception('Avis non trouvé');
    }

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>