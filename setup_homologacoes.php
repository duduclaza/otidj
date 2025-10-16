<?php
/**
 * SETUP DO MÓDULO HOMOLOGAÇÕES
 * 
 * Script de instalação simplificado que executa a migration
 * de forma segura e robusta.
 * 
 * INSTRUÇÕES:
 * 1. Acesse via navegador: http://seu-site.com/setup_homologacoes.php
 * 2. Ou execute via terminal: php setup_homologacoes.php
 * 3. Após instalação bem-sucedida, delete este arquivo por segurança
 */

// Carrega configurações
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Configurar output
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Estilo simples
echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Homologações - SGQ OTI DJ</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2563eb;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
        }
        .step {
            background: #f9fafb;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #60a5fa;
            border-radius: 4px;
        }
        .success {
            background: #dcfce7;
            border-left-color: #22c55e;
            color: #166534;
        }
        .error {
            background: #fee2e2;
            border-left-color: #ef4444;
            color: #991b1b;
        }
        .warning {
            background: #fef3c7;
            border-left-color: #f59e0b;
            color: #92400e;
        }
        .info {
            background: #dbeafe;
            border-left-color: #3b82f6;
            color: #1e40af;
        }
        pre {
            background: #1f2937;
            color: #f3f4f6;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 13px;
        }
        .button {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 20px;
        }
        .button:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Setup Módulo Homologações</h1>
        <p class="subtitle">Sistema de Gestão da Qualidade - OTI DJ</p>
';

