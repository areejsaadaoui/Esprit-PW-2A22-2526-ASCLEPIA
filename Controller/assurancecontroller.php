<?php
include(__DIR__ . '/../config.php');
include(__DIR__ . '/../Model/Assurance.php');

class AssuranceController {

    public function listAssurances() {
        $sql = "SELECT * FROM assurance";
        $db  = config::getConnexion();
        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function addAssurance(Assurance $assurance) {
        $sql = "INSERT INTO assurance VALUES (NULL, :nom_assurance, :description, :prix, :TYPE, :duree, :taux_remboursement)";
        $db  = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom_assurance'      => $assurance->getNomAssurance(),
                'description'        => $assurance->getDescription(),
                'prix'               => $assurance->getPrix(),
                'TYPE'               => $assurance->getTYPE(),
                'duree'              => $assurance->getDuree(),
                'taux_remboursement' => $assurance->getTauxRemboursement(),
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateAssurance(Assurance $assurance, $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'UPDATE assurance SET
                    nom_assurance      = :nom_assurance,
                    description        = :description,
                    prix               = :prix,
                    TYPE               = :TYPE,
                    duree              = :duree,
                    taux_remboursement = :taux_remboursement
                WHERE id_assurance = :id'
            );
            $query->execute([
                'id'                 => $id,
                'nom_assurance'      => $assurance->getNomAssurance(),
                'description'        => $assurance->getDescription(),
                'prix'               => $assurance->getPrix(),
                'TYPE'               => $assurance->getTYPE(),
                'duree'              => $assurance->getDuree(),
                'taux_remboursement' => $assurance->getTauxRemboursement(),
            ]);
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function deleteAssurance($id) {
        $sql = "DELETE FROM assurance WHERE id_assurance = :id";
        $db  = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function showAssurance($id) {
        $sql   = "SELECT * FROM assurance WHERE id_assurance = :id";
        $db    = config::getConnexion();
        $query = $db->prepare($sql);
        try {
            $query->execute([':id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>