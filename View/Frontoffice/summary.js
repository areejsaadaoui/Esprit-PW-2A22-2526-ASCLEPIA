// ========== RÉSUMÉ IA POUR LES POSTS ==========

async function generateSummary(postId) {
    console.log("Génération du résumé pour le post " + postId);
    
    const container = document.getElementById(`summary-${postId}`);
    const btn = document.getElementById(`summary-btn-${postId}`);
    
    if (!container) {
        console.error("Container non trouvé");
        return;
    }
    
    container.style.display = 'block';
    container.innerHTML = '<div style="padding: 12px; background: #f0fdf4; border-radius: 12px;">⏳ Génération du résumé par IA...</div>';
    
    if (btn) btn.style.display = 'none';
    
    // Récupérer le contenu depuis l'attribut data-content du bouton
    let content = btn ? btn.getAttribute('data-content') : null;
    
    if (!content) {
        container.innerHTML = '<div style="background: #fee2e2; padding: 12px; border-radius: 8px;">❌ Impossible de récupérer le contenu du post</div>';
        if (btn) btn.style.display = 'flex';
        return;
    }
    
    // Décoder le JSON si nécessaire
    try {
        if (content.startsWith('"') || content.startsWith("'")) {
            content = JSON.parse(content);
        }
    } catch(e) {
        // Déjà en texte brut
    }
    
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('contenu', content);
    
    try {
        const response = await fetch('../Backoffice/summarize_post.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log("Réponse:", data);
        
        if (data.success) {
            container.innerHTML = `
                <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 12px; border-radius: 8px; margin-top: 10px;">
                    <strong>🤖 Résumé IA :</strong>
                    <p style="margin: 8px 0 0 0; font-size: 0.9rem;">${escapeHtml(data.summary)}</p>
                    <button onclick="hideSummary(${postId})" 
                            style="margin-top: 8px; background: none; border: none; color: #64748b; cursor: pointer;">
                        ✖ Fermer
                    </button>
                </div>
            `;
        } else {
            container.innerHTML = `<div style="background: #fee2e2; padding: 12px; border-radius: 8px;">❌ ${escapeHtml(data.error || 'Erreur')}</div>`;
        }
    } catch(error) {
        console.error("Erreur:", error);
        container.innerHTML = `<div style="background: #fee2e2; padding: 12px; border-radius: 8px;">❌ Erreur de connexion: ${escapeHtml(error.message)}</div>`;
    }
    
    if (btn) btn.style.display = 'flex';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function hideSummary(postId) {
    const container = document.getElementById(`summary-${postId}`);
    const btn = document.getElementById(`summary-btn-${postId}`);
    if (container) container.style.display = 'none';
    if (btn) btn.style.display = 'flex';
}