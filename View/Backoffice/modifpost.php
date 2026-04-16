<?php
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';

$error = '';
$success = '';
$postC = new PostController();
$post = null;

// Récupérer l'ID du post
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ../Frontoffice/postlist.php');
    exit;
}

$id_post = (int)$_GET['id'];
$post = $postC->getPostById($id_post);

if (!$post) {
    header('Location: ../Frontoffice/postlist.php');
    exit;
}

// Fonction pour uploader l'image
function uploadImage($file) {
    $targetDir = __DIR__ . '/uploads/posts/';
    
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($file['name']);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['error' => 'Le fichier n\'est pas une image valide.'];
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'L\'image ne doit pas dépasser 5MB.'];
    }
    
    $allowedFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowedFormats)) {
        return ['error' => 'Seuls les formats JPG, JPEG, PNG, GIF et WEBP sont autorisés.'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => 'uploads/posts/' . $fileName];
    } else {
        return ['error' => 'Erreur lors de l\'upload de l\'image.'];
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['contenu']) && !empty($_POST['contenu'])) {
        
        $nouveauContenu = htmlspecialchars($_POST['contenu']);
        $imagePath = $post->getImage();
        
        // Gérer la nouvelle image si uploadée
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if (!empty($imagePath) && file_exists(__DIR__ . '/' . $imagePath)) {
                unlink(__DIR__ . '/' . $imagePath);
            }
            
            $uploadResult = uploadImage($_FILES['image']);
            if (isset($uploadResult['error'])) {
                $error = $uploadResult['error'];
            } else {
                $imagePath = $uploadResult['success'];
            }
        }
        
        // Supprimer l'image si demandé
        if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
            if (!empty($imagePath) && file_exists(__DIR__ . '/' . $imagePath)) {
                unlink(__DIR__ . '/' . $imagePath);
            }
            $imagePath = '';
        }
        
        if (empty($error)) {
            $updatedPost = new Post(
                $post->getIdPost(),
                $nouveauContenu,
                $post->getDatePost(),
                $imagePath,
                $post->getIdUtilisateur()
            );
            
            $result = $postC->updatePost($updatedPost, $id_post);
            
            if ($result) {
                $success = "Post modifié avec succès !";
                echo '<script>setTimeout(function(){ window.location.href = "../Frontoffice/postlist.php"; }, 2000);</script>';
            } else {
                $error = "Erreur lors de la modification.";
            }
        }
    } else {
        $error = "Veuillez remplir le contenu du message.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASCLEPIA — Modifier un post</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/frontoffice.css">
    <style>
        .image-preview-container {
            margin-top: 10px;
            position: relative;
            display: inline-block;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: var(--radius);
            border: 2px solid var(--border);
            padding: 4px;
            background: var(--white);
        }
        .remove-image {
            position: absolute;
            top: -10px;
            right: -10px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
            transition: var(--transition);
        }
        .remove-image:hover {
            transform: scale(1.1);
        }
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .file-input-wrapper input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .current-image {
            margin: 10px 0;
            padding: 10px;
            background: var(--bg);
            border-radius: var(--radius);
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            margin-top: 8px;
        }
    </style>
</head>
<body>

<nav class="navbar" id="navbar">
    <a href="../frontoffice/index.html" class="navbar-brand">
        <div class="navbar-logo"></div>
        <div class="navbar-name">ASC<span>LEPIA</span></div>
    </a>
    <div class="nav-links" id="navLinks">
        <a href="../frontoffice/index.html#accueil" class="nav-link">Accueil</a>
        <a href="../frontoffice/index.html#services" class="nav-link">Services</a>
        <a href="../frontoffice/index.html#pharmacies" class="nav-link">Pharmacies</a>
        <a href="../frontoffice/index.html#assurances" class="nav-link">Assurances</a>
        <a href="../Frontoffice/postlist.php" class="nav-link active">Caummunauté</a>
        <a href="../frontoffice/index.html#avis" class="nav-link">Plus</a>
    </div>
    <div class="nav-actions">
        <a href="../frontoffice/login.html" class="btn btn-outline-white btn-sm">Se connecter</a>
        <a href="../frontoffice/login.html" class="btn btn-primary btn-sm">S'inscrire</a>
        <div class="hamburger" id="hamburger" onclick="toggleMenu()">
            <span></span><span></span><span></span>
        </div>
    </div>
</nav>

<section class="section-padding" style="background: var(--bg); min-height: 80vh;">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">
                <i class="fa-solid fa-pen-to-square"></i>
                Modifier votre post
            </div>
            <h2 class="section-title">Modifier le message</h2>
            <p class="section-desc">
                Modifiez votre publication et partagez à nouveau avec la communauté.
            </p>
        </div>

        <div class="row" style="justify-content: center;">
            <div class="col-6">
                <div class="card" style="padding: 32px;">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="display: flex; margin-bottom: 20px;">
                            <i class="fa-solid fa-circle-exclamation" style="margin-right: 10px;"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" style="display: flex; margin-bottom: 20px;">
                            <i class="fa-solid fa-circle-check" style="margin-right: 10px;"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form id="postForm" method="POST" action="" enctype="multipart/form-data">
                        
                        <!-- Champ Contenu -->
                        <div class="form-group">
                            <label for="postContent" class="form-label">
                                <i class="fa-solid fa-message"></i> Votre message *
                            </label>
                            <textarea id="postContent" 
                                      name="contenu"  
                                      class="form-control" 
                                      rows="10"  ><?php echo htmlspecialchars($post->getContenu()); ?></textarea>
                            <div class="form-hint" id="nbchar"><?php echo strlen($post->getContenu()); ?> / 2000 caractères</div>
                        </div>
                        
                        <!-- Image actuelle -->
                        <?php 
                        $currentImage = $post->getImage();
                        if (!empty($currentImage) && file_exists(__DIR__ . '/' . $currentImage)):
                        ?>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fa-solid fa-image"></i> Image actuelle
                            </label>
                            <div class="current-image">
                                <img src="<?php echo $currentImage; ?>" alt="Image actuelle" style="max-width: 100%; max-height: 150px; border-radius: var(--radius);">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="delete_image" value="1">
                                    <span>Supprimer cette image</span>
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Champ Upload Nouvelle Image -->
                        <div class="form-group">
                            <label for="imageUpload" class="form-label">
                                <i class="fa-solid fa-upload"></i> Changer l'image (optionnel)
                            </label>
                            <div class="file-input-wrapper">
                                <button type="button" class="btn btn-outline" onclick="document.getElementById('imageUpload').click()">
                                    <i class="fa-solid fa-upload"></i> Choisir une image
                                </button>
                                <input type="file" 
                                       id="imageUpload" 
                                       name="image" 
                                       accept="image/jpeg,image/png,image/gif,image/webp"
                                       style="display: none;">
                            </div>
                            <div class="form-hint">
                                <i class="fa-solid fa-info-circle"></i> Formats acceptés: JPG, PNG, GIF, WEBP. Max 5MB
                            </div>
                            <div id="imagePreviewContainer" style="margin-top: 15px;"></div>
                        </div>
                        
                        <!-- Boutons -->
                        <div style="display: flex; gap: 16px; margin-top: 32px; flex-wrap: wrap;">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-save"></i> Enregistrer les modifications
                            </button>
                            <a href="../Frontoffice/postlist.php" class="btn btn-outline btn-lg">
                                <i class="fa-solid fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-bottom">
            <p>© 2026 ASCLEPIA. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<script src="../Frontoffice/add.js"></script>


</body>
</html>