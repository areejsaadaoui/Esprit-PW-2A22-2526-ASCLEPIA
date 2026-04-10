<?php
class Assurance {
    private ?int $id_assurance;
    private ?string $nom_assurance;
    private ?string $description;
    private ?float $prix;
    private ?string $TYPE;
    private ?int $duree;
    private ?float $taux_remboursement;

    public function __construct(
        ?int $id_assurance,
        ?string $nom_assurance,
        ?string $description,
        ?float $prix,
        ?string $TYPE,
        ?int $duree,
        ?float $taux_remboursement
    ) {
        $this->id_assurance        = $id_assurance;
        $this->nom_assurance       = $nom_assurance;
        $this->description         = $description;
        $this->prix                = $prix;
        $this->TYPE                = $TYPE;
        $this->duree               = $duree;
        $this->taux_remboursement  = $taux_remboursement;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Prix (DT)</th>
                <th>Type</th>
                <th>Durée (mois)</th>
                <th>Taux de remboursement (%)</th>
              </tr>";
        echo "<tr>";
        echo "<td>{$this->id_assurance}</td>";
        echo "<td>{$this->nom_assurance}</td>";
        echo "<td>{$this->description}</td>";
        echo "<td>{$this->prix}</td>";
        echo "<td>{$this->TYPE}</td>";
        echo "<td>{$this->duree}</td>";
        echo "<td>{$this->taux_remboursement}</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters
    public function getId(): ?int                   { return $this->id_assurance; }
    public function getNomAssurance(): ?string       { return $this->nom_assurance; }
    public function getDescription(): ?string        { return $this->description; }
    public function getPrix(): ?float                { return $this->prix; }
    public function getTYPE(): ?string               { return $this->TYPE; }
    public function getDuree(): ?int                 { return $this->duree; }
    public function getTauxRemboursement(): ?float   { return $this->taux_remboursement; }

    // Setters
    public function setId(?int $id): void                      { $this->id_assurance = $id; }
    public function setNomAssurance(?string $nom): void         { $this->nom_assurance = $nom; }
    public function setDescription(?string $d): void            { $this->description = $d; }
    public function setPrix(?float $prix): void                 { $this->prix = $prix; }
    public function setTYPE(?string $type): void                { $this->TYPE = $type; }
    public function setDuree(?int $duree): void                 { $this->duree = $duree; }
    public function setTauxRemboursement(?float $t): void       { $this->taux_remboursement = $t; }
}
?>