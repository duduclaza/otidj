<?php
/**
 * Script para configurar permissões do módulo Auditorias
 * Apenas para Administradores e Super Administradores
 */

// Carregar configurações do .env
$envFile = __DIR__ . '/.env';
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$config = [];
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    list($key, $value) = explode('=', $line, 2);
    $config[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
}

try {
    // Configuração do banco de dados
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s',
        $config['DB_HOST'],
        $config['DB_PORT'],
        $config['DB_DATABASE']
    );
    
    $db = new PDO($dsn, $config['DB_USERNAME'], $config['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "<h2>🔧 Configurando Permissões do Módulo Auditorias</h2>\n";
    echo "<p>Apenas Administradores e Super Administradores terão acesso</p>\n";
    echo "<hr>\n";
    
    // 1. Remover todas as permissões existentes do módulo 'auditorias'
    echo "<p>1️⃣ Removendo permissões antigas do módulo 'auditorias'...</p>\n";
    $stmt = $db->prepare("DELETE FROM profile_permissions WHERE module = 'auditorias'");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    echo "<p>✅ {$deleted} permissões antigas removidas</p>\n";
    
    // 2. Buscar perfis de Administrador
    echo "<p>2️⃣ Buscando perfis de administrador...</p>\n";
    $stmt = $db->query("
        SELECT id, name 
        FROM profiles 
        WHERE name LIKE '%Administrador%' 
           OR name LIKE '%Admin%'
           OR name = 'Administrador'
        ORDER BY name
    ");
    $adminProfiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($adminProfiles)) {
        echo "<p>⚠️ Nenhum perfil de administrador encontrado!</p>\n";
        echo "<p>Criando perfil 'Administrador' padrão...</p>\n";
        
        // Criar perfil Administrador se não existir
        $stmt = $db->prepare("
            INSERT INTO profiles (name, description, created_at, updated_at)
            VALUES ('Administrador', 'Acesso total ao sistema', NOW(), NOW())
        ");
        $stmt->execute();
        $adminId = $db->lastInsertId();
        
        $adminProfiles = [
            ['id' => $adminId, 'name' => 'Administrador']
        ];
        
        echo "<p>✅ Perfil 'Administrador' criado com ID: {$adminId}</p>\n";
    }
    
    echo "<p>📋 Perfis de administrador encontrados:</p>\n";
    echo "<ul>\n";
    foreach ($adminProfiles as $profile) {
        echo "<li>ID {$profile['id']}: {$profile['name']}</li>\n";
    }
    echo "</ul>\n";
    
    // 3. Adicionar permissões completas para cada perfil de administrador
    echo "<p>3️⃣ Adicionando permissões do módulo 'auditorias'...</p>\n";
    
    $stmt = $db->prepare("
        INSERT INTO profile_permissions 
        (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
        VALUES (:profile_id, 'auditorias', 1, 1, 1, 1, 1)
    ");
    
    $added = 0;
    foreach ($adminProfiles as $profile) {
        try {
            $stmt->execute([':profile_id' => $profile['id']]);
            $added++;
            echo "<p>✅ Permissões adicionadas para: {$profile['name']}</p>\n";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                echo "<p>ℹ️ Permissões já existiam para: {$profile['name']}</p>\n";
                $added++;
            } else {
                throw $e;
            }
        }
    }
    
    echo "<p><strong>✅ {$added} perfis configurados com sucesso!</strong></p>\n";
    
    // 4. Verificar as permissões configuradas
    echo "<hr>\n";
    echo "<h3>📊 Permissões Configuradas para o Módulo 'auditorias':</h3>\n";
    
    $stmt = $db->query("
        SELECT 
            p.id,
            p.name AS perfil,
            pp.can_view AS visualizar,
            pp.can_edit AS editar,
            pp.can_delete AS excluir,
            pp.can_import AS importar,
            pp.can_export AS exportar
        FROM profile_permissions pp
        JOIN profiles p ON pp.profile_id = p.id
        WHERE pp.module = 'auditorias'
        ORDER BY p.name
    ");
    
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($permissions)) {
        echo "<p>⚠️ Nenhuma permissão encontrada!</p>\n";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>\n";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Perfil</th><th>Visualizar</th><th>Editar</th><th>Excluir</th><th>Importar</th><th>Exportar</th>";
        echo "</tr>\n";
        
        foreach ($permissions as $perm) {
            echo "<tr>";
            echo "<td>{$perm['id']}</td>";
            echo "<td><strong>{$perm['perfil']}</strong></td>";
            echo "<td>" . ($perm['visualizar'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['editar'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['excluir'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['importar'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['exportar'] ? '✅' : '❌') . "</td>";
            echo "</tr>\n";
        }
        
        echo "</table>\n";
    }
    
    echo "<hr>\n";
    echo "<p style='color: green; font-weight: bold;'>✅ Configuração concluída com sucesso em " . date('d/m/Y H:i:s') . "</p>\n";
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro ao configurar permissões:</h3>\n";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>\n";
    
    if ($e->errorInfo[1] == 2002) {
        echo "<p>Não foi possível conectar ao servidor de banco de dados.</p>\n";
        echo "<p>Verifique se o servidor MySQL está rodando e acessível.</p>\n";
    } elseif ($e->errorInfo[1] == 1045) {
        echo "<p>Erro de autenticação. Verifique o usuário e senha no arquivo .env</p>\n";
    } elseif ($e->errorInfo[1] == 1049) {
        echo "<p>O banco de dados especificado não existe.</p>\n";
    }
    
    echo "<p>Detalhes técnicos:</p>\n";
    echo "<pre>" . print_r($e->errorInfo, true) . "</pre>\n";
}

echo "<hr>\n";
echo "<p>Script finalizado em " . date('d/m/Y H:i:s') . "</p>\n";
