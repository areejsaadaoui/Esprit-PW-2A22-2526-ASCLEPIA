<?php
class Consultation {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query(
            "SELECT * FROM consultation ORDER BY date_consultation DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM consultation WHERE id_consultation = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO consultation (date_consultation, diagnostique, notes)
             VALUES (?, ?, ?)"
        );
        return $stmt->execute([
            $data['date_consultation'],
            $data['diagnostique'],
            $data['notes']
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE consultation SET date_consultation=?, diagnostique=?, notes=?
             WHERE id_consultation=?"
        );
        return $stmt->execute([
            $data['date_consultation'],
            $data['diagnostique'],
            $data['notes'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare(
            "DELETE FROM consultation WHERE id_consultation = ?"
        );
        return $stmt->execute([$id]);
    }
}
?>