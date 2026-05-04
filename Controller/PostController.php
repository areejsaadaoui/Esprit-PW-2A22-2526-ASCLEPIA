<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/Post.php');

class PostController {

    public function addPost(Post $post) {
        $sql = "INSERT INTO post (contenu, date_post, image, id_utilisateur) VALUES (:contenu, :date_post, :image, :id_utilisateur)";
        $db = config::getConnexion();
        $datePost = $post->getDatePost() ?? date('Y-m-d H:i:s');
        $userId = 1;

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

    public function listPosts() {
    $sql = "SELECT * FROM post ORDER BY date_post DESC";
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->execute();
    $results = $req->fetchAll(PDO::FETCH_ASSOC);
    
    $posts = [];
    foreach ($results as $row) {
        $posts[] = new Post(
            $row['id_post'],
            $row['contenu'],
            $row['date_post'],
            $row['image'],
            $row['id_utilisateur'],
            $row['likes']  // ← AJOUTÉ
        );
    }
    return $posts;
}

public function getPostById($id_post) {
    $sql = "SELECT * FROM post WHERE id_post = :id_post";
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->bindValue(':id_post', $id_post, PDO::PARAM_INT);
    $req->execute();
    $row = $req->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        return new Post(
            $row['id_post'],
            $row['contenu'],
            $row['date_post'],
            $row['image'],
            $row['id_utilisateur'],
            $row['likes']  // ← AJOUTÉ
        );
    }
    return null;
}

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

// =====================================================
// MÉTHODES INNOVANTES — ASCLEPIA v2
// =====================================================

/**
 * Signaler un post (flag) — incrément du compteur signalements
 */
public function signalerPost($id_post) {
    $sql = "UPDATE post SET signalements = signalements + 1 WHERE id_post = :id_post";
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->bindValue(':id_post', $id_post, PDO::PARAM_INT);
    return $req->execute();
}

/**
 * Retirer un signalement
 */
public function retirerSignalement($id_post) {
    $sql = "UPDATE post SET signalements = GREATEST(signalements - 1, 0) WHERE id_post = :id_post";
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->bindValue(':id_post', $id_post, PDO::PARAM_INT);
    return $req->execute();
}




/**
 * Liste des posts par popularité = likes + (nb_réponses * 2)
 */
public function listPostsByPopularite() {
    $sql = "SELECT p.*, 
                   COUNT(r.id_rep) AS nb_reponses,
                   (p.likes + COUNT(r.id_rep) * 2) AS score_popularite
            FROM post p
            LEFT JOIN reponse r ON p.id_post = r.id_post
            GROUP BY p.id_post
            ORDER BY score_popularite DESC";
    $db = config::getConnexion();
    $req = $db->prepare($sql);
    $req->execute();
    $results = $req->fetchAll(PDO::FETCH_ASSOC);

    $posts = [];
    foreach ($results as $row) {
        $post = new Post(
            $row['id_post'], $row['contenu'], $row['date_post'],
            $row['image'], $row['id_utilisateur'], $row['likes']
        );
        $post->setNbReponses($row['nb_reponses'] ?? 0);
        $post->setScorePopularite($row['score_popularite'] ?? 0);
        $posts[] = $post;
    }
    return $posts;
}

/**
 * Top posts avec le plus de likes (podium)
 */
public function getTopPosts($limit = 5) {
    $sql = "SELECT p.*, COUNT(r.id_rep) AS nb_reponses
            FROM post p
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

/**
 * Posts signalés (modération)
 */
public function getPostsSignales($seuil = 1) {
    $sql = "SELECT p.*, COUNT(r.id_rep) AS nb_reponses
            FROM post p
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

/**
 * Statistiques globales du forum
 */
public function getStatsGlobales() {
    $db = config::getConnexion();

    // Total posts, likes, signalements
    $req = $db->query("SELECT 
        COUNT(*) AS total_posts,
        SUM(likes) AS total_likes,
        SUM(signalements) AS total_signalements,
        COUNT(CASE WHEN DATE(date_post) = CURDATE() THEN 1 END) AS posts_today,
        COUNT(CASE WHEN image != '' AND image IS NOT NULL THEN 1 END) AS avec_media
        FROM post");
    $stats = $req->fetch(PDO::FETCH_ASSOC);

    // Total réponses
    $req2 = $db->query("SELECT COUNT(*) AS total_reponses FROM reponse");
    $rep = $req2->fetch(PDO::FETCH_ASSOC);
    $stats['total_reponses'] = $rep['total_reponses'];

    // Heure de pointe (distribution horaire)
    $req3 = $db->query("SELECT HOUR(date_post) AS heure, COUNT(*) AS nb FROM post GROUP BY HOUR(date_post) ORDER BY heure");
    $stats['distribution_horaire'] = $req3->fetchAll(PDO::FETCH_ASSOC);

    return $stats;
}

/**
 * Export CSV de tous les posts
 */
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
}