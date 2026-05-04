<?php
require_once '../../config/db.php';

header('Content-Type: application/json');

try {
    // Consultations dans les prochaines 24h
    $stmt = $pdo->prepare(
        "SELECT * FROM consultation 
         WHERE statut = 'planifiée' 
         AND date_consultation BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
         ORDER BY date_consultation ASC"
    );
    $stmt->execute();
    $prochaines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consultations terminées sans ordonnance
    $stmt2 = $pdo->prepare(
        "SELECT c.* FROM consultation c
         LEFT JOIN ordonnance o ON c.id_consultation = o.id_consultation
         WHERE c.statut = 'terminée' AND o.id_ordonnance IS NULL"
    );
    $stmt2->execute();
    $sansOrdonnance = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $notifications = [];

    foreach ($prochaines as $c) {
        $diff = strtotime($c['date_consultation']) - time();
        $heures = round($diff / 3600, 1);
        $notifications[] = [
            'type'    => 'warning',
            'icon'    => 'fa-clock',
            'message' => 'Consultation #' . $c['id_consultation'] . ' dans ' . $heures . 'h',
            'link'    => 'edit_consultation.php?id=' . $c['id_consultation'],
            'time'    => date('d/m H:i', strtotime($c['date_consultation']))
        ];
    }

    foreach ($sansOrdonnance as $c) {
        $notifications[] = [
            'type'    => 'danger',
            'icon'    => 'fa-file-prescription',
            'message' => 'Consultation #' . $c['id_consultation'] . ' terminée sans ordonnance',
            'link'    => 'add_ordonnance.php',
            'time'    => date('d/m H:i', strtotime($c['date_consultation']))
        ];
    }

    echo json_encode([
        'count' => count($notifications),
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    echo json_encode(['count' => 0, 'notifications' => []]);
}
?>