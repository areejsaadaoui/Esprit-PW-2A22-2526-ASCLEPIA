<?php
require_once __DIR__ . '/../../Controller/AvisController.php';

header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $id_avis = (int)($_POST['id_avis'] ?? 0);
    $contenu = trim($_POST['contenu'] ?? '');
    $note = (int)($_POST['note'] ?? 0);

    if ($id_avis <= 0) {
        throw new Exception('ID avis invalide');
    }
    if ($note < 1 || $note > 5) {
        throw new Exception('Note invalide');
    }
    if (strlen($contenu) < 10) {
        throw new Exception('Contenu trop court (min 10 caractères)');
    }
    if (strlen($contenu) > 2000) {
        throw new Exception('Contenu trop long (max 2000 caractères)');
    }

    $controller = new AvisController();
    $result = $controller->updateAvis($id_avis, $contenu, $note);

    if ($result) {
        $response['success'] = true;
    } else {
        throw new Exception('Erreur lors de la mise à jour');
    }

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>