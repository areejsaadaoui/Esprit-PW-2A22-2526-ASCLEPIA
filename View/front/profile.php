<?php
// profile.php - Page de profil utilisateur
session_start();

require_once '../../config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.html');
    exit();
}

// Vérifier que ce n'est pas un admin
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: ../back/dashboard.php');
    exit();
}

$pdo = config::getConnexion();
$user_id = $_SESSION['user_id'] ?? null;
$success_message = '';
$error_message = '';

if (!$user_id) {
    header('Location: login.html');
    exit();
}

// Récupérer les informations de l'utilisateur
$sql = "SELECT * FROM utilisateur WHERE id_user = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$current_avatar_style = $user['avatar_style'] ?? 'default';

// Traitement de la sauvegarde du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $date_naissance = $_POST['date_naissance'] ?? null;
    $description = $_POST['description'] ?? '';

    $update_sql = "UPDATE utilisateur SET 
                   telephone = ?, 
                   adresse = ?, 
                   date_naissance = ?, 
                   description = ?
                   WHERE id_user = ?";
    
    $stmt = $pdo->prepare($update_sql);
    
    if ($stmt->execute([$telephone, $adresse, $date_naissance, $description, $user_id])) {
        $success_message = "Profil mis à jour avec succès !";
        // Recharger les données
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error_message = "Erreur lors de la mise à jour du profil.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mon profil - ASCLEPIA">
    <title>Mon Profil - ASCLEPIA</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/frontoffice.css">
    <link rel="stylesheet" href="../assets/css/avatar.css">

    <style>
        :root {
            --profile-sidebar: #f8fafc;
            --profile-border: #e2e8f0;
        }
        
        .profile-container {
            max-width: 1400px;
            margin: 100px auto 60px;
            padding: 0 24px;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 32px;
        }
        
        /* Sidebar */
        .profile-sidebar {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 32px 24px;
            text-align: center;
            border: 1px solid var(--profile-border);
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .avatar-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .edit-avatar-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--primary);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid white;
        }
        
        .edit-avatar-btn:hover {
            transform: scale(1.1);
            background: var(--primary-dark);
        }
        
        .profile-name {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 16px 0 4px;
        }
        
        .profile-role {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .role-patient {
            background: #e0f2fe;
            color: #0284c7;
        }
        
        .role-medecin {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .role-admin {
            background: #fef3c7;
            color: #d97706;
        }
        
        .profile-stats {
            display: flex;
            justify-content: space-around;
            padding: 16px 0;
            border-top: 1px solid var(--profile-border);
            border-bottom: 1px solid var(--profile-border);
            margin: 20px 0;
        }
        
        .profile-stat {
            text-align: center;
        }
        
        .profile-stat .number {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .profile-stat .label {
            font-size: 0.7rem;
            color: var(--text-muted);
        }
        
        .sidebar-menu {
            text-align: left;
            margin-top: 20px;
        }
        
        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            color: var(--text);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .sidebar-menu-item:hover, .sidebar-menu-item.active {
            background: var(--bg);
            color: var(--primary);
        }
        
        .sidebar-menu-item i {
            width: 20px;
        }
        
        /* Main Content */
        .profile-main {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid var(--profile-border);
            overflow: hidden;
        }
        
        .profile-tabs {
            display: flex;
            border-bottom: 1px solid var(--profile-border);
            background: var(--bg);
            padding: 0 24px;
        }
        
        .profile-tab {
            padding: 16px 24px;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 2px solid transparent;
        }
        
        .profile-tab:hover {
            color: var(--primary);
        }
        
        .profile-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .profile-content {
            padding: 32px;
        }
        
        .profile-section {
            display: none;
        }
        
        .profile-section.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text);
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-family: var(--font-main);
            transition: all 0.3s ease;
        }
        
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-save {
            background: var(--gradient-primary);
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99,102,241,0.3);
        }
        
        .alert {
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .avatar-link {
            margin-top: 20px;
            text-align: center;
        }
        
        .avatar-link .btn {
            width: 100%;
            justify-content: center;
        }
        
        @media (max-width: 992px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
            .profile-sidebar {
                position: static;
            }
        }
        
        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar" id="navbar">
    <a href="indexp.php" class="navbar-brand">
        <div class="navbar-logo"></div>
        <div class="navbar-name">ASC<span>LEPIA</span></div>
    </a>
    
    <div class="nav-actions">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div class="avatar-css avatar-<?php echo $current_avatar_style; ?> small"></div>
            <span style="color: white; font-weight: 500;">Bonjour, <?php echo htmlspecialchars($user['nom']); ?></span>
            <a href="choose_avatar.php" class="btn btn-outline-white btn-sm">
                <i class="fa-solid fa-face-smile"></i> Avatar
            </a>
            <a href="../back/logout.php" class="btn btn-outline-white btn-sm">
                <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>
</nav>

<!-- Profile Container -->
<div class="profile-container">
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="profile-grid">
        <!-- Sidebar -->
        <aside class="profile-sidebar">
            <div class="avatar-wrapper">
                <div class="avatar-css avatar-<?php echo $current_avatar_style; ?> xlarge"></div>
                <div class="edit-avatar-btn" onclick="window.location.href='choose_avatar.php'">
                    <i class="fa-solid fa-pen"></i>
                </div>
            </div>
            <h2 class="profile-name"><?php echo htmlspecialchars($user['nom']); ?></h2>
            <span class="profile-role role-<?php echo $user['role']; ?>">
                <?php 
                if ($user['role'] === 'medecin') echo '👨‍⚕️ Médecin';
                elseif ($user['role'] === 'admin') echo '👑 Administrateur';
                else echo '👤 Patient';
                ?>
            </span>
            
            <div class="profile-stats">
                <div class="profile-stat">
                    <div class="number"><?php echo date('Y', strtotime($user['date_creation'])); ?></div>
                    <div class="label">Membre depuis</div>
                </div>
                <div class="profile-stat">
                    <div class="number">
                        <?php
                        $dateCreation = new DateTime($user['date_creation']);
                        $now = new DateTime();
                        $diff = $dateCreation->diff($now);
                        echo $diff->m + ($diff->y * 12);
                        ?>
                    </div>
                    <div class="label">Mois actif</div>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <div class="sidebar-menu-item active" data-tab="info">
                    <i class="fa-solid fa-user"></i> Informations personnelles
                </div>
                <div class="sidebar-menu-item" data-tab="security">
                    <i class="fa-solid fa-lock"></i> Sécurité
                </div>
            </div>
            
            <div class="avatar-link">
                <a href="choose_avatar.php" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-face-smile"></i> Changer d'avatar
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="profile-main">
            <div class="profile-tabs">
                <div class="profile-tab active" data-section="info">Informations</div>
                <div class="profile-tab" data-section="security">Sécurité</div>
            </div>

            <div class="profile-content">
                <!-- Informations Section -->
                <div class="profile-section active" id="infoSection">
                    <form method="POST" id="profileForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fa-solid fa-user"></i> Nom complet</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['nom']); ?>" disabled style="background:#f5f5f5;">
                            </div>
                            <div class="form-group">
                                <label><i class="fa-solid fa-envelope"></i> Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background:#f5f5f5;">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fa-solid fa-phone"></i> Téléphone</label>
                                <input type="tel" name="telephone" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>" placeholder="+216 XX XXX XXX">
                            </div>
                            <div class="form-group">
                                <label><i class="fa-solid fa-calendar"></i> Date de naissance</label>
                                <input type="date" name="date_naissance" value="<?php echo $user['date_naissance'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fa-solid fa-location-dot"></i> Adresse</label>
                            <input type="text" name="adresse" value="<?php echo htmlspecialchars($user['adresse'] ?? ''); ?>" placeholder="Votre adresse complète">
                        </div>
                        
                        <?php if ($user['role'] === 'medecin'): ?>
                        <div class="form-group">
                            <label><i class="fa-solid fa-stethoscope"></i> Spécialité / Description</label>
                            <textarea name="description" rows="3" placeholder="Votre spécialité, diplômes, expériences..."><?php echo htmlspecialchars($user['description'] ?? ''); ?></textarea>
                        </div>
                        <?php else: ?>
                        <div class="form-group">
                            <label><i class="fa-solid fa-notes-medical"></i> Notes médicales (optionnel)</label>
                            <textarea name="description" rows="3" placeholder="Allergies, traitements en cours, antécédents..."><?php echo htmlspecialchars($user['description'] ?? ''); ?></textarea>
                        </div>
                        <?php endif; ?>
                        
                        <div style="text-align: right;">
                            <button type="submit" class="btn-save">
                                <i class="fa-solid fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sécurité Section -->
                <div class="profile-section" id="securitySection">
                    <form method="POST" action="reset_password.html" id="passwordForm">
                        <div class="form-group">
                            <label><i class="fa-solid fa-key"></i> Nouveau mot de passe</label>
                            <input type="password" name="new_password" placeholder="••••••••" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-check-circle"></i> Confirmer le mot de passe</label>
                            <input type="password" name="confirm_password" placeholder="••••••••" required>
                        </div>
                        <div style="text-align: right;">
                            <button type="submit" class="btn-save">
                                <i class="fa-solid fa-key"></i> Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<footer class="footer" style="margin-top: 60px;">
    <div class="container">
        <div class="footer-bottom">
            <p>© 2026 ASCLEPIA. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<script>
// Navigation des onglets
document.querySelectorAll('.sidebar-menu-item').forEach(item => {
    item.addEventListener('click', function() {
        const tab = this.dataset.tab;
        document.querySelectorAll('.sidebar-menu-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
        
        document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.profile-section').forEach(s => s.classList.remove('active'));
        
        if (tab === 'info') {
            document.querySelector('.profile-tab[data-section="info"]').classList.add('active');
            document.getElementById('infoSection').classList.add('active');
        } else if (tab === 'security') {
            document.querySelector('.profile-tab[data-section="security"]').classList.add('active');
            document.getElementById('securitySection').classList.add('active');
        }
    });
});

document.querySelectorAll('.profile-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const section = this.dataset.section;
        document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        document.querySelectorAll('.profile-section').forEach(s => s.classList.remove('active'));
        document.getElementById(`${section}Section`).classList.add('active');
        
        // Mettre à jour le menu latéral
        const menuMap = { info: 0, security: 1 };
        document.querySelectorAll('.sidebar-menu-item').forEach((item, idx) => {
            if (idx === menuMap[section]) item.classList.add('active');
            else item.classList.remove('active');
        });
    });
});
</script>

</body>
</html>