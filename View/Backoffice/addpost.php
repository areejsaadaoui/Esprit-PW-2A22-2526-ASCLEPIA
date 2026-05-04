<?php
include '../../Controller/PostController.php';
require_once __DIR__ . '/../../Model/Post.php';

$error = '';
$success = '';
$postC = new PostController();


function uploadImage($file) {
    
    $targetDir = __DIR__ . '/uploads/posts/';
    
    // Créer le dossier s'il n'existe pas
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
        
        $id_utilisateur = 1;
        
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
       
// Récupérer l’URL du GIF 
$gifUrl = $_POST['gif_url'] ?? '';
if (!empty($gifUrl)) {
    $imagePath = $gifUrl;  // stockée dans la colonne `image`
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
// Récupérer l'URL du GIF (si sélectionné)
$gifUrl = '';
if (isset($_POST['gif_url']) && !empty($_POST['gif_url'])) {
    $gifUrl = $_POST['gif_url'];
}

// Ensuite, dans la création du post, remplace l'imagePath par le GIF si présent
if (!empty($gifUrl)) {
    $imagePath = $gifUrl;  // Stocke l'URL du GIF
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
            transition: transform 0.2s;
        }
        .image-preview:hover {
            transform: scale(1.02);
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
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }
        .remove-image:hover {
            transform: scale(1.15);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        .file-input-wrapper {
            position: relative;
            display: inline-flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .file-input-wrapper input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        /* Barre amélioration IA */
        .ai-enhance-bar {
            margin: 15px 0;
            padding: 12px 16px;
            background: #f0f9ff;
            border-radius: 12px;
            border-left: 4px solid #0ea5e9;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
        }
        .ai-enhance-bar:hover {
            background: #e0f2fe;
            border-left-color: #0284c7;
        }
        .ai-enhance-bar .btn-accent {
            background: linear-gradient(135deg, #3aedc3, #3ae4ed);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .ai-enhance-bar .btn-accent:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
        }
        body.dark-mode .ai-enhance-bar {
            background: #1e293b;
            border-left-color: #0ea5e9;
        }
        body.dark-mode .ai-enhance-bar:hover {
            background: #334155;
        }
        /* ===== DARK MODE – VERSION ADDPOST ===== */

body.dark-mode {
    background: #1a1a2e !important;
}

/* Section principale */
body.dark-mode .section-padding {
    background: #1a1a2e !important;
}

/* Carte du formulaire */
body.dark-mode .card {
    background: #16213e !important;
    border-color: #2d2d44 !important;
}

/* Titres et textes */
body.dark-mode .section-title,
body.dark-mode .section-desc,
body.dark-mode .section-tag,
body.dark-mode .card h2,
body.dark-mode .form-label {
    color: white !important;
}

body.dark-mode .form-label i {
    color: #0ea5e9 !important;
}

/* Champs de formulaire */
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

/* Navbar */
body.dark-mode .navbar {
    background: #0f0f1a !important;
    border-bottom: 1px solid #2d2d44 !important;
}

body.dark-mode .navbar .navbar-name,
body.dark-mode .navbar .nav-link {
    color: #e0e0e0 !important;
}

body.dark-mode .navbar .nav-link:hover,
body.dark-mode .navbar .nav-link.active {
    color: #0ea5e9 !important;
}

/* Footer */
body.dark-mode .footer {
    background: #0f0f1a !important;
    border-top: 1px solid #2d2d44 !important;
}

body.dark-mode .footer p,
body.dark-mode .footer .footer-section h4,
body.dark-mode .footer .footer-links a {
    color: #c0c0d0 !important;
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

body.dark-mode .btn-outline-white {
    border-color: #475569 !important;
    color: #cbd5e1 !important;
}

body.dark-mode .btn-outline-white:hover {
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

/* Upload d'image */
body.dark-mode .file-input-wrapper .btn-outline {
    background: #0f0f1a !important;
}

body.dark-mode .image-preview {
    background: #0f0f1a !important;
    border-color: #2d2d44 !important;
}

/* Bouton toggle flottant */
.theme-toggle {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #0ea5e9;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 1.3rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    transition: all 0.3s;
    z-index: 9999;
}

.theme-toggle:hover {
    transform: scale(1.1);
}
/* ===== MODAL GIPHY — HARMONISÉE AVEC RÉPONSES ===== */
#gifModal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

#gifModal > div {
    background: white;
    border-radius: 20px;
    padding: 30px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: slideUp 0.3s ease;
}

#gifModal h3 {
    margin: 0 0 20px 0;
    font-size: 1.5rem;
    color: #1e293b;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.gif-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #64748b;
    transition: all 0.2s;
}

.gif-modal-close:hover {
    color: #ef4444;
    transform: scale(1.1);
}

#gifSearchInput {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 1rem;
    margin-bottom: 15px;
    transition: all 0.2s;
}

#gifSearchInput:focus {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14,165,233,0.1);
    outline: none;
}

#gifResults {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
    min-height: 200px;
}

.gif-result {
    width: 100%;
    height: 150px;
    border-radius: 10px;
    object-fit: cover;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid #e2e8f0;
}

.gif-result:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

/* Dark mode modal */
body.dark-mode #gifModal > div {
    background: #16213e;
}

body.dark-mode #gifModal h3 {
    color: #e0e0e0;
}