try {
    // Conectar ao banco
    echo '<div class="step">📡 <strong>Conectando ao banco de dados...</strong></div>';
    
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? '';
    $username = $_ENV['DB_USER'] ?? '';
    $password = $_ENV['DB_PASS'] ?? '';
    
    if (empty($dbname) || empty($username)) {
        throw new Exception('Credenciais do banco não configuradas no .env');
    }
    
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo '<div class="step success">✅ <strong>Conectado com sucesso!</strong></div>';
    
    // Ler arquivos de migration (2 partes)
    echo '<div class="step">📄 <strong>Carregando arquivos de migration...</strong></div>';
    
    $migrationFile1 = __DIR__ . '/database/migrations/01_create_homologacoes_tables.sql';
    $migrationFile2 = __DIR__ . '/database/migrations/02_add_homologacoes_permissions.sql';
    
    if (!file_exists($migrationFile1)) {
        throw new Exception('Arquivo 01_create_homologacoes_tables.sql não encontrado');
    }
    
    if (!file_exists($migrationFile2)) {
        throw new Exception('Arquivo 02_add_homologacoes_permissions.sql não encontrado');
    }
    
    $sql1 = file_get_contents($migrationFile1);
    $sql2 = file_get_contents($migrationFile2);
    
    if (empty($sql1) || empty($sql2)) {
        throw new Exception('Um dos arquivos de migration está vazio');
    }
    
    echo '<div class="step success">✅ <strong>Arquivos carregados!</strong><br>';
    echo '- Parte 1 (Tabelas): ' . strlen($sql1) . ' bytes<br>';
    echo '- Parte 2 (Permissões): ' . strlen($sql2) . ' bytes</div>';
    
    // Executar migrations em sequência
    echo '<div class="step">⚙️ <strong>Executando migrations...</strong></div>';
    
    $totalExecuted = 0;
    $allErrors = [];
    
    // PARTE 1: Criar Tabelas
    echo '<div class="step info">📋 <strong>Parte 1/2:</strong> Criando tabelas...</div>';
    
    $sql1 = preg_replace('/--.*$/m', '', $sql1);
    $statements1 = array_filter(
        array_map('trim', explode(';', $sql1)),
        function($stmt) { return !empty($stmt); }
    );
    
    $executed1 = 0;
    foreach ($statements1 as $statement) {
        try {
            if (empty(trim($statement))) continue;
            $pdo->exec($statement);
            $executed1++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate') === false) {
                $allErrors[] = ['part' => '1', 'error' => $e->getMessage()];
            }
        }
    }
    
    echo '<div class="step success">✅ <strong>Parte 1 concluída:</strong> ' . $executed1 . ' statements</div>';
    
    // PARTE 2: Adicionar Permissões
    echo '<div class="step info">🔒 <strong>Parte 2/2:</strong> Configurando permissões...</div>';
    
    $sql2 = preg_replace('/--.*$/m', '', $sql2);
    $statements2 = array_filter(
        array_map('trim', explode(';', $sql2)),
        function($stmt) { return !empty($stmt); }
    );
    
    $executed2 = 0;
    foreach ($statements2 as $statement) {
        try {
            if (empty(trim($statement))) continue;
            $pdo->exec($statement);
            $executed2++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate') === false && 
                strpos($e->getMessage(), 'Unknown table') === false) {
                $allErrors[] = ['part' => '2', 'error' => $e->getMessage()];
            }
        }
    }
    
    echo '<div class="step success">✅ <strong>Parte 2 concluída:</strong> ' . $executed2 . ' statements</div>';
    
    $totalExecuted = $executed1 + $executed2;
    
    echo '<div class="step success">🎉 <strong>Total executado:</strong> ' . $totalExecuted . ' statements</div>';
    
    if (!empty($allErrors)) {
        echo '<div class="step warning">⚠️ <strong>Avisos durante a execução:</strong>';
        foreach ($allErrors as $error) {
            echo '<br><small>[Parte ' . $error['part'] . '] ' . htmlspecialchars($error['error']) . '</small>';
        }
        echo '</div>';
    }
    
    // Verificar tabelas criadas
    echo '<div class="step">🔍 <strong>Verificando estrutura criada...</strong></div>';
    
    $tables = ['homologacoes', 'homologacoes_responsaveis', 'homologacoes_historico', 'homologacoes_anexos'];
    $tablesFound = [];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $tablesFound[] = $table;
        }
    }
    
    echo '<div class="step ' . (count($tablesFound) === 4 ? 'success' : 'warning') . '">';
    echo '<strong>Tabelas encontradas: ' . count($tablesFound) . '/4</strong><br>';
    foreach ($tablesFound as $table) {
        echo '✅ ' . $table . '<br>';
    }
    echo '</div>';
    
    // Verificar permissões
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM profile_permissions WHERE module = 'homologacoes'");
    $permissions = $stmt->fetch();
    
    echo '<div class="step ' . ($permissions['total'] > 0 ? 'success' : 'warning') . '">';
    echo '<strong>Permissões configuradas: ' . $permissions['total'] . ' perfis</strong>';
    echo '</div>';
    
    // Verificar coluna department
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'department'");
    $hasDepartment = $stmt->rowCount() > 0;
    
    echo '<div class="step ' . ($hasDepartment ? 'success' : 'warning') . '">';
    echo $hasDepartment 
        ? '✅ <strong>Coluna department existe na tabela users</strong>'
        : '⚠️ <strong>Coluna department não encontrada</strong>';
    echo '</div>';
    
    // Resumo final
    echo '<div class="step info">';
    echo '<strong>📋 Próximos passos:</strong><br><br>';
    echo '1. ✅ Acesse o módulo: <a href="/homologacoes" style="color: #2563eb;">/homologacoes</a><br>';
    echo '2. 👥 Configure os departamentos dos usuários (Compras, Logistica)<br>';
    echo '3. 🔒 Configure permissões adicionais via Admin > Gerenciar Perfis<br>';
    echo '4. 🗑️ <strong>Delete este arquivo (setup_homologacoes.php) por segurança</strong><br>';
    echo '</div>';
    
    echo '<div class="step success">';
    echo '<strong>🎉 Instalação concluída com sucesso!</strong>';
    echo '</div>';
    
    echo '<a href="/homologacoes" class="button">Acessar Módulo Homologações</a>';
    
} catch (Exception $e) {
    echo '<div class="step error">';
    echo '<strong>❌ Erro durante a instalação:</strong><br>';
    echo htmlspecialchars($e->getMessage());
    echo '</div>';
    
    echo '<div class="step info">';
    echo '<strong>💡 Solução alternativa:</strong><br>';
    echo 'Execute manualmente no phpMyAdmin o arquivo:<br>';
    echo '<code>database/migrations/create_homologacoes_module.sql</code>';
    echo '</div>';
}

echo '
    </div>
</body>
</html>';
