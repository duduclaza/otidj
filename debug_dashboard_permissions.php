<?php
// Script para debugar permissões do dashboard
require_once __DIR__ . '/src/Config/Database.php';
require_once __DIR__ . '/src/Services/PermissionService.php';

use App\Config\Database;
use App\Services\PermissionService;

try {
    $db = Database::getInstance();
    
    echo "<h2>🔍 DEBUG: Permissões do Dashboard</h2>";
    
    // 1. Verificar se existe módulo "dashboard" na tabela profile_permissions
    echo "<h3>1. Módulos disponíveis na tabela profile_permissions:</h3>";
    $stmt = $db->prepare("SELECT DISTINCT module FROM profile_permissions ORDER BY module");
    $stmt->execute();
    $modules = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($modules as $module) {
        echo "<li>" . htmlspecialchars($module) . "</li>";
    }
    echo "</ul>";
    
    // 2. Verificar se existe especificamente "dashboard"
    echo "<h3>2. Verificar módulo 'dashboard':</h3>";
    $stmt = $db->prepare("SELECT COUNT(*) FROM profile_permissions WHERE module = 'dashboard'");
    $stmt->execute();
    $dashboardExists = $stmt->fetchColumn();
    
    if ($dashboardExists > 0) {
        echo "✅ Módulo 'dashboard' existe na tabela<br>";
        
        // Mostrar quais perfis têm permissão para dashboard
        $stmt = $db->prepare("
            SELECT p.name as profile_name, pp.can_view, pp.can_edit, pp.can_delete 
            FROM profile_permissions pp 
            JOIN profiles p ON pp.profile_id = p.id 
            WHERE pp.module = 'dashboard'
        ");
        $stmt->execute();
        $dashboardPerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Perfis com permissões para dashboard:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Perfil</th><th>View</th><th>Edit</th><th>Delete</th></tr>";
        foreach ($dashboardPerms as $perm) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($perm['profile_name']) . "</td>";
            echo "<td>" . ($perm['can_view'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['can_edit'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($perm['can_delete'] ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ Módulo 'dashboard' NÃO existe na tabela profile_permissions<br>";
        echo "🔧 <strong>PROBLEMA IDENTIFICADO:</strong> O módulo 'dashboard' precisa ser adicionado à tabela!<br>";
    }
    
    // 3. Verificar usuários e seus perfis
    echo "<h3>3. Usuários e seus perfis:</h3>";
    $stmt = $db->prepare("
        SELECT u.id, u.name, u.email, p.name as profile_name 
        FROM users u 
        LEFT JOIN profiles p ON u.profile_id = p.id 
        ORDER BY u.id
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Perfil</th><th>Tem Dashboard?</th></tr>";
    foreach ($users as $user) {
        $hasDashboard = PermissionService::hasPermission($user['id'], 'dashboard', 'view');
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['profile_name'] ?? 'Sem perfil') . "</td>";
        echo "<td>" . ($hasDashboard ? '✅ SIM' : '❌ NÃO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 4. Verificar estrutura da tabela profiles
    echo "<h3>4. Perfis disponíveis:</h3>";
    $stmt = $db->prepare("SELECT id, name, description, is_admin FROM profiles ORDER BY id");
    $stmt->execute();
    $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Descrição</th><th>É Admin?</th></tr>";
    foreach ($profiles as $profile) {
        echo "<tr>";
        echo "<td>" . $profile['id'] . "</td>";
        echo "<td>" . htmlspecialchars($profile['name']) . "</td>";
        echo "<td>" . htmlspecialchars($profile['description']) . "</td>";
        echo "<td>" . ($profile['is_admin'] ? '✅ SIM' : '❌ NÃO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f0f0f0; }
h2 { color: #333; }
h3 { color: #666; margin-top: 30px; }
</style>
