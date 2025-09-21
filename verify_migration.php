<?php
/**
 * Script para verificar se a migration 9 foi executada corretamente
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "🔍 Verificando status das migrations...\n\n";

try {
    $db = Database::getInstance();
    
    // Verificar versão atual das migrations
    $stmt = $db->query('SELECT MAX(version) as version FROM migrations');
    $result = $stmt->fetch();
    $currentVersion = (int)($result['version'] ?? 0);
    
    echo "📊 Versão atual das migrations: {$currentVersion}\n";
    
    if ($currentVersion >= 9) {
        echo "✅ Migration 9 executada com sucesso!\n\n";
        
        // Verificar se as tabelas foram criadas
        $tables = [
            'solicitacoes_melhorias',
            'solicitacoes_melhorias_responsaveis', 
            'solicitacoes_melhorias_anexos'
        ];
        
        echo "🔍 Verificando tabelas criadas:\n";
        foreach ($tables as $table) {
            $stmt = $db->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->fetch()) {
                echo "   ✅ {$table}\n";
            } else {
                echo "   ❌ {$table} - NÃO ENCONTRADA\n";
            }
        }
        
        // Verificar permissões do módulo solicitacao_melhorias
        echo "\n🔍 Verificando permissões do módulo 'solicitacao_melhorias':\n";
        $stmt = $db->query("
            SELECT p.name as profile_name, pp.can_view, pp.can_edit, pp.can_delete 
            FROM profiles p 
            JOIN profile_permissions pp ON p.id = pp.profile_id 
            WHERE pp.module = 'solicitacao_melhorias'
            ORDER BY p.name
        ");
        
        $permissions = $stmt->fetchAll();
        if ($permissions) {
            foreach ($permissions as $perm) {
                $view = $perm['can_view'] ? '✅' : '❌';
                $edit = $perm['can_edit'] ? '✅' : '❌';
                $delete = $perm['can_delete'] ? '✅' : '❌';
                echo "   {$perm['profile_name']}: View {$view} | Edit {$edit} | Delete {$delete}\n";
            }
        } else {
            echo "   ⚠️  Nenhuma permissão encontrada para o módulo\n";
        }
        
        echo "\n🎯 Sistema pronto para usar o módulo de Solicitação de Melhorias!\n";
        
    } else {
        echo "⚠️  Migration 9 ainda não foi executada.\n";
        echo "💡 Execute: php run_migrations.php\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erro ao verificar migrations: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'max_connections_per_hour') !== false) {
        echo "⚠️  Limite de conexões do hosting atingido. Tente novamente em alguns minutos.\n";
    }
}

echo "\n🏁 Verificação concluída.\n";
?>
