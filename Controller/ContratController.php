<?php
require_once(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/Contrat.php');

class ContratController {

    public function listContrats() {
        $sql = "SELECT c.*, a.nom_assurance, a.TYPE AS type_assurance
                FROM contrat c 
                JOIN assurance a ON c.id_assurance = a.id_assurance";
        $db  = config::getConnexion();
        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getContratsByAssurance($id_assurance) {
        $sql = "SELECT c.*, a.nom_assurance, a.TYPE AS type_assurance
                FROM contrat c 
                JOIN assurance a ON c.id_assurance = a.id_assurance
                WHERE c.id_assurance = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([':id' => $id_assurance]);
        return $query->fetchAll();
    }

    public function addContrat(Contrat $contrat) {
        $sql = "INSERT INTO contrat VALUES (NULL, :date_d, :date_f, :id_assurance, :montant, :statut)";
        $db  = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'date_d'       => $contrat->getDateD(),
                'date_f'       => $contrat->getDateF(),
                'id_assurance' => $contrat->getIdAssurance(),
                'montant'      => $contrat->getMontant(),
                'statut'       => $contrat->getStatut(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateContrat(Contrat $contrat, $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'UPDATE contrat SET
                    date_d       = :date_d,
                    date_f       = :date_f,
                    id_assurance = :id_assurance,
                    montant      = :montant,
                    statut       = :statut
                WHERE id_contrat = :id'
            );
            $query->execute([
                'id'           => $id,
                'date_d'       => $contrat->getDateD(),
                'date_f'       => $contrat->getDateF(),
                'id_assurance' => $contrat->getIdAssurance(),
                'montant'      => $contrat->getMontant(),
                'statut'       => $contrat->getStatut(),
            ]);
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function deleteContrat($id) {
        $sql = "DELETE FROM contrat WHERE id_contrat = :id";
        $db  = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function showContrat($id) {
        $sql = "SELECT c.*, a.nom_assurance, a.TYPE AS type_assurance
                FROM contrat c 
                JOIN assurance a ON c.id_assurance = a.id_assurance
                WHERE c.id_contrat = :id";
        $db    = config::getConnexion();
        $query = $db->prepare($sql);
        try {
            $query->execute([':id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function listAssurances() {
        $sql = "SELECT id_assurance, nom_assurance FROM assurance";
        $db  = config::getConnexion();
        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function listAssurancesDetails() {
        $sql = "SELECT * FROM assurance";
        $db  = config::getConnexion();
        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function listActiveContrats($id_patient = 1) {
        $sql = "SELECT c.*, a.nom_assurance, a.TYPE AS type_assurance
                FROM contrat c
                JOIN assurance a ON c.id_assurance = a.id_assurance
                WHERE c.statut = 'Actif'
                  AND (c.date_f IS NULL OR c.date_f = '' OR c.date_f >= CURDATE())
                  AND c.id_patient = :id_patient
                ORDER BY c.date_d DESC";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([':id_patient' => $id_patient]);
        return $query->fetchAll();
    }

    public function addContratWithToken(Contrat $contrat, string $token) {
        $sql = "INSERT INTO contrat (date_d, date_f, id_assurance, montant, statut, id_patient, token) 
                VALUES (:date_d, :date_f, :id_assurance, :montant, :statut, :id_patient, :token)";
        $db  = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'date_d'       => $contrat->getDateD(),
                'date_f'       => $contrat->getDateF(),
                'id_assurance' => $contrat->getIdAssurance(),
                'montant'      => $contrat->getMontant(),
                'statut'       => $contrat->getStatut(),
                'id_patient'   => 1,
                'token'        => $token,
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function getUserEmail($id_patient) {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT email FROM utilisateur WHERE id_user = :id");
        $stmt->execute([':id' => $id_patient]);
        $user = $stmt->fetch();
        return $user['email'] ?? null;
    }

    public function confirmerContratByToken(string $token) {
        $db   = config::getConnexion();
        $stmt = $db->prepare("SELECT id_contrat, statut FROM contrat WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $contrat = $stmt->fetch();

        if (!$contrat) return 'invalid';
        if ($contrat['statut'] !== 'En attente') return 'already_confirmed';

        $upd = $db->prepare("UPDATE contrat SET statut = 'Actif', token = NULL WHERE token = :token");
        $upd->execute([':token' => $token]);
        return 'success';
    }
}
?>