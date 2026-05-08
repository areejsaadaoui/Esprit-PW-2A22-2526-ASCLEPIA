// ===== COMPTEUR DE CARACTÈRES =====
console.clear();
console.log("=== ADD.JS RECHARGÉ PROPREMENT ===");
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

// add.js
console.log("✅ add.js chargé avec succès !");

// =============================================
// ADD.JS - Version propre pour AI Enhance
// =============================================

console.log("=== ADD.JS CHARGÉ CORRECTEMENT ===");

document.addEventListener('DOMContentLoaded', function() {

    const btn = document.getElementById('btnEnhanceAI');
    
    if (btn) {
        console.log("✅ Bouton Améliorer avec l’IA détecté");

        btn.addEventListener('click', async function() {
            console.log("🟢 Bouton cliqué !");

            const textarea = document.getElementById('postContent');
            if (!textarea) {
                alert("Erreur : Champ texte non trouvé");
                return;
            }

            const content = textarea.value.trim();

            if (content.length < 15) {
                alert("❌ Le texte est trop court pour l'IA.");
                return;
            }

            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Amélioration en cours...';
            this.disabled = true;

            try {
    
const response = await fetch('../Backoffice/ai_enhance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id_post=0&contenu=${encodeURIComponent(content)}`
    });

    console.log("Status HTTP:", response.status);

    if (!response.ok) {
        console.log("Erreur HTTP:", response.status);
    }

    const data = await response.json();
    console.log("Réponse complète:", data);

    if (data.success) {
        document.getElementById('aiPreviewText').textContent = data.newContent;
        document.getElementById('aiPreview').style.display = 'block';
        textarea.style.opacity = '0.6';
    } else {
        alert('Erreur : ' + (data.error || 'Erreur inconnue'));
    }
} catch (e) {
    console.error("Erreur fetch:", e);
    alert('Erreur de connexion avec le serveur IA');
}
        });

    } else {
        console.warn("⚠️ Bouton #btnEnhanceAI non trouvé");
    }
});

// ===== FONCTIONS POUR L'IA ENHANCE =====
function acceptAI() {
    const aiPreviewText = document.getElementById('aiPreviewText');
    const textarea = document.getElementById('postContent');
    const aiPreview = document.getElementById('aiPreview');
    
    if (aiPreviewText && textarea) {
        // Récupérer le texte amélioré
        const improvedText = aiPreviewText.textContent || aiPreviewText.innerText;
        
        // Remplacer le contenu du textarea
        textarea.value = improvedText;
        
        // Cacher la prévisualisation
        if (aiPreview) {
            aiPreview.style.display = 'none';
        }
        
        // Remettre l'opacité normale du textarea
        textarea.style.opacity = '1';
        
        // Mettre à jour le compteur de caractères
        if (typeof updateCharCount === 'function') {
            updateCharCount();
        } else if (document.getElementById('nbchar')) {
            const len = textarea.value.length;
            const nbchar = document.getElementById('nbchar');
            nbchar.textContent = len + ' / 2000 caractères (minimum 10 requis)';
        }
        
        // Notification de succès
        showToast('✅ Texte amélioré accepté !', 'success');
    }
}

function rejectAI() {
    const aiPreview = document.getElementById('aiPreview');
    const textarea = document.getElementById('postContent');
    
    if (aiPreview) {
        aiPreview.style.display = 'none';
    }
    if (textarea) {
        textarea.style.opacity = '1';
    }
    
    showToast('❌ Amélioration annulée', 'info');
}

// Fonction pour afficher un toast (si pas déjà présente)
function showToast(message, type = 'success') {
    // Créer le toast s'il n'existe pas
    let toast = document.getElementById('dynamicToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'dynamicToast';
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #10b981;
            color: white;
            padding: 12px 24px;
            border-radius: 50px;
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        document.body.appendChild(toast);
    }
    
    if (type === 'error') {
        toast.style.background = '#ef4444';
    } else if (type === 'info') {
        toast.style.background = '#3b82f6';
    } else {
        toast.style.background = '#10b981';
    }
    
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle')}"></i> ${message}`;
    toast.style.transform = 'translateX(0)';
    
    setTimeout(() => {
        toast.style.transform = 'translateX(400px)';
    }, 3000);
}
// ===== VOICE-TO-POST : Dictée vocale =====
let recognition = null;
let isListening = false;

// Vérifier si le navigateur supporte la Web Speech API
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.continuous = false;  // S'arrête après la pause
    recognition.interimResults = true;  // Affiche les résultats temporaires
    recognition.lang = 'fr-FR';  // Langue française
    recognition.maxAlternatives = 1;
    
    recognition.onstart = function() {
        isListening = true;
        const voiceBtn = document.getElementById('voiceBtn');
        if (voiceBtn) {
            voiceBtn.style.background = '#ef4444';
            voiceBtn.style.color = 'white';
            voiceBtn.innerHTML = '<i class="fas fa-microphone-slash"></i> Arrêter';
        }
        showToastVoice('🎤 Parlez maintenant...', '#ef4444');
    };
    
    recognition.onend = function() {
        isListening = false;
        const voiceBtn = document.getElementById('voiceBtn');
        if (voiceBtn) {
            voiceBtn.style.background = '';
            voiceBtn.style.color = '';
            voiceBtn.innerHTML = '<i class="fas fa-microphone"></i> Dictée vocale';
        }
    };
    
    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        const textarea = document.getElementById('postContent');
        if (textarea) {
            // Ajouter le texte dicté
            const currentText = textarea.value;
            if (currentText) {
                textarea.value = currentText + ' ' + transcript;
            } else {
                textarea.value = transcript.charAt(0).toUpperCase() + transcript.slice(1);
            }
            // Déclencher l'événement input pour le compteur
            textarea.dispatchEvent(new Event('input'));
        }
    };
    
    recognition.onerror = function(event) {
        console.error('Erreur reconnaissance vocale:', event.error);
        showToastVoice('❌ Erreur: ' + event.error, '#ef4444');
        if (voiceBtn) {
            voiceBtn.style.background = '';
            voiceBtn.style.color = '';
            voiceBtn.innerHTML = '<i class="fas fa-microphone"></i> Dictée vocale';
        }
        isListening = false;
    };
} else {
    console.log('Web Speech API non supportée par ce navigateur');
}

function toggleVoiceRecognition() {
    if (!recognition) {
        alert('❌ La reconnaissance vocale n\'est pas supportée par votre navigateur. Utilisez Chrome ou Edge.');
        return;
    }
    
    if (isListening) {
        recognition.stop();
    } else {
        recognition.start();
    }
}

function showToastVoice(msg, color) {
    const toast = document.createElement('div');
    toast.textContent = msg;
    Object.assign(toast.style, {
        position: 'fixed', bottom: '80px', left: '50%',
        transform: 'translateX(-50%)',
        background: color, color: 'white',
        padding: '10px 20px', borderRadius: '30px',
        zIndex: '10000', fontSize: '0.9rem',
        animation: 'fadeInScale 0.3s ease'
    });
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
}