<?php
/**
 * SGQ-OTI DJ - Entry Point Fallback
 * 
 * Este arquivo serve como fallback quando o DocumentRoot não está apontando para /public
 * O .htaccess na raiz deve redirecionar automaticamente, mas este arquivo garante funcionamento.
 */

// Se o arquivo existe em public/index.php, incluir diretamente
$publicIndex = __DIR__ . '/public/index.php';

if (file_exists($publicIndex)) {
    // Ajustar o working directory para public/
    chdir(__DIR__ . '/public');
    
    // Incluir o index.php real
    require $publicIndex;
} else {
    // Erro se não encontrar o arquivo
    http_response_code(500);
    die('Erro: Sistema não configurado corretamente. Arquivo public/index.php não encontrado.');
}
