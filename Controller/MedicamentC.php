<?php 

require_once __DIR__ . '/../config.php';
include __DIR__ . '/../model/medicament.php';
require_once __DIR__ . '/PharmacieC.php';

class medicamentC
{
    function ajouterMedicament($medicament)
    {
        $sql = "INSERT INTO medicament (nom_medicament, prix_medicament, stock, categorie, image, id_pharmacie) VALUES (:nom, :prix, :stock, :categorie, :images, :id_pharmacie)";
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

            // ---- MÉTIER AVANCÉ : ALERTE SMS ----
            if ($medicament->getStock() <= 5) {
                require_once __DIR__ . '/SmsC.php';
                $sms = new SmsC();
                $mon_numero = "+21692717357"; 
                $msg = "📢 ASCLEPIA Alert: Nouveau médicament " . $medicament->getNom() . " ajouté avec un stock critique (" . $medicament->getStock() . ").";
                $sms->sendSms($mon_numero, $msg);
            }
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }

    function afficherMedicaments()
    {
        $sql = "SELECT m.id_medicament, m.nom_medicament AS nom, m.prix_medicament AS prix, m.stock, m.categorie, m.type, m.image AS images, m.id_pharmacie, p.nom AS nom_pharmacie
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
                    nom_medicament = :nom,
                    prix_medicament = :prix,
                    stock = :stock,
                    categorie = :categorie,
                    image = :images,
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

            // ---- MÉTIER AVANCÉ : ALERTE SMS ----
            // Si le nouveau stock est critique (<= 5), on envoie une alerte SMS
            if ($medicament->getStock() <= 5) {
                require_once __DIR__ . '/SmsC.php';
                $sms = new SmsC();
                // Remplacez par votre numéro de téléphone (format international : +216...)
                $mon_numero = "+21692717357"; 
                $msg = "📢 ASCLEPIA Alert: Stock critique pour " . $medicament->getNom() . " (" . $medicament->getStock() . " restants). Pensez à réapprovisionner !";
                $sms->sendSms($mon_numero, $msg);
            }

        } catch (Exception $e) {
            echo "Erreur: " . $e->getMessage();
        }
    }

    function recupererMedicament($id_medicament)
    {
        $sql = "SELECT id_medicament, nom_medicament AS nom, prix_medicament AS prix, stock, categorie, type, image AS images, id_pharmacie
                FROM medicament WHERE id_medicament = :id_medicament";
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