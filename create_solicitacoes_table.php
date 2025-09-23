<?php
/**
 * Script para criar a tabela de solicitações do sistema POPs e ITs
 * Execute este arquivo para adicionar a tabela pops_its_solicitacoes ao banco de dados
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;

echo "🚀 Criando tabela de solicitações do sistema POPs e ITs...\n\n";

try {
    // Conectar ao banco de dados
    $db = Database::getInstance()->getConnection();

    // Verificar se a tabela já existe
    $result = $db->query("SHOW TABLES LIKE 'pops_its_solicitacoes'");
    if ($result->num_rows > 0) {
        echo "⚠️  Tabela 'pops_its_solicitacoes' já existe!\n";
        echo "✅ Processo concluído - nenhuma ação necessária.\n";
        exit(0);
    }

    echo "📋 Criando tabela 'pops_its_solicitacoes'...\n";

    // SQL para criar a tabela
    $sql = "
        CREATE TABLE `pops_its_solicitacoes` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `registro_id` bigint(20) unsigned NOT NULL,
            `solicitante_id` bigint(20) unsigned NOT NULL,
            `tipo_solicitacao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `justificativa` text COLLATE utf8mb4_unicode_ci NOT NULL,
            `status` enum('pendente','aprovada','reprovada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
            `aprovada_por` bigint(20) unsigned DEFAULT NULL,
            `aprovada_em` timestamp NULL DEFAULT NULL,
            `observacao_reprovacao` text COLLATE utf8mb4_unicode_ci,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `pops_its_solicitacoes_registro_id_foreign` (`registro_id`),
            KEY `pops_its_solicitacoes_solicitante_id_foreign` (`solicitante_id`),
            KEY `pops_its_solicitacoes_aprovada_por_foreign` (`aprovada_por`),
            KEY `pops_its_solicitacoes_status_created_at_index` (`status`,`created_at`),
            CONSTRAINT `pops_its_solicitacoes_registro_id_foreign` FOREIGN KEY (`registro_id`) REFERENCES `pops_its_registros` (`id`) ON DELETE CASCADE,
            CONSTRAINT `pops_its_solicitacoes_solicitante_id_foreign` FOREIGN KEY (`solicitante_id`) REFERENCES `users` (`id`),
            CONSTRAINT `pops_its_solicitacoes_aprovada_por_foreign` FOREIGN KEY (`aprovada_por`) REFERENCES `users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Executar a criação da tabela
    if ($db->query($sql) === TRUE) {
        echo "✅ Tabela 'pops_its_solicitacoes' criada com sucesso!\n";
        echo "📊 Estrutura da tabela:\n";
        echo "   - id (Primary Key)\n";
        echo "   - registro_id (Foreign Key -> pops_its_registros)\n";
        echo "   - solicitante_id (Foreign Key -> users)\n";
        echo "   - tipo_solicitacao (varchar)\n";
        echo "   - justificativa (text)\n";
        echo "   - status (enum: pendente, aprovada, reprovada)\n";
        echo "   - aprovada_por (Foreign Key -> users)\n";
        echo "   - aprovada_em (timestamp)\n";
        echo "   - observacao_reprovacao (text)\n";
        echo "   - created_at, updated_at (timestamps)\n\n";

        echo "🎯 Funcionalidades adicionadas:\n";
        echo "   - Sistema de Solicitações de Exclusão\n";
        echo "   - Controle de permissões para administradores\n";
        echo "   - Histórico de aprovações/reprovações\n";
        echo "   - Integração com usuários e registros existentes\n\n";
    } else {
        throw new Exception("Erro ao criar tabela: " . $db->error);
    }

} catch (\Exception $e) {
    echo "❌ Erro ao criar tabela: " . $e->getMessage() . "\n";

    if (strpos($e->getMessage(), 'max_connections_per_hour') !== false) {
        echo "⚠️  Limite de conexões do hosting atingido. Tente novamente em alguns minutos.\n";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "⚠️  Não foi possível conectar ao banco de dados. Verifique as configurações no .env\n";
    }

    exit(1);
}

echo "🎉 Processo concluído! O sistema POPs e ITs agora tem suporte a solicitações de exclusão.\n";
?>
