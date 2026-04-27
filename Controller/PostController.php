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
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $posts = [];
        foreach ($results as $row) {
            $posts[] = new Post(
                $row['id_post'],
                $row['contenu'],
                $row['date_post'],
                $row['image'],
                $row['id_utilisateur']
            );
        }
        return $posts;
    }

    public function getPostById($id_post) {
        $sql = "SELECT * FROM post WHERE id_post = :id_post";
        $db = config::getConnexion();
        $stmt = $db->prepare($sql);
        $stmt->execute([':id_post' => $id_post]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return new Post(
                $row['id_post'],
                $row['contenu'],
                $row['date_post'],
                $row['image'],
                $row['id_utilisateur']
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
}
?>