body.dark-mode #gifSearchInput {
    background: #0f0f1a;
    border-color: #2d2d44;
    color: white;
}

body.dark-mode #gifSearchInput:focus {
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14,165,233,0.2);
}

body.dark-mode .gif-result {
    border-color: #2d2d44;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
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
                        <div class="ai-enhance-bar" style="margin: 15px 0;">
    <button type="button" id="btnEnhanceAI" class="btn btn-accent">
        <i class="fas fa-magic"></i> ✨ Améliorer avec l’IA
    </button>
    <small style="color:#64748b; margin-left:10px;">
        Rend le texte plus professionnel et engageant
    </small>
</div>
                        
                        <!-- Champ Upload Image -->
                        <div class="form-group">
                            <label for="imageUpload" class="form-label">
                                <i class="fa-solid fa-image"></i> Ajouter une image (optionnel)
                            </label>
                           
                            <div class="file-input-wrapper">
                                <button type="button" class="btn btn-outline" onclick="document.getElementById('imageUpload').click()" style="gap: 8px; display: inline-flex; align-items: center;">
                                    <i class="fa-solid fa-upload"></i> Choisir une image
                                </button>
                              
                                <button type="button" class="btn btn-outline" id="openGifBtn" style="gap: 8px; display: inline-flex; align-items: center; margin-left: 10px;">
                                    <i class="fa-solid fa-film"></i> Ajouter un GIF   
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
                       <!-- aiPreview EST HORS DU FORM pour éviter la soumission -->
                        <!-- Boutons -->
                        <div style="display: flex; gap: 16px; margin-top: 32px; flex-wrap: wrap;">
                            <button type="submit" id="submitBtn" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-paper-plane"></i> Publier
                            </button>
                            <a href="../frontoffice/index.html" class="btn btn-outline-white btn-lg" style="background: var(--gray); border-color: var(--gray);">
                                <i class="fa-solid fa-arrow-left"></i> Retour
                            </a>
                        </div>
                        <!-- Bouton GIPHY -->


<!-- Modal GIPHY — Harmonisée -->
<div id="gifModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 20px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 1.5rem;">🎬 Rechercher un GIF</h3>
            <button type="button" class="gif-modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <input type="text" id="gifSearchInput" placeholder="Chercher des GIFs... (ex: chat, sourire, merci)" class="form-control" style="margin-bottom: 15px; width: 100%; padding: 10px 15px; border: 2px solid #e2e8f0; border-radius: 10px;">
        
        <div id="gifResults" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; min-height: 200px;">
            <div style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px 20px;">
                🔍 Tapez pour chercher des GIFs...
            </div>
        </div>
    </div>
</div>

<!-- Champ caché pour stocker l'URL du GIF -->
<input type="hidden" id="gifUrl" name="gif_url" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ✨ AI PREVIEW MODAL — Version compacte, dégradé bleu/vert, en haut au centre -->
<div id="aiPreview" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
     background:rgba(0,0,0,0.5); backdrop-filter:blur(3px); z-index:10000; justify-content:center; align-items:flex-start; padding-top:80px; pointer-events:all;">
    <div style="background:white; border-radius:24px; width:90%; max-width:500px; overflow:hidden; animation:fadeInScale 0.3s ease; margin:0 auto;" onclick="event.stopPropagation()">
        
        <div style="background:linear-gradient(135deg, #0ea5e9, #10b981); padding:16px 20px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="fas fa-magic" style="color:white; font-size:1.2rem;"></i>
                <span style="color:white; font-weight:600; flex:1;">Amélioration IA</span>
                <span id="aiCorrectionsBadge" style="background:rgba(255,255,255,0.2); color:white; font-size:0.65rem; padding:3px 8px; border-radius:20px;"></span>
            </div>
        </div>
        
        <div style="padding:20px;">
            <div style="background:#f0fdf4; border-radius:16px; padding:16px; font-size:0.9rem; line-height:1.6; color:#1e293b;" id="aiPreviewText"></div>
            
            <div style="margin-top:20px; display:flex; gap:10px; justify-content:flex-end;">
                <button onclick="rejectAI()" style="background:#f1f5f9; border:none; border-radius:30px; padding:8px 20px; cursor:pointer; font-weight:500;">Annuler</button>
                <button onclick="acceptAI()" style="background:linear-gradient(135deg, #0ea5e9, #10b981); border:none; border-radius:30px; padding:8px 24px; color:white; cursor:pointer; font-weight:600;">Accepter</button>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row" style="gap: 48px;">
            <div style="flex: 0 0 260px;">
                <div class="footer-brand">
                    <div class="navbar-brand" style="margin-bottom: 16px;">
                        <div class="navbar-logo">⚕️</div>
                        <div class="navbar-name" style="font-size: 1.2rem;">ASC<span class="text-primary">LEPIA</span></div>
                    </div>
                    <p>Votre plateforme médicale</p>
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

<script src="../Frontoffice/add.js?v=<?= time() ?>"></script>
<button class="theme-toggle" id="themeToggle">
    <i class="fas fa-moon"></i>
</button>
<script>
// Dark Mode - addpost.php
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
// ===== GIPHY INTEGRATION =====
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

// Recherche de GIFs avec délai
let searchTimeout;
if (gifSearchInput) {
    gifSearchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (!query) {
            gifResults.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px 20px;">🔍 Tapez pour chercher des GIFs...</div>';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            gifResults.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #0ea5e9; padding: 40px 20px;"><i class="fas fa-spinner fa-spin"></i> Recherche...</div>';
            
            fetch(`https://api.giphy.com/v1/gifs/search?api_key=${GIPHY_API_KEY}&q=${encodeURIComponent(query)}&limit=20&rating=g`)
                .then(response => response.json())
                .then(data => {
                    if (data.data.length === 0) {
                        gifResults.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px 20px;">😢 Aucun GIF trouvé</div>';
                        return;
                    }
                    
                    gifResults.innerHTML = '';
                    data.data.forEach(gif => {
                        const img = document.createElement('img');
                        img.src = gif.images.fixed_height_small.url;
                        img.alt = gif.title;
                        img.classList.add('gif-result');
                        img.style.height = '150px';
                        img.style.objectFit = 'cover';
                        
                        img.addEventListener('click', () => {
                            selectGifFromModal(gif.images.original.url, gif.images.fixed_height_small.url);
                        });
                        
                        gifResults.appendChild(img);
                    });
                })
                .catch(error => {
                    console.error('Erreur GIPHY:', error);
                    gifResults.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #ef4444; padding: 40px 20px;">❌ Erreur de chargement</div>';
                });
        }, 300);
    });
}

