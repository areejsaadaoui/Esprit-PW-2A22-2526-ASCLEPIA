<?php
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';

$error = '';
$success = '';
$postC = new PostController();

// Fonction pour uploader l'image dans le dossier View
function uploadImage($file) {
    // Dossier dans View/uploads/posts/
    $targetDir = __DIR__ . '/uploads/posts/';
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($file['name']);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Vérifier si c'est une vraie image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return ['error' => 'Le fichier n\'est pas une image valide.'];
    }
    
    // Vérifier la taille (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'L\'image ne doit pas dépasser 5MB.'];
    }
    
    // Autoriser certains formats
    $allowedFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($imageFileType, $allowedFormats)) {
        return ['error' => 'Seuls les formats JPG, JPEG, PNG, GIF et WEBP sont autorisés.'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        // Retourner le chemin relatif depuis View
        return ['success' => 'uploads/posts/' . $fileName];
    } else {
        return ['error' => 'Erreur lors de l\'upload de l\'image.'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier seulement le contenu
    if (isset($_POST['contenu']) && !empty($_POST['contenu'])) {
        
        // Nettoyer et sécuriser les données
        $contenu = htmlspecialchars($_POST['contenu']);
        // ID utilisateur fixe = 1 (pas de session)
        $id_utilisateur = 1;
        
        // Gérer l'upload de l'image
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['image']);
            if (isset($uploadResult['error'])) {
                $error = $uploadResult['error'];
            } else {
                $imagePath = $uploadResult['success'];
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error = "Erreur lors de l'upload de l'image.";
        }
        
        if (empty($error)) {
            $post = new Post(
                null,
                $contenu,
                date('Y-m-d H:i:s'),
                $imagePath,
                $id_utilisateur
            );
            
            $result = $postC->addPost($post);
            
            if ($result) {
                $success = "Post publié avec succès !";
                echo '<script>setTimeout(function(){ window.location.href = "../Frontoffice/postlist.php"; }, 2000);</script>';
            } else {
                $error = "Erreur lors de la publication.";
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
    <title>ASCLEPIA — Ajouter un post au forum</title>
    
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
        <a href="../Frontoffice/postlist.php" class="nav-link active">Communauté</a>
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
                Nouvelle publication
            </div>
            <h2 class="section-title">Partagez votre expérience</h2>
            <p class="section-desc">
                Posez une question ou partagez votre témoignage avec la communauté.
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
                                      rows="10" 
                                      ></textarea>
                            <div class="form-error" id="contentError"></div>
                            <div class="form-hint" id="nbchar">0 / 2000 caractères (minimum 10 requis)</div>
                        </div>
                        
                        <!-- Champ Upload Image -->
                        <div class="form-group">
                            <label for="imageUpload" class="form-label">
                                <i class="fa-solid fa-image"></i> Ajouter une image (optionnel)
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
                        
                        <!-- Champs cachés -->
                        <input type="hidden" name="date_post" value="<?php echo date('Y-m-d H:i:s'); ?>">
                        <input type="hidden" name="id_utilisateur" value="1">
                       
                        <!-- Boutons -->
                        <div style="display: flex; gap: 16px; margin-top: 32px; flex-wrap: wrap;">
                            <button type="submit" id="submitBtn" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-paper-plane"></i> Publier le post
                            </button>
                            <button type="button" id="resetBtn" class="btn btn-outline btn-lg">
                                <i class="fa-solid fa-eraser"></i> Effacer
                            </button>
                            <a href="../frontoffice/index.html" class="btn btn-outline-white btn-lg" style="background: var(--gray); border-color: var(--gray);">
                                <i class="fa-solid fa-arrow-left"></i> Retour à l'accueil
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
        <div class="row" style="gap: 48px;">
            <div style="flex: 0 0 260px;">
                <div class="footer-brand">
                    <div class="navbar-brand" style="margin-bottom: 16px;">
                        <div class="navbar-logo">⚕️</div>
                        <div class="navbar-name" style="font-size: 1.2rem;">ASC<span class="text-primary">LEPIA</span></div>
                    </div>
                    <p>Votre plateforme médicale complète.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul class="footer-links">
                        <li><a href="consultation.php">Consultations</a></li>
                        <li><a href="addpharmacie.php">Pharmacies</a></li>
                        <li><a href="assurance.php">Assurances</a></li>
                        <li><a href="../Frontoffice/postlist.php">Forum santé</a></li>
                    </ul>
                </div>
            </div>
            <div class="col">
                <div class="footer-section">
                    <h4>Liens utiles</h4>
                    <ul class="footer-links">
                        <li><a href="../frontoffice/index.html">Accueil</a></li>
                        <li><a href="../frontoffice/login.html">S'inscrire</a></li>
                        <li><a href="../frontoffice/login.html">Se connecter</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 ASCLEPIA. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<script src="../Frontoffice/add.js"></script>

</body>
</html>