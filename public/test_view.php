<?php
// Teste da view de homologações
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste da View de Homologações</h1>";

$viewFile = __DIR__ . '/../views/pages/homologacoes/index.php';
echo "<p><strong>Arquivo:</strong> " . $viewFile . "</p>";
echo "<p><strong>Existe:</strong> " . (file_exists($viewFile) ? "✅ SIM" : "❌ NÃO") . "</p>";

if (file_exists($viewFile)) {
    try {
        // Simular variáveis necessárias
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
        
        echo "<p>Tentando carregar a view...</p>";
        
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        echo "<h2 style='color: green;'>✅ VIEW CARREGADA COM SUCESSO!</h2>";
        echo "<p>Tamanho: " . strlen($content) . " bytes</p>";
        
    } catch (Exception $e) {
        echo "<h2 style='color: red;'>❌ ERRO NA VIEW:</h2>";
        echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    }
} else {
    echo "<p style='color: red;'>Arquivo não encontrado!</p>";
}
?>
