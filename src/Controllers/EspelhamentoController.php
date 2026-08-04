<?php

namespace App\Controllers;

use App\Services\EvolutionService;
use App\Services\ChatwootService;

class EspelhamentoController {
    private EvolutionService $evolutionService;
    private ChatwootService $chatwootService;
    private string $chatwootUrl;
    private string $applicationName;
    private string $secretKeyHmac;

    public function __construct(
        EvolutionService $evolutionService, 
        ChatwootService $chatwootService, 
        string $chatwootUrl, 
        string $applicationName,
        string $secretKeyHmac = ''
    ) {
        $this->evolutionService = $evolutionService;
        $this->chatwootService = $chatwootService;
        $this->chatwootUrl = $chatwootUrl;
        $this->applicationName = $applicationName;
        $this->secretKeyHmac = $secretKeyHmac ?: ($_ENV['SECRET_KEY_HMAC'] ?? '');
    }

    public function processar(): void {
        header('Content-Type: application/json');

        // Pega os parâmetros da URL com segurança
        $accountId = preg_replace('/[^0-9]/', '', $_GET['account_id'] ?? '');
        $company = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['company'] ?? 'org');
        $receivedHash = $_GET['hash'] ?? '';

        // 1. Validação básica de parâmetros
        if (empty($accountId) || empty($receivedHash)) {
            echo json_encode([
                "error" => "Não foi possível identificar a sua sessão no {$this->applicationName}. Por favor, feche esta janela, atualize a página principal e tente novamente."
            ]);
            exit;
        }

        // 2. Validação rigorosa do HMAC enviado pelo Chatwoot
        $calculatedHash = hash_hmac('sha256', (string)$accountId, $this->secretKeyHmac);

        if (!hash_equals($calculatedHash, $receivedHash)) {
            echo json_encode([
                "error" => "Falha de autenticação de segurança: Assinatura HMAC inválida."
            ]);
            exit;
        }

        // 3. Busca o token de acesso real da conta usando o ChatwootService
        $account_token = $this->chatwootService->obterTokenDaConta((int)$accountId);

        if (empty($account_token)) {
            echo json_encode([
                "error" => "Não foi possível obter o token de acesso da conta no Chatwoot. Verifique as credenciais da API."
            ]);
            exit;
        }

        $instanceName = "instance_{$company}_{$accountId}";

        // 4. Cria a instância na Evolution API
        $this->evolutionService->criarInstancia($instanceName);

        // 5. Configura os dados do Chatwoot na instância com o token legítimo
        $chatwootResult = $this->evolutionService->configurarChatwoot($instanceName, [
            "enabled" => true,
            "autoCreate" => true,
            "accountId" => (string)$accountId,
            "token" => $account_token,
            "url" => $this->chatwootUrl,
            "nameInbox" => "WhatsApp - {$company}",
            "signMsg" => true,
            "reopenConversation" => true,
            "conversationPending" => false,
            "mergeBrazilContacts" => true,
            "importContacts" => true,
            "importMessages" => false
        ]);

        if ($chatwootResult['code'] >= 400) {
            $this->evolutionService->deletarInstancia($instanceName);
            echo json_encode([
                "error" => "Falha ao configurar a integração com o Chatwoot. Verifique as credenciais e tente novamente.",
                "http_code" => $chatwootResult['code'],
                "response" => $chatwootResult['response']
            ]);
            exit;
        }

        // 6. Busca o QR Code para exibição
        $connectResult = $this->evolutionService->buscarQrCode($instanceName);
        
        if ($connectResult['code'] === false || empty($connectResult['data'])) {
            $this->evolutionService->deletarInstancia($instanceName);
            echo json_encode(["error" => "Falha de comunicação com a Evolution API"]);
            exit;
        }

        $data = $connectResult['data'];
        $base64 = $data['base64'] ?? $data['qrcode']['base64'] ?? $data['instance']['base64'] ?? null;

        if ($base64) {
            echo json_encode(["base64" => $base64]);
        } else {
            $this->evolutionService->deletarInstancia($instanceName);
            echo json_encode([
                "error" => "Não foi possível gerar o QR Code. A instância foi reiniciada, tente novamente.",
                "http_code" => $connectResult['code'],
                "details" => $data
            ]);
        }
    }
}