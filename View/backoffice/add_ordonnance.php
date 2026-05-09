<?php
require_once '../../config.php';
require_once '../../Controller/OrdonnanceController.php';

$controller = new OrdonnanceController(config::getConnexion());
$success = '';
$errors = [];

$consultations = $controller->getConsultationsTerminees();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_consultation = intval($_POST['id_consultation'] ?? 0);
    $medicaments = trim($_POST['medicaments'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $duree = intval($_POST['duree_traitement'] ?? 0);
    $signature = trim($_POST['signature'] ?? '');

    if (empty($id_consultation)) {
        $errors[] = "Veuillez choisir une consultation.";
    }
    if (empty($medicaments) || strlen($medicaments) < 5) {
        $errors[] = "Les médicaments sont obligatoires (min. 5 caractères).";
    }
    if (empty($instructions) || strlen($instructions) < 5) {
        $errors[] = "Les instructions sont obligatoires (min. 5 caractères).";
    }
    if ($duree <= 0) {
        $errors[] = "La durée du traitement doit être supérieure à 0.";
    }
    if (empty($signature)) {
        $errors[] = "La signature du médecin est obligatoire.";
    }

    if (empty($errors)) {
        // Sauvegarder la signature comme image
        $signatureDir = '../../assets/signatures/';
        if (!is_dir($signatureDir)) {
            mkdir($signatureDir, 0777, true);
        }
        $signatureFile = 'signature_' . time() . '.png';
        $signatureData = str_replace('data:image/png;base64,', '', $signature);
        $signatureData = base64_decode($signatureData);
        file_put_contents($signatureDir . $signatureFile, $signatureData);

        $data = [
            'id_consultation'  => $id_consultation,
            'medicaments'      => $medicaments,
            'instructions'     => $instructions,
            'duree_traitement' => $duree,
            'signature'        => $signatureFile
        ];
        if ($controller->createOrdonnance($data)) {
            $success = "Ordonnance ajoutée avec succès !";
            $consultations = $controller->getConsultationsTerminees();
        } else {
            $errors[] = "Erreur lors de l'ajout.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Ordonnance - ASCLEPIA Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/backoffice.css">
    <link rel="stylesheet" href="../../assets/css/dark.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        #signatureCanvas {
            border: 2px dashed var(--border);
            border-radius: var(--radius);
            cursor: crosshair;
            background: white;
            display: block;
            width: 100%;
            touch-action: none;
        }
        #signatureCanvas:hover { border-color: var(--primary); }
        .signature-toolbar {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .color-btn {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: 2px solid transparent;
            cursor: pointer;
            transition: transform 0.15s;
        }
        .color-btn:hover, .color-btn.active { transform: scale(1.2); border-color: var(--primary); }
        .preview-signature {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 8px;
            margin-top: 8px;
            display: none;
        }
    </style>
</head>
<body>
<div class="admin-wrapper">

    <aside class="sidebar">
        <a href="#" class="sidebar-brand">
            <div class="sidebar-logo">🏥</div>
            <div class="sidebar-title">ASCL<span>EPIA</span></div>
        </a>
        <div class="sidebar-user">
            <div class="user-avatar">A</div>
            <div class="user-info">
                <div class="name">Ala</div>
                <div class="role">Médecin</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-label">Navigation</div>
            <div class="nav-item">
                <a href="dashboard.php">
                    <span class="nav-icon"><i class="fa-solid fa-gauge"></i></span>
                    Dashboard
                </a>
            </div>
            <div class="nav-section-label">Consultation</div>
            <div class="nav-item">
                <a href="list_consultation.php">
                    <span class="nav-icon"><i class="fa-solid fa-calendar-check"></i></span>
                    Consultations
                </a>
            </div>
            <div class="nav-item">
                <a href="add_consultation.php">
                    <span class="nav-icon"><i class="fa-solid fa-plus"></i></span>
                    Ajouter
                </a>
            </div>
            <div class="nav-section-label">Ordonnance</div>
            <div class="nav-item">
                <a href="list_ordonnance.php">
                    <span class="nav-icon"><i class="fa-solid fa-file-prescription"></i></span>
                    Ordonnances
                </a>
            </div>
            <div class="nav-item">
                <a href="add_ordonnance.php" class="active">
                    <span class="nav-icon"><i class="fa-solid fa-plus"></i></span>
                    Ajouter
                </a>
            </div>
        </nav>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <div class="page-title">Nouvelle Ordonnance</div>
                    <div class="breadcrumb">
                        <a href="list_ordonnance.php">Ordonnances</a>
                        <span>/</span>
                        <span>Ajouter</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <button class="dark-toggle" onclick="toggleDark()" id="darkBtn" title="Mode sombre">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        </div>

        <div class="page-content">

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $success ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <ul style="margin:0;padding-left:16px">
                        <?php foreach ($errors as $e): ?>
                            <li><?= $e ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (empty($consultations)): ?>
                <div class="alert alert-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Aucune consultation terminée disponible.
                </div>
            <?php else: ?>

            <div class="card" style="max-width:700px; margin:0 auto;">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-file-prescription" style="color:var(--primary)"></i>
                        Ajouter une ordonnance
                    </div>
                </div>

                <form action="" method="POST" id="formAdd" onsubmit="return validerFormulaire()">

                    <div class="form-group">
                        <label class="form-label">Consultation * <span class="text-muted" style="font-weight:400;font-size:0.8rem">(consultations terminées uniquement)</span></label>
                        <select name="id_consultation" id="id_consultation" class="form-control">
                            <option value="">-- Choisir une consultation --</option>
                            <?php foreach ($consultations as $c): ?>
                            <option value="<?= $c['id_consultation'] ?>">
                                #<?= $c['id_consultation'] ?> — <?= date('d/m/Y H:i', strtotime($c['date_consultation'])) ?> — <?= htmlspecialchars(substr($c['diagnostique'], 0, 40)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-error" id="err_consultation">Veuillez choisir une consultation.</span>
                    </div>

                    <div class="form-group" style="position:relative;">
                        <label class="form-label">Médicaments *
                            <span class="text-muted" style="font-weight:400;font-size:0.8rem">(min. 5 caractères)</span>
                            <span class="badge badge-primary" style="font-size:0.75rem; margin-left:8px;">
                                <i class="fa-solid fa-robot"></i> IA
                            </span>
                        </label>
                        <textarea name="medicaments" id="medicaments" class="form-control"
                            placeholder="Ex: Paracétamol 500mg, Ibuprofène 400mg..."
                            oninput="compter('medicaments', 'count_med', 5); suggererMedicament();"></textarea>
                        <span class="form-hint"><span id="count_med">0</span> caractères</span>
                        <span class="form-error" id="err_med">Les médicaments sont obligatoires (min. 5 caractères).</span>
                        <div id="suggestions_med" style="position:absolute; background:white; border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); z-index:1000; width:100%; display:none; margin-top:4px;"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instructions *
                            <span class="text-muted" style="font-weight:400;font-size:0.8rem">(min. 5 caractères)</span>
                        </label>
                        <textarea name="instructions" id="instructions" class="form-control"
                            placeholder="Ex: Prendre 1 comprimé 3 fois par jour après les repas..."
                            oninput="compter('instructions', 'count_inst', 5)"></textarea>
                        <span class="form-hint"><span id="count_inst">0</span> caractères</span>
                        <span class="form-error" id="err_inst">Les instructions sont obligatoires (min. 5 caractères).</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Durée du traitement *
                            <span class="text-muted" style="font-weight:400;font-size:0.8rem">(en jours)</span>
                        </label>
                        <input type="number" name="duree_traitement" id="duree_traitement" class="form-control"
                            placeholder="Ex: 7" min="1">
                        <span class="form-error" id="err_duree">La durée doit être supérieure à 0.</span>
                    </div>

                    <!-- SIGNATURE -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fa-solid fa-signature" style="color:var(--primary)"></i>
                            Signature du médecin *
                        </label>
                        <p class="form-hint">Dessinez votre signature dans le cadre ci-dessous</p>

                        <canvas id="signatureCanvas" width="660" height="150"></canvas>

                        <div class="signature-toolbar">
                            <span style="font-size:0.82rem; color:var(--text-muted);">Couleur :</span>
                            <div class="color-btn active" style="background:#0f172a;" onclick="setCouleur('#0f172a', this)" title="Noir"></div>
                            <div class="color-btn" style="background:#0ea5e9;" onclick="setCouleur('#0ea5e9', this)" title="Bleu"></div>
                            <div class="color-btn" style="background:#ef4444;" onclick="setCouleur('#ef4444', this)" title="Rouge"></div>

                            <span style="font-size:0.82rem; color:var(--text-muted); margin-left:8px;">Épaisseur :</span>
                            <select id="epaisseur" class="form-control" style="width:80px; padding:4px 8px; font-size:0.82rem;" onchange="setEpaisseur(this.value)">
                                <option value="1">Fine</option>
                                <option value="2" selected>Normale</option>
                                <option value="4">Épaisse</option>
                            </select>

                            <button type="button" onclick="effacerSignature()" class="btn btn-outline btn-sm" style="margin-left:auto;">
                                <i class="fa-solid fa-eraser"></i> Effacer
                            </button>
                        </div>

                        <span class="form-error" id="err_signature">La signature est obligatoire.</span>
                        <input type="hidden" name="signature" id="signatureData">
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Ajouter
                        </button>
                        <a href="list_ordonnance.php" class="btn btn-outline">Annuler</a>
                    </div>

                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('open');
    });

    // ========================
    // SIGNATURE CANVAS
    // ========================
    const canvas = document.getElementById('signatureCanvas');
    const ctx = canvas.getContext('2d');
    let dessin = false;
    let couleur = '#0f172a';
    let epaisseur = 2;

    // Redimensionner canvas
    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        canvas.width = rect.width * ratio;
        canvas.height = 150 * ratio;
        ctx.scale(ratio, ratio);
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        if (e.touches) {
            return {
                x: (e.touches[0].clientX - rect.left),
                y: (e.touches[0].clientY - rect.top)
            };
        }
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }

    canvas.addEventListener('mousedown', (e) => {
        dessin = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    });

    canvas.addEventListener('mousemove', (e) => {
        if (!dessin) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.strokeStyle = couleur;
        ctx.lineWidth = epaisseur;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.stroke();
    });

    canvas.addEventListener('mouseup', () => { dessin = false; });
    canvas.addEventListener('mouseleave', () => { dessin = false; });

    // Support tactile
    canvas.addEventListener('touchstart', (e) => {
        e.preventDefault();
        dessin = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    });

    canvas.addEventListener('touchmove', (e) => {
        e.preventDefault();
        if (!dessin) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.strokeStyle = couleur;
        ctx.lineWidth = epaisseur;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.stroke();
    });

    canvas.addEventListener('touchend', () => { dessin = false; });

    function setCouleur(c, btn) {
        couleur = c;
        document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    function setEpaisseur(val) {
        epaisseur = parseInt(val);
    }

    function effacerSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('signatureData').value = '';
    }

    function signatureVide() {
        const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
        return !data.some(channel => channel !== 0);
    }

    // ========================
    // SUGGESTION IA
    // ========================
    let timer = null;

    function suggererMedicament() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const q = document.getElementById('medicaments').value.trim();
            const box = document.getElementById('suggestions_med');

            if (q.length < 2) { box.style.display = 'none'; return; }

            const dernierMot = q.split(/[,\n]/).pop().trim();
            if (dernierMot.length < 2) { box.style.display = 'none'; return; }

            box.innerHTML = "<div style='padding:10px'>Chargement...</div>";
            box.style.display = 'block';

            fetch('suggest_medicament.php?q=' + encodeURIComponent(dernierMot), {
                cache: 'no-store',
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (!Array.isArray(data) || data.length === 0) {
                    box.innerHTML = "<div style='padding:10px'>Aucune suggestion</div>";
                    return;
                }
                box.innerHTML = data.map(s =>
                    `<div onclick="choisirMedicament('${s.replace(/'/g, "\\'")}')"
                    style="padding:10px 16px; cursor:pointer; font-size:0.9rem; border-bottom:1px solid var(--border);"
                    onmouseover="this.style.background='var(--bg)'"
                    onmouseout="this.style.background='white'">
                    <i class="fa-solid fa-pills" style="color:var(--primary); margin-right:8px;"></i>
                    ${s}</div>`
                ).join('');
                box.style.display = 'block';
            })
            .catch(() => { box.innerHTML = "<div style='padding:10px'>Aucune suggestion</div>"; });
        }, 400);
    }

    function choisirMedicament(texte) {
        const med = document.getElementById('medicaments');
        const parts = med.value.split(/[,\n]/);
        parts[parts.length - 1] = texte;
        med.value = parts.join(', ') + ', ';
        document.getElementById('suggestions_med').style.display = 'none';
        compter('medicaments', 'count_med', 5);
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#medicaments') && !e.target.closest('#suggestions_med')) {
            document.getElementById('suggestions_med').style.display = 'none';
        }
    });

    // ========================
    // VALIDATION
    // ========================
    function compter(champId, compteurId, minimum) {
        const nb = document.getElementById(champId).value.length;
        const el = document.getElementById(compteurId);
        el.textContent = nb;
        el.style.color = nb >= minimum ? 'green' : 'red';
    }

    function validerFormulaire() {
        let valide = true;
        document.querySelectorAll('.form-error').forEach(e => e.style.display = 'none');
        document.querySelectorAll('.form-control').forEach(e => e.classList.remove('is-invalid'));

        const consultation = document.getElementById('id_consultation').value;
        if (!consultation) {
            document.getElementById('id_consultation').classList.add('is-invalid');
            document.getElementById('err_consultation').style.display = 'block';
            valide = false;
        }

        const med = document.getElementById('medicaments').value.trim();
        if (med.length < 5) {
            document.getElementById('medicaments').classList.add('is-invalid');
            document.getElementById('err_med').style.display = 'block';
            valide = false;
        }

        const inst = document.getElementById('instructions').value.trim();
        if (inst.length < 5) {
            document.getElementById('instructions').classList.add('is-invalid');
            document.getElementById('err_inst').style.display = 'block';
            valide = false;
        }

        const duree = parseInt(document.getElementById('duree_traitement').value);
        if (!duree || duree <= 0) {
            document.getElementById('duree_traitement').classList.add('is-invalid');
            document.getElementById('err_duree').style.display = 'block';
            valide = false;
        }

        // Vérifier signature
        if (signatureVide()) {
            document.getElementById('err_signature').style.display = 'block';
            canvas.style.borderColor = 'var(--danger)';
            valide = false;
        } else {
            document.getElementById('signatureData').value = canvas.toDataURL('image/png');
            canvas.style.borderColor = '';
        }

        return valide;
    }

    // ========================
    // MODE SOMBRE
    // ========================
    function toggleDark() {
        document.body.classList.toggle('dark-mode');
        const btn = document.getElementById('darkBtn');
        const isDark = document.body.classList.contains('dark-mode');
        btn.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
        localStorage.setItem('darkMode', isDark);
    }

    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
        document.getElementById('darkBtn').innerHTML = '<i class="fa-solid fa-sun"></i>';
    }
</script>
</body>
<script src="../../assets/js/language-switcher.js"></script>
</html>