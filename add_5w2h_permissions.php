<?php
// Script para adicionar permissões do módulo 5W2H
// EXECUTE APENAS UMA VEZ e depois DELETE este arquivo

require_once __DIR__ . '/src/Config/Database.php';

try {
    $db = App\Config\Database::getInstance();
    
    echo "<h2>Adicionando permissões do módulo 5W2H...</h2>\n";
    
    // 1. Verificar se o módulo já existe
    $stmt = $db->prepare("SELECT id FROM modules WHERE name = ?");
    $stmt->execute(['5w2h_planos']);
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$module) {
        // Inserir módulo 5W2H
        echo "<p>1. Criando módulo 5W2H...</p>\n";
        $stmt = $db->prepare("
            INSERT INTO modules (name, display_name, description, category, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            '5w2h_planos',
            '5W2H - Planos de Ação',
            'Módulo para criação e gestão de planos de ação usando metodologia 5W2H',
            'Gestão da Qualidade'
        ]);
        $moduleId = $db->lastInsertId();
        echo "<p style='color: green;'>✅ Módulo 5W2H criado com ID: {$moduleId}</p>\n";
    } else {
        $moduleId = $module['id'];
        echo "<p style='color: blue;'>ℹ️ Módulo 5W2H já existe com ID: {$moduleId}</p>\n";
    }
    
    // 2. Adicionar permissões do módulo
    $permissions = [
        ['action' => 'view', 'description' => 'Visualizar planos 5W2H'],
        ['action' => 'edit', 'description' => 'Criar e editar planos 5W2H'],
        ['action' => 'delete', 'description' => 'Excluir planos 5W2H'],
        ['action' => 'export', 'description' => 'Exportar dados de planos 5W2H'],
        ['action' => 'import', 'description' => 'Importar dados de planos 5W2H']
    ];
    
    echo "<p>2. Adicionando permissões...</p>\n";
    foreach ($permissions as $perm) {
        // Verificar se já existe
        $stmt = $db->prepare("
            SELECT id FROM permissions 
            WHERE module_id = ? AND action = ?
        ");
        $stmt->execute([$moduleId, $perm['action']]);
        
        if (!$stmt->fetch()) {
            $stmt = $db->prepare("
                INSERT INTO permissions (module_id, action, description, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$moduleId, $perm['action'], $perm['description']]);
            echo "<p style='color: green;'>✅ Permissão '{$perm['action']}' adicionada</p>\n";
        } else {
            echo "<p style='color: blue;'>ℹ️ Permissão '{$perm['action']}' já existe</p>\n";
        }
    }
    
    // 3. Dar permissões para o perfil Administrador
    echo "<p>3. Configurando permissões para Administrador...</p>\n";
    
    // Buscar perfil Administrador
    $stmt = $db->prepare("SELECT id FROM profiles WHERE name = 'Administrador'");
    $stmt->execute();
    $adminProfile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adminProfile) {
        // Buscar todas as permissões do módulo 5W2H
        $stmt = $db->prepare("
            SELECT id, action FROM permissions 
            WHERE module_id = ?
        ");
        $stmt->execute([$moduleId]);
        $modulePermissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($modulePermissions as $permission) {
            // Verificar se já tem a permissão
            $stmt = $db->prepare("
                SELECT id FROM profile_permissions 
                WHERE profile_id = ? AND permission_id = ?
            ");
            $stmt->execute([$adminProfile['id'], $permission['id']]);
            
            if (!$stmt->fetch()) {
                $stmt = $db->prepare("
                    INSERT INTO profile_permissions (profile_id, permission_id, created_at) 
                    VALUES (?, ?, NOW())
                ");
                $stmt->execute([$adminProfile['id'], $permission['id']]);
                echo "<p style='color: green;'>✅ Permissão '{$permission['action']}' adicionada ao Administrador</p>\n";
            } else {
                echo "<p style='color: blue;'>ℹ️ Administrador já tem permissão '{$permission['action']}'</p>\n";
            }
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Perfil Administrador não encontrado</p>\n";
    }
    
    // 4. Dar permissões básicas para outros perfis
    echo "<p>4. Configurando permissões para outros perfis...</p>\n";
    
    $profilePermissions = [
        'Usuário Comum' => ['view'],
        'Supervisor' => ['view', 'edit', 'export'],
        'Analista de Qualidade' => ['view', 'edit', 'export']
    ];
    
    foreach ($profilePermissions as $profileName => $actions) {
        $stmt = $db->prepare("SELECT id FROM profiles WHERE name = ?");
        $stmt->execute([$profileName]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($profile) {
            foreach ($actions as $action) {
                // Buscar ID da permissão
                $stmt = $db->prepare("
                    SELECT id FROM permissions 
                    WHERE module_id = ? AND action = ?
                ");
                $stmt->execute([$moduleId, $action]);
                $permission = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($permission) {
                    // Verificar se já tem
                    $stmt = $db->prepare("
                        SELECT id FROM profile_permissions 
                        WHERE profile_id = ? AND permission_id = ?
                    ");
                    $stmt->execute([$profile['id'], $permission['id']]);
                    
                    if (!$stmt->fetch()) {
                        $stmt = $db->prepare("
                            INSERT INTO profile_permissions (profile_id, permission_id, created_at) 
                            VALUES (?, ?, NOW())
                        ");
                        $stmt->execute([$profile['id'], $permission['id']]);
                        echo "<p style='color: green;'>✅ Permissão '{$action}' adicionada ao {$profileName}</p>\n";
                    }
                }
            }
        }
    }
    
    echo "<h3 style='color: green;'>🎉 PERMISSÕES 5W2H CONFIGURADAS COM SUCESSO!</h3>";
    echo "<p><strong>Agora você pode:</strong></p>";
    echo "<ul>";
    echo "<li>Acessar o módulo 5W2H no menu lateral</li>";
    echo "<li>Criar, visualizar, editar e excluir planos</li>";
    echo "<li>Fazer upload de anexos</li>";
    echo "<li>Imprimir planos detalhados</li>";
    echo "<li>DELETAR este arquivo (add_5w2h_permissions.php)</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Verifique se as tabelas de permissões existem no banco.</p>";
}
?>
