<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/Post.php');

class PostController {

    // ========== ADD POST AVEC ID UTILISATEUR ==========
    public function addPost(Post $post, $userId = null) {
        $sql = "INSERT INTO post (contenu, date_post, image, id_utilisateur, likes, signalements) 
                VALUES (:contenu, :date_post, :image, :id_utilisateur, 0, 0)";
        $db = config::getConnexion();
        $datePost = $post->getDatePost() ?? date('Y-m-d H:i:s');
        
        // Si userId non fourni, essayer de le prendre dans la session
        if ($userId === null && session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        if ($userId === null) {
            $userId = 1; // Fallback (à éviter en prod)
        }

        try {
            $query = $db->prepare($sql);
            return $query->execute([
                'contenu' => $post->getContenu(),
                'date_post' => $datePost,
                'image' => $post->getImage(),
                'id_utilisateur' => $userId
            ]);
        } catch (Exception $e) {
            error_log('PostController::addPost error: ' . $e->getMessage());
            return false;
        }
    }

    // ========== LIST POSTS AVEC NOM UTILISATEUR ==========
    public function listPosts() {
        $sql = "SELECT p.*, u.nom as user_nom, u.avatar_style as user_avatar 
                FROM post p
                LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_user
                ORDER BY p.date_post DESC";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->execute();
        $results = $req->fetchAll(PDO::FETCH_ASSOC);
        
        $posts = [];
        foreach ($results as $row) {
            $post = new Post(
                $row['id_post'],
                $row['contenu'],
                $row['date_post'],
                $row['image'],
                $row['id_utilisateur'],
                $row['likes'] ?? 0,
                $row['signalements'] ?? 0
            );
            $post->setUserNom($row['user_nom'] ?? 'Utilisateur');
            $post->setUserAvatar($row['user_avatar'] ?? 'default');
            $posts[] = $post;
        }
        return $posts;
    }

    // ========== GET POST BY ID AVEC NOM UTILISATEUR ==========
    public function getPostById($id_post) {
        $sql = "SELECT p.*, u.nom as user_nom,  u.avatar_style as user_avatar 
                FROM post p
                LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_user
                WHERE p.id_post = :id_post";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        $req->execute();
        $row = $req->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $post = new Post(
                $row['id_post'],
                $row['contenu'],
                $row['date_post'],
                $row['image'],
                $row['id_utilisateur'],
                $row['likes'] ?? 0,
                $row['signalements'] ?? 0
            );
            $post->setUserNom($row['user_nom'] ?? 'Utilisateur');
            $post->setUserAvatar($row['user_avatar'] ?? 'default');
            return $post;
        }
        return null;
    }

    // ========== UPDATE POST ==========
    public function updatePost(Post $post, $id_post) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE post SET 
                    contenu = :contenu,
                    date_post = :date_post,
                    image = :image,
                    id_utilisateur = :id_utilisateur
                WHERE id_post = :id'
            );
            return $query->execute([
                'id' => $id_post,
                'contenu' => $post->getContenu(),
                'date_post' => $post->getDatePost(),
                'image' => $post->getImage(),
                'id_utilisateur' => $post->getIdUtilisateur()
            ]);
        } catch (PDOException $e) {
            error_log('PostController::updatePost error: ' . $e->getMessage());
            return false;
        }
    }

    // ========== DELETE POST ==========
    public function deletePost($id_post) {
        $sql = "DELETE FROM post WHERE id_post = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id_post);
        try {
            $req->execute();
            return true;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // ========== LIKE METHODS ==========
    public function addLike($id_post) {
        $sql = "UPDATE post SET likes = likes + 1 WHERE id_post = :id_post";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        return $req->execute();
    }

    public function removeLike($id_post) {
        $sql = "UPDATE post SET likes = likes - 1 WHERE id_post = :id_post AND likes > 0";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        return $req->execute();
    }

    // ========== SIGNALEMENT METHODS ==========
    public function signalerPost($id_post) {
        $sql = "UPDATE post SET signalements = signalements + 1 WHERE id_post = :id_post";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        return $req->execute();
    }

    public function retirerSignalement($id_post) {
        $sql = "UPDATE post SET signalements = GREATEST(signalements - 1, 0) WHERE id_post = :id_post";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_post', $id_post, PDO::PARAM_INT);
        return $req->execute();
    }

    // ========== MÉTHODES INNOVANTES ==========
    public function listPostsByPopularite() {
        $sql = "SELECT p.*, u.nom as user_nom,
                       COUNT(r.id_rep) AS nb_reponses,
                       (p.likes + COUNT(r.id_rep) * 2) AS score_popularite
                FROM post p
                LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_user
                LEFT JOIN reponse r ON p.id_post = r.id_post
                GROUP BY p.id_post
                ORDER BY score_popularite DESC";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopPosts($limit = 5) {
        $sql = "SELECT p.*, u.nom as user_nom, COUNT(r.id_rep) AS nb_reponses
                FROM post p
                LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_user
                LEFT JOIN reponse r ON p.id_post = r.id_post
                GROUP BY p.id_post
                ORDER BY p.likes DESC
                LIMIT :limit";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':limit', $limit, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPostsSignales($seuil = 1) {
        $sql = "SELECT p.*, u.nom as user_nom, COUNT(r.id_rep) AS nb_reponses
                FROM post p
                LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_user
                LEFT JOIN reponse r ON p.id_post = r.id_post
                WHERE p.signalements >= :seuil
                GROUP BY p.id_post
                ORDER BY p.signalements DESC";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':seuil', $seuil, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatsGlobales() {
        $db = config::getConnexion();
        $req = $db->query("SELECT 
            COUNT(*) AS total_posts,
            SUM(likes) AS total_likes,
            SUM(signalements) AS total_signalements,
            COUNT(CASE WHEN DATE(date_post) = CURDATE() THEN 1 END) AS posts_today,
            COUNT(CASE WHEN image != '' AND image IS NOT NULL THEN 1 END) AS avec_media
            FROM post");
        $stats = $req->fetch(PDO::FETCH_ASSOC);
        $req2 = $db->query("SELECT COUNT(*) AS total_reponses FROM reponse");
        $rep = $req2->fetch(PDO::FETCH_ASSOC);
        $stats['total_reponses'] = $rep['total_reponses'];
        return $stats;
    }

    public function exportCSV() {
        $sql = "SELECT p.id_post, p.contenu, p.date_post, p.likes, p.signalements, COUNT(r.id_rep) AS nb_reponses
                FROM post p
                LEFT JOIN reponse r ON p.id_post = r.id_post
                GROUP BY p.id_post
                ORDER BY p.date_post DESC";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->execute();
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
 * Extraire les hashtags d'un texte
 */
public function extractHashtags($contenu) {
    preg_match_all('/#([a-zA-Z0-9_]+)/', $contenu, $matches);
    return $matches[1] ?? [];
}
}
?>