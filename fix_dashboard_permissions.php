<?php
// Script para corrigir permissões do dashboard
require_once __DIR__ . '/src/Config/Database.php';
require_once __DIR__ . '/src/Services/PermissionService.php';

use App\Config\Database;
use App\Services\PermissionService;

try {
    $db = Database::getInstance();
    
    echo "<h2>🔧 CORREÇÃO: Permissões do Dashboard</h2>";
    
    // 1. Verificar se existe módulo "dashboard" na tabela profile_permissions
    echo "<h3>1. Verificando módulo 'dashboard' existente:</h3>";
    $stmt = $db->prepare("SELECT COUNT(*) FROM profile_permissions WHERE module = 'dashboard'");
    $stmt->execute();
    $dashboardExists = $stmt->fetchColumn();
    
    if ($dashboardExists > 0) {
        echo "✅ Módulo 'dashboard' já existe (" . $dashboardExists . " registros)<br>";
        
        // Mostrar permissões atuais
        $stmt = $db->prepare("
            SELECT p.name as profile_name, pp.can_view, pp.can_edit, pp.can_delete 
            FROM profile_permissions pp 
            JOIN profiles p ON pp.profile_id = p.id 
            WHERE pp.module = 'dashboard'
            ORDER BY p.name
        ");
        $stmt->execute();
        $currentPerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Permissões atuais:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Perfil</th><th>View</th><th>Edit</th><th>Delete</th></tr>";
        foreach ($currentPerms as $perm) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($perm['profile_name']) . "</td>";
            echo "<td>" . ($perm['can_view'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['can_edit'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['can_delete'] ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "❌ Módulo 'dashboard' NÃO existe na tabela<br>";
        echo "🔧 Vamos adicionar permissões de dashboard para os perfis administrativos...<br><br>";
        
        // Adicionar permissões de dashboard para perfis administrativos
        $stmt = $db->prepare("SELECT id, name, is_admin FROM profiles WHERE is_admin = 1");
        $stmt->execute();
        $adminProfiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($adminProfiles as $profile) {
            echo "➕ Adicionando permissão de dashboard para: " . htmlspecialchars($profile['name']) . "<br>";
            
            $insertStmt = $db->prepare("
                INSERT INTO profile_permissions 
                (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                VALUES (?, 'dashboard', 1, 1, 1, 0, 0)
            ");
            $insertStmt->execute([$profile['id']]);
        }
        
        echo "<br>✅ Permissões de dashboard adicionadas para perfis administrativos!<br>";
    }
    
    // 2. Verificar perfis não-admin que podem precisar de dashboard
    echo "<h3>2. Verificando outros perfis:</h3>";
    $stmt = $db->prepare("SELECT id, name, description FROM profiles WHERE is_admin = 0 ORDER BY name");
    $stmt->execute();
    $otherProfiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Perfis não-administrativos disponíveis:</p>";
    echo "<ul>";
    foreach ($otherProfiles as $profile) {
        // Verificar se já tem permissão de dashboard
        $checkStmt = $db->prepare("SELECT can_view FROM profile_permissions WHERE profile_id = ? AND module = 'dashboard'");
        $checkStmt->execute([$profile['id']]);
        $hasDashboard = $checkStmt->fetchColumn();
        
        echo "<li>";
        echo "<strong>" . htmlspecialchars($profile['name']) . "</strong> ";
        echo "(" . htmlspecialchars($profile['description']) . ") ";
        echo "- Dashboard: " . ($hasDashboard ? '✅ TEM' : '❌ NÃO TEM');
        echo "</li>";
    }
    echo "</ul>";
    
    // 3. Opção para adicionar dashboard a um perfil específico
    if (isset($_GET['add_dashboard_to']) && is_numeric($_GET['add_dashboard_to'])) {
        $profileId = (int)$_GET['add_dashboard_to'];
        
        // Verificar se o perfil existe
        $stmt = $db->prepare("SELECT name FROM profiles WHERE id = ? AND is_admin = 0");
        $stmt->execute([$profileId]);
        $profileName = $stmt->fetchColumn();
        
        if ($profileName) {
            echo "<h3>3. Adicionando dashboard ao perfil: " . htmlspecialchars($profileName) . "</h3>";
            
            // Verificar se já existe
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM profile_permissions WHERE profile_id = ? AND module = 'dashboard'");
            $checkStmt->execute([$profileId]);
            
            if ($checkStmt->fetchColumn() > 0) {
                // Atualizar permissão existente
                $updateStmt = $db->prepare("
                    UPDATE profile_permissions 
                    SET can_view = 1, can_edit = 0, can_delete = 0 
                    WHERE profile_id = ? AND module = 'dashboard'
                ");
                $updateStmt->execute([$profileId]);
                echo "✅ Permissão de dashboard ATUALIZADA para visualização!<br>";
            } else {
                // Inserir nova permissão
                $insertStmt = $db->prepare("
                    INSERT INTO profile_permissions 
                    (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                    VALUES (?, 'dashboard', 1, 0, 0, 0, 0)
                ");
                $insertStmt->execute([$profileId]);
                echo "✅ Permissão de dashboard ADICIONADA para visualização!<br>";
            }
        } else {
            echo "❌ Perfil não encontrado ou é administrativo<br>";
        }
    }
    
    // 4. Links para adicionar dashboard aos perfis
    if (!empty($otherProfiles)) {
        echo "<h3>4. Adicionar dashboard a um perfil:</h3>";
        echo "<p>Clique no perfil para adicionar permissão de dashboard (apenas visualização):</p>";
        echo "<ul>";
        foreach ($otherProfiles as $profile) {
            $checkStmt = $db->prepare("SELECT can_view FROM profile_permissions WHERE profile_id = ? AND module = 'dashboard'");
            $checkStmt->execute([$profile['id']]);
            $hasDashboard = $checkStmt->fetchColumn();
            
            if (!$hasDashboard) {
                echo "<li>";
                echo "<a href='?add_dashboard_to=" . $profile['id'] . "' style='color: blue; text-decoration: underline;'>";
                echo "➕ Adicionar dashboard ao perfil: " . htmlspecialchars($profile['name']);
                echo "</a>";
                echo "</li>";
            }
        }
        echo "</ul>";
    }
    
    // 5. Teste final
    echo "<h3>5. Teste Final - Usuários com acesso ao dashboard:</h3>";
    
    $stmt = $db->prepare("
        SELECT u.id, u.name, u.email, p.name as profile_name 
        FROM users u 
        LEFT JOIN profiles p ON u.profile_id = p.id 
        ORDER BY u.name
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Nome</th><th>Email</th><th>Perfil</th><th>Dashboard?</th></tr>";
    foreach ($users as $user) {
        $hasDashboard = PermissionService::hasPermission($user['id'], 'dashboard', 'view');
        echo "<tr>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['profile_name'] ?? 'Sem perfil') . "</td>";
        echo "<td style='text-align: center;'>" . ($hasDashboard ? '✅' : '❌') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; width: 100%; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f0f0f0; font-weight: bold; }
h2 { color: #333; border-bottom: 2px solid #333; padding-bottom: 5px; }
h3 { color: #666; margin-top: 30px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
a { color: #0066cc; }
a:hover { color: #0052a3; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>
