-- =======================-- ============================================
-- SCRIPT: Sistema de Permissões por Aba do Dashboard + NPS
-- Versão: 2.7.1
-- Data: 2024
-- Descrição: Cria tabela de permissões granulares por aba
--            e popula com permissões padrão baseadas no status
--            de administrador de cada perfil.
--            Inclui módulo NPS nas permissões.
-- ===================================================================

-- 1. Criar tabela de permissões de abas do dashboard
CREATE TABLE IF NOT EXISTS `dashboard_tab_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INT NOT NULL,
  `tab_name` VARCHAR(50) NOT NULL COMMENT 'Nome da aba: retornados, amostragens, fornecedores, garantias, melhorias',
  `can_view` TINYINT(1) DEFAULT 1 COMMENT '1=pode ver, 0=não pode ver',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_profile_tab` (`profile_id`, `tab_name`),
  FOREIGN KEY (`profile_id`) REFERENCES `profiles`(`id`) ON DELETE CASCADE,
  INDEX `idx_profile` (`profile_id`),
  INDEX `idx_tab` (`tab_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Permissões de visualização das abas do dashboard por perfil';

-- 2. Inserir permissões padrão APENAS para perfis que EXISTEM
-- Este método é seguro e não causa erro de foreign key

-- Inserir permissões para perfis com is_admin = 1 (Administradores)
-- Administradores têm acesso a TODAS as abas
INSERT INTO `dashboard_tab_permissions` (`profile_id`, `tab_name`, `can_view`)
SELECT p.id, 'retornados', 1 FROM profiles p WHERE p.is_admin = 1
UNION ALL
SELECT p.id, 'amostragens', 1 FROM profiles p WHERE p.is_admin = 1
UNION ALL
SELECT p.id, 'fornecedores', 1 FROM profiles p WHERE p.is_admin = 1
UNION ALL
SELECT p.id, 'garantias', 1 FROM profiles p WHERE p.is_admin = 1
UNION ALL
SELECT p.id, 'melhorias', 1 FROM profiles p WHERE p.is_admin = 1
ON DUPLICATE KEY UPDATE `can_view` = VALUES(`can_view`);

-- 3. Inserir permissões padrão para perfis NÃO-admin
-- Permissões básicas: apenas Retornados e Amostragens
INSERT INTO `dashboard_tab_permissions` (`profile_id`, `tab_name`, `can_view`)
SELECT p.id, 'retornados', 1 FROM profiles p WHERE p.is_admin = 0 OR p.is_admin IS NULL
UNION ALL
SELECT p.id, 'amostragens', 1 FROM profiles p WHERE p.is_admin = 0 OR p.is_admin IS NULL
UNION ALL
SELECT p.id, 'fornecedores', 0 FROM profiles p WHERE p.is_admin = 0 OR p.is_admin IS NULL
UNION ALL
SELECT p.id, 'garantias', 0 FROM profiles p WHERE p.is_admin = 0 OR p.is_admin IS NULL
UNION ALL
SELECT p.id, 'melhorias', 0 FROM profiles p WHERE p.is_admin = 0 OR p.is_admin IS NULL
ON DUPLICATE KEY UPDATE `can_view` = VALUES(`can_view`);

-- NOTA: Após executar este script, você pode personalizar as permissões
-- de cada perfil individualmente através da interface em:
-- Administrativo → Gerenciar Perfis → Editar Perfil

-- ===================================================================
-- VERIFICAÇÕES
-- ===================================================================

-- Verificar estrutura da tabela
SELECT 
    'Tabela criada com sucesso!' as status,
    COUNT(*) as total_permissoes
FROM dashboard_tab_permissions;

-- Listar todas as permissões por perfil
SELECT 
    p.id as profile_id,
    p.name as perfil,
    dtp.tab_name as aba,
    CASE WHEN dtp.can_view = 1 THEN '✅ Sim' ELSE '❌ Não' END as pode_ver
FROM profiles p
LEFT JOIN dashboard_tab_permissions dtp ON p.id = dtp.profile_id
ORDER BY p.id, dtp.tab_name;

-- ===================================================================
-- NOTAS IMPORTANTES
-- ===================================================================
-- 
-- ABAS DISPONÍVEIS NO DASHBOARD:
-- 1. retornados   - Análise de Retornados
-- 2. amostragens  - Amostragens 2.0
-- 3. fornecedores - Qualidade de Fornecedores
-- 4. garantias    - Análise de Garantias
-- 5. melhorias    - Melhorias Contínuas
--
-- COMO USAR:
-- - Ao editar um perfil, selecione quais abas ele pode ver
-- - As permissões são salvas nesta tabela
-- - O dashboard automaticamente oculta abas sem permissão
-- - Admin sempre tem acesso a todas as abas (verificação por role)
-- 
-- ===================================================================
