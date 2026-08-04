<?php

require_once __DIR__ . '/load-env.php';

// Puxa valores da .env
$evolutionUrl = rtrim($_ENV['EVOLUTION_API_URL'] ?? '', '/');
$evolutionApiKey = $_ENV['EVOLUTION_API_KEY'] ?? '';
$chatwootUrl = rtrim($_ENV['CHATWOOT_URL'] ?? '', '/');
$applicationName = $_ENV['APPLICATION_NAME'] ?? 'Chatwoot';

// Pega os parâmetros da URL com segurança
$accountId = preg_replace('/[^0-9]/', '', $_GET['account_id'] ?? null);
$company = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['company'] ?? 'org');
$account_token = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['token'] ?? null);

// Nome da instância fixo por conta/empresa
$instanceName = "instance_{$company}_{$accountId}";

header('Content-Type: application/json');

# Configurações do evolution
if (empty($evolutionUrl) || empty($evolutionApiKey)) {
    echo json_encode(["error" => "Configurações da Evolution API ausentes no .env"]);
    exit;
}

# Configurações do usuário
if (empty($accountId)) {
    echo json_encode(["error" => "Não foi possível identificar a sua sessão no $applicationName. Por favor, feche esta janela, atualize a página principal e tente novamente."]);
    exit;
}

// ----------------------------------------------------
// 1. Cria a instância na Evolution API (se já existir, apenas prossegue)
// ----------------------------------------------------
$ch = curl_init("{$evolutionUrl}/instance/create");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15); 
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "apikey: {$evolutionApiKey}"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "instanceName" => $instanceName,
    "integration" => "WHATSAPP-BAILEYS"
]));
curl_exec($ch);
curl_close($ch);

// ----------------------------------------------------
// 2. Atualiza/Configura os dados do Chatwoot na instância (Payload corrigido)
// ----------------------------------------------------
$ch = curl_init("{$evolutionUrl}/chatwoot/set/{$instanceName}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "apikey: {$evolutionApiKey}"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "enabled" => true,
    "autoCreate" => true,
    "accountId" => (string)$accountId, // Convertido para string conforme padrão da API
    "token" => $account_token,
    "url" => $chatwootUrl,
    "nameInbox" => "WhatsApp - {$company}", 
    "signMsg" => true,                      
    "reopenConversation" => true,
    "conversationPending" => false,
    "mergeBrazilContacts" => true,        
    "importContacts" => true,
    "importMessages" => false
]));
$responseChatwoot = curl_exec($ch);
$httpCodeChatwoot = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Se falhar ao configurar o Chatwoot, apaga a instância por segurança e avisa
if ($httpCodeChatwoot >= 400) {
    deletarInstancia($evolutionUrl, $evolutionApiKey, $instanceName);
    echo json_encode([
        "error" => "Falha ao configurar a integração com o Chatwoot. Verifique as credenciais e tente novamente.",
        "http_code" => $httpCodeChatwoot,
        "response" => json_decode($responseChatwoot, true)
    ]);
    exit;
}

// ----------------------------------------------------
// 3. Busca o QR Code para exibição
// ----------------------------------------------------
$ch = curl_init("{$evolutionUrl}/instance/connect/{$instanceName}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: {$evolutionApiKey}"
]);
$responseConnect = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($responseConnect === false) {
    deletarInstancia($evolutionUrl, $evolutionApiKey, $instanceName);
    echo json_encode(["error" => "Falha de comunicação com a Evolution API"]);
    exit;
}

$data = json_decode($responseConnect, true);
$base64 = $data['base64'] ?? $data['qrcode']['base64'] ?? $data['instance']['base64'] ?? null;

if ($base64) {
    echo json_encode(["base64" => $base64]);
} else {
    deletarInstancia($evolutionUrl, $evolutionApiKey, $instanceName);
    echo json_encode([
        "error" => "Não foi possível gerar o QR Code. A instância foi reiniciada, tente novamente.",
        "http_code" => $httpCode,
        "details" => $data
    ]);
}

/**
 * Função auxiliar para apagar a instância em caso de erro crítico
 */
function deletarInstancia($url, $apiKey, $instance) {
    $ch = curl_init("{$url}/instance/delete/{$instance}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: {$apiKey}"
    ]);
    curl_exec($ch);
    curl_close($ch);
}