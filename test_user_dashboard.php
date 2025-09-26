<?php
// Script para testar permissões de dashboard de um usuário específico
require_once __DIR__ . '/src/Config/Database.php';
require_once __DIR__ . '/src/Services/PermissionService.php';

use App\Config\Database;
use App\Services\PermissionService;

// Configurar o usuário para testar (altere conforme necessário)
$testUserId = 1; // Altere para o ID do usuário que você quer testar

try {
    $db = Database::getInstance();
    
    echo "<h2>🧪 TESTE: Permissões de Dashboard do Usuário</h2>";
    
    // 1. Informações do usuário
    $stmt = $db->prepare("
        SELECT u.id, u.name, u.email, u.profile_id, p.name as profile_name, p.is_admin
        FROM users u 
        LEFT JOIN profiles p ON u.profile_id = p.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$testUserId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ Usuário com ID $testUserId não encontrado!<br>";
        echo "Usuários disponíveis:<br>";
        
        $stmt = $db->prepare("SELECT id, name, email FROM users ORDER BY id");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($users as $u) {
            echo "- ID: {$u['id']}, Nome: {$u['name']}, Email: {$u['email']}<br>";
        }
        exit;
    }
    
    echo "<h3>👤 Informações do Usuário:</h3>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $user['id'] . "</li>";
    echo "<li><strong>Nome:</strong> " . htmlspecialchars($user['name']) . "</li>";
    echo "<li><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</li>";
    echo "<li><strong>Profile ID:</strong> " . ($user['profile_id'] ?? 'Nenhum') . "</li>";
    echo "<li><strong>Perfil:</strong> " . htmlspecialchars($user['profile_name'] ?? 'Sem perfil') . "</li>";
    echo "<li><strong>É Admin:</strong> " . ($user['is_admin'] ? 'Sim' : 'Não') . "</li>";
    echo "</ul>";
    
    // 2. Verificar se tem permissão usando o PermissionService
    echo "<h3>🔍 Teste de Permissão (PermissionService):</h3>";
    $hasDashboard = PermissionService::hasPermission($user['id'], 'dashboard', 'view');
    echo "<p><strong>Resultado:</strong> " . ($hasDashboard ? '✅ TEM PERMISSÃO' : '❌ NÃO TEM PERMISSÃO') . "</p>";
    
    // 3. Verificar permissões diretas no banco
    echo "<h3>🗄️ Permissões Diretas no Banco:</h3>";
    if ($user['profile_id']) {
        $stmt = $db->prepare("
            SELECT module, can_view, can_edit, can_delete, can_import, can_export
            FROM profile_permissions 
            WHERE profile_id = ? AND module = 'dashboard'
        ");
        $stmt->execute([$user['profile_id']]);
        $dashboardPerm = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dashboardPerm) {
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
        } else {
            echo "<p>❌ <strong>PROBLEMA ENCONTRADO:</strong> Não existe permissão de 'dashboard' para este perfil!</p>";
            
            // Verificar se o perfil tem outras permissões
            $stmt = $db->prepare("
                SELECT module, can_view 
                FROM profile_permissions 
                WHERE profile_id = ? 
                ORDER BY module
            ");
            $stmt->execute([$user['profile_id']]);
            $otherPerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($otherPerms) {
                echo "<p><strong>Outras permissões do perfil:</strong></p>";
                echo "<ul>";
                foreach ($otherPerms as $perm) {
                    echo "<li>" . $perm['module'] . " (view: " . ($perm['can_view'] ? 'Sim' : 'Não') . ")</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>❌ Este perfil não tem NENHUMA permissão configurada!</p>";
            }
        }
    } else {
        echo "<p>❌ Usuário não tem perfil associado!</p>";
    }
    
    // 4. Verificar se é Super Admin
    echo "<h3>👑 Verificação Super Admin:</h3>";
    $isSuperAdmin = PermissionService::isSuperAdmin($user['id']);
    echo "<p><strong>É Super Admin:</strong> " . ($isSuperAdmin ? '✅ SIM' : '❌ NÃO') . "</p>";
    
    // 5. Solução sugerida
    if (!$hasDashboard && !$isSuperAdmin) {
        echo "<h3>🔧 SOLUÇÃO SUGERIDA:</h3>";
        
        if ($user['profile_id']) {
            echo "<p>Para corrigir este problema, execute o seguinte SQL:</p>";
            echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 5px; font-family: monospace;'>";
            echo "INSERT INTO profile_permissions <br>";
            echo "(profile_id, module, can_view, can_edit, can_delete, can_import, can_export) <br>";
            echo "VALUES ({$user['profile_id']}, 'dashboard', 1, 0, 0, 0, 0)<br>";
            echo "ON DUPLICATE KEY UPDATE can_view = 1;";
            echo "</div>";
            
            // Botão para executar a correção
            if (isset($_GET['fix']) && $_GET['fix'] == 'dashboard') {
                echo "<h4>🔧 Executando correção...</h4>";
                
                $fixStmt = $db->prepare("
                    INSERT INTO profile_permissions 
                    (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                    VALUES (?, 'dashboard', 1, 0, 0, 0, 0)
                    ON DUPLICATE KEY UPDATE can_view = 1
                ");
                $fixStmt->execute([$user['profile_id']]);
                
                echo "<p>✅ <strong>CORRIGIDO!</strong> Permissão de dashboard adicionada.</p>";
                echo "<p><a href='?'>🔄 Testar novamente</a></p>";
            } else {
                echo "<p><a href='?fix=dashboard' style='background: #007cba; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🔧 CORRIGIR AUTOMATICAMENTE</a></p>";
            }
        } else {
            echo "<p>❌ Usuário precisa ter um perfil associado primeiro!</p>";
        }
    } else {
        echo "<h3>✅ TUDO OK!</h3>";
        echo "<p>O usuário tem as permissões necessárias para acessar o dashboard.</p>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
table { margin: 10px 0; border-collapse: collapse; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f0f0f0; font-weight: bold; }
h2 { color: #333; border-bottom: 2px solid #333; padding-bottom: 5px; }
h3 { color: #666; margin-top: 25px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
ul { margin: 10px 0; }
li { margin: 3px 0; }
a { color: #007cba; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
