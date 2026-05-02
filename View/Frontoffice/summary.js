// ========== RÉSUMÉ IA POUR LES POSTS ==========

// ========== RÉSUMÉ IA ==========
async function generateSummary(postId) {
    console.log("Génération du résumé pour le post " + postId);
    
    const container = document.getElementById(`summary-${postId}`);
    const btn = document.getElementById(`summary-btn-${postId}`);
    
    if (!container) {
        console.error("Container non trouvé");
        return;
    }
    
    container.style.display = 'block';
    container.innerHTML = '<div style="padding: 12px; background: #f0fdf4; border-radius: 12px;">⏳ Chargement du contenu et génération du résumé...</div>';
    
    if (btn) btn.style.display = 'none';
    
    try {
        // Récupérer le contenu du post via une requête API
        const contentResponse = await fetch('get_post_content.php?id=' + postId);
        const postData = await contentResponse.json();
        
        if (!postData.success) {
            container.innerHTML = `<div style="background: #fee2e2; padding: 12px; border-radius: 8px;">❌ Impossible de récupérer le contenu du post</div>`;
            return;
        }
        
        const content = postData.content;
        
        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('contenu', content);
        
        const response = await fetch('summarize_post.php', {
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