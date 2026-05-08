<?php

require_once __DIR__ . '/../models/Consultation.php';

class ConsultationController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllConsultations(): array {
        $stmt = $this->pdo->query(
            "SELECT * FROM consultation ORDER BY date_consultation DESC"
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([self::class, 'rowToConsultation'], $rows);
    }

    public function getConsultationById(int $id): ?Consultation {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM consultation WHERE id_consultation = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? self::rowToConsultation($row) : null;
    }

    public function existsByDate(string $date_consultation, ?int $excludeId = null): bool {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM consultation WHERE date_consultation = ? AND id_consultation != ?"
            );
            $stmt->execute([$date_consultation, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM consultation WHERE date_consultation = ?"
            );
            $stmt->execute([$date_consultation]);
        }

        return $stmt->fetchColumn() > 0;
    }

    public function createConsultation(Consultation $consultation): bool {
        $stmt = $this->pdo->prepare(
            "INSERT INTO consultation (date_consultation, diagnostique, notes, statut)
             VALUES (?, ?, ?, ?)"
        );

        return $stmt->execute([
            $consultation->getDateConsultation(),
            $consultation->getDiagnostique(),
            $consultation->getNotes(),
            $consultation->getStatut(),
        ]);
    }

    public function updateConsultation(Consultation $consultation): bool {
        if ($consultation->getIdConsultation() === null) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE consultation SET date_consultation = ?, diagnostique = ?, notes = ?, statut = ?
             WHERE id_consultation = ?"
        );

        return $stmt->execute([
            $consultation->getDateConsultation(),
            $consultation->getDiagnostique(),
            $consultation->getNotes(),
            $consultation->getStatut(),
            $consultation->getIdConsultation(),
        ]);
    }

    public function deleteConsultation(int $id): bool {
        $stmt = $this->pdo->prepare(
            "DELETE FROM consultation WHERE id_consultation = ?"
        );

        return $stmt->execute([$id]);
    }

    private static function rowToConsultation(array $row): Consultation {
        return Consultation::fromArray($row);
    }
}

?>