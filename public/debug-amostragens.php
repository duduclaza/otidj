<?php
// Página de diagnóstico para amostragens
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico do Sistema de Amostragens</h1>";

try {
    echo "<h2>1. Testando autoload...</h2>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoload carregado com sucesso<br>";
    
    echo "<h2>2. Testando .env...</h2>";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
    echo "✅ .env carregado com sucesso<br>";
    
    echo "<h2>3. Testando conexão com banco...</h2>";
    $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_DATABASE'];
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    echo "✅ Conexão com banco OK<br>";
    
    echo "<h2>4. Testando tabela amostragens_2...</h2>";
    $stmt = $pdo->prepare('DESCRIBE amostragens_2');
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Tabela amostragens_2 encontrada com " . count($columns) . " colunas:<br>";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})<br>";
    }
    
    echo "<h2>5. Testando Controller...</h2>";
    $controller = new App\Controllers\Amostragens2Controller();
    echo "✅ Amostragens2Controller criado com sucesso<br>";
    
    echo "<h2>6. Testando dados de POST simulado...</h2>";
    $_POST = [
        'numero_nf' => 'TEST123',
        'tipo_produto' => 'toner',
        'produto_id' => 1,
        'codigo_produto' => 'TEST001',
        'nome_produto' => 'Produto Teste',
        'quantidade_recebida' => 10,
        'quantidade_testada' => 5,
        'quantidade_aprovada' => 4,
        'quantidade_reprovada' => 1,
        'fornecedor_id' => 1,
        'responsaveis' => [1],
        'status_final' => 'Pendente'
    ];
    
    $_SESSION['user_id'] = 1;
    $_SESSION['user_filial_id'] = 1;
    
    echo "✅ Dados de POST simulados configurados<br>";
    
    echo "<h2>7. Testando método store (simulação)...</h2>";
    ob_start();
    try {
        $controller->store();
        $output = ob_get_clean();
        echo "✅ Método store executado sem erros<br>";
        echo "Saída: " . htmlspecialchars($output) . "<br>";
    } catch (Exception $e) {
        ob_end_clean();
        echo "❌ ERRO no método store:<br>";
        echo "<pre style='background: #f00; color: #fff; padding: 10px;'>";
        echo "Erro: " . $e->getMessage() . "\n";
        echo "Arquivo: " . $e->getFile() . "\n";
        echo "Linha: " . $e->getLine() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString();
        echo "</pre>";
    }
    
    echo "<h2>✅ DIAGNÓSTICO CONCLUÍDO</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ ERRO ENCONTRADO:</h2>";
    echo "<pre style='background: #f00; color: #fff; padding: 10px;'>";
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
    echo "</pre>";
} catch (Error $e) {
    echo "<h2>❌ ERRO FATAL ENCONTRADO:</h2>";
    echo "<pre style='background: #f00; color: #fff; padding: 10px;'>";
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
    echo "</pre>";
}

echo "<hr>";
echo "<h3>Informações do PHP:</h3>";
echo "Versão PHP: " . PHP_VERSION . "<br>";
echo "Memory limit: " . ini_get('memory_limit') . "<br>";
echo "Max execution time: " . ini_get('max_execution_time') . "<br>";
echo "Upload max filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post max size: " . ini_get('post_max_size') . "<br>";
?>
