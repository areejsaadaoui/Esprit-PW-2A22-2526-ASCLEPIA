<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Reponse.php';

class ReponseController {

   

    public function addReponse(Reponse $reponse) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare("
                INSERT INTO reponse (texte_rep, date_rep, id_utilisateur, id_post)
                VALUES (:texte_rep, :date_rep, :id_utilisateur, :id_post)
            ");
            return $query->execute([
                'texte_rep'      => $reponse->getTexteRep(),
                'date_rep'       => date('Y-m-d H:i:s'),
                'id_utilisateur' => 1,
                'id_post'        => $reponse->getIdPost()
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteReponse($id_rep) {
        try {
            $db = config::getConnexion();
            $req = $db->prepare("DELETE FROM reponse WHERE id_rep = :id");
            $req->bindValue(':id', $id_rep, PDO::PARAM_INT);
            $req->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getReponseById($id_rep) {
        $db = config::getConnexion();
        $sql = "SELECT * FROM reponse WHERE id_rep = :id";
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id_rep, PDO::PARAM_INT);
        $req->execute();
        return $req->fetch(PDO::FETCH_ASSOC);
    }
   public function modifreponse($id_rep, $nouveauTexte) {
     $db = config::getConnexion();
        $sql = "UPDATE reponse SET texte_rep = :texte WHERE id_rep = :id";
        $req = $db->prepare($sql);
        $req->bindValue(':texte', $nouveauTexte, PDO::PARAM_STR);
        $req->bindValue(':id', $id_rep, PDO::PARAM_INT);
        return $req->execute();
    }
 public function getReponsesByPost($id_post) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare("
                SELECT reponse.*, post.contenu AS contenu_post 
                FROM reponse 
                JOIN post ON reponse.id_post = post.id_post 
                WHERE reponse.id_post = :id
            ");
            $query->execute([':id' => $id_post]);
            $results = $query->fetchAll();
            $reponses = [];
            foreach ($results as $row) {
                $reponses[] = new Reponse(
                    $row['id_rep'],
                    $row['texte_rep'],
                    $row['date_rep'],
                    $row['id_utilisateur'],
                    $row['id_post']
                );
            }
            return $reponses;
        } catch (PDOException $e) {
            error_log('getReponsesByPost error: ' . $e->getMessage());
            return [];
        }
    }
  
}
?>