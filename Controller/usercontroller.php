<?php
include(__DIR__ . '/../config.php');

class UserController {

    // ==================== PATIENT ====================
    
    // Ajouter un patient (utilisateur + patient)
    public function addPatient($nom, $email, $mot_de_passe, $adresse, $date_naissance, $telephone) {
        $db = config::getConnexion();
        
        try {
            $db->beginTransaction();
            
            // Vérifier si l'email existe déjà
            $check = $db->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email");
            $check->execute([':email' => $email]);
            if ($check->fetchColumn() > 0) {
                throw new Exception('Cet email existe déjà');
            }
            
            // Insérer dans utilisateur
            $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $sql = "INSERT INTO utilisateur (nom, email, mot_de_passe, adresse, role, date_creation) 
                    VALUES (:nom, :email, :mot_de_passe, :adresse, 'user', NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':nom' => $nom,
                ':email' => $email,
                ':mot_de_passe' => $hashed_password,
                ':adresse' => $adresse
            ]);
            $id_user = $db->lastInsertId();
            
            // Insérer dans patient
            $sql = "INSERT INTO patient (id_user, date_naissance, telephone) 
                    VALUES (:id_user, :date_naissance, :telephone)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id_user' => $id_user,
                ':date_naissance' => $date_naissance,
                ':telephone' => $telephone
            ]);
            
            $db->commit();
            return ['success' => true, 'id_user' => $id_user, 'message' => 'Patient ajouté avec succès'];
            
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Récupérer tous les patients
    public function getAllPatients() {
        $sql = "SELECT u.*, p.id_patient, p.date_naissance, p.telephone 
                FROM utilisateur u 
                INNER JOIN patient p ON u.id_user = p.id_user 
                ORDER BY u.id_user DESC";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Récupérer un patient par son ID
    public function getPatientById($id_user) {
        $sql = "SELECT u.*, p.id_patient, p.date_naissance, p.telephone 
                FROM utilisateur u 
                INNER JOIN patient p ON u.id_user = p.id_user 
                WHERE u.id_user = :id_user";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([':id_user' => $id_user]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    // Mettre à jour un patient
    public function updatePatient($id_user, $nom, $email, $adresse, $date_naissance, $telephone) {
        $db = config::getConnexion();
        
        try {
            $db->beginTransaction();
            
            // Mettre à jour utilisateur
            $sql = "UPDATE utilisateur SET nom = :nom, email = :email, adresse = :adresse WHERE id_user = :id_user";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id_user' => $id_user,
                ':nom' => $nom,
                ':email' => $email,
                ':adresse' => $adresse
            ]);
            
            // Mettre à jour patient
            $sql = "UPDATE patient SET date_naissance = :date_naissance, telephone = :telephone WHERE id_user = :id_user";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id_user' => $id_user,
                ':date_naissance' => $date_naissance,
                ':telephone' => $telephone
            ]);
            
            $db->commit();
            return ['success' => true, 'message' => 'Patient modifié avec succès'];
            
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Supprimer un patient (supprime l'utilisateur, patient est supprimé automatiquement par CASCADE)
    public function deletePatient($id_user) {
        $sql = "DELETE FROM utilisateur WHERE id_user = :id_user";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([':id_user' => $id_user]);
            return ['success' => true, 'message' => 'Patient supprimé avec succès'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Compter le nombre de patients
    public function countPatients() {
        $sql = "SELECT COUNT(*) FROM patient";
        $db = config::getConnexion();
        try {
            $stmt = $db->query($sql);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    // ==================== MEDECIN ====================
    
    // Ajouter un médecin (utilisateur + medecin)
    public function addMedecin($nom, $email, $mot_de_passe, $adresse, $specialite, $numero = null) {
        $db = config::getConnexion();
        
        try {
            $db->beginTransaction();
            
            // Vérifier si l'email existe déjà
            $check = $db->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email");
            $check->execute([':email' => $email]);
            if ($check->fetchColumn() > 0) {
                throw new Exception('Cet email existe déjà');
            }
            
            // Insérer dans utilisateur
            $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $sql = "INSERT INTO utilisateur (nom, email, mot_de_passe, adresse, role, date_creation) 
                    VALUES (:nom, :email, :mot_de_passe, :adresse, 'admin', NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':nom' => $nom,
                ':email' => $email,
                ':mot_de_passe' => $hashed_password,
                ':adresse' => $adresse
            ]);
            $id_user = $db->lastInsertId();
            
            // Insérer dans medcin
            $sql = "INSERT INTO medcin (id_user, specialite, numero) 
                    VALUES (:id_user, :specialite, :numero)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id_user' => $id_user,
                ':specialite' => $specialite,
                ':numero' => $numero
            ]);
            
            $db->commit();
            return ['success' => true, 'id_user' => $id_user, 'message' => 'Médecin ajouté avec succès'];
            
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Récupérer tous les médecins
    public function getAllMedecins() {
        $sql = "SELECT u.*, m.id_medcin, m.specialite, m.numero 
                FROM utilisateur u 
                INNER JOIN medcin m ON u.id_user = m.id_user 
                ORDER BY u.id_user DESC";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Récupérer un médecin par son ID
    public function getMedecinById($id_user) {
        $sql = "SELECT u.*, m.id_medcin, m.specialite, m.numero 
                FROM utilisateur u 
                INNER JOIN medcin m ON u.id_user = m.id_user 
                WHERE u.id_user = :id_user";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([':id_user' => $id_user]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    // Mettre à jour un médecin
    public function updateMedecin($id_user, $nom, $email, $adresse, $specialite, $numero = null) {
        $db = config::getConnexion();
        
        try {
            $db->beginTransaction();
            
            // Mettre à jour utilisateur
            $sql = "UPDATE utilisateur SET nom = :nom, email = :email, adresse = :adresse WHERE id_user = :id_user";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id_user' => $id_user,
                ':nom' => $nom,
                ':email' => $email,
                ':adresse' => $adresse
            ]);
            
            // Mettre à jour medcin
            $sql = "UPDATE medcin SET specialite = :specialite, numero = :numero WHERE id_user = :id_user";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id_user' => $id_user,
                ':specialite' => $specialite,
                ':numero' => $numero
            ]);
            
            $db->commit();
            return ['success' => true, 'message' => 'Médecin modifié avec succès'];
            
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Supprimer un médecin (supprime l'utilisateur, medecin est supprimé automatiquement par CASCADE)
    public function deleteMedecin($id_user) {
        $sql = "DELETE FROM utilisateur WHERE id_user = :id_user";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([':id_user' => $id_user]);
            return ['success' => true, 'message' => 'Médecin supprimé avec succès'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Compter le nombre de médecins
    public function countMedecins() {
        $sql = "SELECT COUNT(*) FROM medcin";
        $db = config::getConnexion();
        try {
            $stmt = $db->query($sql);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    // ==================== METHODES GENERALES ====================
    
    // Vérifier si un email existe déjà
    public function emailExists($email) {
        $sql = "SELECT COUNT(*) FROM utilisateur WHERE email = :email";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([':email' => $email]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // Compter le nombre total d'utilisateurs (patients + médecins)
    public function countAllUsers() {
        $sql = "SELECT COUNT(*) FROM utilisateur";
        $db = config::getConnexion();
        try {
            $stmt = $db->query($sql);
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    // Récupérer tous les utilisateurs avec leur rôle spécifique
    public function getAllUsers() {
        $sql = "SELECT u.*, 
                CASE 
                    WHEN u.role = 'user' THEN 'Patient'
                    WHEN u.role = 'admin' THEN 'Médecin'
                    ELSE 'Inconnu'
                END as type_utilisateur
                FROM utilisateur u 
                ORDER BY u.id_user DESC";
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
?>