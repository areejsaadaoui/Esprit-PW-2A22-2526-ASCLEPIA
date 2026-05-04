<?php

require_once __DIR__ . '/../models/Ordonnance.php';

class OrdonnanceController {
    private PDO $pdo;
    private Ordonnance $model;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->model = new Ordonnance($pdo);
    }

    public function getAllOrdonnances(): array {
        $stmt = $this->pdo->query(
            "SELECT o.*, c.date_consultation, c.diagnostique 
             FROM ordonnance o 
             JOIN consultation c ON o.id_consultation = c.id_consultation 
             ORDER BY o.date_creation DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrdonnanceById(int $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT o.*, c.date_consultation, c.diagnostique 
             FROM ordonnance o 
             JOIN consultation c ON o.id_consultation = c.id_consultation 
             WHERE o.id_ordonnance = ?"
        );
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getConsultationsTerminees(): array {
        $stmt = $this->pdo->query(
            "SELECT c.* FROM consultation c
             LEFT JOIN ordonnance o ON c.id_consultation = o.id_consultation
             WHERE c.statut = 'terminée' AND o.id_ordonnance IS NULL
             ORDER BY c.date_consultation DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrdonnanceByConsultation(int $id_consultation): ?array {
        return $this->model->getByConsultation($id_consultation);
    }

    public function createOrdonnance(array $data): bool {
        return $this->model->create($data);
    }

    public function updateOrdonnance(int $id, array $data): bool {
        return $this->model->update($id, $data);
    }

    public function deleteOrdonnance(int $id): bool {
        return $this->model->delete($id);
    }
}

?>