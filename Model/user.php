<?php

class User
{
    private ?int $idUser;
    private ?string $nom;
    private ?string $email;
    private ?string $motDePasse;
    private ?string $adresse;
    private ?string $role;
    private ?DateTime $dateCreation;
    private ?DateTime $dateNaissance;
    private ?string $telephone;
    private ?string $description;

    public function __construct(
        ?int $idUser,
        ?string $nom,
        ?string $email,
        ?string $motDePasse,
        ?string $adresse,
        ?string $role,
        ?DateTime $dateCreation = null,
        ?DateTime $dateNaissance = null,
        ?string $telephone = null,
        ?string $description = null
    ) {
        $this->idUser = $idUser;
        $this->nom = $nom;
        $this->email = $email;
        $this->motDePasse = $motDePasse;
        $this->adresse = $adresse;
        $this->role = $role;
        $this->dateCreation = $dateCreation;
        $this->dateNaissance = $dateNaissance;
        $this->telephone = $telephone;
        $this->description = $description;
    }

    public function show(): void
    {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Adresse</th>
                <th>Rôle</th>
                <th>Date naissance</th>
                <th>Téléphone</th>
                <th>Description</th>
                <th>Date création</th>
              </tr>";

        echo "<tr>";
        echo "<td>" . htmlspecialchars((string) $this->idUser, ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars((string) $this->nom, ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars((string) $this->email, ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars((string) $this->adresse, ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars((string) $this->role, ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . ($this->dateNaissance ? $this->dateNaissance->format('Y-m-d') : '') . "</td>";
        echo "<td>" . htmlspecialchars((string) $this->telephone, ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars((string) $this->description, ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . ($this->dateCreation ? $this->dateCreation->format('Y-m-d H:i:s') : '') . "</td>";
        echo "</tr>";
        echo "</table>";
    }

    public function getIdUser(): ?int { return $this->idUser; }
    public function setIdUser(?int $idUser): void { $this->idUser = $idUser; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): void { $this->nom = $nom; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): void { $this->email = $email; }

    public function getMotDePasse(): ?string { return $this->motDePasse; }
    public function setMotDePasse(?string $motDePasse): void { $this->motDePasse = $motDePasse; }

    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(?string $adresse): void { $this->adresse = $adresse; }

    public function getRole(): ?string { return $this->role; }
    public function setRole(?string $role): void { $this->role = $role; }

    public function getDateCreation(): ?DateTime { return $this->dateCreation; }
    public function setDateCreation(?DateTime $dateCreation): void { $this->dateCreation = $dateCreation; }

    public function getDateNaissance(): ?DateTime { return $this->dateNaissance; }
    public function setDateNaissance(?DateTime $dateNaissance): void { $this->dateNaissance = $dateNaissance; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): void { $this->telephone = $telephone; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }
}