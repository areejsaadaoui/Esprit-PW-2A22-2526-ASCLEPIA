<?php
require_once __DIR__ . '/../../libs/stripe/stripe-php-20.1.0/init.php';
require_once __DIR__ . '/../../stripe_config.php';
require_once __DIR__ . '/../../Controller/ContratController.php';

header('Content-Type: application/json');

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$contratC   = new ContratController();
$id_contrat = isset($_GET['id_contrat']) ? (int)$_GET['id_contrat'] : 0;
$contrat    = $contratC->showContrat($id_contrat);

if (!$contrat) {
    echo json_encode(['error' => 'Contrat introuvable']);
    exit;
}

$montant = (int)round((float)$contrat['montant'] * 100); // en centimes

$paymentIntent = \Stripe\PaymentIntent::create([
    'amount'   => $montant,
    'currency' => 'eur',
    'metadata' => ['id_contrat' => $id_contrat],
]);

echo json_encode(['clientSecret' => $paymentIntent->client_secret]);