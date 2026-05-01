<?php
// dashboard.php - Page admin sécurisée
session_start();

// Vérifier si l'admin est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: loginadmin.html');
    exit();
}

// Récupérer les infos de l'admin connecté
$adminNom = $_SESSION['user_nom'] ?? 'Administrateur';
$adminEmail = $_SESSION['user_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASCLEPIA — Administration Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/backoffice.css">
    <style>
        .tabs-container {
            margin-bottom: 30px;
        }
        
        .tabs {
            display: flex;
            gap: 12px;
            border-bottom: 2px solid var(--border);
            padding-bottom: 12px;
            align-items: center;
            justify-content: space-between;
        }
        
        .tabs-left {
            display: flex;
            gap: 12px;
        }
        
        .tab-btn {
            padding: 10px 24px;
            background: transparent;
            border: none;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: var(--radius);
            transition: var(--transition);
            position: relative;
        }
        
        .tab-btn:hover {
            color: var(--primary);
            background: rgba(14,165,233,0.1);
        }
        
        .tab-btn.active {
            color: var(--primary);
            background: rgba(14,165,233,0.15);
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -14px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary);
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .user-avatar-small {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
        }
        
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .stat-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-summary-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 20px;
            border: 1px solid var(--border);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .stat-summary-card .number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
        }
        
        .stat-summary-card .label {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 5px;
        }
        
        /* Modal Stats avec animations */
        .stats-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(10px);
            z-index: 3000;
            align-items: center;
            justify-content: center;
        }
        
        .stats-overlay.active {
            display: flex;
        }
        
        .stats-modal {
            background: var(--white);
            border-radius: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            opacity: 0;
            transform: scale(0.9) translateY(30px);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
        }
        
        .stats-overlay.active .stats-modal {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
        
        .stats-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 30px 30px 0 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .stats-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .stats-header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        
        .stats-header .close-stats {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .stats-header .close-stats:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }
        
        .stats-body {
            padding: 30px;
        }
        
        .donut-container {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .donut-chart {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .donut-inner {
            position: absolute;
            width: 140px;
            height: 140px;
            background: var(--white);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            animation: pulseInner 2s infinite;
        }
        
        @keyframes pulseInner {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .donut-inner .total-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
        }
        
        .donut-inner .total-label {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .stats-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-item {
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
            padding: 20px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s;
            opacity: 0;
            transform: translateX(-20px);
            animation: slideIn 0.5s ease forwards;
        }
        
        .stat-item:nth-child(2) {
            animation-delay: 0.2s;
            transform: translateX(20px);
        }
        
        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .stat-item:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .stat-item-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
        }
        
        .stat-item-icon.patients {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-item-icon.medecins {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .stat-item-number {
            font-size: 2rem;
            font-weight: 800;
            margin: 10px 0;
        }
        
        .stat-item-percent {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
        }
        
        .progress-bar-container {
            margin-top: 30px;
            background: #f0f2f5;
            border-radius: 10px;
            padding: 20px;
        }
        
        .progress-item {
            margin-bottom: 20px;
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards;
        }
        
        .progress-item:first-child {
            animation-delay: 0.3s;
        }
        
        .progress-item:last-child {
            animation-delay: 0.4s;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .progress-bar-bg {
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            height: 30px;
        }
        
        .progress-bar-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 10px;
            width: 0%;
        }
        
        .progress-bar-fill.patients {
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .progress-bar-fill.medecins {
            background: linear-gradient(90deg, #f093fb, #f5576c);
        }
        
        .sort-menu {
            position: relative;
            display: inline-block;
        }
        
        .sort-menu-content {
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 5px;
            background: var(--white);
            min-width: 200px;
            border-radius: var(--radius);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
            z-index: 1000;
            overflow: hidden;
        }
        
        .sort-option {
            padding: 10px 16px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: var(--text);
        }
        
        .sort-option:hover {
            background: rgba(14,165,233,0.1);
            color: var(--primary);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text);
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-outline:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .adresse-cell {
            max-width: 200px;
            white-space: normal;
            word-wrap: break-word;
        }
        
        .search-bar {
            margin-bottom: 0;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: var(--white);
            border-radius: var(--radius-lg);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .table-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-icon {
            padding: 6px 10px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo">⚕️</div>
            <div class="sidebar-title">ASC<span>LEPIA</span></div>
        </div>
        
        <div class="sidebar-user">
            <div class="user-avatar" id="adminAvatar"><?php echo strtoupper(substr($adminNom, 0, 2)); ?></div>
            <div class="user-info">
                <div class="name" id="adminName"><?php echo htmlspecialchars($adminNom); ?></div>
                <div class="role">Super Admin</div>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Principal</div>
            
            <div class="nav-item">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span>Tableau de bord</span>
                </a>
            </div>
            
            <div class="nav-item has-sub">
                <a onclick="toggleSubMenu(this)">
                    <i class="fas fa-comments nav-icon"></i>
                    <span>Forum</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <div class="sub-menu">
                    <a href="../Frontoffice/postList.php">Tous les posts</a>
                    <a href="addpost.php">Ajouter un post</a>
                    <a href="dashboard.php">Gestion des posts</a>
                </div>
            </div>
            
            <div class="nav-section-label">Configuration</div>
            
            <div class="nav-item">
                <a href="../front/indexp.php">
                    <i class="fas fa-globe nav-icon"></i>
                    <span>Voir le site</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </nav>
        
        <div class="sidebar-footer">
            <div class="sidebar-version">Version 1.0</div>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <div class="page-title">Gestion des utilisateurs</div>
                    <div class="breadcrumb">
                        <span>Accueil</span>
                        <span>/</span>
                        <span>Utilisateurs</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <button class="topbar-btn" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <div class="topbar-user">
                    <i class="fas fa-user-circle" style="font-size: 1.5rem;"></i>
                    <div>
                        <div class="name" id="topbarName"><?php echo htmlspecialchars($adminNom); ?></div>
                        <div class="role">Administrateur</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="page-content">
            <!-- Statistiques -->
            <div class="stat-summary">
                <div class="stat-summary-card">
                    <div class="number" id="totalPatients">0</div>
                    <div class="label">Patients</div>
                </div>
                <div class="stat-summary-card">
                    <div class="number" id="totalMedecins">0</div>
                    <div class="label">Médecins</div>
                </div>
                <div class="stat-summary-card">
                    <div class="number" id="totalUsers">0</div>
                    <div class="label">Total utilisateurs</div>
                </div>
            </div>
            
            <!-- Tabs avec bouton STATS - MÊME STYLE que les boutons Ajouter -->
            <div class="tabs-container">
                <div class="tabs">
                    <div class="tabs-left">
                        <button class="tab-btn active" onclick="switchTab('patients')">
                            <i class="fas fa-user"></i> Patients
                        </button>
                        <button class="tab-btn" onclick="switchTab('medecins')">
                            <i class="fas fa-user-md"></i> Médecins
                        </button>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="showStatsModal()" style="display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-chart-pie"></i> Statistiques
                    </button>
                </div>
            </div>
            
            <!-- Tableau Patients -->
            <div id="patientsTab" class="tab-content active">
                <div class="filter-bar">
                    <div style="display: flex; gap: 10px; align-items: center; flex: 1;">
                        <div class="search-bar" style="max-width: 300px; position: relative;">
                            <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--gray-light);"></i>
                            <input type="text" id="searchPatient" placeholder="Rechercher un patient..." style="padding-left: 38px;">
                        </div>
                        <button class="btn-outline btn-sm" onclick="toggleSortMenu('patients')" title="Trier" style="display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-arrow-down-wide-short"></i> Trier
                            <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                        </button>
                        <div id="sortMenuPatients" class="sort-menu" style="display: none;">
                            <div class="sort-menu-content">
                                <div class="sort-option" onclick="sortTable('patients', 'nom', 'asc')">
                                    <i class="fas fa-sort-alpha-down"></i> Nom (A → Z)
                                </div>
                                <div class="sort-option" onclick="sortTable('patients', 'nom', 'desc')">
                                    <i class="fas fa-sort-alpha-up"></i> Nom (Z → A)
                                </div>
                                <div class="sort-option" onclick="sortTable('patients', 'date', 'asc')">
                                    <i class="fas fa-calendar-plus"></i> Date (plus ancien)
                                </div>
                                <div class="sort-option" onclick="sortTable('patients', 'date', 'desc')">
                                    <i class="fas fa-calendar-minus"></i> Date (plus récent)
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="openAddModal('patient')">
                        <i class="fas fa-plus"></i> Ajouter un patient
                    </button>
                </div>
                
                <div class="card" style="padding: 0; overflow: hidden;">
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Date d'inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="patientsTable">
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px;">
                                        <i class="fas fa-spinner fa-spin"></i> Chargement...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Tableau Médecins -->
            <div id="medecinsTab" class="tab-content">
                <div class="filter-bar">
                    <div style="display: flex; gap: 10px; align-items: center; flex: 1;">
                        <div class="search-bar" style="max-width: 300px; position: relative;">
                            <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--gray-light);"></i>
                            <input type="text" id="searchMedecin" placeholder="Rechercher un médecin..." style="padding-left: 38px;">
                        </div>
                        <button class="btn-outline btn-sm" onclick="toggleSortMenu('medecins')" title="Trier" style="display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-arrow-down-wide-short"></i> Trier
                            <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                        </button>
                        <div id="sortMenuMedecins" class="sort-menu" style="display: none;">
                            <div class="sort-menu-content">
                                <div class="sort-option" onclick="sortTable('medecins', 'nom', 'asc')">
                                    <i class="fas fa-sort-alpha-down"></i> Nom (A → Z)
                                </div>
                                <div class="sort-option" onclick="sortTable('medecins', 'nom', 'desc')">
                                    <i class="fas fa-sort-alpha-up"></i> Nom (Z → A)
                                </div>
                                <div class="sort-option" onclick="sortTable('medecins', 'date', 'asc')">
                                    <i class="fas fa-calendar-plus"></i> Date (plus ancien)
                                </div>
                                <div class="sort-option" onclick="sortTable('medecins', 'date', 'desc')">
                                    <i class="fas fa-calendar-minus"></i> Date (plus récent)
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="openAddModal('medecin')">
                        <i class="fas fa-plus"></i> Ajouter un médecin
                    </button>
                </div>
                
                <div class="card" style="padding: 0; overflow: hidden;">
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Adresse</th>
                                    <th>Date d'inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="medecinsTable">
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px;">
                                        <i class="fas fa-spinner fa-spin"></i> Chargement...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Stats Innovant avec animations -->
<div id="statsModal" class="stats-overlay">
    <div class="stats-modal">
        <div class="stats-header">
            <button class="close-stats" onclick="closeStatsModal()">
                <i class="fas fa-times"></i>
            </button>
            <h2>
                <i class="fas fa-chart-line"></i> Statistiques Globales
            </h2>
            <p>Analyse détaillée de la répartition des utilisateurs</p>
        </div>
        <div class="stats-body">
            <div class="donut-container">
                <div class="donut-chart" id="donutChart">
                    <svg width="200" height="200" viewBox="0 0 200 200" id="donutSvg">
                        <circle cx="100" cy="100" r="80" fill="none" stroke="#e0e0e0" stroke-width="30"/>
                        <circle cx="100" cy="100" r="80" fill="none" stroke="#667eea" stroke-width="30" id="patientsArc" stroke-dasharray="0 502.4" stroke-linecap="round" transform="rotate(-90 100 100)"/>
                        <circle cx="100" cy="100" r="80" fill="none" stroke="#f5576c" stroke-width="30" id="medecinsArc" stroke-dasharray="0 502.4" stroke-linecap="round" transform="rotate(-90 100 100)"/>
                    </svg>
                    <div class="donut-inner">
                        <div class="total-number" id="modalTotalUsers">0</div>
                        <div class="total-label">Total</div>
                    </div>
                </div>
            </div>
            
            <div class="stats-details">
                <div class="stat-item">
                    <div class="stat-item-icon patients">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <div class="stat-item-number" id="modalPatientsCount">0</div>
                    <div class="stat-item-percent" id="patientsPercent">0%</div>
                    <div style="margin-top: 10px; font-size: 0.85rem; color: var(--text-muted);">des utilisateurs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item-icon medecins">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-item-number" id="modalMedecinsCount">0</div>
                    <div class="stat-item-percent" id="medecinsPercent">0%</div>
                    <div style="margin-top: 10px; font-size: 0.85rem; color: var(--text-muted);">des utilisateurs</div>
                </div>
            </div>
            
            <div class="progress-bar-container">
                <div class="progress-item">
                    <div class="progress-label">
                        <span><i class="fas fa-user-injured"></i> Patients</span>
                        <span id="progressPatientsPercent">0%</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill patients" id="progressPatients">
                            0%
                        </div>
                    </div>
                </div>
                <div class="progress-item">
                    <div class="progress-label">
                        <span><i class="fas fa-user-md"></i> Médecins</span>
                        <span id="progressMedecinsPercent">0%</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill medecins" id="progressMedecins">
                            0%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals (Ajout/Modification) -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">
                <i class="fas fa-user-plus"></i> Ajouter un utilisateur
            </h3>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="userId">
            <input type="hidden" id="userRole">
            
            <div class="form-group">
                <label class="form-label">Nom complet</label>
                <input type="text" class="form-control" id="userNom" placeholder="Nom et prénom">
            </div>
            
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="userEmail" placeholder="email@exemple.com">
            </div>
            
            <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input type="tel" class="form-control" id="userTelephone" placeholder="+212 XXX XXX XXX">
            </div>
            
            <div class="form-group">
                <label class="form-label">Adresse</label>
                <textarea class="form-control" id="userAdresse" rows="2" placeholder="Adresse complète"></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" id="resetPassword"> Réinitialiser le mot de passe
                </label>
                <div id="passwordField" style="display: none;">
                    <input type="password" class="form-control" id="userPassword" placeholder="Nouveau mot de passe">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal()">Annuler</button>
            <button class="btn btn-primary" onclick="saveUser()">Enregistrer</button>
        </div>
    </div>
</div>

<!-- Modal Suppression -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-trash-alt" style="color: var(--danger);"></i> Confirmer la suppression
            </h3>
            <button class="modal-close" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cet utilisateur ?</p>
            <p style="font-size: 0.85rem; color: var(--danger);">Cette action est irréversible.</p>
            <input type="hidden" id="deleteUserId">
            <input type="hidden" id="deleteUserRole">
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeDeleteModal()">Annuler</button>
            <button class="btn btn-danger" onclick="confirmDelete()">Supprimer</button>
        </div>
    </div>
</div>

<script>
    let currentTab = 'patients';
    
    // Variables pour le tri
    let patientsData = [];
    let medecinsData = [];
    let currentSort = {
        patients: { column: 'id', direction: 'desc' },
        medecins: { column: 'id', direction: 'desc' }
    };

    document.addEventListener('DOMContentLoaded', function() {
        loadUsers();
    });
    
    function loadUsers() {
        fetch('back.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    patientsData = data.patients;
                    medecinsData = data.medecins;
                    updateStats(data);
                    renderPatientsTable(patientsData);
                    renderMedecinsTable(medecinsData);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
    }
    
    function updateStats(data) {
        const totalPatients = data.patients.length;
        const totalMedecins = data.medecins.length;
        const totalUsers = totalPatients + totalMedecins;
        
        document.getElementById('totalPatients').textContent = totalPatients;
        document.getElementById('totalMedecins').textContent = totalMedecins;
        document.getElementById('totalUsers').textContent = totalUsers;
    }
    
    // Fonction pour mettre à jour le donut chart
    function updateDonutChart(patientsPercent, medecinsPercent) {
        const circumference = 2 * Math.PI * 80; // 502.6548
        
        const patientsArc = document.getElementById('patientsArc');
        const medecinsArc = document.getElementById('medecinsArc');
        
        if (patientsArc) {
            const patientsOffset = circumference - (patientsPercent / 100) * circumference;
            patientsArc.style.strokeDasharray = `${circumference} ${circumference}`;
            patientsArc.style.strokeDashoffset = patientsOffset;
        }
        
        if (medecinsArc) {
            const medecinsOffset = circumference - (medecinsPercent / 100) * circumference;
            medecinsArc.style.strokeDasharray = `${circumference} ${circumference}`;
            medecinsArc.style.strokeDashoffset = medecinsOffset + (circumference - (patientsPercent / 100) * circumference);
        }
    }
    
    // Réinitialiser les animations du modal
    function resetModalAnimations() {
        const statItems = document.querySelectorAll('.stat-item');
        const progressItems = document.querySelectorAll('.progress-item');
        
        statItems.forEach(item => {
            item.style.animation = 'none';
            item.offsetHeight;
            item.style.animation = 'slideIn 0.5s ease forwards';
        });
        
        progressItems.forEach((item, index) => {
            item.style.animation = 'none';
            item.offsetHeight;
            item.style.animation = `fadeInUp 0.5s ease forwards ${0.3 + index * 0.1}s`;
        });
    }
    
    // Fonction pour afficher le modal des stats
    function showStatsModal() {
        const totalPatients = parseInt(document.getElementById('totalPatients').textContent) || 0;
        const totalMedecins = parseInt(document.getElementById('totalMedecins').textContent) || 0;
        const totalUsers = totalPatients + totalMedecins;
        
        if (totalUsers === 0) {
            showNotification('Aucune donnée disponible pour afficher les statistiques', 'error');
            return;
        }
        
        const patientsPercent = ((totalPatients / totalUsers) * 100).toFixed(1);
        const medecinsPercent = ((totalMedecins / totalUsers) * 100).toFixed(1);
        
        // Mettre à jour les valeurs dans le modal
        document.getElementById('modalTotalUsers').textContent = totalUsers;
        document.getElementById('modalPatientsCount').textContent = totalPatients;
        document.getElementById('modalMedecinsCount').textContent = totalMedecins;
        document.getElementById('patientsPercent').textContent = patientsPercent + '%';
        document.getElementById('medecinsPercent').textContent = medecinsPercent + '%';
        document.getElementById('progressPatientsPercent').textContent = patientsPercent + '%';
        document.getElementById('progressMedecinsPercent').textContent = medecinsPercent + '%';
        
        // Réinitialiser les barres de progression à 0%
        const progressPatients = document.getElementById('progressPatients');
        const progressMedecins = document.getElementById('progressMedecins');
        
        if (progressPatients) {
            progressPatients.style.width = '0%';
            progressPatients.textContent = '0%';
        }
        
        if (progressMedecins) {
            progressMedecins.style.width = '0%';
            progressMedecins.textContent = '0%';
        }
        
        // Animer les barres de progression après un court délai
        setTimeout(() => {
            if (progressPatients) {
                progressPatients.style.width = patientsPercent + '%';
                setTimeout(() => {
                    progressPatients.textContent = patientsPercent + '%';
                }, 500);
            }
            
            if (progressMedecins) {
                progressMedecins.style.width = medecinsPercent + '%';
                setTimeout(() => {
                    progressMedecins.textContent = medecinsPercent + '%';
                }, 500);
            }
        }, 200);
        
        // Mettre à jour le donut chart
        updateDonutChart(parseFloat(patientsPercent), parseFloat(medecinsPercent));
        
        // Réinitialiser et relancer les animations
        resetModalAnimations();
        
        // Afficher le modal
        const modal = document.getElementById('statsModal');
        if (modal) {
            modal.classList.add('active');
        }
    }
    
    function closeStatsModal() {
        const modal = document.getElementById('statsModal');
        if (modal) {
            modal.classList.remove('active');
        }
    }
    
    // Fonction pour afficher/masquer le menu de tri
    function toggleSortMenu(tableType) {
        const menu = document.getElementById(`sortMenu${tableType.charAt(0).toUpperCase() + tableType.slice(1)}`);
        if (!menu) return;
        
        if (menu.style.display === 'none' || menu.style.display === '') {
            document.querySelectorAll('.sort-menu').forEach(m => m.style.display = 'none');
            menu.style.display = 'block';
            
            setTimeout(() => {
                document.addEventListener('click', function closeMenu(e) {
                    if (!menu.contains(e.target) && !e.target.closest('.btn-outline')) {
                        menu.style.display = 'none';
                        document.removeEventListener('click', closeMenu);
                    }
                });
            }, 100);
        } else {
            menu.style.display = 'none';
        }
    }
    
    // Fonction de tri
    function sortTable(tableType, column, direction) {
        currentSort[tableType] = { column, direction };
        
        let sortedData = [];
        if (tableType === 'patients') {
            sortedData = [...patientsData];
        } else {
            sortedData = [...medecinsData];
        }
        
        sortedData.sort((a, b) => {
            let valueA, valueB;
            
            if (column === 'nom') {
                valueA = (a.nom || '').toLowerCase();
                valueB = (b.nom || '').toLowerCase();
            } else if (column === 'date') {
                valueA = new Date(a.date_creation);
                valueB = new Date(b.date_creation);
            } else {
                return 0;
            }
            
            if (direction === 'asc') {
                if (valueA < valueB) return -1;
                if (valueA > valueB) return 1;
                return 0;
            } else {
                if (valueA < valueB) return 1;
                if (valueA > valueB) return -1;
                return 0;
            }
        });
        
        if (tableType === 'patients') {
            renderPatientsTable(sortedData);
            filterPatients();
        } else {
            renderMedecinsTable(sortedData);
            filterMedecins();
        }
        
        const menu = document.getElementById(`sortMenu${tableType.charAt(0).toUpperCase() + tableType.slice(1)}`);
        if (menu) menu.style.display = 'none';
    }
    
    function filterPatients() {
        const searchTerm = document.getElementById('searchPatient').value.toLowerCase();
        const rows = document.querySelectorAll('#patientsTable tr');
        rows.forEach(row => {
            if (row.classList.contains('no-data')) return;
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }
    
    function filterMedecins() {
        const searchTerm = document.getElementById('searchMedecin').value.toLowerCase();
        const rows = document.querySelectorAll('#medecinsTable tr');
        rows.forEach(row => {
            if (row.classList.contains('no-data')) return;
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }
    
    function renderPatientsTable(patients) {
        const tbody = document.getElementById('patientsTable');
        if (!tbody) return;
        
        if (!patients || patients.length === 0) {
            tbody.innerHTML = '<tr class="no-data"><td colspan="6" style="text-align: center; padding: 40px;"><i class="fas fa-inbox"></i> Aucun patient trouvé</td></tr>';
            return;
        }
        tbody.innerHTML = patients.map(patient => `
            <tr>
                <td>${patient.id_user}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="user-avatar-small">${(patient.nom || 'P')[0].toUpperCase()}</div>
                        <span>${escapeHtml(patient.nom || 'Sans nom')}</span>
                    </div>
                </td>
                <td>${escapeHtml(patient.email)}</td>
                <td>${patient.telephone || '-'}</td>
                <td>${formatDate(patient.date_creation)}</td>
                <td class="table-actions">
                    <button class="btn btn-primary btn-icon btn-sm" onclick="editUser(${patient.id_user}, 'patient')">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn btn-danger btn-icon btn-sm" onclick="openDeleteModal(${patient.id_user}, 'patient')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
    
    function renderMedecinsTable(medecins) {
        const tbody = document.getElementById('medecinsTable');
        if (!tbody) return;
        
        if (!medecins || medecins.length === 0) {
            tbody.innerHTML = '<tr class="no-data"><td colspan="7" style="text-align: center; padding: 40px;"><i class="fas fa-inbox"></i> Aucun médecin trouvé</td></tr>';
            return;
        }
        tbody.innerHTML = medecins.map(medecin => `
            <tr>
                <td>${medecin.id_user}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="user-avatar-small">${(medecin.nom || 'M')[0].toUpperCase()}</div>
                        <span>${escapeHtml(medecin.nom || 'Sans nom')}</span>
                    </div>
                </td>
                <td>${escapeHtml(medecin.email)}</td>
                <td>${medecin.telephone || '-'}</td>
                <td class="adresse-cell">${escapeHtml(medecin.adresse) || '-'}</td>
                <td>${formatDate(medecin.date_creation)}</td>
                <td class="table-actions">
                    <button class="btn btn-primary btn-icon btn-sm" onclick="editUser(${medecin.id_user}, 'medecin')">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn btn-danger btn-icon btn-sm" onclick="openDeleteModal(${medecin.id_user}, 'medecin')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
    
    function editUser(id, role) {
        fetch(`back.php?action=get_user&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('userId').value = data.user.id_user;
                    document.getElementById('userRole').value = role;
                    document.getElementById('userNom').value = data.user.nom || '';
                    document.getElementById('userEmail').value = data.user.email || '';
                    document.getElementById('userTelephone').value = data.user.telephone || '';
                    document.getElementById('userAdresse').value = data.user.adresse || '';
                    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Modifier ' + (role === 'patient' ? 'le patient' : 'le médecin');
                    document.getElementById('resetPassword').checked = false;
                    document.getElementById('passwordField').style.display = 'none';
                    document.getElementById('userModal').classList.add('active');
                } else {
                    showNotification(data.message || 'Erreur lors du chargement', 'error');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showNotification('Erreur de connexion', 'error');
            });
    }
    
    function saveUser() {
        const userId = document.getElementById('userId').value;
        const role = document.getElementById('userRole').value;
        const data = {
            id: userId || null,
            nom: document.getElementById('userNom').value,
            email: document.getElementById('userEmail').value,
            telephone: document.getElementById('userTelephone').value,
            adresse: document.getElementById('userAdresse').value,
            role: role,
            reset_password: document.getElementById('resetPassword').checked,
            new_password: document.getElementById('userPassword').value
        };
        fetch('back.php?action=save_user', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                closeModal();
                loadUsers();
                showNotification('Utilisateur sauvegardé avec succès', 'success');
            } else {
                showNotification(result.message || 'Erreur lors de la sauvegarde', 'error');
            }
        });
    }
    
    function confirmDelete() {
        const id = document.getElementById('deleteUserId').value;
        fetch('back.php?action=delete_user', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                closeDeleteModal();
                loadUsers();
                showNotification('Utilisateur supprimé avec succès', 'success');
            } else {
                showNotification(result.message || 'Erreur lors de la suppression', 'error');
            }
        });
    }
    
    function openAddModal(role) {
        document.getElementById('userId').value = '';
        document.getElementById('userRole').value = role;
        document.getElementById('userNom').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userTelephone').value = '';
        document.getElementById('userAdresse').value = '';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Ajouter un ' + (role === 'patient' ? 'patient' : 'médecin');
        document.getElementById('resetPassword').checked = true;
        document.getElementById('passwordField').style.display = 'block';
        document.getElementById('userModal').classList.add('active');
    }
    
    function switchTab(tab) {
        currentTab = tab;
        const tabs = document.querySelectorAll('.tab-btn');
        const contents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(btn => btn.classList.remove('active'));
        contents.forEach(content => content.classList.remove('active'));
        
        if (tab === 'patients') {
            tabs[0].classList.add('active');
            document.getElementById('patientsTab').classList.add('active');
        } else {
            tabs[1].classList.add('active');
            document.getElementById('medecinsTab').classList.add('active');
        }
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('fr-FR');
    }
    
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) sidebar.classList.toggle('open');
    }
    
    function toggleSubMenu(element) {
        if (!element) return;
        const parent = element.closest('.has-sub');
        if (parent) {
            parent.classList.toggle('open');
            const subMenu = parent.querySelector('.sub-menu');
            if (subMenu) subMenu.classList.toggle('open');
        }
    }
    
    function closeModal() {
        const modal = document.getElementById('userModal');
        if (modal) modal.classList.remove('active');
    }
    
    function openDeleteModal(id, role) {
        document.getElementById('deleteUserId').value = id;
        document.getElementById('deleteUserRole').value = role;
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) deleteModal.classList.add('active');
    }
    
    function closeDeleteModal() {
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) deleteModal.classList.remove('active');
    }
    
    function refreshData() {
        loadUsers();
        showNotification('Données actualisées', 'info');
    }
    
    function showNotification(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'}`;
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.maxWidth = '300px';
        alertDiv.style.padding = '12px 16px';
        alertDiv.style.borderRadius = '8px';
        alertDiv.style.backgroundColor = type === 'success' ? '#d1fae5' : type === 'error' ? '#fee2e2' : '#dbeafe';
        alertDiv.style.color = type === 'success' ? '#065f46' : type === 'error' ? '#991b1b' : '#1e40af';
        alertDiv.style.fontSize = '0.875rem';
        alertDiv.style.fontWeight = '500';
        alertDiv.style.boxShadow = '0 4px 6px -1px rgba(0,0,0,0.1)';
        alertDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}" style="margin-right: 8px;"></i> ${message}`;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000);
    }
    
    // Écouteurs d'événements
    const resetPasswordCheckbox = document.getElementById('resetPassword');
    if (resetPasswordCheckbox) {
        resetPasswordCheckbox.addEventListener('change', function() {
            const passwordField = document.getElementById('passwordField');
            if (passwordField) {
                passwordField.style.display = this.checked ? 'block' : 'none';
            }
        });
    }
    
    const searchPatient = document.getElementById('searchPatient');
    if (searchPatient) {
        searchPatient.addEventListener('input', filterPatients);
    }
    
    const searchMedecin = document.getElementById('searchMedecin');
    if (searchMedecin) {
        searchMedecin.addEventListener('input', filterMedecins);
    }
    
    // Fermer les menus au clic en dehors
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.sort-menu') && !e.target.closest('.btn-outline')) {
            document.querySelectorAll('.sort-menu').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    });
    
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
        }
        if (event.target.classList.contains('stats-overlay')) {
            closeStatsModal();
        }
    }
</script>
</body>
</html>