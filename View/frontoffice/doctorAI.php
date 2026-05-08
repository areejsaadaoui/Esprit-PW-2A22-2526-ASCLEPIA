<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dr. ASCLEPIA – AI Insurance Advisor</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/frontoffice.css">
    <link rel="stylesheet" href="../../assets/css/assurance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ── Plan Cards ── */
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 10px;
            width: 100%;
        }
        .plan-card {
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            padding: 15px;
            position: relative;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .plan-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.09);
        }
        .plan-icon { font-size: 22px; margin-bottom: 8px; }
        .plan-name { font-size: 14px; font-weight: 700; color: #111; margin-bottom: 6px; }
        .plan-stats { display: flex; gap: 6px; margin-bottom: 10px; }
        .plan-stat {
            flex: 1; background: #f4f6f8; border-radius: 8px;
            padding: 5px 6px; text-align: center;
        }
        .plan-stat-value { font-size: 14px; font-weight: 700; color: #111; }
        .plan-stat-label { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: 0.04em; }
        .plan-desc { font-size: 11.5px; color: #666; line-height: 1.4; margin-bottom: 10px; }
        .plan-btn {
            width: 100%; padding: 7px 0; background: #1D9E75;
            color: #fff; border: none; border-radius: 8px;
            font-size: 12px; font-weight: 600; cursor: pointer;
        }
        .plan-btn:hover { background: #0F6E56; }

        /* ── Comparison Table ── */
        .comparison-wrap {
            overflow-x: auto; margin-top: 12px;
            border-radius: 12px; border: 1px solid #e5e7eb;
        }
        .comparison-table {
            width: 100%; border-collapse: collapse; font-size: 13px; min-width: 360px;
        }
        .comparison-table thead tr { background: #1D9E75; color: #fff; }
        .comparison-table th { padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; }
        .comparison-table tbody tr:nth-child(even) { background: #f9fafb; }
        .comparison-table td { padding: 9px 14px; color: #333; border-bottom: 1px solid #f0f0f0; }
        .comparison-table td:first-child { font-weight: 600; color: #555; font-size: 12px; }

        /* ── Quick chips ── */
        .quick-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .quick-chip {
            background: #E1F5EE; color: #0F6E56; border: none;
            border-radius: 20px; padding: 5px 12px; font-size: 12px; cursor: pointer;
        }
        .quick-chip:hover { background: #9FE1CB; }

        /* ── Structured bubble ── */
        .message.ai .msg-bubble.structured {
            background: transparent !important;
            padding: 0 !important;
            max-width: 100%;
            width: 100%;
        }
        .message.ai { max-width: 95%; }

        /* ── AI voice text line ── */
        .ai-voice-text {
            font-size: 14px;
            color: #222;
            line-height: 1.6;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="ai-page-header">
    <div class="ai-page-logo">ASCL<span>EPIA</span></div>
    <div class="ai-page-badge"><i class="fa-solid fa-robot"></i> AI Advisor</div>
    <div class="ai-page-hint">💡 Say <strong>"Hi Doctor"</strong> to activate voice</div>
    <a href="assurancefront.php" class="ai-page-back">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<div class="ai-page-main">
    <div class="avatar-container">
        <div class="avatar" id="avatar">🩺</div>
        <div class="status-text" id="statusText">Waiting for you...</div>
    </div>

    <div class="chat-container" id="chatContainer">
        <div class="empty-state">
            <div class="big">🩺</div>
            Hello! I'm Dr. ASCLEPIA.<br>
            Ask me anything about insurance plans,<br>
            or say <strong>"Hi Doctor"</strong> to use your voice.
        </div>
    </div>

    <div class="controls">
        <button class="btn-mic" id="micBtn" title="Hold to speak">🎤</button>
        <div class="input-row">
            <input type="text" id="textInput" placeholder="Ask about insurance plans..." />
            <button class="btn-send" onclick="sendMessage()">Send ➤</button>
        </div>
    </div>
</div>

<div class="wake-indicator">
    <div class="wake-dot"></div>
    Always listening for "Hi Doctor"
</div>

<script>
    const avatar     = document.getElementById('avatar');
    const statusText = document.getElementById('statusText');
    const chatBox    = document.getElementById('chatContainer');
    const textInput  = document.getElementById('textInput');
    const micBtn     = document.getElementById('micBtn');
    let chatHistory  = [];
    let emptyState   = chatBox.querySelector('.empty-state');
    let mode         = 'wake';
    let recognition  = null;
    let silenceTimer = null;

    const typeIcons = {
        dental: '🦷', health: '❤️', vision: '👁️', family: '👨‍👩‍👧',
        senior: '🧓', orthodontic: '😁', default: '🏥'
    };
    function getIcon(type) {
        const t = (type || '').toLowerCase();
        for (const k in typeIcons) {
            if (t.includes(k)) return typeIcons[k];
        }
        return typeIcons.default;
    }

    // ── Build one plan card ──
    function buildCard(plan) {
        const card = document.createElement('div');
        card.className = 'plan-card';

        const icon = document.createElement('div');
        icon.className = 'plan-icon';
        icon.textContent = getIcon(plan.type);
        card.appendChild(icon);

        const name = document.createElement('div');
        name.className = 'plan-name';
        name.textContent = plan.name;
        card.appendChild(name);

        const stats = document.createElement('div');
        stats.className = 'plan-stats';
        stats.innerHTML = `
            <div class="plan-stat">
                <div class="plan-stat-value">${plan.price} DT</div>
                <div class="plan-stat-label">/ year</div>
            </div>
            <div class="plan-stat">
                <div class="plan-stat-value">${plan.reimbursement}%</div>
                <div class="plan-stat-label">covered</div>
            </div>
            <div class="plan-stat">
                <div class="plan-stat-value">${plan.duration}m</div>
                <div class="plan-stat-label">duration</div>
            </div>`;
        card.appendChild(stats);

        if (plan.description) {
            const desc = document.createElement('div');
            desc.className = 'plan-desc';
            desc.textContent = plan.description;
            card.appendChild(desc);
        }

        const btn = document.createElement('button');
        btn.className = 'plan-btn';
        btn.textContent = 'Tell me more';
        btn.onclick = () => sendMessage('Tell me more about ' + plan.name);
        card.appendChild(btn);

        return card;
    }

    // ── Build comparison table ──
    function buildComparisonTable(plans) {
        const wrap = document.createElement('div');
        wrap.className = 'comparison-wrap';

        const table = document.createElement('table');
        table.className = 'comparison-table';

        // Header
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        const headers = ['Feature', ...plans.map(p => p.name)];
        headers.forEach(h => {
            const th = document.createElement('th');
            th.textContent = h;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        table.appendChild(thead);

        // Rows
        const tbody = document.createElement('tbody');
        const rows = [
            { label: 'Price / year', key: p => p.price + ' DT' },
            { label: 'Reimbursement', key: p => p.reimbursement + '%' },
            { label: 'Duration', key: p => p.duration + ' months' },
            { label: 'Type', key: p => p.type },
        ];
        rows.forEach(row => {
            const tr = document.createElement('tr');
            const td0 = document.createElement('td');
            td0.textContent = row.label;
            tr.appendChild(td0);
            plans.forEach(plan => {
                const td = document.createElement('td');
                td.textContent = row.key(plan);
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
        table.appendChild(tbody);
        wrap.appendChild(table);
        return wrap;
    }

    // ── Render full AI response ──
    function renderResponse(data) {
        const wrap = document.createElement('div');

        // AI's natural text always shown first
        if (data.response) {
            const txt = document.createElement('div');
            txt.className = 'ai-voice-text';
            txt.textContent = data.response;
            wrap.appendChild(txt);
        }

        // Cards (when plans were mentioned)
        if (data.plans && data.plans.length > 0) {
            if (data.is_comparison && data.plans.length > 1) {
                // Comparison table
                wrap.appendChild(buildComparisonTable(data.plans));
            } else {
                // Cards grid
                const grid = document.createElement('div');
                grid.className = 'plans-grid';
                data.plans.forEach(plan => grid.appendChild(buildCard(plan)));
                wrap.appendChild(grid);
            }

            // Quick chips
            const chips = document.createElement('div');
            chips.className = 'quick-chips';
            ['Compare these plans', 'Which is cheapest?', 'Which has best coverage?'].forEach(q => {
                const chip = document.createElement('button');
                chip.className = 'quick-chip';
                chip.textContent = q;
                chip.onclick = () => sendMessage(q);
                chips.appendChild(chip);
            });
            wrap.appendChild(chips);
        }

        return wrap;
    }

    // ── Chat helpers ──
    function addMessage(content, role, isStructured) {
        if (emptyState) { emptyState.remove(); emptyState = null; }
        const msg = document.createElement('div');
        msg.className = 'message ' + role;

        const msgAvatar = document.createElement('div');
        msgAvatar.className = 'msg-avatar';
        msgAvatar.textContent = role === 'ai' ? '🩺' : '👤';

        const bubble = document.createElement('div');
        bubble.className = isStructured ? 'msg-bubble structured' : 'msg-bubble';

        if (isStructured) {
            bubble.appendChild(content);
        } else {
            bubble.textContent = content;
        }

        msg.appendChild(msgAvatar);
        msg.appendChild(bubble);
        chatBox.appendChild(msg);
        chatBox.scrollTop = chatBox.scrollHeight;
        return msg;
    }

    function addThinking() {
        if (emptyState) { emptyState.remove(); emptyState = null; }
        const msg = document.createElement('div');
        msg.className = 'message ai';
        msg.innerHTML = `
            <div class="msg-avatar">🩺</div>
            <div class="msg-bubble thinking-dots"><span>●</span><span>●</span><span>●</span></div>`;
        chatBox.appendChild(msg);
        chatBox.scrollTop = chatBox.scrollHeight;
        return msg;
    }

    // ── Language helpers ──
    function detectLangFromText(text) {
        const frWords = ['assurance','contrat','votre','vous','est','les','des','une','pour','avec','que','remboursement','quel','quelle','quels','cherche','bonjour'];
        const words   = text.toLowerCase().split(' ');
        const frCount = words.filter(w => frWords.includes(w)).length;
        return frCount >= 1 ? 'fr' : 'en';
    }
    function detectLang(text) {
        const frWords = ['assurance','contrat','votre','vous','est','les','des','une','pour','avec','que','remboursement'];
        const words   = text.toLowerCase().split(' ');
        const frCount = words.filter(w => frWords.includes(w)).length;
        return frCount >= 2 ? 'fr-FR' : 'en-US';
    }

    // ── Speech ──
    function speak(text) {
        window.speechSynthesis.cancel();
        try { recognition.stop(); } catch(e) {}

        const utter = new SpeechSynthesisUtterance(text);
        utter.lang  = detectLang(text);
        utter.rate  = 1.0;

        utter.onstart = () => {
            avatar.className = 'avatar speaking';
            setStatus('Dr. ASCLEPIA is speaking...', 'speaking');
        };
        utter.onend = () => {
            avatar.className = 'avatar';
            setStatus('Waiting for you...');
            mode = 'wake';
            try { recognition.start(); } catch(e) {}
        };
        window.speechSynthesis.speak(utter);
    }

    function setStatus(text, cls) {
        statusText.textContent = text;
        statusText.className   = 'status-text' + (cls ? ' ' + cls : '');
    }

    async function sendMessage(text) {
        const message = text || textInput.value.trim();
        if (!message) return;

        textInput.value = '';
        addMessage(message, 'user', false);
        avatar.className = 'avatar thinking';
        setStatus('Thinking...', 'active');
        const thinking = addThinking();

        // Add to history before sending
        chatHistory.push({ role: 'user', content: message });

        try {
            const res  = await fetch('aiAgent.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message,
                    messages: chatHistory,
                    lang: detectLangFromText(message),
                })
            });
            const data = await res.json();
            thinking.remove();

            if (data.error) {
                addMessage(data.error, 'ai', false);
                chatHistory.pop(); // remove failed message
                avatar.className = 'avatar';
                setStatus('Waiting for you...');
                return;
            }

            // Save assistant reply to history
            chatHistory.push({ role: 'assistant', content: data.response || '' });

            // Always render with the rich renderer
            const rendered = renderResponse(data);
            addMessage(rendered, 'ai', true);

            // Speak only the text part (not card data)
            speak(data.response || '');

        } catch (err) {
            thinking.remove();
            chatHistory.pop(); // remove failed message
            addMessage('Could not reach the AI. Make sure Ollama is running.', 'ai', false);
            avatar.className = 'avatar';
            setStatus('Waiting for you...');
        }
    }

    // ── Speech recognition ──
    function initRecognition() {
        if (!('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)) {
            alert('Speech recognition not supported. Please use Chrome or Edge.');
            return;
        }
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SR();
        recognition.continuous     = true;
        recognition.interimResults = true;
        recognition.lang           = ''; // default, will be overridden by utterance lang

        recognition.onresult = (e) => {
            const transcript = Array.from(e.results)
                .map(r => r[0].transcript).join(' ').toLowerCase().trim();

            if (mode === 'wake') {
                if (transcript.includes('hi doctor') || transcript.includes('hey doctor') || transcript.includes('aidoctor')||transcript.includes('bonjour docteur') || transcript.includes('salut docteur')) {
                    switchToListenMode();
                }
            } else if (mode === 'listen') {
                const last = e.results[e.results.length - 1];
                textInput.value = last[0].transcript.trim();
                clearTimeout(silenceTimer);
                silenceTimer = setTimeout(() => {
                    const text = textInput.value.trim();
                    if (text) { switchToWakeMode(); sendMessage(text); }
                }, 2500);
            }
        };

        recognition.onend = () => {
            if (mode === 'wake' || mode === 'listen') {
                try { recognition.start(); } catch(e) {}
            }
        };
        recognition.onerror = (e) => {
            if (e.error === 'no-speech') return;
            console.warn('Recognition error:', e.error);
        };
        try { recognition.start(); } catch(e) {}
    }

    function switchToListenMode() {
        mode = 'listen';
        micBtn.classList.add('active');
        avatar.className = 'avatar listening';
        setStatus('Listening... speak now!', 'active');
        textInput.value = '';
        textInput.placeholder = 'Listening...';
        setTimeout(() => { if (mode === 'listen') switchToWakeMode(); }, 8000);
    }

    function switchToWakeMode() {
        mode = 'wake';
        micBtn.classList.remove('active');
        avatar.className = 'avatar';
        setStatus('Waiting for you...');
        textInput.placeholder = 'Ask about insurance plans...';
    }

    micBtn.addEventListener('click', () => {
        if (mode === 'listen') {
            const text = textInput.value.trim();
            switchToWakeMode();
            if (text) sendMessage(text);
        } else {
            switchToListenMode();
        }
    });

    textInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') sendMessage(); });

    let audioUnlocked = false;
    function unlockAudio() {
        if (audioUnlocked) return;
        audioUnlocked = true;
        const s = new SpeechSynthesisUtterance('');
        s.volume = 0;
        window.speechSynthesis.speak(s);
    }
    document.addEventListener('click', unlockAudio, { once: false });
    document.addEventListener('keydown', unlockAudio, { once: false });

    initRecognition();
</script>
</body>
</html>