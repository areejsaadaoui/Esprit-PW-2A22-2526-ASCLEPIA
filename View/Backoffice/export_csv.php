<?php
/**
 * ASCLEPIA — Export CSV des posts
 * Accessible depuis le dashboard admin
 */
require_once __DIR__ . '/../../Controller/PostController.php';

// Sécurité basique (à remplacer par session en prod)
$postC = new PostController();
$rows  = $postC->exportCSV();

// Headers HTTP pour téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="asclepia_posts_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// BOM UTF-8 pour Excel (affichage correct des accents)
fputs($output, "\xEF\xBB\xBF");

// En-têtes CSV
fputcsv($output, [
    'ID', 'Contenu', 'Date publication', 'Likes',
    'Signalements', 'Sentiment', 'Nb réponses'
], ';');

foreach ($rows as $row) {
    fputcsv($output, [
        $row['id_post'],
        // Supprimer les retours à la ligne dans le CSV
        str_replace(["\r\n", "\r", "\n"], ' ', strip_tags($row['contenu'])),
        $row['date_post'],
        $row['likes'],
        $row['signalements'] ?? 0,
        $row['sentiment'] ?? 'non analysé',
        $row['nb_reponses']
    ], ';');
}

fclose($output);
exit;
