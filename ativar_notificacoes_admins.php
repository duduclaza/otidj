<?php
/**
 * SCRIPT RÁPIDO: Ativar notificações para administradores
 * 
 * Este script simplesmente ativa a permissão pode_aprovar_pops_its
 * para todos os administradores ativos do sistema.
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Config/Database.php';

use App\Config\Database;

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Ativar Notificações</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #2563eb; border-bottom: 3px solid #2563eb; padding-bottom: 10px; }
    .success { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 15px 0; border-radius: 4px; }
    .error { background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 15px 0; border-radius: 4px; }
    .info { background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin: 15px 0; border-radius: 4px; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th { background: #1f2937; color: white; padding: 12px; text-align: left; }
    td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
    .badge-yes { background: #10b981; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
    .badge-no { background: #ef4444; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
    code { background: #f3f4f6; padding: 2px 6px; border-radius: 3px; }
    .btn { display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 10px 5px; }
    .btn:hover { background: #059669; }
</style></head><body><div class='container'>";

echo "<h1>🔔 Ativar Notificações POPs e ITs</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    $db = Database::getInstance();
    
    // Verificar se coluna existe
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_pops_its'");
    $colunaExiste = $stmt->rowCount() > 0;
    
    if (!$colunaExiste) {
        echo "<div class='error'>";
        echo "<strong>❌ ERRO: Coluna não existe!</strong><br><br>";
        echo "A coluna <code>pode_aprovar_pops_its</code> não foi encontrada na tabela <code>users</code>.<br>";
        echo "Por favor, execute a migration primeiro:<br><br>";
        echo "<code>database/migrations/add_pode_aprovar_pops_its_column.sql</code><br><br>";
        echo "<strong>Ou execute este SQL:</strong><br>";
        echo "<pre style='background: #1f2937; color: white; padding: 15px; border-radius: 6px; overflow-x: auto;'>";
        echo "ALTER TABLE users \n";
        echo "ADD COLUMN pode_aprovar_pops_its TINYINT(1) DEFAULT 0 \n";
        echo "COMMENT 'Indica se o administrador recebe emails de POPs/ITs pendentes';";
        echo "</pre>";
        echo "</div>";
        echo "</div></body></html>";
        exit;
    }
    
    echo "<div class='success'>✅ <strong>Coluna <code>pode_aprovar_pops_its</code> existe!</strong></div>";
    
    // Buscar administradores ANTES
    echo "<h2>📊 Status ANTES da Ativação</h2>";
    $stmt = $db->query("
        SELECT id, name, email, role, status, pode_aprovar_pops_its
        FROM users 
        WHERE role = 'admin'
        ORDER BY name
    ");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "<div class='error'>❌ <strong>Nenhum administrador encontrado no sistema!</strong></div>";
        echo "</div></body></html>";
        exit;
    }
    
    echo "<table>";
    echo "<tr><th>Nome</th><th>Email</th><th>Status</th><th>Notificações POPs?</th></tr>";
    
    $comPermissaoAntes = 0;
    foreach ($admins as $admin) {
        if ($admin['pode_aprovar_pops_its'] == 1) $comPermissaoAntes++;
        
        echo "<tr>";
        echo "<td><strong>{$admin['name']}</strong></td>";
        echo "<td>{$admin['email']}</td>";
        echo "<td>" . ($admin['status'] === 'active' ? 'Ativo' : 'Inativo') . "</td>";
        echo "<td><span class='badge-" . ($admin['pode_aprovar_pops_its'] == 1 ? 'yes' : 'no') . "'>";
        echo ($admin['pode_aprovar_pops_its'] == 1 ? 'SIM ✓' : 'NÃO ✗');
        echo "</span></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>Total:</strong> " . count($admins) . " administrador(es)</p>";
    echo "<p><strong>Com notificações ativas:</strong> $comPermissaoAntes</p>";
    
    // ATIVAR PARA TODOS
    if (isset($_GET['confirmar']) && $_GET['confirmar'] === 'sim') {
        
        echo "<h2>⚡ Ativando Notificações...</h2>";
        
        $stmt = $db->prepare("
            UPDATE users 
            SET pode_aprovar_pops_its = 1 
            WHERE role = 'admin' AND status = 'active'
        ");
        $result = $stmt->execute();
        $affected = $stmt->rowCount();
        
        if ($result) {
            echo "<div class='success'>";
            echo "<strong>✅ SUCESSO!</strong><br>";
            echo "Notificações ativadas para <strong>$affected</strong> administrador(es) ativo(s)!";
            echo "</div>";
            
            // Mostrar status DEPOIS
            echo "<h2>📊 Status DEPOIS da Ativação</h2>";
            $stmt = $db->query("
                SELECT id, name, email, role, status, pode_aprovar_pops_its
                FROM users 
                WHERE role = 'admin'
                ORDER BY name
            ");
            $adminsDepois = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<tr><th>Nome</th><th>Email</th><th>Status</th><th>Notificações POPs?</th></tr>";
            
            $comPermissaoDepois = 0;
            foreach ($adminsDepois as $admin) {
                if ($admin['pode_aprovar_pops_its'] == 1) $comPermissaoDepois++;
                
                echo "<tr>";
                echo "<td><strong>{$admin['name']}</strong></td>";
                echo "<td>{$admin['email']}</td>";
                echo "<td>" . ($admin['status'] === 'active' ? 'Ativo' : 'Inativo') . "</td>";
                echo "<td><span class='badge-" . ($admin['pode_apovar_pops_its'] == 1 ? 'yes' : 'no') . "'>";
                echo ($admin['pode_aprovar_pops_its'] == 1 ? 'SIM ✓' : 'NÃO ✗');
                echo "</span></td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<p><strong>Com notificações ativas agora:</strong> $comPermissaoDepois de " . count($adminsDepois) . "</p>";
            
            echo "<div class='info'>";
            echo "<strong>🎉 Pronto!</strong><br>";
            echo "Agora todos os administradores ativos receberão notificações quando houver POPs ou ITs pendentes de aprovação.<br><br>";
            echo "<strong>Próximos passos:</strong><br>";
            echo "1. Teste criando um novo POP ou IT<br>";
            echo "2. Verifique o sininho 🔔 no sistema<br>";
            echo "3. Verifique o email dos administradores<br>";
            echo "</div>";
            
            echo "<a href='/diagnostico_notificacoes_pops.php' class='btn'>📊 Ver Diagnóstico Completo</a>";
            
        } else {
            echo "<div class='error'>";
            echo "<strong>❌ ERRO ao atualizar!</strong><br>";
            echo "Nenhum registro foi atualizado.";
            echo "</div>";
        }
        
    } else {
        // Mostrar botão de confirmação
        echo "<div class='info'>";
        echo "<strong>⚠️ Você está prestes a:</strong><br>";
        echo "• Ativar <code>pode_aprovar_pops_its = 1</code> para TODOS administradores ativos<br>";
        echo "• Isso permitirá que eles recebam notificações de POPs e ITs pendentes<br>";
        echo "</div>";
        
        echo "<a href='?confirmar=sim' class='btn' style='background: #10b981;'>✅ SIM, ATIVAR PARA TODOS</a>";
        echo "<a href='/diagnostico_notificacoes_pops.php' class='btn' style='background: #6b7280;'>📊 Ver Diagnóstico Completo</a>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'><strong>❌ ERRO:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre style='background: #1f2937; color: white; padding: 15px; border-radius: 6px; overflow-x: auto;'>";
    echo htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
}

echo "</div></body></html>";
