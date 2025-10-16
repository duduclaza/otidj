<?php
/**
 * Diagnóstico COMPLETO de Erro 500 - Homologações
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>🔍 Diagnóstico COMPLETO de Homologações</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; } 
    .ok { color: green; font-weight: bold; } 
    .erro { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
    pre { background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

try {
    // 1. Testar carregamento básico
    echo "<div class='section'>";
    echo "<h2>1️⃣ Carregando Sistema...</h2>";
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p class='ok'>✅ Autoload OK</p>";
    echo "</div>";

    // 2. Testar .env
    echo "<div class='section'>";
    echo "<h2>2️⃣ Carregando Configurações...</h2>";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo "<p class='ok'>✅ .env carregado</p>";
    echo "</div>";

    // 3. Testar conexão banco
    echo "<div class='section'>";
    echo "<h2>3️⃣ Testando Banco de Dados...</h2>";
    $db = App\Config\Database::getInstance();
    echo "<p class='ok'>✅ Conexão com banco OK</p>";
    echo "<p>Banco: " . $_ENV['DB_DATABASE'] . "</p>";
    echo "</div>";

    // 4. Verificar tabelas
    echo "<div class='section'>";
    echo "<h2>4️⃣ Verificando Tabelas...</h2>";
    $tabelas = ['homologacoes', 'homologacoes_responsaveis', 'homologacoes_historico', 'homologacoes_anexos'];
    $tabelasOK = true;
    
    foreach ($tabelas as $tabela) {
        $stmt = $db->query("SHOW TABLES LIKE '$tabela'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='ok'>✅ Tabela <b>$tabela</b> existe</p>";
        } else {
            $tabelasOK = false;
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
    echo "</div>";
    echo "<div class='section'>";
    echo "<h2>7️⃣ Verificando View...</h2>";
    $viewPath = __DIR__ . '/../views/homologacoes/kanban.php';
    if (file_exists($viewPath)) {
        echo "<p class='ok'>✅ View kanban.php existe</p>";
    } else {
        echo "<p class='erro'>❌ View kanban.php NÃO encontrada em: $viewPath</p>";
    }
    echo "</div>";
    
    // 9. TESTE REAL - Tentar carregar a página
    echo "<div class='section'>";
    echo "<h2>8️⃣ TESTE REAL - Executando Controller...</h2>";
    try {
        if (!isset($_SESSION['user_id'])) {
            session_start();
            // Buscar primeiro admin
            $stmt = $db->query("SELECT id FROM users WHERE id = 1 LIMIT 1");
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
            }
        }
        
        echo "<p>Tentando executar HomologacoesController->index()...</p>";
        $controller = new App\Controllers\HomologacoesController();
        ob_start();
        $controller->index();
        $output = ob_get_clean();
        echo "<p class='ok'>✅ Controller executado SEM ERROS!</p>";
        echo "<p class='ok'>✅ Página carregaria normalmente!</p>";
    } catch (Exception $e) {
        echo "<p class='erro'>❌ ERRO ao executar controller:</p>";
        echo "<pre style='background: #ffe; padding: 10px; border: 1px solid orange;'>";
        echo $e->getMessage() . "\n";
        echo "Arquivo: " . $e->getFile() . "\n";
        echo "Linha: " . $e->getLine();
        echo "</pre>";
    }
    echo "</div>";

    echo "<hr>";
    echo "<div class='section'>";
    echo "<h2>✅ RESUMO</h2>";
    if ($tabelasOK) {
        echo "<p class='ok'>✅ Todas as tabelas existem!</p>";
    } else {
        echo "<p class='erro'>❌ Faltam tabelas! Execute o SQL.</p>";
    }
    echo "<p><strong>Se todas as verificações acima estão OK</strong>, o sistema deve funcionar!</p>";
    echo "<p><strong>Se há ❌ vermelhos</strong>, corrija os problemas indicados.</p>";
    echo "<hr>";
    echo "<p><a href='/homologacoes' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>🚀 Testar /homologacoes</a></p>";
    echo "</div>";

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
