<?php
/**
 * Script de Verificação do Módulo de Homologações
 * Execute este arquivo para verificar se tudo está configurado corretamente
 */

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$db = App\Config\Database::getInstance();

echo "<h1>🔍 Verificação do Módulo de Homologações</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .check { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
</style>";

// 1. Verificar tabelas
echo "<div class='section'>";
echo "<h2>1️⃣ Verificando Tabelas do Banco de Dados</h2>";

$tabelas = ['homologacoes', 'homologacoes_responsaveis', 'homologacoes_historico', 'homologacoes_anexos'];
$tabelasOk = true;

foreach ($tabelas as $tabela) {
    try {
        $stmt = $db->query("SHOW TABLES LIKE '$tabela'");
        if ($stmt->rowCount() > 0) {
            echo "✅ <span class='check'>Tabela `$tabela` existe</span><br>";
        } else {
            echo "❌ <span class='error'>Tabela `$tabela` NÃO existe</span><br>";
            $tabelasOk = false;
        }
    } catch (Exception $e) {
        echo "❌ <span class='error'>Erro ao verificar tabela `$tabela`: " . $e->getMessage() . "</span><br>";
        $tabelasOk = false;
    }
}

if (!$tabelasOk) {
    echo "<p class='error'>⚠️ Execute o arquivo database/homologacoes_kanban.sql para criar as tabelas!</p>";
}
echo "</div>";

// 2. Verificar coluna department
echo "<div class='section'>";
echo "<h2>2️⃣ Verificando Coluna 'department' na Tabela Users</h2>";

