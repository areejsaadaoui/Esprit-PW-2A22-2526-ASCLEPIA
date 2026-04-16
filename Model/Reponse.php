<?php
class Reponse {
    private ?int    $id_rep;
    private ?string $texte_rep;
    private ?string $date_rep;
    private ?int    $id_utilisateur;
    private ?int    $id_post;

    public function __construct(
        ?int $id_rep, ?string $texte_rep, 
        ?string $date_rep, ?int $id_utilisateur, 
        ?int $id_post
    ) {
        $this->id_rep         = $id_rep;
        $this->texte_rep      = $texte_rep;
        $this->date_rep       = $date_rep;
        $this->id_utilisateur = $id_utilisateur;
        $this->id_post        = $id_post;
    }

    // Getters
    public function getIdRep()          { return $this->id_rep; }
    public function getTexteRep()       { return $this->texte_rep; }
    public function getDateRep()        { return $this->date_rep; }
    public function getIdUtilisateur()  { return $this->id_utilisateur; }
    public function getIdPost()         { return $this->id_post; }
    public function getAuteur() { return $this->auteur ?? 'Anonyme'; }
    // Setters
    public function setTexteRep(?string $texte_rep) { $this->texte_rep = $texte_rep; }
    public function setIdPost(?int $id_post)        { $this->id_post = $id_post; }
}
?>