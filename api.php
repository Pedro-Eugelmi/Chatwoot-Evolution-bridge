<?php
// Exibe erros temporariamente para debug (se estiver testando)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/load-env.php';

// ATENÇÃO: É obrigatório dar require_once nas classes se não estiver usando o Composer Autoload!
require_once __DIR__ . '/src/Services/EvolutionService.php';
require_once __DIR__ . '/src/Controllers/EspelhamentoController.php';

use App\Services\EvolutionService;
use App\Controllers\EspelhamentoController;

$evolutionUrl = rtrim($_ENV['EVOLUTION_API_URL'] ?? '', '/');
$evolutionApiKey = $_ENV['EVOLUTION_API_KEY'] ?? '';
$chatwootUrl = rtrim($_ENV['CHATWOOT_URL'] ?? '', '/');
$applicationName = $_ENV['APPLICATION_NAME'] ?? 'Chatwoot';

if (empty($evolutionUrl) || empty($evolutionApiKey)) {
    echo json_encode(["error" => "Configurações da Evolution API ausentes no .env"]);
    exit;
}

try {
    $evolutionService = new EvolutionService($evolutionUrl, $evolutionApiKey);
    $controller = new EspelhamentoController($evolutionService, $chatwootUrl, $applicationName);
    
    // Executa e deixa o controller dar o echo no JSON
    $controller->processar();
} catch (\Throwable $e) {
    // Se der qualquer erro crítico, ele captura e retorna em JSON em vez de tela branca
    echo json_encode([
        "error" => "Erro interno no servidor: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ]);
}