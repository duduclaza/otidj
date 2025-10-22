<?php
/**
 * Script para configurar permissões do módulo Auditorias
 * Apenas para Administradores e Super Administradores
 * 
 * Execute este script uma vez via navegador ou CLI
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Config/Database.php';

use App\Config\Database;

try {
    $db = Database::getInstance()->getConnection();
    
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
        $stmt->execute([':profile_id' => $profile['id']]);
        $added++;
        echo "<p>✅ Permissões adicionadas para: {$profile['name']}</p>\n";
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
    echo "<h3>✅ Configuração Concluída!</h3>\n";
    echo "<p><strong>Apenas os perfis listados acima terão acesso ao módulo Auditorias.</strong></p>\n";
    echo "<p>O menu 'Auditorias' só aparecerá para usuários com perfil de Administrador ou Super Administrador.</p>\n";
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro ao configurar permissões:</h3>\n";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>\n";
    echo "<p>Verifique se:</p>\n";
    echo "<ul>\n";
    echo "<li>O banco de dados está acessível</li>\n";
    echo "<li>As tabelas 'profiles' e 'profile_permissions' existem</li>\n";
    echo "<li>As credenciais do banco estão corretas no arquivo .env</li>\n";
    echo "</ul>\n";
}
