<?php
/**
 * TESTE SIMPLES - Sistema de Notificações
 * Acesse: https://djbr.sgqoti.com.br/TESTE_SIMPLES_NOTIFICACAO.php
 */

session_start();

// Carregar credenciais do .env
$envFile = __DIR__ . '/.env';
$dbConfig = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        
        if ($name == 'DB_HOST') $dbConfig['host'] = $value;
        if ($name == 'DB_PORT') $dbConfig['port'] = $value;
        if ($name == 'DB_DATABASE') $dbConfig['database'] = $value;
        if ($name == 'DB_USERNAME') $dbConfig['username'] = $value;
        if ($name == 'DB_PASSWORD') $dbConfig['password'] = $value;
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Rápido - Notificações</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 { 
            text-align: center; 
            font-size: 32px;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .card { 
            background: rgba(255,255,255,0.95); 
            padding: 25px; 
            border-radius: 12px; 
            margin: 20px 0; 
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            color: #333;
        }
        .card h2 { 
            color: #667eea; 
            margin-top: 0;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .status-ok { color: #10b981; font-weight: bold; }
        .status-error { color: #ef4444; font-weight: bold; }
        .status-warning { color: #f59e0b; font-weight: bold; }
        .info-row { 
            padding: 10px;
            margin: 8px 0;
            background: #f9fafb;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
        }
        .label { color: #6b7280; }
        .value { font-weight: bold; color: #111827; }
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            margin: 5px;
            transition: all 0.3s;
        }
        button:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-danger {
            background: #ef4444;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        pre {
            background: #1f2937;
            color: #10b981;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
        }
        .big-status {
            text-align: center;
            font-size: 48px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico Rápido - Notificações</h1>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="card">
                <h2>❌ Não Autenticado</h2>
                <p>Você precisa estar logado para ver este diagnóstico.</p>
                <button onclick="window.location.href='/login'">Fazer Login</button>
            </div>
        <?php else: ?>
            
            <!-- USUÁRIO -->
            <div class="card">
                <h2>👤 Usuário Logado</h2>
                <div class="info-row">
                    <span class="label">Nome:</span>
                    <span class="value"><?= htmlspecialchars($_SESSION['user_name'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value"><?= htmlspecialchars($_SESSION['user_email'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="label">ID:</span>
                    <span class="value"><?= htmlspecialchars($_SESSION['user_id']) ?></span>
                </div>
            </div>
            
            <!-- SESSÃO -->
            <div class="card">
                <h2>🔔 Status na Sessão</h2>
                
                <?php if (isset($_SESSION['notificacoes_ativadas'])): ?>
                    <div class="big-status">
                        <?= $_SESSION['notificacoes_ativadas'] ? '✅ ATIVADO' : '🔕 DESATIVADO' ?>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Valor:</span>
                        <span class="value"><?= var_export($_SESSION['notificacoes_ativadas'], true) ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Tipo:</span>
                        <span class="value"><?= gettype($_SESSION['notificacoes_ativadas']) ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="label">Sino na sidebar:</span>
                        <span class="value <?= $_SESSION['notificacoes_ativadas'] ? 'status-ok' : 'status-error' ?>">
                            <?= $_SESSION['notificacoes_ativadas'] ? 'DEVE APARECER' : 'NÃO DEVE APARECER' ?>
                        </span>
                    </div>
                <?php else: ?>
                    <p class="status-warning">⚠️ Variável não definida na sessão!</p>
                    <p>Faça logout e login novamente.</p>
                <?php endif; ?>
            </div>
            
            <!-- BANCO DE DADOS -->
            <div class="card">
                <h2>💾 Status no Banco de Dados</h2>
                
                <?php
                try {
                    // Conectar ao banco
                    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset=utf8mb4";
                    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]);
                    
                    echo '<p class="status-ok">✅ Conexão com banco OK</p>';
                    
                    // Verificar coluna
                    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'notificacoes_ativadas'");
                    $columnExists = $stmt->rowCount() > 0;
                    
                    if ($columnExists) {
                        echo '<p class="status-ok">✅ Coluna existe no banco</p>';
                        
                        // Buscar valor do usuário
                        $stmt = $pdo->prepare("SELECT notificacoes_ativadas FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($user) {
                            $dbValue = (int)$user['notificacoes_ativadas'];
                            $sessionValue = isset($_SESSION['notificacoes_ativadas']) ? (int)$_SESSION['notificacoes_ativadas'] : 1;
                            
                            echo '<div class="info-row">';
                            echo '<span class="label">Valor no banco:</span>';
                            echo '<span class="value">' . ($dbValue == 1 ? '🔔 ATIVADO (1)' : '🔕 DESATIVADO (0)') . '</span>';
                            echo '</div>';
                            
                            if ($dbValue === $sessionValue) {
                                echo '<p class="status-ok">✅ Sessão SINCRONIZADA com o banco!</p>';
                            } else {
                                echo '<p class="status-warning">⚠️ ATENÇÃO: Sessão DIFERENTE do banco!</p>';
                                echo '<div class="info-row">';
                                echo '<span class="label">Sessão:</span>';
                                echo '<span class="value">' . $sessionValue . '</span>';
                                echo '</div>';
                                echo '<div class="info-row">';
                                echo '<span class="label">Banco:</span>';
                                echo '<span class="value">' . $dbValue . '</span>';
                                echo '</div>';
                                echo '<p><strong>Solução:</strong> Faça logout e login para sincronizar.</p>';
                            }
                        }
                    } else {
                        echo '<p class="status-error">❌ Coluna NÃO existe!</p>';
                        echo '<p>Execute esta migration no phpMyAdmin:</p>';
                        echo '<pre>ALTER TABLE users 
ADD COLUMN notificacoes_ativadas TINYINT(1) NOT NULL DEFAULT 1 
COMMENT \'1 = Notificações ativadas, 0 = Notificações desativadas\' 
AFTER status;</pre>';
                    }
                    
                } catch (PDOException $e) {
                    echo '<p class="status-error">❌ Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
            </div>
            
            <!-- AÇÕES -->
            <div class="card" style="text-align: center;">
                <h2>🔧 Ações</h2>
                <button onclick="window.location.href='/profile'">📝 Ir para Perfil</button>
                <button onclick="window.location.href='/logout'" class="btn-danger">🚪 Fazer Logout</button>
                <button onclick="window.location.reload()">🔄 Recarregar</button>
            </div>
            
        <?php endif; ?>
        
        <p style="text-align: center; margin-top: 30px; opacity: 0.8;">
            SGQ OTI DJ - v2.6.2 - <?= date('d/m/Y H:i:s') ?>
        </p>
    </div>
</body>
</html>
