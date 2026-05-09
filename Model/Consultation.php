<?php

class Consultation {
    private ?int $id_consultation;
    private string $date_consultation;
    private string $diagnostique;
    private string $notes;
    private string $statut;
    private ?int $id_patient;

    public function __construct(
        ?int $id_consultation = null,
        string $date_consultation = '',
        string $diagnostique = '',
        string $notes = '',
        string $statut = 'planifiée',
        ?int $id_patient = null
    ) {
        $this->id_consultation   = $id_consultation;
        $this->date_consultation = $date_consultation;
        $this->diagnostique      = $diagnostique;
        $this->notes             = $notes;
        $this->statut            = $statut;
        $this->id_patient        = $id_patient;
    }

    public static function fromArray(array $data): self {
        return new self(
            isset($data['id_consultation']) ? intval($data['id_consultation']) : null,
            $data['date_consultation'] ?? '',
            $data['diagnostique']      ?? '',
            $data['notes']             ?? '',
            $data['statut']            ?? 'planifiée',
            isset($data['id_patient']) ? intval($data['id_patient']) : null
        );
    }

    public function toArray(): array {
        return [
            'id_consultation'   => $this->id_consultation,
            'date_consultation' => $this->date_consultation,
            'diagnostique'      => $this->diagnostique,
            'notes'             => $this->notes,
            'statut'            => $this->statut,
            'id_patient'        => $this->id_patient,
        ];
    }

    public function getIdConsultation(): ?int {
        return $this->id_consultation;
    }

    public function setIdConsultation(int $id_consultation): void {
        $this->id_consultation = $id_consultation;
    }

    public function getDateConsultation(): string {
        return $this->date_consultation;
    }

    public function setDateConsultation(string $date_consultation): void {
        $this->date_consultation = $date_consultation;
    }

    public function getDiagnostique(): string {
        return $this->diagnostique;
    }

    public function setDiagnostique(string $diagnostique): void {
        $this->diagnostique = $diagnostique;
    }

    public function getNotes(): string {
        return $this->notes;
    }

    public function setNotes(string $notes): void {
        $this->notes = $notes;
    }

    public function getStatut(): string {
        return $this->statut;
    }

    public function setStatut(string $statut): void {
        $this->statut = $statut;
    }

    public function getIdPatient(): ?int {
        return $this->id_patient;
    }

    public function setIdPatient(?int $id_patient): void {
        $this->id_patient = $id_patient;
    }
}

?>