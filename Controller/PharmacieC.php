<?php 

require_once __DIR__ . '/../config.php';
include __DIR__ . '/../model/pharmacie.php';

class pharmacieC
{
    public function ajouterPharmacie($pharmacie)
    {
        $sql = "INSERT INTO pharmacie (nom, adresse, telephone,email) VALUES (:nom, :adresse, :telephone,:email)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $pharmacie->getNom(),
                'adresse' => $pharmacie->getAdresse(),
                'telephone' => $pharmacie->getTelephone(),
                'email' => $pharmacie->getEmail()
            ]);
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }
    public function listepharmacie()
    {
        $sql = "SELECT * FROM pharmacie";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
    public function supprimerPharmacie($id_pharmacie)
    {
        $sql = "DELETE FROM pharmacie WHERE id_pharmacie = :id_pharmacie";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_pharmacie', $id_pharmacie);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
    public function modifierPharmacie($pharmacie, $id_pharmacie)
    {
        if ($pharmacie->getId() !== $id_pharmacie) {
            echo "ID de pharmacie ne correspond pas.";
            return;
        }
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE pharmacie SET 
                    nom = :nom, 
                    adresse = :adresse, 
                    telephone = :telephone,
                    email = :email
                WHERE id_pharmacie = :id_pharmacie'
            );
            $query->execute([
                'nom' => $pharmacie->getNom(),
                'adresse' => $pharmacie->getAdresse(),
                'telephone' => $pharmacie->getTelephone(),
                'email' => $pharmacie->getEmail(),
                'id_pharmacie' => $id_pharmacie
            ]);
        } catch (Exception $e) {
            echo "Erreur: " . $e->getMessage();
        }
    }
}