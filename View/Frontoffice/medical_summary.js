// ========== RÉSUMÉ IA MÉDICAL INTELLIGENT v2 ==========
/*
UI EXACTE SPEC :
┌─────────────────────────────────────────────────────────────┐
│  🤖 RÉSUMÉ IA (généré automatiquement)                      │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ Patient avec fièvre 38.5°C, courbatures, fatigue      │  │
│  │ intense depuis 3 jours. Paracétamol inefficace.       │  │
│  │ Demande conseil durée/traitements grippe.             │  │
│  └───────────────────────────────────────────────────────┘  │
│  [📋 Copier]                                     [✖ Fermer]  │
└─────────────────────────────────────────────────────────────┘
*/

async function generateMedicalSummary(postId, content = null) {
    const container = document.getElementById(`summary-${postId}`);
    const btn = document.getElementById(`summary-btn-${postId}`);
    
    if (!container) {
        console.error('❌ Container summary-', postId, 'non trouvé');
        return;
    }
    
    // État loading
    container.style.display = 'block';
    container.innerHTML = getLoadingCard();
    if (btn) btn.style.display = 'none';
    
    try {
        // Contenu depuis data-attribute ou fetch
        let postContent = content || getPostContent(postId);
        if (!postContent || postContent.length < 50) {
            throw new Error('Contenu trop court (<50 chars)');
        }
        
        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('contenu', postContent);
        
        const response = await fetch('../Backoffice/summarize_post.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log('✅ Résumé médical:', data);
        
        if (data.success) {
            showMedicalCard(container, postId, data.summary, data.char_count);
        } else {
            container.innerHTML = `<div class="ai-error">❌ ${data.error || 'Erreur API'}</div>`;
        }
        
    } catch (error) {
        console.error('❌ Erreur résumé:', error);
        container.innerHTML = `<div class="ai-error">❌ Erreur: ${error.message}</div>`;
    } finally {
        if (btn) btn.style.display = 'flex';
    }
}

function getLoadingCard() {
    return `
        <div class="ai-summary-card loading">
            <div class="ai-header">🤖 RÉSUMÉ IA</div>
            <div class="ai-content">
                <div style="padding:12px;background:#f0f9ff;border-radius:8px">
                    ⏳ Extraction médicale en cours... (IA HF)
                </div>
            </div>
        </div>
    `;
}

function showMedicalCard(container, postId, summary, charCount) {
    // ASCII ART EXACT + responsive
    const cardHTML = `
        <div class="ai-summary-card" id="ai-card-${postId}">
            <div class="ai-border-top">┌─────────────────────────────────────────────────────────────┐</div>
            <div class="ai-line">│  <span class="ai-emoji">🤖</span> RÉSUMÉ IA <span class="ai-badge">(généré automatiquement)</span>        │</div>
            <div class="ai-border-inner">│  ┌───────────────────────────────────────────────────────┐  │</div>
            <div class="ai-content-line">│  │ ${summary.split(' ').slice(0,25).join(' ')} │  │</div>
            ${summary.length > 60 ? `<div class="ai-content-line">│  │ ${summary.split(' ').slice(25).join(' ')} │  │</div>` : ''}
            <div class="ai-border-inner">│  └───────────────────────────────────────────────────────┘  │</div>
            <div class="ai-actions">│  <button class="ai-copy-btn" onclick="copySummary(${postId})">[📋 Copier]</button>  <button class="ai-close-btn" onclick="hideSummary(${postId})">[✖ Fermer]</button>  │</div>
            <div class="ai-border-bottom">└─────────────────────────────────────────────────────────────┘</div>
            <div class="ai-footer">${charCount} chars • IA Médicale HF</div>
        </div>
    `;
    
    container.innerHTML = cardHTML;
    
    // Auto-close 15s
    setTimeout(() => hideSummary(postId), 15000);
    
    // Focus trap + click outside
    document.addEventListener('click', function outsideClick(e) {
        if (!container.contains(e.target)) hideSummary(postId);
    }, { once: true });
}

function copySummary(postId) {
    const card = document.getElementById(`ai-card-${postId}`);
    const summaryText = card.querySelector('.ai-content-line').textContent.trim().slice(5, -5);
    
    navigator.clipboard.writeText(summaryText).then(() => {
        const btn = card.querySelector('.ai-copy-btn');
        const oldText = btn.textContent;
        btn.textContent = '✓ Copié!';
        btn.style.background = '#10b981';
        setTimeout(() => {
            btn.textContent = oldText;
            btn.style.background = '';
        }, 1500);
    }).catch(() => alert('Copie échouée'));
}

function hideSummary(postId) {
    const container = document.getElementById(`summary-${postId}`);
    const btn = document.getElementById(`summary-btn-${postId}`);
    if (container) {
        container.style.display = 'none';
        container.innerHTML = '';
    }
    if (btn) btn.style.display = 'flex';
}

// Helpers
function getPostContent(postId) {
    const btn = document.getElementById(`summary-btn-${postId}`);
    if (btn) {
        let content = btn.getAttribute('data-content');
        return content ? JSON.parse(content) : null;
    }
    return null;
}

// Init au click bouton
document.addEventListener('click', function(e) {
    if (e.target.matches('[onclick*="generateSummary"], [data-post-id]')) {
        e.preventDefault();
        const postId = e.target.dataset.postId || e.target.getAttribute('data-post-id');
        const content = e.target.getAttribute('data-content');
        generateMedicalSummary(postId, content);
    }
});

// CSS injecté (dark mode + responsive)
const style = document.createElement('style');
style.textContent = `
.ai-summary-card {
    font-family: 'Courier New', monospace;
    max-width: 100%;
    margin: 12px 0;
    animation: fadeInSlide 0.4s ease-out;
    backdrop-filter: blur(10px);
}
@keyframes fadeInSlide {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.ai-summary-card.loading { text-align: center; }
.ai-border-top, .ai-border-bottom, .ai-border-inner, .ai-line, .ai-content-line, .ai-actions {
    padding: 0 4px;
    line-height: 1.2;
    font-size: 13px;
    white-space: pre;
}
.ai-line { font-weight: bold; color: #059669; }
.ai-emoji { font-size: 16px; }
.ai-badge { font-size: 11px; opacity: 0.8; }
.ai-content-line { color: #1f2937; }
.ai-actions button {
    background: #f3f4f6; border: none; padding: 2px 8px; 
    border-radius: 4px; cursor: pointer; font-family: inherit; font-size: 11px;
    margin: 0 4px; transition: 0.2s;
}
.ai-actions button:hover { background: #e5e7eb; transform: scale(1.05); }
.ai-copy-btn { color: #059669; }
.ai-close-btn { color: #dc2626; }
.ai-footer {
    font-size: 10px; text-align: right; margin-top: 4px;
    color: #6b7280; font-family: sans-serif;
}
.ai-error {
    background: #fee2e2; color: #dc2626; padding: 12px; 
    border-radius: 8px; border-left: 4px solid #dc2626;
}

/* DARK MODE */
body.dark-mode .ai-summary-card { color: #f8fafc; }
body.dark-mode .ai-line { color: #34d399; }
body.dark-mode .ai-content-line { color: #e2e8f0; }
body.dark-mode .ai-actions button {
    background: #374151; color: #f9fafb;
}
body.dark-mode .ai-actions button:hover { background: #4b5563; }
body.dark-mode .ai-footer { color: #9ca3af; }

/* MOBILE */
@media (max-width: 768px) {
    .ai-summary-card { font-size: 12px; }
    .ai-actions button { font-size: 10px; padding: 1px 6px; }
}

/* POSITION FIXED OPTIONNELLE */
.ai-summary-card.fixed {
    position: fixed; top: 20%; left: 50%; transform: translateX(-50%);
    max-width: 90vw; max-height: 80vh; overflow-y: auto;
    box-shadow: 0 25px 50px rgba(0,0,0,0.25); z-index: 9999;
}
`;
document.head.appendChild(style);

console.log('🚀 medical_summary.js chargé - Résumé IA Médicale prêt!');

