// ===== COMPTEUR DE CARACTÈRES =====
const textarea = document.getElementById('postContent');
const nbchar = document.getElementById('nbchar');

if (textarea && nbchar) {
    const initialLen = textarea.value.length;
    nbchar.textContent = initialLen + ' / 2000 caractères (minimum 10 requis)';
    
    if (initialLen > 2000) {
        nbchar.style.color = '#ef4444';
    } else if (initialLen < 10 && initialLen > 0) {
        nbchar.style.color = '#f59e0b';
    } else {
        nbchar.style.color = '#64748b';
    }
    
    textarea.addEventListener('input', function() {
        const len = this.value.length;
        nbchar.textContent = len + ' / 2000 caractères (minimum 10 requis)';
        
        if (len > 2000) {
            nbchar.style.color = '#ef4444';
        } else if (len < 10 && len > 0) {
            nbchar.style.color = '#f59e0b';
        } else {
            nbchar.style.color = '#64748b';
        }
    });
}

// ===== GESTION DE L'APERÇU D'IMAGE (UPLOAD) =====
const imageInput = document.getElementById('imageUpload');
const previewContainer = document.getElementById('imagePreviewContainer');
let c = null;

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
                c = ev.target.result;
                previewContainer.innerHTML = `
                    <div class="image-preview-container" style="margin-top: 10px; position: relative; display: inline-block;">
                        <img src="${ev.target.result}" alt="Aperçu" class="image-preview" style="max-width: 200px; max-height: 200px; border-radius: 12px; border: 2px solid #e2e8f0; padding: 4px; background: white;">
                        <div class="remove-image" onclick="removeImage()" style="position: absolute; top: -10px; right: -10px; background: #ef4444; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 12px;">
                            <i class="fa-solid fa-times"></i>
                        </div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }
    });
}

// ===== VALIDATION DU FORMULAIRE AVANT ENVOI =====
const postForm = document.getElementById('postForm');
if (postForm) {
    postForm.addEventListener('submit', function(e) {
        const contenu = document.getElementById('postContent');
        if (contenu && contenu.value.length < 10) {
            e.preventDefault();
            alert('❌ Le message doit contenir au moins 10 caractères.');
            contenu.focus();
            return false;
        } else if (contenu && contenu.value.length > 2000) {
            e.preventDefault();
            alert('❌ Le message ne doit pas dépasser 2000 caractères.');
            contenu.focus();
            return false;
        }
        return true;
    });
}

// ===== SUPPRIMER L'APERÇU DE L'IMAGE UPLOADÉE =====
function removeImage() {
    const imageInput = document.getElementById('imageUpload');
    const previewContainer = document.getElementById('imagePreviewContainer');
    if (imageInput) imageInput.value = '';
    if (previewContainer) previewContainer.innerHTML = '';
    c = null;
}

// ===== GIPHY INTEGRATION (à placer APRES la déclaration des constantes GIPHY) =====
// Les constantes comme gifUrlInput, gifModal doivent être définies AVANT
// Normalement, ces variables sont dans ton HTML (addpost.php)

// Supprimer le GIF (et vider l’aperçu)
function removeGif() {
    document.getElementById('gifUrl').value = '';
    const previewContainer = document.getElementById('imagePreviewContainer');
    if (previewContainer) {
        previewContainer.innerHTML = ''; // Efface l’aperçu
    }
}