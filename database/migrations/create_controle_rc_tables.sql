-- Migration: Criar tabelas do módulo Controle de RC
-- Data: 2025-01-29
-- Descrição: Tabela principal de registros de RC e tabela de evidências (MEDIUMBLOB)
-- VERSÃO CORRIGIDA: Foreign keys opcionais para compatibilidade

-- Tabela principal de registros RC
CREATE TABLE IF NOT EXISTS `controle_rc` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `numero_registro` VARCHAR(50) NOT NULL UNIQUE,
    `data_abertura` DATE NOT NULL,
    `origem` ENUM('Telefone', 'E-mail', 'Presencial', 'Formulário', 'Contrato', 'Auditoria', 'Outros') NOT NULL,
    `cliente_nome` VARCHAR(255) NOT NULL,
    `categoria` ENUM('Técnica', 'Atendimento', 'Logística', 'Contrato', 'Faturamento', 'Qualidade', 'Prazos', 'Produto', 'Outros') NOT NULL,
    `numero_serie` VARCHAR(100) NULL,
    `fornecedor_id` INT UNSIGNED NULL,
    `testes_realizados` TEXT NULL,
    `acoes_realizadas` TEXT NULL,
    `conclusao` TEXT NULL,
    `usuario_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_numero_registro` (`numero_registro`),
    INDEX `idx_data_abertura` (`data_abertura`),
    INDEX `idx_categoria` (`categoria`),
    INDEX `idx_usuario_id` (`usuario_id`),
    INDEX `idx_fornecedor_id` (`fornecedor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de evidências (MEDIUMBLOB para armazenar arquivos no banco)
CREATE TABLE IF NOT EXISTS `controle_rc_evidencias` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `rc_id` INT UNSIGNED NOT NULL,
    `arquivo_blob` MEDIUMBLOB NOT NULL,
    `nome_arquivo` VARCHAR(255) NOT NULL,
    `tipo_arquivo` VARCHAR(100) NOT NULL,
    `tamanho` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_rc_id` (`rc_id`),
    FOREIGN KEY (`rc_id`) REFERENCES `controle_rc`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentários das tabelas
ALTER TABLE `controle_rc` COMMENT = 'Registros de controle de reclamações (RC)';
ALTER TABLE `controle_rc_evidencias` COMMENT = 'Evidências anexadas aos registros de RC (armazenadas em MEDIUMBLOB)';
