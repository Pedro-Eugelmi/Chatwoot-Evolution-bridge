<?php

namespace App\Services;

class ChatwootService {
    private string $chatwootUrl;
    private string $apiToken;

    public function __construct(string $chatwootUrl, string $apiToken) {
        $this->chatwootUrl = rtrim($chatwootUrl, '/');
        $this->apiToken = $apiToken;
    }

    /**
     * Busca ou gera um token de acesso válido para a conta especificada no Chatwoot.
     */
    public function obterTokenDaConta(int $accountId): ?string {
        $url = "{$this->chatwootUrl}/api/v1/accounts/{$accountId}/agents";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "api_access_token: {$this->apiToken}",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            
            // Pega o token do primeiro agente administrator/owner disponível na conta
            foreach ($data as $agent) {
                if (isset($agent['access_token']) && !empty($agent['access_token'])) {
                    return $agent['access_token'];
                }
            }
        }

        // Fallback: Se não achar via API de agentes, você pode retornar um token genérico 
        // ou o próprio token global configurado no .env se a sua versão aceitar
        return $this->apiToken ?: null;
    }
}