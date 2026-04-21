<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Avis.php';

class AvisController {
    private $db;

    public function __construct() {
        $this->db = config::getConnexion();
    }

   
    public function addAvis($contenu, $image, $auteur = 'Anonyme', $categorie = 'general', $note = null, $titre = '') {
        $sql = "INSERT INTO avis (contenu, date_avis, image, id_utilisateur, auteur, categorie, note, titre) 
                VALUES (:contenu, NOW(), :image, NULL, :auteur, :categorie, :note, :titre)";
        $req = $this->db->prepare($sql);
        return $req->execute([
            ':contenu' => $contenu,
            ':image' => $image,
            ':auteur' => $auteur,
            ':categorie' => $categorie,
            ':note' => $note,
            ':titre' => $titre
        ]);
    }

    public function deleteAvis($id_avis) {
        $sql = "DELETE FROM avis WHERE id_avis = :id";
        $req = $this->db->prepare($sql);
        $req->bindValue(':id', $id_avis, PDO::PARAM_INT);
        return $req->execute();
    }

  
    public function updateAvis($id_avis, $contenu, $note = null, $titre = '') {
        $sql = "UPDATE avis SET contenu = :contenu, note = :note, titre = :titre WHERE id_avis = :id";
        $req = $this->db->prepare($sql);
        return $req->execute([
            ':id' => $id_avis,
            ':contenu' => $contenu,
            ':note' => $note,
            ':titre' => $titre
        ]);
    }

    public function getAvisById($id_avis) {
        $sql = "SELECT * FROM avis WHERE id_avis = :id";
        $req = $this->db->prepare($sql);
        $req->bindValue(':id', $id_avis, PDO::PARAM_INT);
        $req->execute();
        return $req->fetch(PDO::FETCH_ASSOC);
    }
}
?>