// Sélectionner un GIF depuis la modale
function selectGifFromModal(fullUrl, thumbUrl) {
    gifUrlInput.value = fullUrl;
    
    // Afficher l'aperçu dans le conteneur
    const previewContainer = document.getElementById('imagePreviewContainer');
    if (previewContainer) {
        previewContainer.innerHTML = `
            <div class="image-preview-container">
                <img src="${thumbUrl}" class="image-preview" style="max-width: 200px; max-height: 200px; border-radius: 12px; border: 2px solid #e2e8f0; padding: 4px; background: white;">
                <div class="remove-image" onclick="removeGif()" title="Supprimer">
                    <i class="fa-solid fa-times"></i>
                </div>
            </div>
        `;
    }
    
    gifModal.style.display = 'none';
    gifSearchInput.value = '';
    gifResults.innerHTML = '<div style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px 20px;">🔍 Tapez pour chercher des GIFs...</div>';
    
    showToastAddPost('🎬 GIF sélectionné !', '#ec4899');
}

// Supprimer le GIF 
function removeGif() {
    gifUrlInput.value = '';
    const previewContainer = document.getElementById('imagePreviewContainer');
    if (previewContainer) {
        previewContainer.innerHTML = '';
    }
    showToastAddPost('❌ GIF supprimé', '#64748b');
}

//  notifications pour le formulaire de post
function showToastAddPost(msg, color = '#0f172a') {
    const t = document.createElement('div');
    t.textContent = msg;
    Object.assign(t.style, {
        position: 'fixed', bottom: '25px', left: '50%',
        transform: 'translateX(-50%)',
        background: color, color: 'white',
        padding: '10px 24px', borderRadius: '30px',
        zIndex: '99999', fontSize: '0.9rem', fontWeight: '600',
        boxShadow: '0 4px 20px rgba(0,0,0,0.15)',
        animation: 'fadeInScale 0.3s ease'
    });
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2500);
}

    function showAIToast(msg, color) {
        const t = document.createElement('div');
        t.textContent = msg;
        Object.assign(t.style, {
            position: 'fixed', bottom: '24px', left: '50%',
            transform: 'translateX(-50%)',
            background: color, color: '#fff',
            padding: '10px 24px', borderRadius: '30px',
            zIndex: '99999', fontWeight: '600', fontSize: '0.9rem',
            boxShadow: '0 4px 20px rgba(0,0,0,0.15)'
        });
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 3000);
    }
    

</script>

</body>
</html>