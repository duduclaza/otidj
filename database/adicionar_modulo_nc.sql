-- =====================================================
-- ADICIONAR MÓDULO NÃO CONFORMIDADES NO SISTEMA
-- =====================================================
-- Data: 2025-11-17
-- Descrição: Script para adicionar o módulo de NC
--            nas permissões do sistema
-- =====================================================

-- Verificar se a tabela modules existe, se não, criar
CREATE TABLE IF NOT EXISTS `modules` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir módulo de Não Conformidades
INSERT INTO `modules` (`key`, `name`, `description`, `active`) 
VALUES ('nao_conformidades', 'Não Conformidades', 'Gestão de não conformidades com apontamento por admins e resolução por supervisores', 1)
ON DUPLICATE KEY UPDATE 
  `name` = 'Não Conformidades',
  `description` = 'Gestão de não conformidades com apontamento por admins e resolução por supervisores',
  `active` = 1;

-- =====================================================
-- VERIFICAR SE EXISTE TABELA DE PERMISSÕES
-- =====================================================

-- Se houver uma tabela profile_permissions ou permissions
-- você pode adicionar as permissões padrão aqui

-- Exemplo (ajustar conforme estrutura do seu banco):
/*
INSERT INTO profile_permissions (profile_id, module_key, can_view, can_create, can_edit, can_delete)
SELECT 
  p.id,
  'nao_conformidades',
  1, 1, 1, 1
FROM profiles p
WHERE p.name IN ('Super Administrador', 'Administrador')
ON DUPLICATE KEY UPDATE 
  can_view = 1, 
  can_create = 1, 
  can_edit = 1, 
  can_delete = 1;
*/

-- =====================================================
-- VERIFICAÇÃO
-- =====================================================

-- Verificar se módulo foi inserido
SELECT * FROM modules WHERE `key` = 'nao_conformidades';

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
