<?php
/**
 * Script de Verificação - Módulo Controle de RC
 * Execute este arquivo via navegador ou CLI para diagnosticar problemas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Verificação Controle RC</title>";
echo "<style>body{font-family:sans-serif;padding:20px;background:#f5f5f5;}";
echo ".success{color:green;font-weight:bold;}.error{color:red;font-weight:bold;}";
echo ".warning{color:orange;font-weight:bold;}.info{color:blue;}.box{background:white;padding:15px;margin:10px 0;border-radius:5px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}";
echo "h2{border-bottom:2px solid #333;padding-bottom:10px;}h3{color:#555;}</style></head><body>";

echo "<h1>🔍 Verificação do Módulo Controle de RC</h1>";

// 1. Verificar arquivos
echo "<div class='box'><h2>1️⃣ Arquivos do Sistema</h2>";

$files = [
    'Controller' => __DIR__ . '/src/Controllers/ControleRcController.php',
    'View Index' => __DIR__ . '/views/pages/controle-rc/index.php',
    'View Print' => __DIR__ . '/views/pages/controle-rc/print.php',
    'Migration' => __DIR__ . '/database/migrations/create_controle_rc_tables.sql',
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        $size = filesize($path);
        echo "<p class='success'>✅ $name existe (" . number_format($size) . " bytes)</p>";
    } else {
        echo "<p class='error'>❌ $name NÃO ENCONTRADO: $path</p>";
    }
}
echo "</div>";

// 2. Verificar Database
echo "<div class='box'><h2>2️⃣ Conexão com Banco de Dados</h2>";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/src/Config/Database.php';
    
    $db = \App\Config\Database::getInstance();
    echo "<p class='success'>✅ Conexão com banco estabelecida</p>";
    
    // Verificar tabelas
    echo "<h3>Tabelas:</h3>";
    $tables = ['controle_rc', 'controle_rc_evidencias'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("DESCRIBE `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p class='success'>✅ Tabela '$table' existe (" . count($columns) . " colunas)</p>";
            echo "<p class='info' style='margin-left:20px;font-size:12px;'>Colunas: " . implode(', ', $columns) . "</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Tabela '$table' NÃO EXISTE</p>";
            echo "<p class='warning' style='margin-left:20px;'>Execute: database/migrations/create_controle_rc_tables.sql</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 3. Verificar rotas
echo "<div class='box'><h2>3️⃣ Configuração de Rotas</h2>";

$indexFile = __DIR__ . '/public/index.php';
if (file_exists($indexFile)) {
    $content = file_get_contents($indexFile);
    
    $routes = [
        '/controle-de-rc' => 'ControleRcController::class, \'index\'',
        '/controle-rc/list' => 'ControleRcController::class, \'list\'',
        '/controle-rc/create' => 'ControleRcController::class, \'create\'',
        '/controle-rc/update' => 'ControleRcController::class, \'update\'',
        '/controle-rc/delete' => 'ControleRcController::class, \'delete\'',
    ];
    
    foreach ($routes as $route => $expected) {
        if (strpos($content, $route) !== false) {
            echo "<p class='success'>✅ Rota '$route' configurada</p>";
        } else {
            echo "<p class='error'>❌ Rota '$route' NÃO CONFIGURADA</p>";
        }
    }
} else {
    echo "<p class='error'>❌ Arquivo public/index.php não encontrado</p>";
}
echo "</div>";

// 4. Verificar Middleware
echo "<div class='box'><h2>4️⃣ Middleware de Permissões</h2>";

$middlewareFile = __DIR__ . '/src/Middleware/PermissionMiddleware.php';
if (file_exists($middlewareFile)) {
    $content = file_get_contents($middlewareFile);
    
    if (strpos($content, "'controle_rc'") !== false || strpos($content, "'/controle-rc'") !== false) {
        echo "<p class='success'>✅ Módulo 'controle_rc' mapeado no middleware</p>";
    } else {
        echo "<p class='warning'>⚠️ Módulo 'controle_rc' NÃO está no middleware</p>";
        echo "<p class='info' style='margin-left:20px;'>Adicione no PermissionMiddleware.php</p>";
    }
} else {
    echo "<p class='error'>❌ PermissionMiddleware.php não encontrado</p>";
}
echo "</div>";

// 5. Verificar Permissões no Banco
echo "<div class='box'><h2>5️⃣ Permissões no Banco de Dados</h2>";

try {
    $stmt = $db->query("SELECT * FROM profile_permissions WHERE module = 'controle_rc'");
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($permissions) > 0) {
        echo "<p class='success'>✅ " . count($permissions) . " perfil(is) com permissão para 'controle_rc'</p>";
        
        echo "<table border='1' cellpadding='5' cellspacing='0' style='margin-top:10px;'>";
        echo "<tr><th>Profile ID</th><th>View</th><th>Edit</th><th>Delete</th><th>Export</th></tr>";
        foreach ($permissions as $perm) {
            echo "<tr>";
            echo "<td>" . $perm['profile_id'] . "</td>";
            echo "<td>" . ($perm['can_view'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['can_edit'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['can_delete'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['can_export'] ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Nenhum perfil tem permissão para 'controle_rc'</p>";
        echo "<p class='info' style='margin-left:20px;'>Configure em: Administrativo → Gerenciar Perfis</p>";
        echo "<p class='info' style='margin-left:20px;'>Ou execute:</p>";
        echo "<pre style='background:#f0f0f0;padding:10px;'>INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_export)\nVALUES (1, 'controle_rc', 1, 1, 1, 1);</pre>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao verificar permissões: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 6. Testar Controller
echo "<div class='box'><h2>6️⃣ Teste do Controller</h2>";

try {
    require_once __DIR__ . '/src/Controllers/ControleRcController.php';
    echo "<p class='success'>✅ ControleRcController pode ser carregado sem erros</p>";
    
    // Verificar métodos
    $methods = ['index', 'list', 'create', 'update', 'delete', 'show', 'print', 'exportReport', 'downloadEvidencia'];
    $reflection = new ReflectionClass('App\Controllers\ControleRcController');
    
    foreach ($methods as $method) {
        if ($reflection->hasMethod($method)) {
            echo "<p class='success'>✅ Método '$method()' existe</p>";
        } else {
            echo "<p class='error'>❌ Método '$method()' NÃO EXISTE</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao carregar controller: " . $e->getMessage() . "</p>";
    echo "<pre style='background:#fff0f0;padding:10px;color:#d00;'>" . $e->getTraceAsString() . "</pre>";
}
echo "</div>";

// 7. Resumo
echo "<div class='box' style='background:#e8f5e9;'><h2>📊 Resumo</h2>";
echo "<p><strong>Próximos Passos:</strong></p>";
echo "<ol>";
echo "<li>Se houver erros vermelhos (❌), corrija-os primeiro</li>";
echo "<li>Se houver avisos laranja (⚠️), configure as permissões</li>";
echo "<li>Teste acessando: <a href='/controle-de-rc'>/controle-de-rc</a></li>";
echo "<li>Se ainda houver erro 500, verifique os logs do PHP</li>";
echo "</ol>";
echo "</div>";

echo "<div class='box' style='background:#fff3e0;'><h2>🔧 Comandos Úteis</h2>";
echo "<h3>Criar tabelas:</h3>";
echo "<pre style='background:#f0f0f0;padding:10px;'>mysql -u usuario -p banco < database/migrations/create_controle_rc_tables.sql</pre>";

echo "<h3>Adicionar permissão:</h3>";
echo "<pre style='background:#f0f0f0;padding:10px;'>INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_export)\nVALUES (1, 'controle_rc', 1, 1, 1, 1);</pre>";

echo "<h3>Verificar logs PHP:</h3>";
echo "<pre style='background:#f0f0f0;padding:10px;'>tail -f /var/log/php_errors.log</pre>";
echo "</div>";

echo "<p style='text-align:center;margin-top:30px;color:#999;'>SGQ OTI DJ - Verificação Controle RC v1.0</p>";
echo "</body></html>";
?>
