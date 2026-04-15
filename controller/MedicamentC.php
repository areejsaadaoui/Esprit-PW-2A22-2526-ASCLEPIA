<?php 

require_once __DIR__ . '/../config.php';
include __DIR__ . '/../model/medicament.php';
require_once '../../Controller/PharmacieC.php';

class medicamentC
{
    function ajouterMedicament($medicament)
    {
        $sql = "INSERT INTO medicament (nom, prix, stock, categorie, images, id_pharmacie) VALUES (:nom, :prix, :stock, :categorie, :images, :id_pharmacie)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $medicament->getNom(),
                'prix' => $medicament->getPrix(),
                'stock' => $medicament->getStock(),
                'categorie' => $medicament->getCategorie(),
                'images' => $medicament->getImages(),
                'id_pharmacie' => $medicament->getIdPharmacie()
            ]);
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }

    function afficherMedicaments()
    {
        $sql = "SELECT m.*, p.nom as nom_pharmacie 
                FROM medicament m 
                JOIN pharmacie p ON m.id_pharmacie = p.id_pharmacie";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    function supprimerMedicament($id_medicament)
    {
        $sql = "DELETE FROM medicament WHERE id_medicament = :id_medicament";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_medicament', $id_medicament);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    function modifierMedicament($medicament, $id_medicament)
    {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE medicament SET 
                    nom = :nom, 
                    prix = :prix, 
                    stock = :stock,
                    categorie = :categorie,
                    images = :images,
                    id_pharmacie = :id_pharmacie
                WHERE id_medicament = :id_medicament'
            );
            $query->execute([
                'nom' => $medicament->getNom(),
                'prix' => $medicament->getPrix(),
                'stock' => $medicament->getStock(),
                'categorie' => $medicament->getCategorie(),
                'images' => $medicament->getImages(),
                'id_pharmacie' => $medicament->getIdPharmacie(),
                'id_medicament' => $id_medicament
            ]);
        } catch (Exception $e) {
            echo "Erreur: " . $e->getMessage();
        }
    }

    function recupererMedicament($id_medicament)
    {
        $sql = "SELECT * from medicament where id_medicament = :id_medicament";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_medicament' => $id_medicament]);
            $medicament = $query->fetch();
            return $medicament;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}