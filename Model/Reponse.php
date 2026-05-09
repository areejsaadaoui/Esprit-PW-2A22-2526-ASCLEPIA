<?php
class Reponse {
    private $id_rep;
    private $texte_rep;
    private $date_rep;
    private $id_utilisateur;
    private $id_post;
    private $auteur_nom;

    public function __construct($id_rep, $texte_rep, $date_rep, $id_utilisateur, $id_post) {
        $this->id_rep = $id_rep;
        $this->texte_rep = $texte_rep;
        $this->date_rep = $date_rep;
        $this->id_utilisateur = $id_utilisateur;
        $this->id_post = $id_post;
    }

    public function getIdRep() { return $this->id_rep; }
    public function getTexteRep() { return $this->texte_rep; }
    public function getDateRep() { return $this->date_rep; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }
    public function getIdPost() { return $this->id_post; }
    
    public function setAuteurNom($nom) {
        $this->auteur_nom = $nom;
        return $this;
    }
    
    public function getAuteurNom() {
        return $this->auteur_nom ?? 'Utilisateur #' . $this->id_utilisateur;
    }
}
?>