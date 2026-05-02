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

// ===== SUPPRIMER L'APERÇU DU GIF =====
function removeGif() {
    document.getElementById('gifUrl').value = '';
    const previewContainer = document.getElementById('imagePreviewContainer');
    if (previewContainer) {
        previewContainer.innerHTML = '';
    }
}

// ===== SUGGESTION AUTOMATIQUE DE RÉPONSES (AVEC API) =====
const suggestionTextarea = document.getElementById('postContent') || document.getElementById('texte_rep');

if (suggestionTextarea) {
    // Créer la boîte de suggestions
    const suggestionBox = document.createElement('div');
    suggestionBox.id = 'suggestionsBox';
    suggestionBox.style.cssText = 'margin-top: 10px; padding: 10px; background: #f0fdf4; border-radius: 12px; display: none; font-size: 0.85rem;';
    suggestionBox.innerHTML = '<strong>💡 Suggestions de réponses :</strong><div id="suggestionsList"></div>';
    suggestionTextarea.parentNode.insertBefore(suggestionBox, suggestionTextarea.nextSibling);
    
    let typingTimer;
    
    suggestionTextarea.addEventListener('input', function() {
        clearTimeout(typingTimer);
        const text = this.value.trim();
        
        if (text.length < 8) {
            suggestionBox.style.display = 'none';
            return;
        }
        
        typingTimer = setTimeout(() => {
            // 🔽 Chemin CORRECT vers le fichier API (dans Backoffice)
            fetch(`../Backoffice/suggestion_api.php?text=${encodeURIComponent(text)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.suggestions && data.suggestions.length > 0) {
                        const suggestionsList = document.getElementById('suggestionsList');
                        suggestionsList.innerHTML = data.suggestions.map(suggestion => `
                            <div style="padding: 8px 0; cursor: pointer; color: #166534; border-bottom: 1px solid #d1fae5;" 
                                 onclick="insertSuggestion('${suggestion.replace(/'/g, "\\'")}')">
                                💬 ${suggestion}
                            </div>
                        `).join('');
                        suggestionBox.style.display = 'block';
                    } else {
                        suggestionBox.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Erreur API:', error);
                    suggestionBox.style.display = 'none';
                });
        }, 600);
    });
}

// ===== INSÉRER LA SUGGESTION DANS LE CHAMP =====
function insertSuggestion(text) {
    const textarea = document.getElementById('postContent') || document.getElementById('texte_rep');
    if (textarea) {
        textarea.value = text;
        textarea.dispatchEvent(new Event('input'));
        document.getElementById('suggestionsBox').style.display = 'none';
    }
}