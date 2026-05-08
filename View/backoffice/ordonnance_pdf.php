<?php
require_once '../../config/db.php';
require_once '../../controllers/OrdonnanceController.php';
require_once '../../libs/fpdf.php';

$controller = new OrdonnanceController($pdo);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ordonnance = $controller->getOrdonnanceById($id);

if (!$ordonnance) {
    die("Ordonnance introuvable.");
}

// Générer QR Code simple (texte encodé en base64)
$qrData = "ASCLEPIA-ORD-" . $ordonnance['id_ordonnance'] . "-CONS-" . $ordonnance['id_consultation'];
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($qrData);

// Télécharger le QR code
$qrImage = tempnam(sys_get_temp_dir(), 'qr') . '.png';
file_put_contents($qrImage, file_get_contents($qrUrl));

class PDF extends FPDF {
    function Header() {
        // Logo/Titre
        $this->SetFillColor(14, 165, 233);
        $this->Rect(0, 0, 210, 35, 'F');
        $this->SetFont('Arial', 'B', 22);
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

$pdf = new PDF();
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

// Ligne séparatrice
$pdf->SetDrawColor(14, 165, 233);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

// Infos consultation
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(0, 0, 0);
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
$pdf->SetX(15);
$medicaments = explode("\n", $ordonnance['medicaments']);
foreach ($medicaments as $med) {
    if (trim($med)) {
        $pdf->SetX(15);
        $pdf->Cell(5, 7, chr(149), 0, 0);
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
$pdf->SetX(15);
$pdf->MultiCell(180, 7, $ordonnance['instructions'], 0, 'L');

$pdf->Ln(5);

// Durée traitement
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

// Signature médecin
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(235, 248, 255);
$pdf->Rect(15, $pdf->GetY(), 180, 8, 'F');
$pdf->Cell(180, 8, '  MEDECIN', 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 10);
$pdf->SetX(15);
$pdf->Cell(50, 7, 'Nom :', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(130, 7, 'Dr. Ala', 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->SetX(15);
$pdf->Cell(50, 7, 'Signature :', 0, 0);

$signaturePath = __DIR__ . '/../../assets/signatures/' . ($ordonnance['signature'] ?? '');
if (!empty($ordonnance['signature']) && file_exists($signaturePath)) {
    $pdf->Image($signaturePath, 65, $pdf->GetY() - 1, 60, 20);
    $pdf->Ln(25);
} else {
    $pdf->Ln(15);
    $pdf->SetX(15);
    $pdf->Line(15, $pdf->GetY(), 80, $pdf->GetY());
    $pdf->Ln(5);
}

// QR Code
$pdf->SetXY(150, $pdf->GetY() - 35);
$pdf->Image($qrImage, 155, $pdf->GetY(), 40, 40);
$pdf->SetXY(150, $pdf->GetY() + 42);
$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(50, 5, 'Scanner pour verifier', 0, 1, 'C');

// Supprimer le fichier temporaire
unlink($qrImage);

// Télécharger le PDF
$pdf->Output('D', 'Ordonnance_' . $ordonnance['id_ordonnance'] . '_' . date('Ymd') . '.pdf');
?>