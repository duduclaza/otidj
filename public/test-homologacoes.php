<?php
// Script temporário para diagnosticar o erro
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $db = App\Config\Database::getInstance();
    echo "<h2>✅ Conexão com banco OK</h2>";
    
    // Verificar se tabelas existem
    $tabelas = ['homologacoes', 'homologacoes_responsaveis', 'homologacoes_historico', 'homologacoes_anexos'];
    
    echo "<h3>Verificando tabelas:</h3>";
    foreach ($tabelas as $tabela) {
        $stmt = $db->query("SHOW TABLES LIKE '$tabela'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabela <b>$tabela</b> existe<br>";
        } else {
            echo "❌ Tabela <b>$tabela</b> NÃO existe<br>";
        }
    }
    
    // Verificar coluna department
    echo "<h3>Verificando coluna department:</h3>";
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'department'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Coluna <b>department</b> existe na tabela users<br>";
    } else {
        echo "❌ Coluna <b>department</b> NÃO existe na tabela users<br>";
    }
    
    echo "<hr>";
    echo "<h3>Testando Controller:</h3>";
    
    // Simular sessão
    if (!isset($_SESSION['user_id'])) {
        echo "⚠️ Usuário não está logado. Criando sessão de teste...<br>";
        // Buscar primeiro usuário admin
        $stmt = $db->query("SELECT u.id FROM users u LEFT JOIN profiles p ON u.profile_id = p.id WHERE p.name IN ('Super Admin', 'Administrador') LIMIT 1");
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            $_SESSION['user_id'] = $admin['id'];
            echo "✅ Usando usuário ID: {$admin['id']}<br>";
        }
    }
    
    if (isset($_SESSION['user_id'])) {
        $controller = new App\Controllers\HomologacoesController();
        echo "✅ Controller instanciado com sucesso<br>";
        
        echo "<h4>Tentando carregar página...</h4>";
        ob_start();
        $controller->index();
        $output = ob_get_clean();
        echo "✅ Página carregada com sucesso!<br>";
        echo "<a href='/homologacoes'>Ir para Homologações</a>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ ERRO:</h2>";
    echo "<pre style='background: #fee; padding: 10px; border: 1px solid red;'>";
    echo "<b>Mensagem:</b> " . $e->getMessage() . "\n\n";
    echo "<b>Arquivo:</b> " . $e->getFile() . "\n";
    echo "<b>Linha:</b> " . $e->getLine() . "\n\n";
    echo "<b>Stack trace:</b>\n" . $e->getTraceAsString();
    echo "</pre>";
    
    echo "<hr>";
    echo "<h3>🔧 SOLUÇÃO:</h3>";
    echo "<ol>";
    echo "<li>Execute o arquivo <code>database/homologacoes_kanban.sql</code> no banco de dados</li>";
    echo "<li>Ou acesse <a href='/verificar_homologacoes.php'>verificar_homologacoes.php</a> para diagnóstico completo</li>";
    echo "</ol>";
}
?>
