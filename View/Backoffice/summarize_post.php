<?php
/**
 * ASCLEPIA — AI Smart Summary
 * Résumé médical intelligent via Anthropic API
 */
header('Content-Type: application/json; charset=utf-8');

$contenu = trim($_POST['contenu'] ?? '');
$postId  = (int)($_POST['post_id'] ?? 0);

if (strlen($contenu) < 30) {
    echo json_encode(['success' => false, 'error' => 'Texte trop court pour un résumé (minimum 30 caractères)']);
    exit;
}

// ── Appel Anthropic API ──────────────────────────────────────────────
$payload = json_encode([
    'model'      => 'claude-sonnet-4-20250514',
    'max_tokens' => 200,
    'messages'   => [[
        'role'    => 'user',
        'content' => "Tu es un assistant médical. Résume ce post de forum médical.

RÈGLES STRICTES :
1. Résumé entre 60 et 120 caractères MAXIMUM
2. Format : \"[Patient/Utilisateur] avec [problème principal]. [info clé]. [demande si présente].\"
3. Garde : symptômes clés, durée, médicaments mentionnés, demande de l'utilisateur
4. Supprime : répétitions, détails inutiles (\"je bois de l'eau\", \"je me repose\"), salutations
5. Réponds UNIQUEMENT avec le résumé, sans explication, sans guillemets, une seule ligne

POST À RÉSUMER :
{$contenu}"
    ]]
]);

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'anthropic-version: 2023-06-01',
        'x-api-key: '
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['success' => false, 'error' => 'Erreur réseau : ' . $curlErr]);
    exit;
}

$result = json_decode($response, true);

if ($httpCode === 200 && isset($result['content'][0]['text'])) {
    $summary = trim($result['content'][0]['text']);
    $summary = trim($summary, '"\'');
    // Tronquer si dépassement (sécurité)
    if (strlen($summary) > 150) {
        $summary = mb_substr($summary, 0, 147) . '...';
    }

    echo json_encode([
        'success'    => true,
        'summary'    => $summary,
        'char_count' => strlen($summary),
        'message'    => '✅ Résumé généré (' . strlen($summary) . ' caractères)'
    ]);
} else {
    $errMsg = $result['error']['message'] ?? ('Erreur API HTTP ' . $httpCode);
    echo json_encode(['success' => false, 'error' => $errMsg]);
}
?>
