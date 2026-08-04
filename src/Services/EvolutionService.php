<?php
namespace App\Services;

class EvolutionService {
    private string $url;
    private string $apiKey;

    public function __construct(string $url, string $apiKey) {
        $this->url = rtrim($url, '/');
        $this->apiKey = $apiKey;
    }

    public function criarInstancia(string $instanceName): void {
        $ch = curl_init("{$this->url}/instance/create");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "apikey: {$this->apiKey}"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "instanceName" => $instanceName,
            "integration" => "WHATSAPP-BAILEYS"
        ]));
        curl_exec($ch);
        curl_close($ch);
    }

    public function configurarChatwoot(string $instanceName, array $payload): array {
        $ch = curl_init("{$this->url}/chatwoot/set/{$instanceName}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "apikey: {$this->apiKey}"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'code' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }

    public function buscarQrCode(string $instanceName): array {
        $ch = curl_init("{$this->url}/instance/connect/{$instanceName}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->apiKey}"
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'code' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }

    public function deletarInstancia(string $instanceName): void {
        $ch = curl_init("{$this->url}/instance/delete/{$instanceName}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->apiKey}"
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}