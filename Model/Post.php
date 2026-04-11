<?php
class Post {
    private ?int    $id_post;
    private ?string $contenu;
    private ?string $date_post;
    private ?string $image;
    private ?int    $id_utilisateur;

    // Constructeur
    public function __construct( ?int    $id_post, ?string $contenu, ?string $date_post, ?string $image, ?int    $id_utilisateur ) {
        $this->id_post        = $id_post;
        $this->contenu        = $contenu;
        $this->date_post      = $date_post;
        $this->image          = $image;
        $this->id_utilisateur = $id_utilisateur;
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

    // getters
    public function getIdPost():        ?int    { return $this->id_post; }
    public function getContenu():       ?string { return $this->contenu; }
    public function getDatePost():      ?string { return $this->date_post; }
    public function getImage():         ?string { return $this->image; }
    public function getIdUtilisateur(): ?int    { return $this->id_utilisateur; }
 
    // setters
    public function setIdPost(?int $id_post):               void { $this->id_post = $id_post; }
    public function setContenu(?string $contenu):           void { $this->contenu = $contenu; }
    public function setDatePost(?string $date_post):        void { $this->date_post = $date_post; }
    public function setImage(?string $image):               void { $this->image = $image; }
    public function setIdUtilisateur(?int $id_utilisateur): void { $this->id_utilisateur = $id_utilisateur;}
}
?>