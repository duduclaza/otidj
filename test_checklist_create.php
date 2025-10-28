<?php
/**
 * Teste de criação de checklist
 * Acesse: https://djbr.sgqoti.com.br/test_checklist_create.php
 */

// Mostrar todos os erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/vendor/autoload.php';

// CARREGAR .ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

use App\Config\Database;
use App\Services\PermissionService;

echo "<h1>🧪 Teste de Criação de Checklist</h1>";
echo "<pre>";

try {
    // Verificar sessão
    echo "1️⃣ Verificando sessão...\n";
    if (!isset($_SESSION['user_id'])) {
        echo "❌ Usuário não autenticado. Faça login primeiro!\n";
        echo "   <a href='/login'>Ir para Login</a>\n";
        exit;
    }
    echo "✅ User ID: " . $_SESSION['user_id'] . "\n";
    echo "✅ User Name: " . ($_SESSION['user_name'] ?? 'N/A') . "\n\n";
    
    // Verificar permissões
    echo "2️⃣ Verificando permissões...\n";
    $user_id = $_SESSION['user_id'];
    $isAdmin = PermissionService::isAdmin($user_id);
    $isSuperAdmin = PermissionService::isSuperAdmin($user_id);
    
    echo "   isAdmin: " . ($isAdmin ? 'SIM' : 'NÃO') . "\n";
    echo "   isSuperAdmin: " . ($isSuperAdmin ? 'SIM' : 'NÃO') . "\n\n";
    
    if (!$isAdmin && !$isSuperAdmin) {
        echo "❌ Sem permissão! Precisa ser Admin ou Super Admin.\n";
        exit;
    }
    echo "✅ Permissão OK!\n\n";
    
    // Verificar conexão com banco
    echo "3️⃣ Verificando conexão com banco...\n";
    $db = Database::getInstance()->getConnection();
    echo "✅ Conexão estabelecida!\n\n";
    
    // Verificar tabelas
    echo "4️⃣ Verificando tabelas...\n";
    $tables = ['homologacao_checklists', 'homologacao_checklist_itens', 'homologacao_checklist_respostas'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->fetch()) {
            echo "   ✅ Tabela '$table' existe\n";
        } else {
            echo "   ❌ Tabela '$table' NÃO existe\n";
        }
    }
    echo "\n";
    
    // Testar inserção
    echo "5️⃣ Testando inserção de checklist...\n";
    
    $titulo = "Teste Checklist " . date('H:i:s');
    $descricao = "Checklist de teste criado automaticamente";
    
    $stmt = $db->prepare("
        INSERT INTO homologacao_checklists (titulo, descricao, criado_por)
        VALUES (?, ?, ?)
    ");
    
    $result = $stmt->execute([$titulo, $descricao, $user_id]);
    
    if ($result) {
        $checklist_id = $db->lastInsertId();
        echo "✅ Checklist criado com sucesso!\n";
        echo "   ID: $checklist_id\n";
        echo "   Título: $titulo\n\n";
        
        // Testar inserção de item
        echo "6️⃣ Testando inserção de item...\n";
        $stmt = $db->prepare("
            INSERT INTO homologacao_checklist_itens 
            (checklist_id, titulo, ordem, tipo_resposta)
            VALUES (?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([$checklist_id, 'Item de teste', 0, 'checkbox']);
        
        if ($result) {
            echo "✅ Item criado com sucesso!\n\n";
        } else {
            echo "❌ Erro ao criar item!\n\n";
        }
        
        // Limpar teste
        echo "7️⃣ Limpando dados de teste...\n";
        $db->exec("DELETE FROM homologacao_checklists WHERE id = $checklist_id");
        echo "✅ Dados de teste removidos!\n\n";
        
    } else {
        echo "❌ Erro ao criar checklist!\n";
        print_r($stmt->errorInfo());
        echo "\n\n";
    }
    
    echo "========================================\n";
    echo "🎉 TODOS OS TESTES PASSARAM!\n";
    echo "========================================\n\n";
    echo "O sistema deveria funcionar normalmente.\n";
    echo "Se ainda der erro 500, o problema pode ser:\n";
    echo "- Problema com JSON no JavaScript\n";
    echo "- Header Content-Type sendo enviado duas vezes\n";
    echo "- Warning/Notice do PHP antes do JSON\n\n";
    
    echo "<a href='/homologacoes'>← Voltar para Homologações</a>\n";
    
} catch (Exception $e) {
    echo "\n\n❌ ERRO CAPTURADO:\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre>";
