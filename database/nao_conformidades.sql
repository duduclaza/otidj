-- =====================================================
-- SISTEMA DE NÃO CONFORMIDADES (NC)
-- =====================================================
-- Data: 2025-11-17
-- Descrição: Sistema completo de gestão de não conformidades
--            com apontamento por admins e resolução por supervisores
-- =====================================================

-- Tabela principal de Não Conformidades
CREATE TABLE IF NOT EXISTS `nao_conformidades` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL COMMENT 'Título resumido da NC',
  `descricao` TEXT NOT NULL COMMENT 'Descrição detalhada do problema',
  
  -- Controle de usuários
  `usuario_criador_id` INT(11) UNSIGNED NOT NULL COMMENT 'Admin que apontou a NC',
  `usuario_responsavel_id` INT(11) UNSIGNED NOT NULL COMMENT 'Supervisor responsável pela correção',
  `usuario_acao_id` INT(11) UNSIGNED NULL COMMENT 'Quem registrou a ação corretiva',
  `usuario_solucao_id` INT(11) UNSIGNED NULL COMMENT 'Quem marcou como solucionada',
  
  -- Ação corretiva
  `acao_corretiva` TEXT NULL COMMENT 'Descrição da ação tomada pelo responsável',
  `data_acao` DATETIME NULL COMMENT 'Data/hora do registro da ação',
  
  -- Status e datas
  `status` ENUM('pendente', 'em_andamento', 'solucionada') NOT NULL DEFAULT 'pendente',
  `data_solucao` DATETIME NULL COMMENT 'Data/hora da solução',
  
  -- Timestamps
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_criador` (`usuario_criador_id`),
  KEY `idx_responsavel` (`usuario_responsavel_id`),
  KEY `idx_created` (`created_at`),
  
  CONSTRAINT `fk_nc_criador` FOREIGN KEY (`usuario_criador_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_nc_responsavel` FOREIGN KEY (`usuario_responsavel_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_nc_acao` FOREIGN KEY (`usuario_acao_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_nc_solucao` FOREIGN KEY (`usuario_solucao_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Não Conformidades';

