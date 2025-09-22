<?php
// Teste simples para verificar se o sistema está funcionando
require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

try {
    $db = App\Config\Database::getInstance();
    echo "✅ Conexão com banco OK<br>";
    
    // Verificar tabela amostragens
    $stmt = $db->prepare("SHOW TABLES LIKE 'amostragens'");
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "✅ Tabela amostragens existe<br>";
        
        // Contar registros
        $stmt = $db->prepare("SELECT COUNT(*) FROM amostragens");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        echo "📊 Total de amostragens: $count<br>";
        
        // Verificar estrutura
        $stmt = $db->prepare("DESCRIBE amostragens");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "📋 Colunas da tabela amostragens:<br>";
        foreach ($columns as $col) {
            echo "- {$col['Field']} ({$col['Type']})<br>";
        }
    } else {
        echo "❌ Tabela amostragens não existe<br>";
    }
    
    // Verificar tabela evidências
    $stmt = $db->prepare("SHOW TABLES LIKE 'amostragens_evidencias'");
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "✅ Tabela amostragens_evidencias existe<br>";
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM amostragens_evidencias");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        echo "📊 Total de evidências: $count<br>";
    } else {
        echo "❌ Tabela amostragens_evidencias não existe<br>";
    }
    
    // Testar controller
    echo "<br>🧪 Testando AmostragemController...<br>";
    $controller = new App\Controllers\AmostragemController();
    echo "✅ Controller instanciado com sucesso<br>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    echo "📍 Arquivo: " . $e->getFile() . " linha " . $e->getLine() . "<br>";
}

echo "<br><a href='/toners/amostragens'>🔗 Ir para Amostragens</a>";
?>
