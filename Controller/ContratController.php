<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/Contrat.php');

class ContratController {

    public function listContrats() {
        $sql = "SELECT c.*, a.nom_assurance 
                FROM contrat c 
                JOIN assurance a ON c.id_assurance = a.id_assurance";
        $db  = config::getConnexion();
        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
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
        $sql = "SELECT c.*, a.nom_assurance 
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
}
?>