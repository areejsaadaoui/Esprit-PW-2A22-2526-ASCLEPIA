<?php
require_once '../config/db.php';
require_once '../models/Consultation.php';

class ConsultationController {
    private $model;

    public function __construct($pdo) {
        $this->model = new Consultation($pdo);
    }

    public function listerConsultations() {
        return $this->model->getAll();
    }

    public function voirConsultation($id) {
        return $this->model->getById($id);
    }

    public function ajouterConsultation($data) {
        $errors = [];

        if (empty($data['date_consultation'])) {
            $errors[] = "La date est obligatoire.";
        } elseif (strtotime($data['date_consultation']) > time()) {
            $errors[] = "La date ne peut pas être dans le futur.";
        }

        if (empty($data['diagnostique'])) {
            $errors[] = "Le diagnostique est obligatoire.";
        } elseif (strlen($data['diagnostique']) < 10) {
            $errors[] = "Le diagnostique doit contenir au moins 10 caractères.";
        }

        if (empty($data['notes'])) {
            $errors[] = "Les notes sont obligatoires.";
        } elseif (strlen($data['notes']) < 5) {
            $errors[] = "Les notes doivent contenir au moins 5 caractères.";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $result = $this->model->create($data);
        return ['success' => $result, 'errors' => []];
    }

    public function modifierConsultation($id, $data) {
        $errors = [];

        if (empty($data['date_consultation'])) {
            $errors[] = "La date est obligatoire.";
        } elseif (strtotime($data['date_consultation']) > time()) {
            $errors[] = "La date ne peut pas être dans le futur.";
        }

        if (empty($data['diagnostique'])) {
            $errors[] = "Le diagnostique est obligatoire.";
        } elseif (strlen($data['diagnostique']) < 10) {
            $errors[] = "Le diagnostique doit contenir au moins 10 caractères.";
        }

        if (empty($data['notes'])) {
            $errors[] = "Les notes sont obligatoires.";
        } elseif (strlen($data['notes']) < 5) {
            $errors[] = "Les notes doivent contenir au moins 5 caractères.";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $result = $this->model->update($id, $data);
        return ['success' => $result, 'errors' => []];
    }

    public function supprimerConsultation($id) {
        return $this->model->delete($id);
    }
}
?>