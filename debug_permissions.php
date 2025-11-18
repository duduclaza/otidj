<?php
// Script para debug de permissões - Controle RC
// Execute este arquivo para verificar suas permissões

session_start();
require_once 'config/database.php';
require_once 'src/Services/PermissionService.php';

echo "<h2>🔍 DEBUG DE PERMISSÕES - CONTROLE RC</h2>";

// Verificar se está logado
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Usuário não está logado!</p>";
    echo "<p><a href='/login'>Fazer login</a></p>";
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'N/A';
$userRole = $_SESSION['user_role'] ?? 'N/A';

echo "<h3>👤 Informações do Usuário:</h3>";
echo "<ul>";
echo "<li><strong>ID:</strong> {$userId}</li>";
echo "<li><strong>Nome:</strong> {$userName}</li>";
echo "<li><strong>Role:</strong> {$userRole}</li>";
echo "</ul>";

// Verificar permissões específicas
$permissions = [
    'controle_rc' => 'view',
    'controle_rc' => 'edit',
    'controle_rc' => 'create',
    'controle_rc' => 'delete'
];

echo "<h3>🔐 Verificação de Permissões:</h3>";

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    
    // Verificar se tem permissão geral para controle_rc
    $hasPermission = \App\Services\PermissionService::hasPermission($userId, 'controle_rc', 'view');
    
    if ($hasPermission) {
        echo "<p style='color: green;'>✅ <strong>TEM PERMISSÃO</strong> para Controle RC (view)</p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>SEM PERMISSÃO</strong> para Controle RC (view)</p>";
    }
    
    // Verificar outras permissões
    $actions = ['edit', 'create', 'delete'];
    foreach ($actions as $action) {
        $hasPerm = \App\Services\PermissionService::hasPermission($userId, 'controle_rc', $action);
        $status = $hasPerm ? "✅" : "❌";
        $color = $hasPerm ? "green" : "red";
        echo "<p style='color: {$color};'>{$status} Controle RC ({$action})</p>";
    }
    
    // Verificar perfil do usuário
    echo "<h3>👥 Perfil do Usuário:</h3>";
    $stmt = $db->prepare("
        SELECT p.name as profile_name, p.id as profile_id
        FROM users u 
        LEFT JOIN profiles p ON u.profile_id = p.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profile) {
        echo "<p><strong>Perfil:</strong> {$profile['profile_name']} (ID: {$profile['profile_id']})</p>";
        
        // Verificar permissões do perfil
        echo "<h3>🎯 Permissões do Perfil:</h3>";
        $stmt = $db->prepare("
            SELECT pp.module, pp.action, pp.allowed
            FROM profile_permissions pp
            WHERE pp.profile_id = ? AND pp.module = 'controle_rc'
        ");
        $stmt->execute([$profile['profile_id']]);
        $profilePerms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($profilePerms) {
            foreach ($profilePerms as $perm) {
                $status = $perm['allowed'] ? "✅ Permitido" : "❌ Negado";
                $color = $perm['allowed'] ? "green" : "red";
                echo "<p style='color: {$color};'>{$status}: {$perm['module']} - {$perm['action']}</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Nenhuma permissão específica encontrada para controle_rc</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Perfil não encontrado!</p>";
    }
    
    // Verificar se é admin
    echo "<h3>🔑 Verificações Especiais:</h3>";
    if ($userRole === 'admin' || $userRole === 'super_admin') {
        echo "<p style='color: green;'>✅ Usuário é ADMIN - deve ter acesso total</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Usuário NÃO é admin - depende de permissões específicas</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao verificar permissões: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🛠️ Soluções Possíveis:</h3>";
echo "<ol>";
echo "<li><strong>Se você é ADMIN:</strong> Verifique se seu role está correto na tabela users</li>";
echo "<li><strong>Se você NÃO é admin:</strong> Peça para um admin dar permissão para 'controle_rc' no seu perfil</li>";
echo "<li><strong>Verificar módulo:</strong> Acesse Administrativo > Gerenciar Perfis e configure permissões</li>";
echo "</ol>";

echo "<p><a href='/controle-de-rc' style='background: blue; color: white; padding: 10px; text-decoration: none;'>🔄 Tentar acessar Controle RC</a></p>";
echo "<p><a href='/admin/profiles' style='background: green; color: white; padding: 10px; text-decoration: none;'>⚙️ Gerenciar Perfis</a></p>";
?>
