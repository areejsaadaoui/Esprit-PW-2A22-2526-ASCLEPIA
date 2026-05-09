<?php
session_start(); // ← AJOUTER CETTE LIGNE

include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';

// Vérification de la session utilisateur
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userNom = $_SESSION['user_nom'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$userRole = $_SESSION['user_role'] ?? '';
$userId = $_SESSION['user_id'] ?? null;
$userAvatar = $_SESSION['user_avatar'] ?? 'default';

// Si l'utilisateur est connecté, récupérer l'avatar depuis la base si nécessaire
if ($isLoggedIn && $userId) {
    try {
        require_once '../../config.php';
        $conn = config::getConnexion();
        $sql = "SELECT avatar_style FROM utilisateur WHERE id_user = :id_user";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id_user' => $userId]);
        $row = $stmt->fetch();
        if ($row && !empty($row['avatar_style'])) {
            $userAvatar = $row['avatar_style'];
            $_SESSION['user_avatar'] = $userAvatar;
        }
    } catch (Exception $e) {
        $userAvatar = 'default';
    }
}

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

// ===== VÉRIFICATION DES DROITS =====
// 🔒 Seul le propriétaire du post peut le modifier (admin NON autorisé)
$isOwner = ($post->getIdUtilisateur() == $userId);

if (!$isOwner) {
    header('Location: ../Frontoffice/postlist.php?error=unauthorized');
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
        // Gestion du GIF (si l'utilisateur en a sélectionné un)
$gifUrl = $_POST['gif_url'] ?? '';
if (!empty($gifUrl)) {
    $imagePath = $gifUrl;
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
    <link rel="stylesheet" href="../assets/css/avatar.css">
    <style>
            /* ===== AVATAR ===== */
        .post-avatar {
            width: 36px !important;
            height: 36px !important;
            font-size: 0.9rem !important;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            animation: floatSoft 3s infinite;
            transition: 0.2s;
        }
        
        .post-avatar:hover {
            transform: scale(1.1);
        }
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
        /* ===== DARK MODE – VERSION MODIFPOST (COMPLÉMENT) ===== */

/* Carte principale */
body.dark-mode .card {
    background: #16213e !important;
    border-color: #2d2d44 !important;
}
/* Section principale */
body.dark-mode .section-padding {
    background: #1a1a2e !important;
}

/* Titres de section */
body.dark-mode .section-title,
body.dark-mode .section-desc,
body.dark-mode .section-tag {
    color: white !important;
}

body.dark-mode .section-tag i {
    color: #0ea5e9 !important;
}

/* Labels et textes du formulaire */
body.dark-mode .form-label {
    color: white !important;
}

body.dark-mode .form-label i {
    color: #0ea5e9 !important;
}

/* Champ contenu */
body.dark-mode .form-control {
    background: #0f0f1a !important;
    border-color: #2d2d44 !important;
    color: white !important;
}

body.dark-mode .form-control:focus {
    border-color: #0ea5e9 !important;
    box-shadow: 0 0 0 3px rgba(14,165,233,0.2) !important;
}

body.dark-mode .form-control::placeholder {
    color: #a0a0c0 !important;
}

/* Compteur de caractères */
body.dark-mode .form-hint {
    color: #a0a0c0 !important;
}

/* Image actuelle */
body.dark-mode .current-image {
    background: #0f0f1a !important;
    border: 1px solid #2d2d44 !important;
}

body.dark-mode .checkbox-label span {
    color: #e0e0e0 !important;
}

/* Boutons */
body.dark-mode .btn-primary {
    background: #0ea5e9 !important;
    border: none !important;
    color: white !important;
}

body.dark-mode .btn-outline {
    border-color: #475569 !important;
    color: #cbd5e1 !important;
}

body.dark-mode .btn-outline:hover {
    background: #334155 !important;
    color: white !important;
}

/* Alertes */
body.dark-mode .alert-danger {
    background: #7f1a1a !important;
    color: #fecaca !important;
    border-color: #991b1b !important;
}

body.dark-mode .alert-success {
    background: #14532d !important;
    color: #bbf7d0 !important;
    border-color: #166534 !important;
}

/* Aperçu image */
body.dark-mode .image-preview {
    background: #0f0f1a !important;
    border-color: #2d2d44 !important;
}
/* Modal GIPHY */
.gif-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
    justify-content: center;
    align-items: center;
}

.gif-modal-content {
    background: white;
    width: 80%;
    max-width: 800px;
    max-height: 80vh;
    border-radius: 20px;
    overflow: hidden;
    animation: fadeInScale 0.3s ease;
}

.gif-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid var(--border);
}

.gif-modal-header h3 {
    margin: 0;
    color: var(--primary);
}

.gif-modal-close {
    font-size: 28px;
    cursor: pointer;
    transition: 0.2s;
}

.gif-modal-close:hover {
    color: var(--danger);
}

.gif-modal-body {
    padding: 20px;
}

#gifSearchInput {
    margin-bottom: 20px;
}

