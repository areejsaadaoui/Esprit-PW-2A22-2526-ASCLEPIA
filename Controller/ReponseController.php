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

    public function listrep($id_post) {
    $sql = "SELECT reponse.*, post.contenu AS contenu_post 
    FROM reponse 
    JOIN post 
    ON reponse.id_post = post.id_post 
    WHERE reponse.id_post = :id_post";
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->bindValue(':id_post', $id_post, PDO::PARAM_INT);
    $req->execute();
    
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

// =====================================================
// MÉTHODES INNOVANTES — RÉPONSES v2
// =====================================================

/**
 * Ajouter/supprimer une réaction emoji sur une réponse
 * @param int $id_rep
 * @param string $emoji  ex: "❤️", "😂", "🔥", "👍", "😮"
 * @param string $action "add" | "remove"
 */
public function toggleReaction($id_rep, $emoji, $action = 'add') {
    $db = config::getConnexion();
    // Récupérer les réactions actuelles
    $req = $db->prepare("SELECT reactions FROM reponse WHERE id_rep = :id");
    $req->bindValue(':id', $id_rep, PDO::PARAM_INT);
    $req->execute();
    $row = $req->fetch(PDO::FETCH_ASSOC);
    if (!$row) return false;

    $reactions = [];
    if (!empty($row['reactions'])) {
        $reactions = json_decode($row['reactions'], true) ?? [];
    }

    if ($action === 'add') {
        $reactions[$emoji] = ($reactions[$emoji] ?? 0) + 1;
    } elseif ($action === 'remove') {
        if (isset($reactions[$emoji])) {
            $reactions[$emoji]--;
            if ($reactions[$emoji] <= 0) unset($reactions[$emoji]);
        }
    }

    $json = json_encode($reactions, JSON_UNESCAPED_UNICODE);
    $upd = $db->prepare("UPDATE reponse SET reactions = :r WHERE id_rep = :id");
    $upd->bindValue(':r', $json, PDO::PARAM_STR);
    $upd->bindValue(':id', $id_rep, PDO::PARAM_INT);
    return $upd->execute();
}

/**
 * Statistiques des réponses par post (pour le dashboard)
 */
public function getStatsReponses() {
    $db = config::getConnexion();
    $req = $db->query("
        SELECT p.id_post, LEFT(p.contenu, 50) AS extrait, 
               COUNT(r.id_rep) AS nb_reponses,
               MAX(r.date_rep) AS derniere_reponse
        FROM post p
        LEFT JOIN reponse r ON p.id_post = r.id_post
        GROUP BY p.id_post
        ORDER BY nb_reponses DESC
        LIMIT 10
    ");
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Réponses récentes (toutes confondues, pour la modération)
 */
public function getReponsesRecentes($limit = 20) {
    $db = config::getConnexion();
    $req = $db->prepare("
        SELECT r.*, LEFT(p.contenu, 60) AS extrait_post
        FROM reponse r
        JOIN post p ON r.id_post = p.id_post
        ORDER BY r.date_rep DESC
        LIMIT :limit
    ");
    $req->bindValue(':limit', $limit, PDO::PARAM_INT);
    $req->execute();
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Nombre de réponses par post
 */
public function countReponsesByPost($id_post) {
    $db = config::getConnexion();
    $req = $db->prepare("SELECT COUNT(*) FROM reponse WHERE id_post = :id");
    $req->bindValue(':id', $id_post, PDO::PARAM_INT);
    $req->execute();
    return (int) $req->fetchColumn();
}

}
?>