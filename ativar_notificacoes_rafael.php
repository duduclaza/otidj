<?php
/**
 * Script para ativar notificações POPs/ITs para Rafael Camargo
 * 
 * INSTRUÇÕES:
 * 1. Acesse via navegador: https://djbr.sgqoti.com.br/ativar_notificacoes_rafael.php
 * 2. O script vai verificar e ativar automaticamente
 * 3. DELETE este arquivo após usar!
 */

session_start();
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// HTML Header
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ativar Notificações Rafael - POPs/ITs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .step {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔔 Ativar Notificações POPs/ITs - Rafael Camargo</h1>
        
        <?php
        try {
            // Conectar ao banco
            $db = Database::getInstance();
            
            echo '<div class="info">✅ Conexão com banco de dados estabelecida!</div>';
            
            // PASSO 1: Verificar estrutura da tabela
            echo '<div class="step"><h3>📋 PASSO 1: Verificando estrutura da tabela users</h3>';
            
            $columns = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<table>';
            echo '<tr><th>Coluna</th><th>Tipo</th><th>Nulo</th><th>Chave</th></tr>';
            
            $hasId = false;
            $hasPodeAprovar = false;
            $primaryKey = '';
            
            foreach ($columns as $col) {
                echo '<tr>';
                echo '<td><code>' . $col['Field'] . '</code></td>';
                echo '<td>' . $col['Type'] . '</td>';
                echo '<td>' . $col['Null'] . '</td>';
                echo '<td>' . ($col['Key'] ?: '-') . '</td>';
                echo '</tr>';
                
                if ($col['Field'] === 'id') $hasId = true;
                if ($col['Field'] === 'pode_aprovar_pops_its') $hasPodeAprovar = true;
                if ($col['Key'] === 'PRI') $primaryKey = $col['Field'];
            }
            echo '</table>';
            
            echo '<p><strong>Primary Key:</strong> <code>' . $primaryKey . '</code></p>';
            echo '</div>';
            
            // PASSO 2: Verificar se coluna pode_aprovar_pops_its existe
            echo '<div class="step"><h3>🔍 PASSO 2: Verificando coluna pode_aprovar_pops_its</h3>';
            
            if (!$hasPodeAprovar) {
                echo '<div class="warning">⚠️ Coluna <code>pode_aprovar_pops_its</code> NÃO existe!</div>';
                echo '<p><strong>Criando coluna...</strong></p>';
                
                try {
                    $db->exec("ALTER TABLE users ADD COLUMN pode_aprovar_pops_its TINYINT(1) DEFAULT 0");
                    echo '<div class="success">✅ Coluna criada com sucesso!</div>';
                    $hasPodeAprovar = true;
                } catch (Exception $e) {
                    echo '<div class="error">❌ Erro ao criar coluna: ' . $e->getMessage() . '</div>';
                }
            } else {
                echo '<div class="success">✅ Coluna <code>pode_aprovar_pops_its</code> existe!</div>';
            }
            echo '</div>';
            
            // PASSO 3: Buscar dados do Rafael
            echo '<div class="step"><h3>👤 PASSO 3: Buscando dados de Rafael Camargo</h3>';
            
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute(['rafael.camargo@djlocacao.com.br']);
            $rafael = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$rafael) {
                echo '<div class="error">❌ Usuário rafael.camargo@djlocacao.com.br NÃO encontrado!</div>';
                echo '<p>Verifique se o email está correto no cadastro.</p>';
            } else {
                echo '<div class="success">✅ Usuário encontrado!</div>';
                echo '<table>';
                echo '<tr><th>Campo</th><th>Valor</th></tr>';
                
                foreach ($rafael as $key => $value) {
                    if (!in_array($key, ['password', 'remember_token'])) {
                        echo '<tr>';
                        echo '<td><strong>' . $key . '</strong></td>';
                        echo '<td>' . ($value ?: '<em>NULL</em>') . '</td>';
                        echo '</tr>';
                    }
                }
                echo '</table>';
            }
            echo '</div>';
            
            // PASSO 4: Ativar notificações
            if ($rafael && $hasPodeAprovar) {
                echo '<div class="step"><h3>🚀 PASSO 4: Ativando notificações</h3>';
                
                $valorAtual = $rafael['pode_aprovar_pops_its'] ?? 0;
                
                if ($valorAtual == 1) {
                    echo '<div class="info">ℹ️ Notificações JÁ estão ativadas para Rafael!</div>';
                } else {
                    echo '<p>Valor atual: <code>' . $valorAtual . '</code></p>';
                    echo '<p><strong>Atualizando para 1...</strong></p>';
                    
                    try {
                        $stmt = $db->prepare("
                            UPDATE users 
                            SET pode_aprovar_pops_its = 1 
                            WHERE email = ?
                        ");
                        $stmt->execute(['rafael.camargo@djlocacao.com.br']);
                        
                        $affected = $stmt->rowCount();
                        
                        if ($affected > 0) {
                            echo '<div class="success">✅ SUCESSO! Notificações ativadas para Rafael!</div>';
                            echo '<p>Linhas afetadas: <strong>' . $affected . '</strong></p>';
                        } else {
                            echo '<div class="warning">⚠️ Nenhuma linha foi atualizada. Verifique as condições.</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="error">❌ Erro ao atualizar: ' . $e->getMessage() . '</div>';
                    }
                }
                echo '</div>';
            }
            
            // PASSO 5: Verificar resultado final
            echo '<div class="step"><h3>✔️ PASSO 5: Verificação Final</h3>';
            
            $stmt = $db->prepare("
                SELECT 
                    name,
                    email,
                    role,
                    status,
                    pode_aprovar_pops_its
                FROM users 
                WHERE email = ?
            ");
            $stmt->execute(['rafael.camargo@djlocacao.com.br']);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                echo '<table>';
                echo '<tr><th>Campo</th><th>Valor</th><th>Status</th></tr>';
                
                echo '<tr>';
                echo '<td><strong>Nome</strong></td>';
                echo '<td>' . $resultado['name'] . '</td>';
                echo '<td>-</td>';
                echo '</tr>';
                
                echo '<tr>';
                echo '<td><strong>Email</strong></td>';
                echo '<td>' . $resultado['email'] . '</td>';
                echo '<td>-</td>';
                echo '</tr>';
                
                echo '<tr>';
                echo '<td><strong>Role</strong></td>';
                echo '<td>' . $resultado['role'] . '</td>';
                echo '<td>' . ($resultado['role'] === 'admin' ? '✅' : '❌') . '</td>';
                echo '</tr>';
                
                echo '<tr>';
                echo '<td><strong>Status</strong></td>';
                echo '<td>' . $resultado['status'] . '</td>';
                echo '<td>' . ($resultado['status'] === 'active' ? '✅' : '❌') . '</td>';
                echo '</tr>';
                
                echo '<tr>';
                echo '<td><strong>Pode Aprovar POPs/ITs</strong></td>';
                echo '<td>' . $resultado['pode_aprovar_pops_its'] . '</td>';
                echo '<td>' . ($resultado['pode_aprovar_pops_its'] == 1 ? '✅' : '❌') . '</td>';
                echo '</tr>';
                
                echo '</table>';
                
                // Diagnóstico final
                if ($resultado['pode_aprovar_pops_its'] == 1 && 
                    $resultado['role'] === 'admin' && 
                    $resultado['status'] === 'active') {
                    echo '<div class="success">';
                    echo '<h3>🎉 CONFIGURAÇÃO COMPLETA!</h3>';
                    echo '<p>Rafael Camargo agora receberá emails quando houver POPs/ITs pendentes de aprovação.</p>';
                    echo '</div>';
                } else {
                    echo '<div class="error">';
                    echo '<h3>⚠️ CONFIGURAÇÃO INCOMPLETA</h3>';
                    echo '<p>Verifique os itens marcados com ❌ acima.</p>';
                    echo '</div>';
                }
            }
            echo '</div>';
            
            // PASSO 6: Listar todos os admins
            echo '<div class="step"><h3>👥 PASSO 6: Todos os Administradores</h3>';
            
            $stmt = $db->query("
                SELECT 
                    name,
                    email,
                    role,
                    status,
                    pode_aprovar_pops_its
                FROM users 
                WHERE role = 'admin'
                ORDER BY pode_aprovar_pops_its DESC, name
            ");
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<table>';
            echo '<tr><th>Nome</th><th>Email</th><th>Status</th><th>Notificações POPs/ITs</th></tr>';
            
            foreach ($admins as $admin) {
                echo '<tr>';
                echo '<td>' . $admin['name'] . '</td>';
                echo '<td>' . $admin['email'] . '</td>';
                echo '<td>' . $admin['status'] . '</td>';
                echo '<td>';
                if ($admin['pode_aprovar_pops_its'] == 1) {
                    echo '<span style="color: green;">✅ Ativado</span>';
                } else {
                    echo '<span style="color: red;">❌ Desativado</span>';
                }
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<h3>❌ ERRO GERAL</h3>';
            echo '<p><strong>Mensagem:</strong> ' . $e->getMessage() . '</p>';
            echo '<p><strong>Arquivo:</strong> ' . $e->getFile() . '</p>';
            echo '<p><strong>Linha:</strong> ' . $e->getLine() . '</p>';
            echo '</div>';
        }
        ?>
        
        <div class="warning" style="margin-top: 30px;">
            <h3>⚠️ IMPORTANTE</h3>
            <p><strong>DELETE este arquivo após usar!</strong></p>
            <p>Ele não deve ficar acessível publicamente por questões de segurança.</p>
            <code>rm ativar_notificacoes_rafael.php</code>
        </div>
        
        <div class="info">
            <h3>📝 Próximos Passos</h3>
            <ol>
                <li>Teste criando um novo POP/IT no sistema</li>
                <li>Verifique se Rafael recebe o email</li>
                <li>Confira os logs em <code>storage/logs/error.log</code></li>
            </ol>
        </div>
    </div>
</body>
</html>
