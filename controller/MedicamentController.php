<?php 

include '../config.php';
class medicamentC
{
    function ajouterMedicament($medicament)
    {
        $sql = "INSERT INTO medicament (nom, prix, stock,categorie,images) VALUES (:nom,  :prix, :stock,:categorie,:images)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $medicament->getNom(),
                'prix' => $medicament->getPrix(),
                'stock' => $medicament->getStock(),
                'categorie' => $medicament->getCategorie(),
                'images' => $medicament->getImages  ()
            ]);
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }

    function afficherMedicaments()
    {
        $sql = "SELECT * FROM medicament";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}