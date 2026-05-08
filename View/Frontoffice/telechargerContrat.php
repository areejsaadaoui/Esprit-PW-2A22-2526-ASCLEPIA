<?php
require_once __DIR__ . '/../../libs/fpdf/fpdf.php';
require_once __DIR__ . '/../../Controller/ContratController.php';

$contratC   = new ContratController();
$id_contrat = isset($_GET['id_contrat']) ? (int)$_GET['id_contrat'] : 0;
$contrat    = $contratC->showContrat($id_contrat);

if (!$contrat) die('Contrat introuvable.');

// Décoder la signature base64
$signatureData = $contrat['signature'] ?? '';
$signaturePath = null;
if (!empty($signatureData)) {
    $signatureImg  = str_replace('data:image/png;base64,', '', $signatureData);
    $signatureImg  = base64_decode($signatureImg);
    $signaturePath = sys_get_temp_dir() . '/sig_' . $id_contrat . '.png';
    file_put_contents($signaturePath, $signatureImg);
}

class ContratPDF extends FPDF {
    function Header() {
        // Fond header
        $this->SetFillColor(14, 165, 233);
        $this->Rect(0, 0, 210, 40, 'F');
        $this->SetFillColor(16, 185, 129);
        $this->Rect(0, 30, 210, 10, 'F');

        // Titre
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 24);
        $this->SetY(8);
        $this->Cell(0, 10, 'ASCLEPIA', 0, 1, 'C');
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 8, 'Contrat d\'assurance sante', 0, 1, 'C');
        $this->Ln(12);
        $this->SetTextColor(0, 0, 0);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFillColor(14, 165, 233);
        $this->Rect(0, 277, 210, 20, 'F');
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 10, '© 2025 ASCLEPIA - Tous droits reserves', 0, 0, 'C');
    }
}

$pdf = new ContratPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 25);

// ---- INFOS CONTRAT ----
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(14, 165, 233);
$pdf->Cell(0, 10, 'Informations du contrat', 0, 1);
$pdf->SetDrawColor(14, 165, 233);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(4);

$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0, 0, 0);

$rows = [
    ['Assurance',       $contrat['nom_assurance']],
    ['Type',            $contrat['type_assurance']],
    ['Date de debut',   $contrat['date_d']],
    ['Date de fin',     $contrat['date_f'] ?: '—'],
    ['Montant total',   number_format((float)$contrat['montant'], 2) . ' DT'],
    ['Statut',          $contrat['statut']],
];

foreach ($rows as $row) {
    $pdf->SetFillColor(240, 249, 255);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(60, 9, $row[0], 0, 0, 'L', true);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(130, 9, $row[1], 0, 1, 'L', true);
}

$pdf->Ln(10);

// ---- CONDITIONS ----
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(14, 165, 233);
$pdf->Cell(0, 10, 'Conditions generales', 0, 1);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(4);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 116, 139);
$pdf->MultiCell(0, 6, 
    "En signant ce contrat, le souscripteur accepte les conditions generales d'utilisation d'ASCLEPIA ".
    "et confirme que les informations fournies sont exactes et completes. ".
    "Ce contrat est valable pour la duree specifiee et prend effet a partir de la date de debut indiquee."
);

$pdf->Ln(10);

// ---- SIGNATURES ----
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(14, 165, 233);
$pdf->Cell(0, 10, 'Signatures', 0, 1);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(6);

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(95, 8, 'Signature du souscripteur', 0, 0, 'C');
$pdf->Cell(95, 8, 'Cachet ASCLEPIA', 0, 1, 'C');
$pdf->Ln(2);

// Signature utilisateur
$sigY = $pdf->GetY();
$pdf->SetDrawColor(200, 200, 200);
$pdf->Rect(10, $sigY, 85, 40);

if ($signaturePath && file_exists($signaturePath)) {
    $pdf->Image($signaturePath, 12, $sigY + 2, 81, 36);
}

// Cachet ASCLEPIA
$pdf->SetFillColor(14, 165, 233);
$pdf->Rect(115, $sigY, 85, 40, 'D');
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(14, 165, 233);
$pdf->SetXY(115, $sigY + 8);
$pdf->Cell(85, 8, 'ASCLEPIA', 0, 1, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100, 116, 139);
$pdf->SetX(115);
$pdf->Cell(85, 6, 'Assurance Sante', 0, 1, 'C');
$pdf->SetX(115);
$pdf->Cell(85, 6, 'contact.asclepia@gmail.com', 0, 1, 'C');
$pdf->SetX(115);
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(16, 185, 129);
$pdf->Cell(85, 6, 'Document officiel', 0, 1, 'C');

$pdf->Ln(50);

// Date
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(148, 163, 184);
$pdf->Cell(0, 6, 'Document genere le ' . date('d/m/Y à H:i'), 0, 1, 'C');

// Nettoyage
if ($signaturePath && file_exists($signaturePath)) {
    unlink($signaturePath);
}

$pdf->Output('D', 'Contrat_ASCLEPIA_' . $id_contrat . '.pdf');