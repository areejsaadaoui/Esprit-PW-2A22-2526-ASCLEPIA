<?php
require_once '../../config/db.php';
require_once '../../controllers/ConsultationController.php';

$controller = new ConsultationController($pdo);
$consultations = $controller->getAllConsultations();

// Headers pour téléchargement Excel
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="consultations_' . date('Ymd_His') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM pour Excel (affichage correct des caractères français)
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// En-têtes colonnes
fputcsv($output, [
    'ID',
    'Date Consultation',
    'Diagnostique',
    'Notes',
    'Statut',
    'Date Création'
], ';');

// Données
foreach ($consultations as $c) {
    fputcsv($output, [
        $c->getIdConsultation(),
        $c->getDateConsultation(),
        $c->getDiagnostique(),
        $c->getNotes(),
        $c->getStatut(),
        date('d/m/Y')
    ], ';');
}

fclose($output);
exit;
?>