-- Tabela de anexos (evidências, fotos, documentos)
CREATE TABLE IF NOT EXISTS `nao_conformidades_anexos` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nc_id` INT(11) UNSIGNED NOT NULL COMMENT 'ID da NC',
  `nome_arquivo` VARCHAR(255) NOT NULL COMMENT 'Nome original do arquivo',
  `tipo_arquivo` VARCHAR(100) NOT NULL COMMENT 'MIME type',
  `tamanho_bytes` INT(11) UNSIGNED NOT NULL COMMENT 'Tamanho em bytes',
  `caminho_arquivo` VARCHAR(500) NOT NULL COMMENT 'Caminho no servidor',
  `tipo_anexo` ENUM('evidencia_inicial', 'evidencia_acao', 'outro') NOT NULL DEFAULT 'evidencia_inicial',
  `usuario_id` INT(11) UNSIGNED NOT NULL COMMENT 'Quem fez upload',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_nc` (`nc_id`),
  KEY `idx_tipo` (`tipo_anexo`),
  
  CONSTRAINT `fk_anexo_nc` FOREIGN KEY (`nc_id`) REFERENCES `nao_conformidades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_anexo_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Anexos das NCs';

-- =====================================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE (OPCIONAL)
-- =====================================================

-- NOTA: Índices são opcionais mas recomendados para performance
-- Se houver erro, mantenha comentado ou crie manualmente

/*
-- Busca por responsável e status
CREATE INDEX `idx_responsavel_status` ON `nao_conformidades` (`usuario_responsavel_id`, `status`);

-- Busca por criador e status
CREATE INDEX `idx_criador_status` ON `nao_conformidades` (`usuario_criador_id`, `status`);

-- Busca temporal
CREATE INDEX `idx_datas` ON `nao_conformidades` (`created_at`, `data_solucao`);
*/

-- =====================================================
-- VIEWS ÚTEIS (OPCIONAL)
-- =====================================================

-- NOTA: View é opcional. Se houver erro, mantenha comentado.
-- Para criar a view, descomente as linhas abaixo:

/*
CREATE OR REPLACE VIEW `vw_nao_conformidades_completo` AS
SELECT 
    nc.id,
    nc.titulo,
    nc.descricao,
    nc.status,
    nc.acao_corretiva,
    nc.created_at,
    nc.data_acao,
    nc.data_solucao,
    uc.name as criador_nome,
    uc.email as criador_email,
    ur.name as responsavel_nome,
    ur.email as responsavel_email,
    ua.name as acao_usuario_nome,
    us.name as solucao_usuario_nome,
    COUNT(DISTINCT a.id) as total_anexos,
    DATEDIFF(COALESCE(nc.data_solucao, NOW()), nc.created_at) as dias_aberto
FROM nao_conformidades nc
LEFT JOIN users uc ON nc.usuario_criador_id = uc.id
LEFT JOIN users ur ON nc.usuario_responsavel_id = ur.id
LEFT JOIN users ua ON nc.usuario_acao_id = ua.id
LEFT JOIN users us ON nc.usuario_solucao_id = us.id
LEFT JOIN nao_conformidades_anexos a ON nc.id = a.nc_id
GROUP BY nc.id;
*/

-- =====================================================
-- DADOS INICIAIS (EXEMPLOS PARA TESTE)
-- =====================================================

-- Não inserir dados de exemplo em produção
-- Descomentar apenas para desenvolvimento/teste

/*
-- Exemplo de NC pendente
INSERT INTO nao_conformidades 
(titulo, descricao, usuario_criador_id, usuario_responsavel_id, status)
VALUES 
('Toner vazando na impressora HP 402', 'Foi identificado vazamento de toner preto na impressora HP LaserJet 402 do setor de vendas', 1, 2, 'pendente');

-- Exemplo de NC em andamento
INSERT INTO nao_conformidades 
(titulo, descricao, usuario_criador_id, usuario_responsavel_id, status, acao_corretiva, usuario_acao_id, data_acao)
VALUES 
('Qualidade ruim do papel', 'Lote de papel com qualidade inferior ao padrão', 1, 3, 'em_andamento', 'Iniciado contato com fornecedor para troca do lote', 3, NOW());
*/

-- =====================================================
-- PERMISSÕES E MÓDULOS
-- =====================================================

-- NOTA: A tabela 'modules' pode não existir em todos os sistemas
-- Se existir, descomentar a linha abaixo para registrar o módulo

-- INSERT IGNORE INTO modules (id, `key`, name, description, created_at) 
-- VALUES (NULL, 'nao_conformidades', 'Não Conformidades', 'Gestão de não conformidades com apontamento e resolução', NOW());

-- =====================================================
-- TRIGGERS ÚTEIS (OPCIONAL)
-- =====================================================

-- NOTA: Triggers são opcionais. Se houver erro, comente esta seção.
-- Para criar os triggers, execute as linhas abaixo separadamente no MySQL:

/*
DELIMITER $$

DROP TRIGGER IF EXISTS `before_nc_status_update`$$

CREATE TRIGGER `before_nc_status_update` 
BEFORE UPDATE ON `nao_conformidades`
FOR EACH ROW
BEGIN
    -- Apenas permitir avanço no status
    IF OLD.status = 'solucionada' AND NEW.status != 'solucionada' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Não é permitido reabrir uma NC solucionada';
    END IF;
    
    -- Se marcar como em_andamento, deve ter ação corretiva
    IF NEW.status = 'em_andamento' AND (NEW.acao_corretiva IS NULL OR NEW.acao_corretiva = '') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'É necessário registrar uma ação corretiva';
    END IF;
END$$

DELIMITER ;
*/

-- =====================================================
-- QUERIES ÚTEIS PARA RELATÓRIOS
-- =====================================================

/*
-- NCs pendentes por responsável
SELECT 
    ur.name as responsavel,
    COUNT(*) as total_pendentes
FROM nao_conformidades nc
JOIN users ur ON nc.usuario_responsavel_id = ur.id
WHERE nc.status = 'pendente'
GROUP BY ur.id
ORDER BY total_pendentes DESC;

-- Tempo médio de resolução
SELECT 
    AVG(DATEDIFF(data_solucao, created_at)) as media_dias_resolucao
FROM nao_conformidades
WHERE status = 'solucionada';

-- NCs por mês
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as mes,
    COUNT(*) as total_ncs,
    SUM(CASE WHEN status = 'solucionada' THEN 1 ELSE 0 END) as solucionadas
FROM nao_conformidades
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY mes DESC;

-- Ranking de responsáveis mais eficientes
SELECT 
    ur.name as responsavel,
    COUNT(*) as total_ncs,
    SUM(CASE WHEN status = 'solucionada' THEN 1 ELSE 0 END) as solucionadas,
    ROUND(AVG(DATEDIFF(COALESCE(data_solucao, NOW()), created_at)), 1) as media_dias
FROM nao_conformidades nc
JOIN users ur ON nc.usuario_responsavel_id = ur.id
GROUP BY ur.id
HAVING total_ncs > 0
ORDER BY solucionadas DESC, media_dias ASC;
*/

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
