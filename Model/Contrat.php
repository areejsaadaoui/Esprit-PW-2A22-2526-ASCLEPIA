<?php
class Contrat {
    private ?int $id_contrat;
    private ?string $date_d;
    private ?string $date_f;
    private ?int $id_assurance;
    private ?float $montant;
    private ?string $statut;

    public function __construct(
        ?int $id_contrat,
        ?string $date_d,
        ?string $date_f,
        ?int $id_assurance,
        ?float $montant,
        ?string $statut
    ) {
        $this->id_contrat   = $id_contrat;
        $this->date_d       = $date_d;
        $this->date_f       = $date_f;
        $this->id_assurance = $id_assurance;
        $this->montant      = $montant;
        $this->statut       = $statut;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>
                <th>ID</th><th>Date début</th><th>Date fin</th>
                <th>ID Assurance</th><th>Montant</th><th>Statut</th>
              </tr>";
        echo "<tr>";
        echo "<td>{$this->id_contrat}</td>";
        echo "<td>{$this->date_d}</td>";
        echo "<td>{$this->date_f}</td>";
        echo "<td>{$this->id_assurance}</td>";
        echo "<td>{$this->montant}</td>";
        echo "<td>{$this->statut}</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters
    public function getIdContrat(): ?int      { return $this->id_contrat; }
    public function getDateD(): ?string       { return $this->date_d; }
    public function getDateF(): ?string       { return $this->date_f; }
    public function getIdAssurance(): ?int    { return $this->id_assurance; }
    public function getMontant(): ?float      { return $this->montant; }
    public function getStatut(): ?string      { return $this->statut; }

    // Setters
    public function setIdContrat(?int $id): void      { $this->id_contrat = $id; }
    public function setDateD(?string $d): void        { $this->date_d = $d; }
    public function setDateF(?string $f): void        { $this->date_f = $f; }
    public function setIdAssurance(?int $id): void    { $this->id_assurance = $id; }
    public function setMontant(?float $m): void       { $this->montant = $m; }
    public function setStatut(?string $s): void       { $this->statut = $s; }
}
?>
