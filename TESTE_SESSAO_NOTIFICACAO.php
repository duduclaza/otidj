<?php
/**
 * TESTE DE DIAGNÓSTICO - Sistema de Notificações
 * Acesse: https://djbr.sgqoti.com.br/TESTE_SESSAO_NOTIFICACAO.php
 */

session_start();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - Notificações</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e293b; color: #e2e8f0; }
        .box { background: #334155; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #3b82f6; }
        .success { border-left-color: #10b981; }
        .error { border-left-color: #ef4444; }
        .warning { border-left-color: #f59e0b; }
        h1 { color: #60a5fa; }
        h2 { color: #93c5fd; margin-top: 0; }
        code { background: #1e293b; padding: 2px 6px; border-radius: 4px; color: #fbbf24; }
        .value { color: #34d399; font-weight: bold; }
        .label { color: #94a3b8; }
        button { 
            background: #3b82f6; 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 14px;
            margin: 5px;
        }
        button:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico - Sistema de Notificações</h1>
    
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="box error">
            <h2>❌ Não Autenticado</h2>
            <p>Você precisa estar logado no sistema para ver este diagnóstico.</p>
            <button onclick="window.location.href='/login'">Fazer Login</button>
        </div>
    <?php else: ?>
        
        <!-- INFORMAÇÕES DO USUÁRIO -->
        <div class="box success">
            <h2>👤 Informações do Usuário</h2>
            <p><span class="label">ID:</span> <span class="value"><?= $_SESSION['user_id'] ?></span></p>
            <p><span class="label">Nome:</span> <span class="value"><?= $_SESSION['user_name'] ?? 'N/A' ?></span></p>
            <p><span class="label">Email:</span> <span class="value"><?= $_SESSION['user_email'] ?? 'N/A' ?></span></p>
            <p><span class="label">Role:</span> <span class="value"><?= $_SESSION['user_role'] ?? 'N/A' ?></span></p>
        </div>
        
        <!-- STATUS DA SESSÃO -->
        <div class="box <?= isset($_SESSION['notificacoes_ativadas']) ? 'success' : 'warning' ?>">
            <h2>🔔 Status das Notificações na Sessão</h2>
            
            <?php if (isset($_SESSION['notificacoes_ativadas'])): ?>
                <p><span class="label">Valor na sessão:</span> 
                    <code class="value">
                        <?= var_export($_SESSION['notificacoes_ativadas'], true) ?>
                    </code>
                </p>
                
                <p><span class="label">Tipo do valor:</span> 
                    <code class="value"><?= gettype($_SESSION['notificacoes_ativadas']) ?></code>
                </p>
                
                <p><span class="label">Sino deve aparecer?</span> 
                    <span class="value" style="font-size: 24px;">
                        <?= $_SESSION['notificacoes_ativadas'] ? '✅ SIM' : '❌ NÃO' ?>
                    </span>
                </p>
                
                <?php if ($_SESSION['notificacoes_ativadas']): ?>
                    <p style="color: #10b981;">✅ Notificações ATIVADAS - Sino deve aparecer na sidebar</p>
                <?php else: ?>
                    <p style="color: #ef4444;">🔕 Notificações DESATIVADAS - Sino NÃO deve aparecer</p>
                <?php endif; ?>
            <?php else: ?>
                <p style="color: #f59e0b;">⚠️ Variável <code>notificacoes_ativadas</code> NÃO está definida na sessão!</p>
                <p>Isso significa que você fez login antes da funcionalidade ser implementada.</p>
                <p><strong>Solução:</strong> Faça logout e login novamente.</p>
            <?php endif; ?>
        </div>
        
        <!-- STATUS NO BANCO DE DADOS -->
        <div class="box">
            <h2>💾 Status no Banco de Dados</h2>
            <?php
            try {
                require_once __DIR__ . '/vendor/autoload.php';
                $db = App\Config\Database::getInstance();
                
                // Verificar se coluna existe
                $checkColumn = $db->query("SHOW COLUMNS FROM users LIKE 'notificacoes_ativadas'");
                $columnExists = $checkColumn->rowCount() > 0;
                
                if (!$columnExists):
            ?>
                <p style="color: #ef4444;">❌ Coluna <code>notificacoes_ativadas</code> NÃO existe no banco!</p>
                <p>Execute a migration primeiro:</p>
                <pre style="background: #1e293b; padding: 10px; border-radius: 4px; overflow-x: auto;">ALTER TABLE users 
ADD COLUMN notificacoes_ativadas TINYINT(1) NOT NULL DEFAULT 1 
COMMENT '1 = Notificações ativadas, 0 = Notificações desativadas' 
AFTER status;</pre>
            <?php else: ?>
                <p style="color: #10b981;">✅ Coluna existe no banco de dados</p>
                
                <?php
                // Buscar valor do usuário logado
                $stmt = $db->prepare("SELECT id, name, email, notificacoes_ativadas FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userData):
                ?>
                    <p><span class="label">Valor no banco:</span> 
                        <code class="value"><?= $userData['notificacoes_ativadas'] ?></code>
                        <?= $userData['notificacoes_ativadas'] == 1 ? '(🔔 ATIVADO)' : '(🔕 DESATIVADO)' ?>
                    </p>
                    
                    <?php
                    // Comparar sessão com banco
                    $sessaoAtivada = isset($_SESSION['notificacoes_ativadas']) ? $_SESSION['notificacoes_ativadas'] : true;
                    $bancoAtivado = (bool)$userData['notificacoes_ativadas'];
                    
                    if ($sessaoAtivada === $bancoAtivado):
                    ?>
                        <p style="color: #10b981;">✅ Sessão está SINCRONIZADA com o banco</p>
                    <?php else: ?>
                        <p style="color: #f59e0b;">⚠️ ATENÇÃO: Sessão está DIFERENTE do banco!</p>
                        <p><span class="label">Sessão:</span> <code><?= var_export($sessaoAtivada, true) ?></code></p>
                        <p><span class="label">Banco:</span> <code><?= var_export($bancoAtivado, true) ?></code></p>
                        <p><strong>Solução:</strong> Faça logout e login novamente para sincronizar.</p>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
            <?php
            } catch (Exception $e) {
                echo '<p style="color: #ef4444;">❌ Erro ao consultar banco: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>
        
        <!-- TODAS AS VARIÁVEIS DA SESSÃO -->
        <div class="box">
            <h2>📋 Todas as Variáveis da Sessão</h2>
            <pre style="background: #1e293b; padding: 10px; border-radius: 4px; overflow-x: auto;"><?php print_r($_SESSION); ?></pre>
        </div>
        
        <!-- AÇÕES -->
        <div class="box">
            <h2>🔧 Ações Rápidas</h2>
            <button onclick="window.location.href='/profile'">📝 Ir para Perfil</button>
            <button onclick="window.location.href='/logout'" class="btn-danger">🚪 Fazer Logout</button>
            <button onclick="window.location.reload()">🔄 Recarregar Página</button>
        </div>
        
    <?php endif; ?>
    
    <div class="box">
        <h2>📖 Instruções</h2>
        <ol style="line-height: 1.8;">
            <li>Se a sessão está diferente do banco → <strong>Faça logout e login</strong></li>
            <li>Se a coluna não existe → <strong>Execute a migration</strong></li>
            <li>Se tudo está correto mas sino não some → <strong>Limpe o cache (Ctrl+Shift+Delete)</strong></li>
            <li>Após qualquer mudança → <strong>Faça logout/login para renovar sessão</strong></li>
        </ol>
    </div>
    
    <p style="text-align: center; color: #64748b; margin-top: 40px;">
        Sistema SGQ OTI DJ - v2.6.2 - <?= date('d/m/Y H:i:s') ?>
    </p>
</body>
</html>
