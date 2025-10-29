-- OPCIONAL: Adicionar Foreign Keys ao Controle de RC
-- Execute este arquivo APENAS se as tabelas 'users' e 'fornecedores' existirem
-- e tiverem estrutura compatível (INT UNSIGNED para IDs)

-- Verificar se as tabelas existem antes de adicionar constraints:
-- SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'u230868210_djsgqpro' AND TABLE_NAME IN ('users', 'fornecedores');

-- PASSO 1: Adicionar FK para usuario_id (se tabela users existe)
-- Descomente a linha abaixo se a tabela users existe e tem id INT UNSIGNED:
-- ALTER TABLE `controle_rc` ADD CONSTRAINT `fk_controle_rc_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT;

-- PASSO 2: Adicionar FK para fornecedor_id (se tabela fornecedores existe)
-- Descomente a linha abaixo se a tabela fornecedores existe e tem id INT UNSIGNED:
-- ALTER TABLE `controle_rc` ADD CONSTRAINT `fk_controle_rc_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores`(`id`) ON DELETE SET NULL;

-- NOTA: Se você não tem a tabela 'fornecedores', pode criar uma básica:
/*
CREATE TABLE IF NOT EXISTS `fornecedores` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(255) NOT NULL,
    `cnpj` VARCHAR(18) NULL,
    `email` VARCHAR(255) NULL,
    `telefone` VARCHAR(20) NULL,
    `ativo` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_cnpj` (`cnpj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Depois adicione a FK:
ALTER TABLE `controle_rc` ADD CONSTRAINT `fk_controle_rc_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores`(`id`) ON DELETE SET NULL;
*/
