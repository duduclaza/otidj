<?php
/**
 * DIAGNÓSTICO COMPLETO: Sistema de Notificações POPs e ITs
 * 
 * Este script verifica:
 * 1. Se a coluna pode_aprovar_pops_its existe
 * 2. Quais administradores têm a permissão ativa
 * 3. Se a tabela de notificações existe
 * 4. Se há notificações sendo criadas
 * 5. Se o EmailService está configurado
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Config/Database.php';

use App\Config\Database;

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Diagnóstico POPs e ITs</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #2563eb; border-bottom: 3px solid #2563eb; padding-bottom: 10px; }
    h2 { color: #059669; margin-top: 30px; }
    .success { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 10px 0; border-radius: 4px; }
    .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 10px 0; border-radius: 4px; }
    .error { background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 10px 0; border-radius: 4px; }
    .info { background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin: 10px 0; border-radius: 4px; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th { background: #1f2937; color: white; padding: 12px; text-align: left; }
    td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
    tr:hover { background: #f9fafb; }
    .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
    .badge-yes { background: #10b981; color: white; }
    .badge-no { background: #ef4444; color: white; }
    code { background: #f3f4f6; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    pre { background: #1f2937; color: #f3f4f6; padding: 15px; border-radius: 6px; overflow-x: auto; }
</style></head><body><div class='container'>";

echo "<h1>🔍 Diagnóstico Completo: Notificações POPs e ITs</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    $db = Database::getInstance();
    echo "<div class='success'>✅ <strong>Conexão com banco de dados OK</strong></div>";
    
    // ===== 1. VERIFICAR COLUNA pode_aprovar_pops_its =====
    echo "<h2>1️⃣ Verificar Coluna de Permissão</h2>";
    
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_pops_its'");
    $colunaExiste = $stmt->rowCount() > 0;
    
    if ($colunaExiste) {
        echo "<div class='success'>✅ <strong>Coluna <code>pode_aprovar_pops_its</code> existe na tabela users</strong></div>";
    } else {
        echo "<div class='error'>❌ <strong>PROBLEMA: Coluna <code>pode_aprovar_pops_its</code> NÃO existe!</strong><br>";
        echo "Execute a migration: <code>database/migrations/add_pode_aprovar_pops_its_column.sql</code></div>";
    }
    
    // ===== 2. BUSCAR TODOS ADMINISTRADORES =====
    echo "<h2>2️⃣ Administradores Cadastrados</h2>";
    
    $stmt = $db->query("
        SELECT 
            id, 
            name, 
            email, 
            role, 
            status,
            " . ($colunaExiste ? "pode_aprovar_pops_its" : "0 as pode_aprovar_pops_its") . "
        FROM users 
        WHERE role = 'admin'
        ORDER BY name
    ");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "<div class='warning'>⚠️ <strong>Nenhum administrador encontrado no sistema</strong></div>";
    } else {
        echo "<p>Total de administradores: <strong>" . count($admins) . "</strong></p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Status</th><th>Pode Aprovar POPs?</th></tr>";
        
        $comPermissao = 0;
        foreach ($admins as $admin) {
            $status = $admin['status'] === 'active' ? 'Ativo' : 'Inativo';
            $statusClass = $admin['status'] === 'active' ? 'success' : 'warning';
            
            $podeAprovar = $admin['pode_aprovar_pops_its'] == 1;
            if ($podeAprovar) $comPermissao++;
            
            echo "<tr>";
            echo "<td>{$admin['id']}</td>";
            echo "<td><strong>{$admin['name']}</strong></td>";
            echo "<td>{$admin['email']}</td>";
            echo "<td><span class='badge badge-" . ($admin['status'] === 'active' ? 'yes' : 'no') . "'>{$status}</span></td>";
            echo "<td><span class='badge badge-" . ($podeAprovar ? 'yes' : 'no') . "'>" . ($podeAprovar ? 'SIM ✓' : 'NÃO ✗') . "</span></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if ($comPermissao === 0) {
            echo "<div class='error'>❌ <strong>PROBLEMA ENCONTRADO:</strong> Nenhum administrador tem a permissão <code>pode_aprovar_pops_its</code> ativa!<br>";
            echo "<strong>Solução:</strong> Vá em <strong>Perfil do Usuário</strong> e marque o checkbox <strong>\"Pode Aprovar POPs e ITs\"</strong> para os administradores desejados.</div>";
        } else {
            echo "<div class='success'>✅ <strong>$comPermissao administrador(es) com permissão ativa</strong></div>";
        }
    }
    
    // ===== 3. VERIFICAR TABELA DE NOTIFICAÇÕES =====
    echo "<h2>3️⃣ Tabela de Notificações</h2>";
    
    $stmt = $db->query("SHOW TABLES LIKE 'notifications'");
    $tabelaExiste = $stmt->rowCount() > 0;
    
    if ($tabelaExiste) {
        echo "<div class='success'>✅ <strong>Tabela <code>notifications</code> existe</strong></div>";
        
        // Contar notificações POPs e ITs
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM notifications 
            WHERE type LIKE '%pops_its%'
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Total de notificações POPs/ITs no sistema: <strong>{$result['total']}</strong></p>";
        
        // Últimas 10 notificações POPs
        $stmt = $db->query("
            SELECT n.*, u.name as user_name
            FROM notifications n
            LEFT JOIN users u ON n.user_id = u.id
            WHERE n.type LIKE '%pops_its%'
            ORDER BY n.created_at DESC
            LIMIT 10
        ");
        $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($notificacoes)) {
            echo "<p><strong>Últimas 10 notificações:</strong></p>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Usuário</th><th>Título</th><th>Tipo</th><th>Data</th><th>Lida?</th></tr>";
            foreach ($notificacoes as $notif) {
                $lida = $notif['read_at'] ? 'Sim' : 'Não';
                echo "<tr>";
                echo "<td>{$notif['id']}</td>";
                echo "<td>{$notif['user_name']}</td>";
                echo "<td>{$notif['title']}</td>";
                echo "<td><code>{$notif['type']}</code></td>";
                echo "<td>" . date('d/m/Y H:i', strtotime($notif['created_at'])) . "</td>";
                echo "<td>{$lida}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<div class='error'>❌ <strong>PROBLEMA: Tabela <code>notifications</code> NÃO existe!</strong></div>";
    }
    
    // ===== 4. VERIFICAR CONFIGURAÇÕES DE EMAIL =====
    echo "<h2>4️⃣ Configurações de Email</h2>";
    
    $emailConfig = [
        'MAIL_HOST' => $_ENV['MAIL_HOST'] ?? 'NÃO CONFIGURADO',
        'MAIL_PORT' => $_ENV['MAIL_PORT'] ?? 'NÃO CONFIGURADO',
        'MAIL_USERNAME' => $_ENV['MAIL_USERNAME'] ?? 'NÃO CONFIGURADO',
        'MAIL_FROM' => $_ENV['MAIL_FROM'] ?? 'NÃO CONFIGURADO',
        'MAIL_FROM_NAME' => $_ENV['MAIL_FROM_NAME'] ?? 'NÃO CONFIGURADO'
    ];
    
    $emailOk = true;
    echo "<table>";
    echo "<tr><th>Configuração</th><th>Valor</th></tr>";
    foreach ($emailConfig as $key => $value) {
        $isConfigured = $value !== 'NÃO CONFIGURADO';
        if (!$isConfigured) $emailOk = false;
        
        echo "<tr>";
        echo "<td><code>$key</code></td>";
        echo "<td>" . ($key === 'MAIL_PASSWORD' ? '***' : $value) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($emailOk) {
        echo "<div class='success'>✅ <strong>Configurações de email OK</strong></div>";
    } else {
        echo "<div class='warning'>⚠️ <strong>Algumas configurações de email estão faltando</strong></div>";
    }
    
    // ===== 5. TESTAR CRIAÇÃO DE NOTIFICAÇÃO =====
    echo "<h2>5️⃣ Teste de Notificação</h2>";
    
    echo "<div class='info'>
        <strong>Para testar:</strong><br>
        1. Crie um novo registro POP ou IT<br>
        2. Verifique os logs em <code>logs/pops_its_debug.log</code><br>
        3. Verifique o sininho 🔔 no sistema<br>
        4. Verifique a caixa de entrada do email
    </div>";
    
    // ===== RESUMO FINAL =====
    echo "<h2>📊 Resumo do Diagnóstico</h2>";
    
    $problemas = [];
    $sucessos = [];
    
    if ($colunaExiste) {
        $sucessos[] = "Coluna pode_aprovar_pops_its existe";
    } else {
        $problemas[] = "Coluna pode_aprovar_pops_its NÃO existe - Execute a migration";
    }
    
    if ($comPermissao > 0) {
        $sucessos[] = "$comPermissao administrador(es) com permissão ativa";
    } else {
        $problemas[] = "Nenhum administrador com permissão ativa - Ative nas configurações do perfil";
    }
    
    if ($tabelaExiste) {
        $sucessos[] = "Tabela notifications existe";
    } else {
        $problemas[] = "Tabela notifications NÃO existe";
    }
    
    if ($emailOk) {
        $sucessos[] = "Configurações de email completas";
    } else {
        $problemas[] = "Configurações de email incompletas";
    }
    
    if (!empty($sucessos)) {
        echo "<div class='success'><strong>✅ Pontos OK:</strong><ul>";
        foreach ($sucessos as $s) {
            echo "<li>$s</li>";
        }
        echo "</ul></div>";
    }
    
    if (!empty($problemas)) {
        echo "<div class='error'><strong>❌ Problemas Encontrados:</strong><ul>";
        foreach ($problemas as $p) {
            echo "<li>$p</li>";
        }
        echo "</ul></div>";
    } else {
        echo "<div class='success'><strong>🎉 Sistema configurado corretamente!</strong><br>";
        echo "Se mesmo assim não estiver recebendo notificações, verifique:<br>";
        echo "1. Spam/lixo eletrônico do email<br>";
        echo "2. Logs do sistema em <code>logs/pops_its_debug.log</code><br>";
        echo "3. Se o usuário realmente tem o checkbox marcado no perfil</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'><strong>❌ ERRO:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</div></body></html>";
