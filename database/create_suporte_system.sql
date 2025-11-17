-- ========================================
-- SISTEMA DE SUPORTE
-- Admin solicita ajuda ao Super Admin
-- Data: 17/11/2025
-- ========================================

CREATE TABLE IF NOT EXISTS `suporte_solicitacoes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL COMMENT 'Título da solicitação',
  `descricao` TEXT NOT NULL COMMENT 'Descrição detalhada do problema/dúvida',
  `anexos` LONGTEXT NULL COMMENT 'JSON com informações dos anexos',
  
  -- Status e controle
  `status` ENUM('Pendente', 'Em Análise', 'Concluído') NOT NULL DEFAULT 'Pendente',
  `resolucao` TEXT NULL COMMENT 'O que foi feito para resolver',
  
  -- Auditoria
  `solicitante_id` INT(11) NOT NULL COMMENT 'ID do admin que solicitou',
  `resolvido_por` INT(11) NULL COMMENT 'ID do super admin que resolveu',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `resolvido_em` TIMESTAMP NULL DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_solicitante` (`solicitante_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Sistema de suporte: Admins solicitam ajuda ao Super Admin';

-- ========================================
-- ÍNDICES ADICIONAIS
-- ========================================

CREATE INDEX IF NOT EXISTS `idx_status_data` ON `suporte_solicitacoes` (`status`, `created_at`);

-- ========================================
-- DADOS DE EXEMPLO (OPCIONAL)
-- ========================================

-- Descomentar para inserir exemplo:
-- INSERT INTO `suporte_solicitacoes` (
--     titulo,
--     descricao,
--     status,
--     solicitante_id
-- ) VALUES (
--     'Dúvida sobre permissões',
--     'Como configurar permissões personalizadas para um novo perfil?',
--     'Pendente',
--     1
-- );

-- ========================================
-- QUERIES ÚTEIS
-- ========================================

-- Ver todas pendentes
-- SELECT * FROM suporte_solicitacoes WHERE status = 'Pendente' ORDER BY created_at DESC;

-- Ver por solicitante
-- SELECT * FROM suporte_solicitacoes WHERE solicitante_id = 1 ORDER BY created_at DESC;

-- Estatísticas
-- SELECT status, COUNT(*) as total FROM suporte_solicitacoes GROUP BY status;
