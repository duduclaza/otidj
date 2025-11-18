<?php
// Teste da biblioteca Carbon
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste Carbon (Horário de Brasília)</h1>";

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p>✅ Autoload OK</p>";
    
    // Configurar Brasília
    \Carbon\Carbon::setLocale('pt_BR');
    date_default_timezone_set('America/Sao_Paulo');
    
    echo "<p>✅ Carbon carregado com sucesso!</p>";
    
    // Testar horários
    $agora = \Carbon\Carbon::now('America/Sao_Paulo');
    $utc = \Carbon\Carbon::now('UTC');
    
    echo "<h2>Horários:</h2>";
    echo "<p><strong>UTC:</strong> " . $utc->format('d/m/Y H:i:s') . "</p>";
    echo "<p><strong>Brasília:</strong> " . $agora->format('d/m/Y H:i:s') . "</p>";
    echo "<p><strong>Diferença:</strong> " . $utc->diffForHumans($agora) . "</p>";
    
    echo "<h2>Formatações:</h2>";
    echo "<p><strong>Completa:</strong> " . $agora->format('d/m/Y H:i:s') . "</p>";
    echo "<p><strong>Por extenso:</strong> " . $agora->translatedFormat('l, d \\d\\e F \\d\\e Y \\à\\s H:i') . "</p>";
    echo "<p><strong>Relativa:</strong> " . $agora->diffForHumans() . "</p>";
    
    echo "<h2 style='color: green;'>✅ CARBON FUNCIONANDO PERFEITAMENTE!</h2>";
    echo "<p>O sistema está pronto para usar horário de Brasília!</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERRO:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    
    echo "<hr>";
    echo "<h3>Solução:</h3>";
    echo "<p>Execute: <code>composer require nesbot/carbon</code></p>";
    echo "<p>Ou faça upload da pasta vendor/ completa.</p>";
}
?>
