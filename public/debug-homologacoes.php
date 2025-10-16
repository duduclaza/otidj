<?php
/**
 * Diagnóstico Simples de Erro 500 - Homologações
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico de Homologações</h1>";
echo "<style>body { font-family: Arial; padding: 20px; } .ok { color: green; font-weight: bold; } .erro { color: red; font-weight: bold; }</style>";

try {
    // 1. Testar carregamento básico
    echo "<h2>1. Carregando Sistema...</h2>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p class='ok'>✅ Autoload OK</p>";

    // 2. Testar .env
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo "<p class='ok'>✅ .env carregado</p>";

    // 3. Testar conexão banco
    echo "<h2>2. Testando Banco de Dados...</h2>";
    $db = App\Config\Database::getInstance();
    echo "<p class='ok'>✅ Conexão com banco OK</p>";

    // 4. Verificar tabelas
    echo "<h2>3. Verificando Tabelas...</h2>";
    $tabelas = ['homologacoes', 'homologacoes_responsaveis', 'homologacoes_historico', 'homologacoes_anexos'];
    
    foreach ($tabelas as $tabela) {
        $stmt = $db->query("SHOW TABLES LIKE '$tabela'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='ok'>✅ Tabela <b>$tabela</b> existe</p>";
        } else {
            echo "<p class='erro'>❌ Tabela <b>$tabela</b> NÃO EXISTE</p>";
            echo "<p><strong>SOLUÇÃO:</strong> Execute o SQL em <code>database/homologacoes_kanban.sql</code></p>";
        }
    }

    // 5. Verificar coluna department
    echo "<h2>4. Verificando Coluna department...</h2>";
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'department'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='ok'>✅ Coluna <b>department</b> existe</p>";
    } else {
        echo "<p class='erro'>❌ Coluna <b>department</b> NÃO existe</p>";
        echo "<p><strong>SOLUÇÃO:</strong> Execute: <code>ALTER TABLE users ADD COLUMN department VARCHAR(100) AFTER email;</code></p>";
    }

    // 6. Verificar permissões
    echo "<h2>5. Verificando Permissões...</h2>";
    $stmt = $db->query("SELECT COUNT(*) as total FROM profile_permissions WHERE module = 'homologacoes'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['total'] > 0) {
        echo "<p class='ok'>✅ Permissões configuradas ({$result['total']} perfil(is))</p>";
    } else {
        echo "<p class='erro'>❌ Nenhuma permissão configurada para módulo 'homologacoes'</p>";
    }

    // 7. Testar Controller
    echo "<h2>6. Testando Controller...</h2>";
    if (class_exists('App\Controllers\HomologacoesController')) {
        echo "<p class='ok'>✅ HomologacoesController existe</p>";
        
        // Testar instanciação
        try {
            session_start();
            $_SESSION['user_id'] = 1; // Simular usuário
            $controller = new App\Controllers\HomologacoesController();
            echo "<p class='ok'>✅ Controller instanciado com sucesso</p>";
        } catch (Exception $e) {
            echo "<p class='erro'>❌ Erro ao instanciar controller: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='erro'>❌ HomologacoesController NÃO encontrado</p>";
    }

    // 8. Verificar View
    echo "<h2>7. Verificando View...</h2>";
    $viewPath = __DIR__ . '/../views/homologacoes/kanban.php';
    if (file_exists($viewPath)) {
        echo "<p class='ok'>✅ View kanban.php existe</p>";
    } else {
        echo "<p class='erro'>❌ View kanban.php NÃO encontrada em: $viewPath</p>";
    }

    echo "<hr>";
    echo "<h2>✅ RESUMO</h2>";
    echo "<p><strong>Se todas as verificações acima estão OK</strong>, o sistema deve funcionar!</p>";
    echo "<p><strong>Se há ❌ vermelhos</strong>, corrija os problemas indicados.</p>";
    echo "<hr>";
    echo "<p><a href='/homologacoes' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Testar /homologacoes</a></p>";

} catch (Exception $e) {
    echo "<h2 class='erro'>❌ ERRO CRÍTICO:</h2>";
    echo "<pre style='background: #fee; padding: 15px; border: 2px solid red;'>";
    echo "<strong>Mensagem:</strong> " . $e->getMessage() . "\n\n";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "\n";
    echo "<strong>Linha:</strong> " . $e->getLine() . "\n\n";
    echo "<strong>Stack Trace:</strong>\n" . $e->getTraceAsString();
    echo "</pre>";
}

echo "<hr>";
echo "<p style='color: #666; font-size: 12px;'>⚠️ DELETE este arquivo após usar (debug-homologacoes.php)</p>";
?>
