<?php
class Avis {
    private $id_avis;
    private $contenu;
    private $date_avis;
    private $image;
    private $id_utilisateur;
    public $auteur;
    public $categorie;
    public $note;
    public $titre;

    public function __construct($id_avis, $contenu, $date_avis, $image, $id_utilisateur) {
        $this->id_avis = $id_avis;
        $this->contenu = $contenu;
        $this->date_avis = $date_avis;
        $this->image = $image;
        $this->id_utilisateur = $id_utilisateur;
    }

    // getters
    public function getIdAvis() { return $this->id_avis; }
    public function getContenu() { return $this->contenu; }
    public function getDateAvis() { return $this->date_avis; }
    public function getImage() { return $this->image; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }
}
?>