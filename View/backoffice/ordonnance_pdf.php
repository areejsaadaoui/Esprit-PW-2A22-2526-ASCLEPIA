<?php
require_once '../../config/db.php';
require_once '../../models/Ordonnance.php';
require_once '../../libs/fpdf.php';

// ✅ Fix fonts path (IMPORTANT)
define('FPDF_FONTPATH', __DIR__ . '/../../libs/font/');

$model = new Ordonnance($pdo);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ordonnance = $model->getById($id);

if (!$ordonnance) {
    die("Ordonnance introuvable.");
}

// Générer QR Code
$qrData = "ASCLEPIA-ORD-" . $ordonnance['id_ordonnance'] . "-CONS-" . $ordonnance['id_consultation'];
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qrData);

// Télécharger le QR code
$qrImage = tempnam(sys_get_temp_dir(), 'qr') . '.png';
file_put_contents($qrImage, file_get_contents($qrUrl));

// Classe PDF
class PDF extends FPDF {

    function Header() {
        // ⚠️ FORCER Arial pour éviter Helvetica
        $this->SetFont('Arial', 'B', 22);

        // Header design
        $this->SetFillColor(14, 165, 233);
        $this->Rect(0, 0, 210, 35, 'F');

        $this->SetTextColor(255, 255, 255);
        $this->SetY(8);
        $this->Cell(0, 10, 'ASCLEPIA', 0, 1, 'C');

        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 8, 'Plateforme Medicale - Cabinet Medical', 0, 1, 'C');

        $this->Ln(5);
        $this->SetTextColor(0, 0, 0);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFillColor(14, 165, 233);
        $this->Rect(0, $this->GetY(), 210, 20, 'F');

        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, 'ASCLEPIA - Document genere automatiquement - ' . date('d/m/Y H:i'), 0, 0, 'C');
    }
}

// Création PDF
$pdf = new PDF();

// ✅ IMPORTANT : forcer une font AVANT AddPage
$pdf->SetFont('Arial', '', 12);

$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 25);

// Titre ordonnance
$pdf->SetY(42);
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(14, 165, 233);
$pdf->Cell(0, 10, 'ORDONNANCE MEDICALE', 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 6, 'N° ' . str_pad($ordonnance['id_ordonnance'], 6, '0', STR_PAD_LEFT), 0, 1, 'C');

$pdf->Ln(5);

// Ligne
$pdf->SetDrawColor(14, 165, 233);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

// Infos consultation
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(235, 248, 255);
$pdf->Rect(15, $pdf->GetY(), 180, 8, 'F');
$pdf->Cell(180, 8, '  INFORMATIONS DE LA CONSULTATION', 0, 1, 'L');

$pdf->Ln(2);

$pdf->SetFont('Arial', '', 10);
$pdf->SetX(15);
$pdf->Cell(50, 7, 'Consultation N° :', 0, 0);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(130, 7, '#' . $ordonnance['id_consultation'], 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->SetX(15);
$pdf->Cell(50, 7, 'Date consultation :', 0, 0);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(130, 7, date('d/m/Y H:i', strtotime($ordonnance['date_consultation'])), 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->SetX(15);
$pdf->Cell(50, 7, 'Date ordonnance :', 0, 0);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(130, 7, date('d/m/Y H:i', strtotime($ordonnance['date_creation'])), 0, 1);

// Diagnostique
$pdf->SetFont('Arial', '', 10);
$pdf->SetX(15);
$pdf->Cell(50, 7, 'Diagnostique :', 0, 0);

$pdf->SetFont('Arial', 'B', 10);
$pdf->MultiCell(130, 7, $ordonnance['diagnostique'], 0, 'L');

$pdf->Ln(5);

// Médicaments
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(235, 248, 255);
$pdf->Rect(15, $pdf->GetY(), 180, 8, 'F');
$pdf->Cell(180, 8, '  MEDICAMENTS PRESCRITS', 0, 1, 'L');

$pdf->Ln(2);

$pdf->SetFont('Arial', '', 10);
$medicaments = explode("\n", $ordonnance['medicaments']);

foreach ($medicaments as $med) {
    if (trim($med)) {
        $pdf->SetX(15);
        $pdf->Cell(5, 7, '-', 0, 0);
        $pdf->MultiCell(170, 7, trim($med), 0, 'L');
    }
}

$pdf->Ln(5);

// Instructions
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(235, 248, 255);
$pdf->Rect(15, $pdf->GetY(), 180, 8, 'F');
$pdf->Cell(180, 8, '  INSTRUCTIONS', 0, 1, 'L');

$pdf->Ln(2);

$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(180, 7, $ordonnance['instructions'], 0, 'L');

$pdf->Ln(5);

// Durée
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(235, 248, 255);
$pdf->Rect(15, $pdf->GetY(), 180, 8, 'F');
$pdf->Cell(180, 8, '  DUREE DU TRAITEMENT', 0, 1, 'L');

$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(14, 165, 233);
$pdf->Cell(180, 10, $ordonnance['duree_traitement'] . ' jours', 0, 1, 'C');

$pdf->SetTextColor(0, 0, 0);

$pdf->Ln(5);

// Médecin
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(235, 248, 255);
$pdf->Rect(15, $pdf->GetY(), 180, 8, 'F');
$pdf->Cell(180, 8, '  MEDECIN', 0, 1, 'L');

$pdf->Ln(2);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(50, 7, 'Nom :', 0, 0);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(130, 7, 'Dr. Ala', 0, 1);

$pdf->Ln(10);
$pdf->Line(15, $pdf->GetY(), 80, $pdf->GetY());

// QR Code
$pdf->Image($qrImage, 155, $pdf->GetY() - 30, 40, 40);

unlink($qrImage);

// Télécharger PDF
$pdf->Output('D', 'Ordonnance_' . $ordonnance['id_ordonnance'] . '.pdf');
?>