<?php

// Carrega os valores da .env
$caminhoEnv = __DIR__ . '/.env';

if (file_exists($caminhoEnv)) {
    $linhas = file($caminhoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        // Ignora comentários
        if (str_starts_with(trim($linha), '#')) continue;
        
        // Divide a linha na primeira ocorrência de '='
        list($nome, $valor) = explode('=', $linha, 2);
        $nome = trim($nome);
        $valor = trim($valor);
        
        // Remove aspas caso estejam presentes no valor (.env às vezes usa)
        $valor = trim($valor, '"\'');
        
        if (!array_key_exists($nome, $_ENV)) {
            putenv(sprintf('%s=%s', $nome, $valor));
            $_ENV[$nome] = $valor;
            $_SERVER[$nome] = $valor;
        }
    }
}