<?php
/**
 * Script para executar migrations manualmente
 * Execute este arquivo para atualizar o banco de dados para a versão mais recente
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Migration;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "🚀 Iniciando execução das migrations...\n\n";

try {
    $migration = new Migration();
    
    echo "📋 Executando migrations do banco de dados...\n";
    $migration->runMigrations();
    
    echo "✅ Migrations executadas com sucesso!\n";
    echo "📊 Sistema atualizado para a versão mais recente.\n\n";
    
    echo "🎯 Funcionalidades atualizadas:\n";
    echo "   - Sistema de Solicitação de Melhorias (Migration 9)\n";
    echo "   - Tabelas: solicitacoes_melhorias, solicitacoes_melhorias_responsaveis, solicitacoes_melhorias_anexos\n";
    echo "   - Permissões atualizadas para todos os perfis\n";
    echo "   - Módulo 'solicitacao_melhorias' adicionado ao sistema\n\n";
    
} catch (\Exception $e) {
    echo "❌ Erro ao executar migrations: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'max_connections_per_hour') !== false) {
        echo "⚠️  Limite de conexões do hosting atingido. Tente novamente em alguns minutos.\n";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "⚠️  Não foi possível conectar ao banco de dados. Verifique as configurações no .env\n";
    }
    
    exit(1);
}

echo "🎉 Processo concluído! O sistema está pronto para uso.\n";
?>
