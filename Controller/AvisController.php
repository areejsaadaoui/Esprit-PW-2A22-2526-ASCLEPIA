<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Avis.php';

class AvisController {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

    public function addAvis($contenu, $image, $id_utilisateur = null, $note = null) {
        $sql = "INSERT INTO avis (contenu, date_avis, image, id_utilisateur, note)
                VALUES (:contenu, NOW(), :image, :id_utilisateur, :note)";
        $req = $this->db->prepare($sql);
        return $req->execute([
            ':contenu' => $contenu,
            ':image' => $image,
            ':id_utilisateur' => $id_utilisateur,
            ':note' => $note,
        ]);
    }

    // Liste des avis (pour le front)
    public function listAvis($limit = 50) {
        $limit = (int)$limit;
        if ($limit <= 0) $limit = 50;

        $sql = "SELECT id_avis, contenu, date_avis, image, note
                FROM avis
                ORDER BY date_avis DESC
                LIMIT :lim";
        $req = $this->db->prepare($sql);
        $req->bindValue(':lim', $limit, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }


    public function deleteAvis($id_avis) {
        $sql = "DELETE FROM avis WHERE id_avis = :id";
        $req = $this->db->prepare($sql);
        $req->bindValue(':id', $id_avis, PDO::PARAM_INT);
        return $req->execute();
    }

  
    public function updateAvis($id_avis, $contenu, $note = null) {
        $sql = "UPDATE avis SET contenu = :contenu, note = :note WHERE id_avis = :id";
        $req = $this->db->prepare($sql);
        return $req->execute([
            ':id' => $id_avis,
            ':contenu' => $contenu,
            ':note' => $note,
        ]);
    }

    public function getAvisById($id_avis) {
        $sql = "SELECT a.*, u.id_user, u.nom
                FROM avis a
                LEFT JOIN utilisateur u ON a.id_utilisateur = u.id_user
                WHERE a.id_avis = :id";
        $req = $this->db->prepare($sql);
        $req->bindValue(':id', $id_avis, PDO::PARAM_INT);
        $req->execute();
        return $req->fetch(PDO::FETCH_ASSOC);
    }
    public function countAvis() {
        $sql = "SELECT COUNT(*) as total FROM avis";
        $req = $this->db->query($sql);
        $result = $req->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

}
?>
