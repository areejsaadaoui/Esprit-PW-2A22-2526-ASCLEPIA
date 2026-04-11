<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/Post.php');

class PostController {

    
    public function searchPosts($keyword) {
        $sql = "SELECT p.*, u.nom AS nom_auteur, u.role
                FROM post p
                LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_user
                WHERE p.contenu LIKE :keyword OR u.nom LIKE :keyword
                ORDER BY p.date_post DESC";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['keyword' => '%' . $keyword . '%']);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function deletePost($id_post) {
        $sql = "DELETE FROM post WHERE id_post = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id_post);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addPost(Post $post) {
        $sql = "INSERT INTO post (contenu, date_post, image, id_utilisateur) VALUES (:contenu, :date_post, :image, :id_utilisateur)";
        $db = config::getConnexion();
        $datePost = $post->getDatePost() ?? date('Y-m-d H:i:s');
        $userId = $post->getIdUtilisateur() ?? 0;

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


    public function showPost($id_post) {
        $sql = "SELECT p.*, u.nom AS nom_auteur, u.role
                FROM post p
                LEFT JOIN utilisateur u ON p.id_utilisateur = u.id_user
                WHERE p.id_post = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);

        try {
            $query->execute(['id' => $id_post]);
            $post = $query->fetch();
            if ($post && empty($post['nom_auteur'])) {
                $post['nom_auteur'] = 'Anonyme';
            }
            return $post;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    public function uploadImage($file, $uploadDir) {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize      = 2 * 1024 * 1024; // 2 Mo
 
        // Vérif MIME réelle (pas juste l'extension)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
 
        if (!in_array($mime, $allowedMimes)) return false;
        if ($file['size'] > $maxSize) return false;
 
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'post_' . uniqid() . '.' . strtolower($ext);
        $uploadDir = rtrim($uploadDir, DIRECTORY_SEPARATOR . '/\\') . DIRECTORY_SEPARATOR;

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return false;
            }
        }

        $dest = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $filename;
        }
        return false;
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

    public function countPosts() {
        $sql = "SELECT COUNT(*) as total FROM post";
        $db = config::getConnexion();
        try {
            $query = $db->query($sql);
            $result = $query->fetch();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
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

   
}
?>