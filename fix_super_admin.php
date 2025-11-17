<?php
/**
 * Script de Correção: Definir du.claza@gmail.com como Super Admin
 * Data: 17/11/2025
 * 
 * Acesse: https://djbr.sgqoti.com.br/fix_super_admin.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

// Configurações
$email = 'du.claza@gmail.com';

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Correção Super Admin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔧 Correção Super Admin - SGQ OTI DJ</h1>";

try {
    $db = Database::getInstance();
    
    echo "<h2>1️⃣ Verificando situação atual</h2>";
    
    // Verificar situação atual
    $stmt = $db->prepare("
        SELECT id, name, email, role 
        FROM users 
        WHERE email = :email
    ");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<div class='error'>❌ <strong>Erro:</strong> Usuário {$email} não encontrado no banco de dados!</div>";
        echo "<div class='info'>💡 Verifique se o email está correto.</div>";
        exit;
    }
    
    echo "<div class='info'>";
    echo "<strong>Usuário encontrado:</strong><br>";
    echo "ID: {$user['id']}<br>";
    echo "Nome: {$user['name']}<br>";
    echo "Email: {$user['email']}<br>";
    echo "Role Atual: <code>{$user['role']}</code>";
    echo "</div>";
    
    // Verificar se já é super_admin
    if ($user['role'] === 'super_admin') {
        echo "<div class='success'>✅ <strong>Já está correto!</strong> Usuário já é super_admin.</div>";
    } else {
        echo "<div class='warning'>⚠️ <strong>Precisa corrigir:</strong> Role atual é <code>{$user['role']}</code>, deveria ser <code>super_admin</code>.</div>";
        
        echo "<h2>2️⃣ Corrigindo para super_admin</h2>";
        
        // Atualizar para super_admin
        $stmt = $db->prepare("
            UPDATE users 
            SET role = 'super_admin' 
            WHERE email = :email
        ");
        $stmt->execute([':email' => $email]);
        
        echo "<div class='success'>✅ <strong>Atualizado com sucesso!</strong> Role alterado para super_admin.</div>";
    }
    
    // Verificar todos super_admins
    echo "<h2>3️⃣ Verificando todos os super admins no sistema</h2>";
    
    $stmt = $db->prepare("
        SELECT id, name, email, role, created_at 
        FROM users 
        WHERE role = 'super_admin'
        ORDER BY created_at
    ");
    $stmt->execute();
    $superAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($superAdmins) > 1) {
        echo "<div class='warning'>⚠️ <strong>Atenção:</strong> Existem " . count($superAdmins) . " super admins no sistema!</div>";
    } else {
        echo "<div class='success'>✅ <strong>Perfeito!</strong> Apenas 1 super admin no sistema.</div>";
    }
    
    echo "<table>";
    echo "<thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Cadastrado em</th></tr></thead>";
    echo "<tbody>";
    foreach ($superAdmins as $sa) {
        $highlight = $sa['email'] === $email ? " style='background: #d4edda;'" : "";
        echo "<tr{$highlight}>";
        echo "<td>{$sa['id']}</td>";
        echo "<td>{$sa['name']}</td>";
        echo "<td>{$sa['email']}</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($sa['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    
    // Listar todos admins
    echo "<h2>4️⃣ Todos os administradores do sistema</h2>";
    
    $stmt = $db->prepare("
        SELECT id, name, email, role 
        FROM users 
        WHERE role IN ('admin', 'super_admin')
        ORDER BY FIELD(role, 'super_admin', 'admin'), name
    ");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th></tr></thead>";
    echo "<tbody>";
    foreach ($admins as $admin) {
        $tipo = $admin['role'] === 'super_admin' ? '🔑 SUPER ADMIN' : '👤 ADMIN';
        $highlight = $admin['email'] === $email ? " style='background: #d4edda;'" : "";
        echo "<tr{$highlight}>";
        echo "<td>{$admin['id']}</td>";
        echo "<td>{$admin['name']}</td>";
        echo "<td>{$admin['email']}</td>";
        echo "<td>{$tipo}</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    
    // Instruções finais
    echo "<h2>5️⃣ Próximos passos</h2>";
    echo "<div class='info'>";
    echo "<strong>Para que as alterações tenham efeito:</strong><br><br>";
    echo "1. ✅ Faça <strong>LOGOUT</strong> no sistema SGQ<br>";
    echo "2. ✅ Faça <strong>LOGIN</strong> novamente com <code>{$email}</code><br>";
    echo "3. ✅ A sessão será atualizada com role = 'super_admin'<br>";
    echo "4. ✅ Acesse <a href='/suporte'>/suporte</a> para testar<br>";
    echo "5. ✅ <strong>DELETE este arquivo</strong> após a correção por segurança<br>";
    echo "</div>";
    
    echo "<div class='warning'>";
    echo "<strong>⚠️ IMPORTANTE - Segurança:</strong><br>";
    echo "Após confirmar que está funcionando, DELETE este arquivo:<br>";
    echo "<code>fix_super_admin.php</code><br>";
    echo "Este script permite alteração direta no banco de dados.";
    echo "</div>";
    
    echo "<div class='success'>";
    echo "<strong>✅ Correção concluída com sucesso!</strong><br>";
    echo "Usuário <strong>{$email}</strong> agora é <strong>super_admin</strong>.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "❌ <strong>Erro ao conectar ao banco de dados:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<strong>💡 Solução alternativa:</strong><br>";
    echo "Execute o script SQL manualmente no phpMyAdmin:<br>";
    echo "<code>database/fix_super_admin_duclaza.sql</code>";
    echo "</div>";
}

echo "</body></html>";