.gif-results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
    max-height: 50vh;
    overflow-y: auto;
}

.gif-result {
    width: 100%;
    border-radius: 12px;
    cursor: pointer;
    transition: transform 0.2s;
}

.gif-result:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/* Dark mode pour la modal */
body.dark-mode .gif-modal-content {
    background: #16213e;
    border-color: #2d2d44;
}

body.dark-mode .gif-modal-header {
    border-bottom-color: #2d2d44;
}

body.dark-mode .gif-modal-header h3 {
    color: #0ea5e9;
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
    </style>
</head>
<body>
<nav class="navbar" id="navbar">
    <a href="../frontoffice/indexp.php" class="navbar-brand">
        <div class="navbar-logo"></div>
        <div class="navbar-name">ASC<span>LEPIA</span></div>
    </a>
    <div class="nav-links" id="navLinks">
        <a href="../front/indexp.php#accueil" class="nav-link">Accueil</a>
        <a href="../front/indexp.php#services" class="nav-link">Services</a>
        <a href="../front/indexp.php#pharmacies" class="nav-link">Pharmacies</a>
        <a href="../front/indexp.php#assurances" class="nav-link">Assurances</a>
        <a href="../Frontoffice/postlist.php" class="nav-link active">Communauté</a>
        <a href="../front/indexp.php#avis" class="nav-link">Plus</a>
    </div>
    <div class="nav-actions">
        <?php if ($isLoggedIn): ?>
    <div style="display: flex; align-items: center; gap: 12px;">
        <div class="avatar-css avatar-<?= htmlspecialchars($userAvatar) ?> small"
             style="width: 36px; height: 36px; border-radius: 50%;"></div>
        <span style="color: white;">Bonjour, <?= htmlspecialchars($userNom) ?></span>
        <a href="../back/logout.php" class="btn btn-outline-white btn-sm">
            <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
        <?php else: ?>
            <a href="../frontoffice/login.html" class="btn btn-outline-white btn-sm">Se connecter</a>
            <a href="../frontoffice/loginuser.html" class="btn btn-primary btn-sm">S'inscrire</a>
        <?php endif; ?>
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
                            <!-- Bouton GIPHY -->
<div class="form-group">
    <button type="button" class="btn btn-outline" id="openGifBtn">
        <i class="fa-brands fa-giphy"></i> Changer / Ajouter un GIF
    </button>
</div>

<!-- Modal GIPHY -->
<div id="gifModal" class="gif-modal">
    <div class="gif-modal-content">
        <div class="gif-modal-header">
            <h3><i class="fa-brands fa-giphy"></i> Rechercher un GIF</h3>
            <span class="gif-modal-close">&times;</span>
        </div>
        <div class="gif-modal-body">
            <input type="text" id="gifSearchInput" placeholder="Rechercher un GIF (ex: bonjour, rire, santé...)" class="form-control">
            <div id="gifResults" class="gif-results-grid"></div>
        </div>
    </div>
</div>

<!-- Champ caché pour stocker l'URL du GIF -->
<input type="hidden" id="gifUrl" name="gif_url" value="<?= htmlspecialchars($post->getImage() ?? '') ?>">
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

<button class="theme-toggle" id="themeToggle">
    <i class="fas fa-moon"></i>
</button>

<script>
    // Dark Mode - modifreponse.php
   (function() {
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;
    
    const body = document.body;
    
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
        themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    } else {
        body.classList.remove('dark-mode');
        themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    }
    
    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        
        if (body.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            localStorage.setItem('theme', 'light');
            themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        }
    });
})();
</script>


