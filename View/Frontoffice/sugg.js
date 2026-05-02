/**
 * ASCLEPIA — Intelligent Suggestions System
 * Appel direct à l'API Claude depuis le navigateur
 * ⚠️ Remplace ANTHROPIC_API_KEY par ta vraie clé
 */

(function () {
    'use strict';

    // ══════════════════════════════════════════
    // 🔑 CONFIGURATION — mets ta clé ici
    // ══════════════════════════════════════════
    const ANTHROPIC_API_KEY = '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';
    const MODEL = 'claude-haiku-4-5-20251001';
    // ══════════════════════════════════════════

    let suggestionContainer = null;
    let loadingDots = null;
    let debounceTimer = null;
    let isLoadingInitial = false;

    /* === CSS === */
    function injectStyles() {
        if (document.getElementById('asclepia-sugg-styles')) return;
        const style = document.createElement('style');
        style.id = 'asclepia-sugg-styles';
        style.textContent = `
            #suggestionBubbles {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 8px;
                padding: 14px 18px;
                margin-bottom: 14px;
                background: #ffffff;
                border: 1.5px solid #e2f0fb;
                border-radius: 16px;
                box-shadow: 0 1px 3px rgba(14,165,233,0.06), 0 4px 16px rgba(14,165,233,0.08);
                position: relative;
                overflow: hidden;
                animation: suggFadeIn 0.4s ease;
                transition: border-color 0.25s, box-shadow 0.25s;
            }
            #suggestionBubbles::before {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0;
                height: 3px;
                background: linear-gradient(90deg, #0ea5e9, #10b981, #0ea5e9);
                background-size: 200% 100%;
                animation: suggShimmer 3s linear infinite;
            }
            #suggestionBubbles.flash {
                border-color: #10b981;
                box-shadow: 0 1px 3px rgba(16,185,129,0.1), 0 4px 20px rgba(16,185,129,0.2);
            }
            #suggestionLabel {
                display: flex;
                align-items: center;
                gap: 5px;
                color: #64748b;
                font-size: 10.5px;
                font-weight: 700;
                letter-spacing: 0.7px;
                text-transform: uppercase;
                white-space: nowrap;
                flex-shrink: 0;
            }
            .sugg-icon {
                width: 16px; height: 16px;
                background: linear-gradient(135deg, #0ea5e9, #10b981);
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 8px;
                color: white;
            }
            .sugg-divider { width: 1px; height: 18px; background: #e2e8f0; flex-shrink: 0; }
            .suggestion-bubble {
                background: #f0f9ff;
                color: #0369a1;
                border: 1.5px solid #bae6fd;
                padding: 6px 14px;
                border-radius: 100px;
                font-size: 12.5px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.18s ease;
                white-space: nowrap;
                font-family: inherit;
                line-height: 1;
                opacity: 0;
                transform: translateY(6px) scale(0.95);
                animation: suggBubbleIn 0.3s ease forwards;
                position: relative;
                overflow: hidden;
            }
            .suggestion-bubble::after {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg, #0ea5e9, #10b981);
                opacity: 0;
                transition: opacity 0.18s;
            }
            .suggestion-bubble span { position: relative; z-index: 1; }
            .suggestion-bubble:hover { border-color: #0ea5e9; color: white; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(14,165,233,0.25); }
            .suggestion-bubble:hover::after { opacity: 1; }
            .suggestion-bubble:active { transform: translateY(0) scale(0.97); }
            .suggestion-bubble.variant-green { background: #f0fdf4; color: #047857; border-color: #a7f3d0; }
            .suggestion-bubble.variant-green::after { background: linear-gradient(135deg, #10b981, #0ea5e9); }
            .sugg-loading { display: flex; align-items: center; gap: 4px; padding: 2px 0; }
            .sugg-loading span { width: 5px; height: 5px; border-radius: 50%; background: #0ea5e9; animation: suggDot 1.2s ease infinite; }
            .sugg-loading span:nth-child(2) { animation-delay: 0.2s; background: #38bdf8; }
            .sugg-loading span:nth-child(3) { animation-delay: 0.4s; background: #10b981; }
            @keyframes suggFadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
            @keyframes suggBubbleIn { to { opacity: 1; transform: translateY(0) scale(1); } }
            @keyframes suggShimmer { 0% { background-position: 0% 0; } 100% { background-position: 200% 0; } }
            @keyframes suggDot { 0%, 80%, 100% { transform: scale(1); opacity: 0.4; } 40% { transform: scale(1.4); opacity: 1; } }
            body.dark-mode #suggestionBubbles { background: #0f1f35; border-color: #1e3a5f; }
            body.dark-mode #suggestionLabel { color: #94a3b8; }
            body.dark-mode .sugg-divider { background: #1e3a5f; }
            body.dark-mode .suggestion-bubble { background: #1e3a5f; color: #7dd3fc; border-color: #1d4ed8; }
            body.dark-mode .suggestion-bubble.variant-green { background: #052e16; color: #6ee7b7; border-color: #064e3b; }
        `;
        document.head.appendChild(style);
    }

    /* === BUILD UI === */
    function createSuggestionContainer(textarea) {
        const existing = document.getElementById('suggestionBubbles');
        if (existing) existing.remove();

        suggestionContainer = document.createElement('div');
        suggestionContainer.id = 'suggestionBubbles';

        const label = document.createElement('div');
        label.id = 'suggestionLabel';
        label.innerHTML = '<span class="sugg-icon">✦</span> Suggestions IA';
        suggestionContainer.appendChild(label);

        const divider = document.createElement('div');
        divider.className = 'sugg-divider';
        suggestionContainer.appendChild(divider);

        loadingDots = document.createElement('div');
        loadingDots.className = 'sugg-loading';
        loadingDots.innerHTML = '<span></span><span></span><span></span>';
        loadingDots.style.display = 'none';
        suggestionContainer.appendChild(loadingDots);

        textarea.parentNode.insertBefore(suggestionContainer, textarea);
    }

    /* === APPEL API CLAUDE (direct depuis le navigateur) === */
    async function callClaude(prompt) {
        const response = await fetch('https://api.anthropic.com/v1/messages', {
            method: 'POST',
            headers: {
                'x-api-key': ANTHROPIC_API_KEY,
                'anthropic-version': '2023-06-01',
                'content-type': 'application/json',
                // Nécessaire pour les appels depuis le navigateur
                'anthropic-dangerous-direct-browser-access': 'true'
            },
            body: JSON.stringify({
                model: MODEL,
                max_tokens: 150,
                messages: [{ role: 'user', content: prompt }]
            })
        });

        if (!response.ok) {
            const err = await response.json().catch(() => ({}));
            throw new Error('API ' + response.status + ': ' + (err.error?.message || ''));
        }

        const data = await response.json();
        const text = data.content?.[0]?.text || '';

        // Extraire le JSON de la réponse
        const match = text.match(/\{[\s\S]*\}/);
        if (match) {
            const parsed = JSON.parse(match[0]);
            if (Array.isArray(parsed.suggestions)) {
                return parsed.suggestions.filter(s => s && s.trim()).slice(0, 3);
            }
        }
        return [];
    }

    /* === SUGGESTIONS INITIALES === */
    async function loadInitialSuggestions() {
        if (isLoadingInitial) return;
        isLoadingInitial = true;
        showLoading(true);

        const prompt = `Tu es un assistant pour un forum médical en français (ASCLEPIA).
Génère exactement 3 courtes suggestions (max 7 mots chacune) que quelqu'un pourrait écrire en réponse à un sujet de santé.
Variées, naturelles, utiles dans un forum médical.
Réponds UNIQUEMENT avec ce JSON exact : {"suggestions": ["suggestion1", "suggestion2", "suggestion3"]}`;

        try {
            const suggestions = await callClaude(prompt);
            renderSuggestions(suggestions.length ? suggestions : getFallback(''));
        } catch (err) {
            console.warn('[ASCLEPIA Suggestions] Initial load failed:', err.message);
            renderSuggestions(getFallback(''));
        } finally {
            showLoading(false);
            isLoadingInitial = false;
        }
    }

    /* === SUGGESTIONS CONTEXTUELLES === */
    async function fetchContextualSuggestions(text) {
        const prompt = `Tu es un assistant pour un forum médical en français (ASCLEPIA).
L'utilisateur écrit : "${text}"
Génère exactement 3 suggestions contextuelles courtes (max 7 mots) pour continuer ce message.
Réponds UNIQUEMENT avec ce JSON exact : {"suggestions": ["suggestion1", "suggestion2", "suggestion3"]}`;

        try {
            const suggestions = await callClaude(prompt);
            showLoading(false);
            suggestions.length ? renderSuggestions(suggestions) : clearSuggestions();
        } catch (err) {
            console.warn('[ASCLEPIA Suggestions] Context failed:', err.message);
            showLoading(false);
            renderSuggestions(getFallback(text));
        }
    }

    /* === EVENTS === */
    function attachEventListeners(textarea) {
        textarea.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const text = this.value.trim();
            if (text.length === 0) { clearSuggestions(); loadInitialSuggestions(); return; }
            if (text.length < 2) { clearSuggestions(); return; }
            showLoading(true);
            debounceTimer = setTimeout(() => fetchContextualSuggestions(text), 400);
        });
    }

    /* === RENDER === */
    function renderSuggestions(suggestions) {
        if (!suggestionContainer) return;
        suggestionContainer.querySelectorAll('.suggestion-bubble').forEach(b => b.remove());
        suggestions.slice(0, 3).forEach(function (s, i) {
            if (!s.trim()) return;
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'suggestion-bubble' + (i === 2 ? ' variant-green' : '');
            b.style.animationDelay = (i * 0.09) + 's';
            b.innerHTML = '<span>' + s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</span>';
            b.addEventListener('click', () => insertSuggestion(s));
            suggestionContainer.appendChild(b);
        });
    }

    /* === INSERT === */
    function insertSuggestion(suggestion) {
        const textarea = document.querySelector('textarea[name="texte_rep"]');
        if (!textarea) return;
        const current = textarea.value;
        let newText;
        if (!current) { newText = suggestion; }
        else if (current.endsWith(' ') || current.endsWith('\n')) { newText = current + suggestion; }
        else {
            const lastSpace = current.lastIndexOf(' ');
            newText = lastSpace === -1 ? suggestion : current.slice(0, lastSpace + 1) + suggestion;
        }
        textarea.value = newText;
        textarea.focus();
        textarea.setSelectionRange(newText.length, newText.length);
        suggestionContainer.classList.add('flash');
        setTimeout(() => suggestionContainer.classList.remove('flash'), 400);
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchContextualSuggestions(newText.trim()), 500);
    }

    /* === LOADING === */
    function showLoading(show) {
        if (!loadingDots) return;
        loadingDots.style.display = show ? 'flex' : 'none';
        suggestionContainer.querySelectorAll('.suggestion-bubble').forEach(b => {
            b.style.opacity = show ? '0.35' : '';
            b.style.pointerEvents = show ? 'none' : '';
        });
    }

    function clearSuggestions() {
        if (!suggestionContainer) return;
        suggestionContainer.querySelectorAll('.suggestion-bubble').forEach(b => b.remove());
    }

    /* === FALLBACK LOCAL === */
    function getFallback(text) {
        const t = text.toLowerCase();
        if (t.includes('doul')) return ['douleur persistante depuis 3 jours', 'douleur thoracique intense', 'a empiré ce matin'];
        if (t.includes('fiè') || t.includes('fiev')) return ['fièvre à 39°C', 'fièvre depuis hier soir', 'fièvre avec frissons'];
        if (t.includes('fati')) return ['fatigue chronique inexpliquée', 'fatigue intense le matin', 'épuisement depuis 2 semaines'];
        if (t.includes('mal')) return ['mal de tête sévère', 'mal au ventre persistant', 'maladie auto-immune'];
        return ['consulter un médecin', 'symptômes préoccupants', 'depuis combien de temps ?'];
    }

    /* === INIT === */
    function init() {
        const textarea = document.querySelector('textarea[name="texte_rep"]');
        if (!textarea) { setTimeout(init, 800); return; }
        injectStyles();
        createSuggestionContainer(textarea);
        attachEventListeners(textarea);
        loadInitialSuggestions();
        console.log('[ASCLEPIA Suggestions] ✓ Ready — mode: direct browser API');
    }

    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', init)
        : init();
})();