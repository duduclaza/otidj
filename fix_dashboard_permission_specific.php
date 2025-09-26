<?php
// Script específico para corrigir permissões do dashboard
require_once __DIR__ . '/src/Config/Database.php';
require_once __DIR__ . '/src/Services/PermissionService.php';

use App\Config\Database;
use App\Services\PermissionService;

// CONFIGURAÇÃO: Altere aqui o ID do usuário que está com problema
$problematicUserId = 2; // Altere para o ID do usuário que deveria ter acesso

try {
    $db = Database::getInstance();
    
    echo "<h2>🔧 CORREÇÃO ESPECÍFICA: Permissão Dashboard</h2>";
    
    // 1. Informações do usuário
    $stmt = $db->prepare("
        SELECT u.id, u.name, u.email, u.profile_id, p.name as profile_name, p.is_admin
        FROM users u 
        LEFT JOIN profiles p ON u.profile_id = p.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$problematicUserId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ Usuário com ID $problematicUserId não encontrado!<br>";
        
        // Listar usuários disponíveis
        echo "<h3>Usuários disponíveis:</h3>";
        $stmt = $db->prepare("SELECT u.id, u.name, u.email, p.name as profile_name FROM users u LEFT JOIN profiles p ON u.profile_id = p.id ORDER BY u.id");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Perfil</th></tr>";
        foreach ($users as $u) {
            echo "<tr>";
            echo "<td>" . $u['id'] . "</td>";
            echo "<td>" . htmlspecialchars($u['name']) . "</td>";
            echo "<td>" . htmlspecialchars($u['email']) . "</td>";
            echo "<td>" . htmlspecialchars($u['profile_name'] ?? 'Sem perfil') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>Edite este arquivo e altere \$problematicUserId para o ID correto.</strong></p>";
        exit;
    }
    
    echo "<h3>👤 Usuário Analisado:</h3>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $user['id'] . "</li>";
    echo "<li><strong>Nome:</strong> " . htmlspecialchars($user['name']) . "</li>";
    echo "<li><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</li>";
    echo "<li><strong>Perfil:</strong> " . htmlspecialchars($user['profile_name'] ?? 'Sem perfil') . "</li>";
    echo "<li><strong>É Admin:</strong> " . ($user['is_admin'] ? 'Sim' : 'Não') . "</li>";
    echo "</ul>";
    
    // 2. Teste de permissão atual
    echo "<h3>🧪 Teste de Permissão Atual:</h3>";
    $hasDashboard = PermissionService::hasPermission($user['id'], 'dashboard', 'view');
    echo "<p><strong>Resultado:</strong> " . ($hasDashboard ? '✅ TEM PERMISSÃO' : '❌ NÃO TEM PERMISSÃO') . "</p>";
    
    // 3. Verificar se o módulo dashboard existe para o perfil
    if ($user['profile_id']) {
        echo "<h3>🗄️ Verificação no Banco de Dados:</h3>";
        
        $stmt = $db->prepare("
            SELECT module, can_view, can_edit, can_delete, can_import, can_export
            FROM profile_permissions 
            WHERE profile_id = ? AND module = 'dashboard'
        ");
        $stmt->execute([$user['profile_id']]);
        $dashboardPerm = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dashboardPerm) {
            echo "<p>✅ <strong>Permissão encontrada no banco:</strong></p>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Módulo</th><th>View</th><th>Edit</th><th>Delete</th><th>Import</th><th>Export</th></tr>";
            echo "<tr>";
            echo "<td>" . $dashboardPerm['module'] . "</td>";
            echo "<td>" . ($dashboardPerm['can_view'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($dashboardPerm['can_edit'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($dashboardPerm['can_delete'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($dashboardPerm['can_import'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($dashboardPerm['can_export'] ? '✅' : '❌') . "</td>";
            echo "</tr>";
            echo "</table>";
            
            if (!$dashboardPerm['can_view']) {
                echo "<p>⚠️ <strong>PROBLEMA:</strong> Permissão existe mas can_view = 0</p>";
            }
        } else {
            echo "<p>❌ <strong>PROBLEMA:</strong> Não existe permissão 'dashboard' para este perfil!</p>";
        }
        
        // Mostrar todas as permissões do perfil
        echo "<h4>Todas as permissões do perfil:</h4>";
        $stmt = $db->prepare("
            SELECT module, can_view, can_edit, can_delete
            FROM profile_permissions 
            WHERE profile_id = ? 
            ORDER BY module
        ");
        $stmt->execute([$user['profile_id']]);
        $allPerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($allPerms) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Módulo</th><th>View</th><th>Edit</th><th>Delete</th></tr>";
            foreach ($allPerms as $perm) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($perm['module']) . "</td>";
                echo "<td>" . ($perm['can_view'] ? '✅' : '❌') . "</td>";
                echo "<td>" . ($perm['can_edit'] ? '✅' : '❌') . "</td>";
                echo "<td>" . ($perm['can_delete'] ? '✅' : '❌') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ Este perfil não tem NENHUMA permissão configurada!</p>";
        }
    }
    
    // 4. Botão para corrigir automaticamente
    if (!$hasDashboard && $user['profile_id']) {
        echo "<h3>🔧 CORREÇÃO AUTOMÁTICA:</h3>";
        
        if (isset($_GET['fix']) && $_GET['fix'] == 'dashboard') {
            echo "<p>🔧 Executando correção...</p>";
            
            // Verificar se já existe a permissão
            $checkStmt = $db->prepare("SELECT id FROM profile_permissions WHERE profile_id = ? AND module = 'dashboard'");
            $checkStmt->execute([$user['profile_id']]);
            
            if ($checkStmt->fetch()) {
                // Atualizar permissão existente
                $updateStmt = $db->prepare("
                    UPDATE profile_permissions 
                    SET can_view = 1 
                    WHERE profile_id = ? AND module = 'dashboard'
                ");
                $updateStmt->execute([$user['profile_id']]);
                echo "<p>✅ <strong>CORRIGIDO!</strong> Permissão de dashboard atualizada para can_view = 1</p>";
            } else {
                // Inserir nova permissão
                $insertStmt = $db->prepare("
                    INSERT INTO profile_permissions 
                    (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                    VALUES (?, 'dashboard', 1, 0, 0, 0, 0)
                ");
                $insertStmt->execute([$user['profile_id']]);
                echo "<p>✅ <strong>CORRIGIDO!</strong> Permissão de dashboard adicionada com can_view = 1</p>";
            }
            
            echo "<p><a href='?' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔄 Testar Novamente</a></p>";
            
        } else {
            echo "<p>Clique no botão abaixo para adicionar/corrigir a permissão de dashboard:</p>";
            echo "<p><a href='?fix=dashboard' style='background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔧 CORRIGIR PERMISSÃO DE DASHBOARD</a></p>";
        }
    } else if ($hasDashboard) {
        echo "<h3>✅ TUDO OK!</h3>";
        echo "<p>O usuário tem permissão para acessar o dashboard.</p>";
        echo "<p><strong>Teste:</strong> Faça login com este usuário e acesse <a href='/dashboard'>/dashboard</a></p>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
table { margin: 10px 0; border-collapse: collapse; width: 100%; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f0f0f0; font-weight: bold; }
h2 { color: #333; border-bottom: 2px solid #333; padding-bottom: 5px; }
h3 { color: #666; margin-top: 25px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
ul { margin: 10px 0; }
li { margin: 3px 0; }
a { color: #007cba; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
