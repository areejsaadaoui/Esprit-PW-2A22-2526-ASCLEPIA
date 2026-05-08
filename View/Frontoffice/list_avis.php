<?php
require_once __DIR__ . '/../../Controller/AvisController.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $controller = new AvisController();

    // Récupérer le paramètre limit (optionnel)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000;

    // Récupérer tous les avis
    $avisList = $controller->listAvis($limit);

    // Formater les données pour le JSON
    $formattedAvis = [];
    foreach ($avisList as $avis) {
        $formattedAvis[] = [
            'id_avis' => $avis['id_avis'],
            'contenu' => $avis['contenu'],
            'note' => (int)$avis['note'],
            'date_avis' => $avis['date_avis'],
            'id_utilisateur' => 1,
            'image' => $avis['image'],
        ];
    }

    echo json_encode([
        'success' => true,
        'avis' => $formattedAvis,
        'total' => count($formattedAvis)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>