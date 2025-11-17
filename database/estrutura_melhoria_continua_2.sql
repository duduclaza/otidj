-- ========================================
-- ESTRUTURA COMPLETA DA TABELA MELHORIA_CONTINUA_2
-- Com suporte a motivo de recusa
-- Data: 17/11/2025
-- ========================================

CREATE TABLE IF NOT EXISTS `melhoria_continua_2` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL COMMENT 'Título da melhoria',
  `departamento_id` INT(11) NULL COMMENT 'ID do departamento (FK para departamentos)',
  `descricao` TEXT NOT NULL COMMENT 'Descrição detalhada da melhoria',
  
  -- Campos 5W2H
  `o_que` TEXT NOT NULL COMMENT 'O que será feito?',
  `como` TEXT NOT NULL COMMENT 'Como será feito?',
  `onde` TEXT NOT NULL COMMENT 'Onde será feito?',
  `porque` TEXT NOT NULL COMMENT 'Por que será feito?',
  `quando` DATE NULL COMMENT 'Quando será feito?',
  `quanto_custa` DECIMAL(10,2) NULL COMMENT 'Quanto custa? (em R$)',
  
  -- Informações adicionais
  `responsaveis` VARCHAR(500) NULL COMMENT 'IDs dos responsáveis separados por vírgula',
  `resultado_esperado` TEXT NOT NULL COMMENT 'Resultado esperado com a melhoria',
  `idealizador` VARCHAR(255) NOT NULL COMMENT 'Nome do idealizador da ideia',
  
  -- Controle de status e pontuação
  `status` ENUM('Pendente análise', 'Enviado para Aprovação', 'Em andamento', 'Concluída', 'Recusada', 'Pendente Adaptação') 
    NOT NULL DEFAULT 'Pendente análise' COMMENT 'Status atual da melhoria',
  `pontuacao` TINYINT(1) NULL COMMENT 'Pontuação de 0 a 3 atribuída pelo admin',
  
  -- Observações e motivo de recusa
  `observacao` TEXT NULL COMMENT 'Observações gerais. Quando status=Recusada, armazena RECUSADA: [motivo]',
  
  -- Anexos
  `anexos` LONGTEXT NULL COMMENT 'JSON com informações dos anexos',
  
  -- Auditoria
  `criado_por` INT(11) NOT NULL COMMENT 'ID do usuário que criou',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_departamento` (`departamento_id`),
  KEY `idx_status` (`status`),
  KEY `idx_criado_por` (`criado_por`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Tabela de melhorias contínuas com metodologia 5W2H e sistema de recusa';

-- ========================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- ========================================

-- Índice composto para filtros mais comuns
CREATE INDEX IF NOT EXISTS `idx_status_departamento` ON `melhoria_continua_2` (`status`, `departamento_id`);

-- Índice para busca por data
CREATE INDEX IF NOT EXISTS `idx_quando` ON `melhoria_continua_2` (`quando`);

-- ========================================
-- EXEMPLOS DE QUERIES
-- ========================================

-- 1. Buscar melhorias recusadas com motivo
-- SELECT 
--     id,
--     titulo,
--     departamento_id,
--     status,
--     REPLACE(observacao, 'RECUSADA: ', '') as motivo_recusa,
--     criado_por,
--     created_at
-- FROM melhoria_continua_2
-- WHERE status = 'Recusada'
-- ORDER BY updated_at DESC;

-- 2. Buscar melhorias por departamento
-- SELECT * FROM melhoria_continua_2 
-- WHERE departamento_id = 1 
-- ORDER BY created_at DESC;

-- 3. Buscar melhorias por período
-- SELECT * FROM melhoria_continua_2 
-- WHERE DATE(created_at) BETWEEN '2025-01-01' AND '2025-12-31'
-- ORDER BY created_at DESC;

-- 4. Estatísticas por status
-- SELECT 
--     status,
--     COUNT(*) as total,
--     AVG(pontuacao) as pontuacao_media
-- FROM melhoria_continua_2
-- GROUP BY status
-- ORDER BY total DESC;

-- ========================================
-- CONSTRAINTS (OPCIONAL)
-- ========================================

-- Adicionar chave estrangeira para departamentos (se existir)
-- ALTER TABLE `melhoria_continua_2` 
-- ADD CONSTRAINT `fk_departamento` 
-- FOREIGN KEY (`departamento_id`) 
-- REFERENCES `departamentos` (`id`) 
-- ON DELETE SET NULL ON UPDATE CASCADE;

-- Adicionar chave estrangeira para usuários (se existir)
-- ALTER TABLE `melhoria_continua_2` 
-- ADD CONSTRAINT `fk_criado_por` 
-- FOREIGN KEY (`criado_por`) 
-- REFERENCES `users` (`id`) 
-- ON DELETE RESTRICT ON UPDATE CASCADE;
