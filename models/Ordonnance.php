<?php
class Ordonnance {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query(
            "SELECT o.*, c.date_consultation, c.diagnostique 
             FROM ordonnance o 
             JOIN consultation c ON o.id_consultation = c.id_consultation 
             ORDER BY o.date_creation DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT o.*, c.date_consultation, c.diagnostique 
             FROM ordonnance o 
             JOIN consultation c ON o.id_consultation = c.id_consultation 
             WHERE o.id_ordonnance = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByConsultation($id_consultation) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM ordonnance WHERE id_consultation = ?"
        );
        $stmt->execute([$id_consultation]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getConsultationsTerminees() {
        $stmt = $this->pdo->query(
            "SELECT c.* FROM consultation c
             LEFT JOIN ordonnance o ON c.id_consultation = o.id_consultation
             WHERE c.statut = 'terminée' AND o.id_ordonnance IS NULL
             ORDER BY c.date_consultation DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO ordonnance (medicaments, instructions, duree_traitement, id_consultation)
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['medicaments'],
            $data['instructions'],
            $data['duree_traitement'],
            $data['id_consultation']
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE ordonnance SET medicaments=?, instructions=?, duree_traitement=?
             WHERE id_ordonnance=?"
        );
        return $stmt->execute([
            $data['medicaments'],
            $data['instructions'],
            $data['duree_traitement'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare(
            "DELETE FROM ordonnance WHERE id_ordonnance = ?"
        );
        return $stmt->execute([$id]);
    }
}
?>