<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../../config.php';

$idsParam = $_GET['ids'] ?? '';
$ids = array_values(array_filter(array_map('intval', explode(',', $idsParam))));

if (empty($ids)) {
    echo json_encode(new stdClass());
    exit;
}

$db = config::getConnexion();
$in = implode(',', array_fill(0, count($ids), '?'));
$sql = "SELECT id_contrat, statut, date_f FROM contrat WHERE id_contrat IN ($in)";
$stmt = $db->prepare($sql);
$stmt->execute($ids);
$rows = $stmt->fetchAll();

$out = [];
foreach ($rows as $r) {
    $out[(string)$r['id_contrat']] = [
        'statut' => $r['statut'],
        'date_f' => $r['date_f'],
    ];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);

