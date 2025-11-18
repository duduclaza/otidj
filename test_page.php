<?php
// Teste simples da página de homologações
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste de Carregamento</h1>";

// Verificar se existe o arquivo
$viewFile = __DIR__ . '/views/pages/homologacoes/index.php';
echo "<p>Arquivo existe: " . (file_exists($viewFile) ? "SIM" : "NÃO") . "</p>";
echo "<p>Caminho: " . $viewFile . "</p>";

// Tentar incluir
try {
    echo "<p>Tentando incluir...</p>";
    
    // Simular variáveis que a view precisa
    $homologacoes = [
        'aguardando_recebimento' => [],
        'recebido' => [],
        'em_analise' => [],
        'em_homologacao' => [],
        'aprovado' => [],
        'reprovado' => []
    ];
    $departamentos = [];
    $canCreate = true;
    
    ob_start();
    include $viewFile;
    $content = ob_get_clean();
    
    echo "<p style='color: green;'>SUCESSO! Arquivo carregado.</p>";
    echo "<p>Tamanho do conteúdo: " . strlen($content) . " bytes</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>ERRO: " . $e->getMessage() . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . "</p>";
}
