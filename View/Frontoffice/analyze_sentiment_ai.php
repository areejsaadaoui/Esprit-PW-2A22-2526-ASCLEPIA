<?php
/**
 * ASCLEPIA — Analyse de sentiment par IA (Grok API)
 * POST: content (string)
 * GET:  id_post (int) — analyse et sauvegarde en BDD
 * Retourne JSON: { sentiment, confidence, explanation }
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../Controller/PostController.php';

// Configuration API Grok (xAI)
define('XAI_API_KEY', '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');

// ── Récupération du texte ──────────────────────────────────
$content = '';
$id_post = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    $id_post = (int)($_POST['id_post'] ?? 0);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id_post = (int)($_GET['id_post'] ?? 0);
    if ($id_post) {
        $postC = new PostController();
        $post  = $postC->getPostById($id_post);
        if ($post) {
            $content = method_exists($post, 'getContenu') ? $post->getContenu() : ($post->$content ?? '');
        }
    }
}

if (empty($content)) {
    echo json_encode(['success' => false, 'error' => 'Contenu vide']);
    exit;
}

// ── Appel API Grok ──────────────────────────────────────
$systemPrompt = <<<SYSPROMPT
Tu es un analyste expert en sentiment de textes médicaux et communautaires francophones.
Analyse le texte fourni et retourne UNIQUEMENT un JSON valide (sans markdown, sans backticks) avec cette structure exacte:
{
  "sentiment": "positif"|"negatif"|"neutre"|"toxique",
  "confidence": 0.0-1.0,
  "explanation": "raison courte en 1 phrase max"
}

Règles de classification:
- "positif" : contenu bienveillant, encourageant, favorable, reconnaissant
- "negatif" : plainte, déception, critique, inquiétude, tristesse
- "neutre"  : information factuelle, question neutre, sans émotion marquée
- "toxique" : insultes, harcèlement, propos offensants, discours haineux

Réponds UNIQUEMENT avec le JSON. Aucun autre texte.
SYSPROMPT;

$payload = json_encode([
    'model'      => 'grok-beta',  // ou 'grok-2-latest' selon disponibilité
    'max_tokens' => 200,
    'temperature' => 0.1,
    'messages'   => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => substr($content, 0, 2000)]
    ]
]);

$ch = curl_init('https://api.x.ai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . XAI_API_KEY
    ],
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$raw  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Gestion des erreurs cURL
if ($curlError) {
    echo json_encode([
        'success' => false,
        'error'   => 'Erreur de connexion API Grok',
        'details' => $curlError
    ]);
    exit;
}

// Gestion des erreurs HTTP
if ($code !== 200) {
    echo json_encode([
        'success' => false,
        'error'   => 'API Grok indisponible',
        'http_code' => $code,
        'response' => $raw
    ]);
    exit;
}

$apiResp = json_decode($raw, true);
if (!isset($apiResp['choices'][0]['message']['content'])) {
    echo json_encode([
        'success' => false,
        'error'   => 'Réponse API invalide',
        'response' => $raw
    ]);
    exit;
}

$rawText = trim($apiResp['choices'][0]['message']['content']);

// Nettoyer les éventuels backticks/markdown
$rawText = preg_replace('/^```json\s*/i', '', $rawText);
$rawText = preg_replace('/^```\s*/', '', $rawText);
$rawText = preg_replace('/\s*```$/', '', $rawText);

$result = json_decode($rawText, true);

// Validation des données
$allowed = ['positif', 'negatif', 'neutre', 'toxique'];
$sentiment = in_array($result['sentiment'] ?? '', $allowed) ? $result['sentiment'] : 'neutre';
$confidence = isset($result['confidence']) ? floatval($result['confidence']) : 0.5;
$explanation = $result['explanation'] ?? $result['explication'] ?? 'Analyse terminée';

// ── Sauvegarde en BDD si id_post fourni ───────────────────
if ($id_post > 0 && $sentiment) {
    try {
        $postC = new PostController();
        if (method_exists($postC, 'updateSentiment')) {
            $postC->updateSentiment($id_post, $sentiment);
        } elseif (method_exists($postC, 'updatePostSentiment')) {
            $postC->updatePostSentiment($id_post, $sentiment);
        } else {
            // Méthode de sauvegarde alternative si les précédentes n'existent pas
            $db = config::getConnexion();
            $stmt = $db->prepare("UPDATE post SET sentiment = :sentiment WHERE id_post = :id_post");
            $stmt->execute(['sentiment' => $sentiment, 'id_post' => $id_post]);
        }
    } catch (Exception $e) {
        error_log("Erreur sauvegarde sentiment: " . $e->getMessage());
    }
}

echo json_encode([
    'success'     => true,
    'sentiment'   => $sentiment,
    'confidence'  => round($confidence, 2),
    'explanation' => $explanation,
    'id_post'     => $id_post,
    'api'         => 'grok'
]);
?>