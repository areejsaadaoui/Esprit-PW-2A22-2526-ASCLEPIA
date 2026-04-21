<?php
// =============================================
// 1. AJOUT : VÉRIFICATION DE SESSION ADMIN
// =============================================
session_start();

// Vérifier que l'utilisateur est connecté ET qu'il est admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || 
    !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé. Veuillez vous reconnecter.']);
    exit();
}

// =============================================
// 2. LE RESTE DE VOTRE CODE ORIGINAL (inchangé)
// =============================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration de la base de données
$host = 'localhost';
$dbname = 'fleliss';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion: ' . $e->getMessage()]);
    exit();
}

// Récupérer l'action (si aucune action, c'est une requête GET normale pour récupérer tous les users)
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Si c'est une requête GET sans action, retourner tous les utilisateurs
if ($method === 'GET' && empty($action) && empty($_GET['id'])) {
    // Récupérer les patients
    $stmt = $pdo->query("SELECT id_user, nom, email, telephone, adresse, date_creation FROM utilisateur WHERE role = 'patient' ORDER BY id_user DESC");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupérer les médecins
    $stmt = $pdo->query("SELECT id_user, nom, email, telephone, adresse, date_creation, description as specialite FROM utilisateur WHERE role = 'medecin' ORDER BY id_user DESC");
    $medecins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'patients' => $patients, 'medecins' => $medecins]);
    exit();
}

switch($action) {
    case 'get_user':
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT id_user, nom, email, telephone, adresse, description as specialite FROM utilisateur WHERE id_user = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
        }
        break;
        
    case 'save_user':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            exit();
        }
        
        if (empty($data['nom']) || empty($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'Nom et email requis']);
            exit();
        }
        
        try {
            if (!empty($data['id'])) {
                // UPDATE
                $sql = "UPDATE utilisateur SET nom = :nom, email = :email, telephone = :telephone, adresse = :adresse";
                $params = [
                    ':nom' => $data['nom'],
                    ':email' => $data['email'],
                    ':telephone' => $data['telephone'] ?? null,
                    ':adresse' => $data['adresse'] ?? null,
                    ':id' => $data['id']
                ];
                
                if ($data['role'] === 'medecin') {
                    $sql .= ", description = :specialite";
                    $params[':specialite'] = $data['specialite'] ?? null;
                }
                
                if (!empty($data['reset_password']) && !empty($data['new_password'])) {
                    $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
                    $sql .= ", mot_de_passe = :password";
                    $params[':password'] = $hashedPassword;
                }
                
                $sql .= " WHERE id_user = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                echo json_encode(['success' => true, 'message' => 'Utilisateur modifié']);
            } else {
                // INSERT
                $hashedPassword = password_hash($data['new_password'] ?: 'password123', PASSWORD_DEFAULT);
                
                if ($data['role'] === 'medecin') {
                    $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, email, telephone, adresse, mot_de_passe, role, description, date_creation) VALUES (:nom, :email, :telephone, :adresse, :password, :role, :specialite, NOW())");
                    $stmt->execute([
                        ':nom' => $data['nom'],
                        ':email' => $data['email'],
                        ':telephone' => $data['telephone'] ?? null,
                        ':adresse' => $data['adresse'] ?? null,
                        ':password' => $hashedPassword,
                        ':role' => $data['role'],
                        ':specialite' => $data['specialite'] ?? null
                    ]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, email, telephone, adresse, mot_de_passe, role, date_creation) VALUES (:nom, :email, :telephone, :adresse, :password, :role, NOW())");
                    $stmt->execute([
                        ':nom' => $data['nom'],
                        ':email' => $data['email'],
                        ':telephone' => $data['telephone'] ?? null,
                        ':adresse' => $data['adresse'] ?? null,
                        ':password' => $hashedPassword,
                        ':role' => $data['role']
                    ]);
                }
                echo json_encode(['success' => true, 'message' => 'Utilisateur ajouté']);
            }
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['success' => false, 'message' => 'Cet email existe déjà']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
            }
        }
        break;
        
    case 'delete_user':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || empty($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID requis']);
            exit();
        }
        
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id_user = :id");
        $stmt->execute([':id' => $data['id']]);
        
        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
        break;
        
    case 'check_session':
        // AMÉLIORATION : retourne les vraies infos de session
        echo json_encode([
            'is_admin' => true, 
            'nom' => $_SESSION['user_nom'] ?? 'Administrateur',
            'email' => $_SESSION['user_email'] ?? '',
            'logged_in' => true
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action non valide']);
}
?>