<script>
// ===== GIPHY INTEGRATION POUR MODIFPOST =====
const GIPHY_API_KEY = 'yqqP8DPRQPO1Uph9Zg5EAptLuvWfRx0U';

const openGifBtn = document.getElementById('openGifBtn');
const gifModal = document.getElementById('gifModal');
const closeModal = document.querySelector('.gif-modal-close');
const gifSearchInput = document.getElementById('gifSearchInput');
const gifResults = document.getElementById('gifResults');
const gifUrlInput = document.getElementById('gifUrl');

// Ouvrir la modal
if (openGifBtn) {
    openGifBtn.addEventListener('click', () => {
        gifModal.style.display = 'flex';
        gifSearchInput.value = '';
        gifResults.innerHTML = '<div style="text-align:center; padding:40px;">🔍 Recherchez un GIF...</div>';
    });
}

// Fermer la modal
if (closeModal) {
    closeModal.addEventListener('click', () => {
        gifModal.style.display = 'none';
    });
}

// Fermer en cliquant à l'extérieur
window.addEventListener('click', (e) => {
    if (e.target === gifModal) {
        gifModal.style.display = 'none';
    }
});

// Recherche de GIFs
if (gifSearchInput) {
    gifSearchInput.addEventListener('keyup', function() {
        const query = this.value.trim();
        if (query.length < 2) return;
        
        gifResults.innerHTML = '<div style="text-align:center; padding:40px;">🔍 Chargement des GIFs...</div>';
        
        fetch(`https://api.giphy.com/v1/gifs/search?api_key=${GIPHY_API_KEY}&q=${encodeURIComponent(query)}&limit=20&rating=g`)
            .then(response => response.json())
            .then(data => {
                if (data.data.length === 0) {
                    gifResults.innerHTML = '<div style="text-align:center; padding:40px;">😢 Aucun GIF trouvé</div>';
                    return;
                }
                
                gifResults.innerHTML = '';
                data.data.forEach(gif => {
                    const img = document.createElement('img');
                    img.src = gif.images.fixed_height_small.url;
                    img.alt = gif.title;
                    img.classList.add('gif-result');
                    img.style.cursor = 'pointer';
                    img.style.borderRadius = '12px';
                    img.style.width = '100%';
                    
                    img.addEventListener('click', () => {
                        gifUrlInput.value = gif.images.original.url;
                        
                        const previewContainer = document.getElementById('imagePreviewContainer');
                        if (previewContainer) {
                            previewContainer.innerHTML = `
                                <div class="image-preview-container">
                                    <img src="${gif.images.fixed_height_small.url}" class="image-preview" style="max-width: 200px; max-height: 200px; border-radius: 12px; border: 2px solid #e2e8f0; padding: 4px; background: white;">
                                    <div class="remove-image" onclick="removeGif()">
                                        <i class="fa-solid fa-times"></i>
                                    </div>
                                </div>
                            `;
                        }
                        
                        gifModal.style.display = 'none';
                    });
                    
                    gifResults.appendChild(img);
                });
            })
            .catch(error => {
                console.error('Erreur GIPHY:', error);
                gifResults.innerHTML = '<div style="text-align:center; padding:40px;">❌ Erreur de chargement</div>';
            });
    });
}

// Supprimer le GIF
function removeGif() {
    document.getElementById('gifUrl').value = '';
    const previewContainer = document.getElementById('imagePreviewContainer');
    if (previewContainer) {
        previewContainer.innerHTML = '';
    }
}
</script>
</body>
</html>