try {
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'department'");
    if ($stmt->rowCount() > 0) {
        echo "✅ <span class='check'>Coluna `department` existe na tabela `users`</span><br>";
        
        // Verificar usuários com department configurado
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE department IS NOT NULL AND department != ''");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            echo "✅ <span class='check'>{$result['total']} usuário(s) com departamento configurado</span><br>";
            
            // Mostrar distribuição
            $stmt = $db->query("
                SELECT department, COUNT(*) as total 
                FROM users 
                WHERE department IS NOT NULL AND department != '' 
                GROUP BY department
            ");
            $distribuicao = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<ul>";
            foreach ($distribuicao as $dept) {
                echo "<li>{$dept['department']}: {$dept['total']} usuário(s)</li>";
            }
            echo "</ul>";
        } else {
            echo "⚠️ <span class='warning'>Nenhum usuário com departamento configurado</span><br>";
            echo "<p>Execute os comandos SQL abaixo para configurar:</p>";
            echo "<pre>UPDATE users SET department = 'Compras' WHERE id IN (1, 2, 3);
UPDATE users SET department = 'Logistica' WHERE id IN (4, 5, 6);</pre>";
        }
    } else {
        echo "❌ <span class='error'>Coluna `department` NÃO existe na tabela `users`</span><br>";
        echo "<p>Execute o comando SQL abaixo:</p>";
        echo "<pre>ALTER TABLE users ADD COLUMN department VARCHAR(100) DEFAULT NULL AFTER email;</pre>";
    }
} catch (Exception $e) {
    echo "❌ <span class='error'>Erro ao verificar coluna: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// 3. Verificar permissões
echo "<div class='section'>";
echo "<h2>3️⃣ Verificando Permissões do Módulo</h2>";

try {
    $stmt = $db->query("
        SELECT p.name as perfil, pp.can_view, pp.can_edit, pp.can_delete
        FROM profile_permissions pp
        JOIN profiles p ON pp.profile_id = p.id
        WHERE pp.module = 'homologacoes'
    ");
    $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($permissoes) > 0) {
        echo "✅ <span class='check'>Permissões configuradas para " . count($permissoes) . " perfil(is)</span><br>";
        echo "<table border='1' cellpadding='5' style='margin-top: 10px;'>";
        echo "<tr><th>Perfil</th><th>Visualizar</th><th>Editar</th><th>Excluir</th></tr>";
        foreach ($permissoes as $perm) {
            echo "<tr>";
            echo "<td>{$perm['perfil']}</td>";
            echo "<td>" . ($perm['can_view'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['can_edit'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['can_delete'] ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ <span class='warning'>Nenhuma permissão configurada para o módulo 'homologacoes'</span><br>";
        echo "<p>Execute o script database/homologacoes_kanban.sql ou configure manualmente via interface</p>";
    }
} catch (Exception $e) {
    echo "❌ <span class='error'>Erro ao verificar permissões: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// 4. Verificar arquivos
echo "<div class='section'>";
echo "<h2>4️⃣ Verificando Arquivos do Sistema</h2>";

$arquivos = [
    'Controller' => __DIR__ . '/src/Controllers/HomologacoesController.php',
    'View' => __DIR__ . '/views/homologacoes/kanban.php',
    'SQL' => __DIR__ . '/database/homologacoes_kanban.sql',
];

$arquivosOk = true;
foreach ($arquivos as $tipo => $caminho) {
    if (file_exists($caminho)) {
        echo "✅ <span class='check'>$tipo: " . basename($caminho) . " existe</span><br>";
    } else {
        echo "❌ <span class='error'>$tipo: " . basename($caminho) . " NÃO existe</span><br>";
        $arquivosOk = false;
    }
}
echo "</div>";

// 5. Verificar rotas
echo "<div class='section'>";
echo "<h2>5️⃣ Verificando Configuração de Rotas</h2>";

$indexContent = file_get_contents(__DIR__ . '/public/index.php');
if (strpos($indexContent, "'/homologacoes'") !== false) {
    echo "✅ <span class='check'>Rotas configuradas em public/index.php</span><br>";
} else {
    echo "❌ <span class='error'>Rotas NÃO configuradas em public/index.php</span><br>";
}

$middlewareContent = file_get_contents(__DIR__ . '/src/Middleware/PermissionMiddleware.php');
if (strpos($middlewareContent, "'/homologacoes'") !== false) {
    echo "✅ <span class='check'>Mapeamento configurado em PermissionMiddleware.php</span><br>";
} else {
    echo "❌ <span class='error'>Mapeamento NÃO configurado em PermissionMiddleware.php</span><br>";
}
echo "</div>";

// 6. Verificar usuários que podem criar homologações
echo "<div class='section'>";
echo "<h2>6️⃣ Usuários que Podem Criar Homologações</h2>";

try {
    // Super Admins
    $stmt = $db->query("
        SELECT u.id, u.name, u.email, p.name as perfil
        FROM users u
        LEFT JOIN profiles p ON u.profile_id = p.id
        WHERE p.name IN ('Super Admin', 'Administrador')
        AND u.status = 'active'
    ");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Usuários de Compras com permissão
    $stmt = $db->query("
        SELECT u.id, u.name, u.email, u.department, p.name as perfil
        FROM users u
        LEFT JOIN profiles p ON u.profile_id = p.id
        LEFT JOIN profile_permissions pp ON p.id = pp.profile_id
        WHERE LOWER(u.department) IN ('compras', 'administrativo', 'admin')
        AND pp.module = 'homologacoes'
        AND pp.can_edit = 1
        AND u.status = 'active'
    ");
    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($admins) + count($compras);
    
    if ($total > 0) {
        echo "✅ <span class='check'>$total usuário(s) pode(m) criar homologações</span><br>";
        
        if (count($admins) > 0) {
            echo "<h3>Administradores:</h3><ul>";
            foreach ($admins as $user) {
                echo "<li>{$user['name']} ({$user['email']}) - {$user['perfil']}</li>";
            }
            echo "</ul>";
        }
        
        if (count($compras) > 0) {
            echo "<h3>Departamento Compras/Admin:</h3><ul>";
            foreach ($compras as $user) {
                echo "<li>{$user['name']} ({$user['email']}) - {$user['department']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "⚠️ <span class='warning'>Nenhum usuário pode criar homologações</span><br>";
        echo "<p>Configure o departamento dos usuários ou adicione permissões aos perfis</p>";
    }
} catch (Exception $e) {
    echo "❌ <span class='error'>Erro ao verificar usuários: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// 7. Resumo final
echo "<div class='section'>";
echo "<h2>📊 Resumo Final</h2>";

$erros = 0;
$avisos = 0;

if (!$tabelasOk) $erros++;
if (!$arquivosOk) $erros++;

if ($erros > 0) {
    echo "<p class='error'>❌ Sistema NÃO está pronto para uso. Corrija os erros acima.</p>";
} elseif ($avisos > 0) {
    echo "<p class='warning'>⚠️ Sistema está funcional mas requer atenção nos avisos acima.</p>";
} else {
    echo "<p class='check'>✅ Sistema está configurado e pronto para uso!</p>";
    echo "<p><a href='/homologacoes' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Acessar Módulo de Homologações</a></p>";
}
echo "</div>";

echo "<div class='section'>";
echo "<h2>📚 Próximos Passos</h2>";
echo "<ol>";
echo "<li>Se houver erros, execute o arquivo <code>database/homologacoes_kanban.sql</code></li>";
echo "<li>Configure o departamento dos usuários via SQL ou interface web</li>";
echo "<li>Configure permissões para os perfis em <strong>Administrativo > Gerenciar Perfis</strong></li>";
echo "<li>Teste criando uma homologação em <code>/homologacoes</code></li>";
echo "<li>Delete este arquivo após a verificação (segurança)</li>";
echo "</ol>";
echo "</div>";

echo "<p style='text-align: center; color: #666; margin-top: 30px;'>⚠️ <strong>IMPORTANTE:</strong> Delete este arquivo após usar (verificar_homologacoes.php)</p>";
?>
