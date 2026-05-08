<?php
header('Content-Type: application/json');

// Utilisation de la configuration sécurisée (ignorée par Git)
require_once '../../config_api.php';
$apiKey = GROQ_API_KEY; 

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['error' => 'Message vide']);
    exit;
}

require_once '../../Controller/PharmacieC.php';
require_once '../../Controller/MedicamentC.php';

$pharmacieC = new pharmacieC();
$medicamentC = new medicamentC();

// Récupération des données avec les bons noms de fonctions
$listePharmaciesObj = $pharmacieC->listepharmacie();
$listeMedicamentsObj = $medicamentC->afficherMedicaments();

// Conversion en tableaux
$listePharmacies = $listePharmaciesObj->fetchAll();
$listeMedicaments = $listeMedicamentsObj->fetchAll();

// On prépare un résumé des données pour l'IA
$pharmaciesNames = array_map(function($p) { return $p['nom'] . ' (' . $p['adresse'] . ')'; }, array_slice($listePharmacies, 0, 5));
$medsNames = array_map(function($m) { return $m['nom']; }, array_slice($listeMedicaments, 0, 5));

$context = "Tu es l'assistant intelligent d'ASCLEPIA. 
Données actuelles de la plateforme :
- Pharmacies enregistrées : " . count($listePharmacies) . " (Exemples: " . implode(', ', $pharmaciesNames) . ").
- Médicaments disponibles : " . count($listeMedicaments) . " (Exemples: " . implode(', ', $medsNames) . ").
Réponds de manière concise et utilise ces données si l'utilisateur pose des questions sur les pharmacies ou médicaments disponibles sur ASCLEPIA.";

$data = [
    'model' => 'llama-3.1-8b-instant',
    'messages' => [
        [
            'role' => 'system',
            'content' => $context
        ],
        [
            'role' => 'user',
            'content' => $userMessage
        ]
    ],
    'temperature' => 0.7
];

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);

// Désactiver la vérification SSL si vous êtes sur XAMPP et que ça bloque
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'Erreur de connexion : ' . curl_error($ch)]);
} else {
    echo $response;
}

curl_close($ch);
?>
