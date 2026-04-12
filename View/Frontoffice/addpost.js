// addpost.js addpost.php et updatepost.php
document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // COMPTEUR DE CARACTÈRES
    // ============================================
    const textarea = document.getElementById('postContent');
    const charCount = document.getElementById('charCount');
    
    if (textarea && charCount) {
        // Mettre à jour le compteur au chargement
        const initialLen = textarea.value.length;
        charCount.textContent = initialLen + ' / 2000 caractères (minimum 10 requis)';
        
        // Couleur initiale
        if (initialLen < 10 && initialLen > 0) {
            charCount.style.color = '#f59e0b';
        } else if (initialLen > 2000) {
            charCount.style.color = '#ef4444';
        } else {
            charCount.style.color = '#64748b';
        }
        
        // Écouter l'événement input
        textarea.addEventListener('input', function() {
            const len = this.value.length;
            charCount.textContent = len + ' / 2000 caractères (minimum 10 requis)';
            
            if (len > 2000) {
                charCount.style.color = '#ef4444';
            } else if (len < 10 && len > 0) {
                charCount.style.color = '#f59e0b';
            } else {
                charCount.style.color = '#64748b';
            }
        });
    }
    // APERÇU DE L'IMAGE (pour updatepost.php)
    const imageInput = document.getElementById('imageUpload');
    const previewContainer = document.getElementById('imagePreviewContainer');
    
    if (imageInput && previewContainer) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('❌ L\'image ne doit pas dépasser 5MB.');
                    imageInput.value = '';
                    return;
                }
                
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('❌ Seuls les formats JPG, PNG, GIF et WEBP sont autorisés.');
                    imageInput.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(ev) {
                    previewContainer.innerHTML = `
                        <div class="image-preview-container" style="margin-top: 10px; position: relative; display: inline-block;">
                            <strong>Nouvelle image :</strong><br>
                            <img src="${ev.target.result}" style="max-width: 200px; max-height: 200px; border-radius: 12px; border: 2px solid #e2e8f0; padding: 4px; background: white;">
                            <div onclick="removePreview()" style="position: absolute; top: -10px; right: -10px; background: #ef4444; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px;">
                                <i class="fa-solid fa-times"></i>
                            </div>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Fonction pour supprimer l'aperçu (accessible globalement)
    window.removePreview = function() {
        const imageInput = document.getElementById('imageUpload');
        const previewContainer = document.getElementById('imagePreviewContainer');
        if (imageInput) imageInput.value = '';
        if (previewContainer) previewContainer.innerHTML = '';
    };
    

    // VALIDATION AVANT ENVOI

    const postForm = document.getElementById('postForm');
    
    if (postForm) {
        postForm.addEventListener('submit', function(e) {
            const contenu = document.getElementById('postContent').value;
            
            if (contenu.length < 10) {
                e.preventDefault();
                alert('❌ Le message doit contenir au moins 10 caractères.');
                document.getElementById('postContent').focus();
                return false;
            }
            
            if (contenu.length > 2000) {
                e.preventDefault();
                alert('❌ Le message ne doit pas dépasser 2000 caractères.');
                document.getElementById('postContent').focus();
                return false;
            }
            
            return true;
        });
    }
    

    // BOUTON RESET (pour addpost.php)

    const resetBtn = document.getElementById('resetBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (confirm('Effacer le formulaire ?')) {
                if (textarea) {
                    textarea.value = '';
                    if (charCount) {
                        charCount.textContent = '0 / 2000 caractères (minimum 10 requis)';
                        charCount.style.color = '#64748b';
                    }
                }
                
                // Réinitialiser l'aperçu de l'image
                if (imageInput) imageInput.value = '';
                if (previewContainer) previewContainer.innerHTML = '';
            }
        });
    }
    
    // teba3 addpost.php
      function removeImage() {
        imageInput.value = '';
        previewContainer.innerHTML = '';
        currentImageData = null;
    }
    // teba3 showpost.php
    // addpost.js - Fonctionne pour addpost.php et updatepost.php

document.addEventListener('DOMContentLoaded', function() {
  
    // NAVBAR SCROLL EFFECT
    const navbar = document.getElementById('navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 30);
        });
    }
    // MOBILE MENU
    window.toggleMenu = function() {
        const navLinks = document.getElementById('navLinks');
        if (navLinks) navLinks.classList.toggle('open');
    };
    // dashboard 
     function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
    }
    
    // Animation des barres
    document.addEventListener('DOMContentLoaded', function() {
        const bars = document.querySelectorAll('.bar');
        bars.forEach(bar => {
            const height = bar.style.height;
            bar.style.height = '0px';
            setTimeout(() => {
                bar.style.height = height;
            }, 100);
        });
    });

    
  
});
});