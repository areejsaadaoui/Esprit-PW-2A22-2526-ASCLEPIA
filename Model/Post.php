<?php
class Post {
    private $id_post;
    private $contenu;
    private $date_post;
    private $image;
    private $id_utilisateur;
    private $likes;
    private $signalements;
    private $user_nom;
    private $user_avatar;

    public function __construct($id_post, $contenu, $date_post, $image, $id_utilisateur, $likes = 0, $signalements = 0) {
        $this->id_post = $id_post;
        $this->contenu = $contenu;
        $this->date_post = $date_post;
        $this->image = $image;
        $this->id_utilisateur = $id_utilisateur;
        $this->likes = $likes;
        $this->signalements = $signalements;
    }

    // Getters
    public function getIdPost() { return $this->id_post; }
    public function getContenu() { return $this->contenu; }
    public function getDatePost() { return $this->date_post; }
    public function getImage() { return $this->image; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }
    public function getLikes() { return $this->likes; }
    public function getSignalements() { return $this->signalements; }
    
    public function setUserNom($nom) {
        $this->user_nom = $nom;
        return $this;
    }
    public function getUserNom() {
        return $this->user_nom;
    }
    
    public function getUserFullName() {
        return $this->user_nom ?? 'Utilisateur #' . $this->id_utilisateur;
    }
    
    public function setUserAvatar($avatar) {
        $this->user_avatar = $avatar;
        return $this;
    }
    public function getUserAvatar() {
        return $this->user_avatar ?? 'default';
    }
}
?>