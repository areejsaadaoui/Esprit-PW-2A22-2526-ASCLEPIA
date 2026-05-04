<?php
class Post {
    private ?int    $id_post;
    private ?string $contenu;
    private ?string $date_post;
    private ?string $image;
    private ?int    $id_utilisateur;
    private ?int    $likes;
    // Nouveaux attributs innovants
    private ?int    $signalements = 0;
    private ?string $sentiment    = null;
    private ?string $hashtags     = null;
    private ?int    $nb_reponses  = 0;
    private ?int    $score_popularite = 0;

    public function __construct( ?int $id_post, ?string $contenu, ?string $date_post, ?string $image, ?int $id_utilisateur, ?int $likes = 0 ) {
        $this->id_post        = $id_post;
        $this->contenu        = $contenu;
        $this->date_post      = $date_post;
        $this->image          = $image;
        $this->id_utilisateur = $id_utilisateur;
        $this->likes          = $likes;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Contenu</th><th>Date de publication</th><th>Image</th><th>ID de l'utilisateur</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id_post}</td>";
        echo "<td>{$this->contenu}</td>";
        echo "<td>{$this->date_post}</td>";
        echo "<td>{$this->image}</td>";
        echo "<td>{$this->id_utilisateur}</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters existants
    public function getIdPost():         ?int    { return $this->id_post; }
    public function getContenu():        ?string { return $this->contenu; }
    public function getDatePost():       ?string { return $this->date_post; }
    public function getImage():          ?string { return $this->image; }
    public function getIdUtilisateur():  ?int    { return $this->id_utilisateur; }
    public function getLikes():          ?int    { return $this->likes; }

    // Setters existants
    public function setIdPost(?int $id_post):               void { $this->id_post = $id_post; }
    public function setContenu(?string $contenu):           void { $this->contenu = $contenu; }
    public function setDatePost(?string $date_post):        void { $this->date_post = $date_post; }
    public function setImage(?string $image):               void { $this->image = $image; }
    public function setIdUtilisateur(?int $id_utilisateur): void { $this->id_utilisateur = $id_utilisateur; }
    public function setLikes(?int $likes):                  void { $this->likes = $likes; }

    // Nouveaux getters/setters innovants
    public function getSignalements():      ?int    { return $this->signalements; }
    public function setSignalements(?int $s): void  { $this->signalements = $s; }

    public function getSentiment():         ?string { return $this->sentiment; }
    public function setSentiment(?string $s): void  { $this->sentiment = $s; }

    public function getHashtags():          ?string { return $this->hashtags; }
    public function setHashtags(?string $h): void   { $this->hashtags = $h; }

    public function getNbReponses():        ?int    { return $this->nb_reponses; }
    public function setNbReponses(?int $n): void    { $this->nb_reponses = $n; }

    public function getScorePopularite():   ?int    { return $this->score_popularite; }
    public function setScorePopularite(?int $s): void { $this->score_popularite = $s; }

    /**
     * Détecter les hashtags dans le contenu et retourner un tableau
     */
    public function extractHashtags(): array {
        preg_match_all('/#([a-zA-ZÀ-ÿ0-9_]+)/', $this->contenu ?? '', $matches);
        return array_unique($matches[1] ?? []);
    }



}
?>