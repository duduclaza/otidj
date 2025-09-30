<?php
// Script de diagnóstico para logs de visualização POPs e ITs
session_start();

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

use App\Config\Database;

echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnóstico - Logs POPs e ITs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .success { color: #388e3c; }
        .error { color: #d32f2f; }
        .warning { color: #f57c00; }
        .info { color: #1976d2; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { background: #1976d2; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #1565c0; }
    </style>
</head>
<body>";

echo "<div class='container'>";
echo "<h1>🔍 Diagnóstico - Sistema de Logs POPs e ITs</h1>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

try {
    // Conectar ao banco
    $db = Database::getInstance();
    echo "<p class='success'>✅ Conexão com banco de dados OK</p>";
    
    // 1. Verificar se a tabela existe
    echo "<h2>1. Verificação da Tabela</h2>";
    $stmt = $db->query("SHOW TABLES LIKE 'pops_its_logs_visualizacao'");
    $tabelaExiste = $stmt->fetch() !== false;
    
    if ($tabelaExiste) {
        echo "<p class='success'>✅ Tabela 'pops_its_logs_visualizacao' existe</p>";
        
        // Verificar estrutura da tabela
        $stmt = $db->query("DESCRIBE pops_its_logs_visualizacao");
        $estrutura = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Estrutura da Tabela:</h3>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($estrutura as $campo) {
            echo "<tr>";
            echo "<td>{$campo['Field']}</td>";
            echo "<td>{$campo['Type']}</td>";
            echo "<td>{$campo['Null']}</td>";
            echo "<td>{$campo['Key']}</td>";
            echo "<td>{$campo['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p class='error'>❌ Tabela 'pops_its_logs_visualizacao' NÃO existe</p>";
        echo "<p class='warning'>⚠️ Tentando criar a tabela...</p>";
        
        $sql = "
            CREATE TABLE IF NOT EXISTS pops_its_logs_visualizacao (
                id INT AUTO_INCREMENT PRIMARY KEY,
                registro_id INT NOT NULL,
                usuario_id INT NOT NULL,
                visualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                user_agent TEXT NULL,
                FOREIGN KEY (registro_id) REFERENCES pops_its_registros(id) ON DELETE CASCADE,
                FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_registro_id (registro_id),
                INDEX idx_usuario_id (usuario_id),
                INDEX idx_visualizado_em (visualizado_em)
            )
        ";
        
        try {
            $db->exec($sql);
            echo "<p class='success'>✅ Tabela criada com sucesso!</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro ao criar tabela: " . $e->getMessage() . "</p>";
        }
    }
    
    // 2. Contar registros
    echo "<h2>2. Contagem de Registros</h2>";
    try {
        $stmt = $db->query("SELECT COUNT(*) as total FROM pops_its_logs_visualizacao");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalLogs = $result['total'];
        echo "<p class='info'>📊 Total de logs: <strong>{$totalLogs}</strong></p>";
        
        if ($totalLogs > 0) {
            // Mostrar últimos 10 logs
            echo "<h3>Últimos 10 Logs:</h3>";
            $stmt = $db->query("
                SELECT 
                    l.id,
                    l.visualizado_em,
                    u.name as usuario_nome,
                    u.email as usuario_email,
                    r.nome_arquivo,
                    r.versao,
                    t.titulo,
                    t.tipo
                FROM pops_its_logs_visualizacao l
                LEFT JOIN users u ON l.usuario_id = u.id
                LEFT JOIN pops_its_registros r ON l.registro_id = r.id
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                ORDER BY l.visualizado_em DESC 
                LIMIT 10
            ");
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($logs)) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Data/Hora</th><th>Usuário</th><th>Arquivo</th><th>Título</th><th>Tipo</th></tr>";
                foreach ($logs as $log) {
                    echo "<tr>";
                    echo "<td>{$log['id']}</td>";
                    echo "<td>" . date('d/m/Y H:i:s', strtotime($log['visualizado_em'])) . "</td>";
                    echo "<td>{$log['usuario_nome']} ({$log['usuario_email']})</td>";
                    echo "<td>{$log['nome_arquivo']} v{$log['versao']}</td>";
                    echo "<td>{$log['titulo']}</td>";
                    echo "<td>{$log['tipo']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro ao contar registros: " . $e->getMessage() . "</p>";
    }
    
    // 3. Verificar tabelas relacionadas
    echo "<h2>3. Verificação de Tabelas Relacionadas</h2>";
    
    $tabelas = ['pops_its_registros', 'pops_its_titulos', 'users'];
    foreach ($tabelas as $tabela) {
        $stmt = $db->query("SHOW TABLES LIKE '{$tabela}'");
        $existe = $stmt->fetch() !== false;
        
        if ($existe) {
            $stmt = $db->query("SELECT COUNT(*) as total FROM {$tabela}");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total = $result['total'];
            echo "<p class='success'>✅ Tabela '{$tabela}': {$total} registros</p>";
        } else {
            echo "<p class='error'>❌ Tabela '{$tabela}' não existe</p>";
        }
    }
    
    // 4. Testar endpoint da API
    echo "<h2>4. Teste do Endpoint da API</h2>";
    echo "<p class='info'>🔗 Endpoint: /pops-its/logs/visualizacao</p>";
    
    // Simular uma sessão de admin para teste
    if (!isset($_SESSION['user_id'])) {
        echo "<p class='warning'>⚠️ Usuário não logado - simulando admin para teste</p>";
        // Buscar um usuário admin para simular
        $stmt = $db->query("
            SELECT u.id 
            FROM users u 
            JOIN profiles p ON u.profile_id = p.id 
            WHERE p.name = 'Administrador' 
            LIMIT 1
        ");
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            $_SESSION['user_id'] = $admin['id'];
            echo "<p class='info'>👤 Simulando usuário admin ID: {$admin['id']}</p>";
        }
    }
    
    // 5. Inserir log de teste
    echo "<h2>5. Teste de Inserção de Log</h2>";
    if (isset($_SESSION['user_id'])) {
        // Buscar um registro para teste
        $stmt = $db->query("SELECT id FROM pops_its_registros WHERE status = 'APROVADO' LIMIT 1");
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($registro) {
            try {
                $stmt = $db->prepare("
                    INSERT INTO pops_its_logs_visualizacao 
                    (registro_id, usuario_id, user_agent, visualizado_em) 
                    VALUES (?, ?, ?, NOW())
                ");
                $result = $stmt->execute([
                    $registro['id'], 
                    $_SESSION['user_id'], 
                    'Debug Script - ' . date('Y-m-d H:i:s')
                ]);
                
                if ($result) {
                    echo "<p class='success'>✅ Log de teste inserido com sucesso!</p>";
                    echo "<p class='info'>📝 Registro ID: {$registro['id']}, Usuário ID: {$_SESSION['user_id']}</p>";
                } else {
                    echo "<p class='error'>❌ Falha ao inserir log de teste</p>";
                }
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Erro ao inserir log: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='warning'>⚠️ Nenhum registro aprovado encontrado para teste</p>";
        }
    } else {
        echo "<p class='warning'>⚠️ Não foi possível simular usuário para teste</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro geral: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</div>";

// Botões de ação
echo "<div class='container'>";
echo "<h2>🔧 Ações de Diagnóstico</h2>";
echo "<button class='btn' onclick='window.location.reload()'>🔄 Recarregar Diagnóstico</button>";
echo "<button class='btn' onclick='testarAPI()'>🧪 Testar API</button>";
echo "<button class='btn' onclick='criarTabela()'>🏗️ Recriar Tabela</button>";
echo "</div>";

echo "<script>
async function testarAPI() {
    try {
        const response = await fetch('/pops-its/teste-logs');
        const result = await response.json();
        alert('Resultado do teste:\\n' + JSON.stringify(result, null, 2));
    } catch (error) {
        alert('Erro ao testar API: ' + error.message);
    }
}

async function criarTabela() {
    if (confirm('Deseja recriar a tabela de logs? Isso apagará todos os logs existentes!')) {
        // Implementar se necessário
        alert('Funcionalidade não implementada neste diagnóstico');
    }
}
</script>";

echo "</body></html>";
?>
