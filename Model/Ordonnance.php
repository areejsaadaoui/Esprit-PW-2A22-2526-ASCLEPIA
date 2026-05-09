<?php
class Ordonnance {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM ordonnance WHERE id_ordonnance = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    public function getByConsultation($id_consultation) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM ordonnance WHERE id_consultation = ?"
        );
        $stmt->execute([$id_consultation]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO ordonnance (medicaments, instructions, duree_traitement, id_consultation, signature)
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['medicaments'],
            $data['instructions'],
            $data['duree_traitement'],
            $data['id_consultation'],
            $data['signature']
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