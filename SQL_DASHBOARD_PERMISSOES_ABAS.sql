-- ============================================
-- PERMISSÕES GRANULARES PARA ABAS DO DASHBOARD
-- Data: 07/11/2025
-- ============================================

-- Criar tabela de permissões de abas do dashboard
CREATE TABLE IF NOT EXISTS dashboard_tab_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    tab_name VARCHAR(50) NOT NULL,
    can_view TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    
    -- Índices
    UNIQUE KEY unique_profile_tab (profile_id, tab_name),
    INDEX idx_profile (profile_id),
    INDEX idx_tab (tab_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ABAS DISPONÍVEIS:
-- 'retornados'    - Aba de Toners Retornados
-- 'amostragens'   - Aba de Amostragens 2.0
-- 'fornecedores'  - Aba de Análise de Fornecedores
-- 'garantias'     - Aba de Garantias
-- 'melhorias'     - Aba de Melhorias Contínuas
-- ============================================

-- ============================================
-- EXEMPLOS DE INSERÇÃO (COMENTADOS)
-- ============================================

-- Dar acesso a TODAS as abas para o perfil Admin (ID 1)
-- INSERT INTO dashboard_tab_permissions (profile_id, tab_name, can_view)
-- VALUES
--     (1, 'retornados', 1),
--     (1, 'amostragens', 1),
--     (1, 'fornecedores', 1),
--     (1, 'garantias', 1),
--     (1, 'melhorias', 1)
-- ON DUPLICATE KEY UPDATE can_view = 1;

-- Dar acesso apenas a algumas abas para perfil Operacional (ID 3)
-- INSERT INTO dashboard_tab_permissions (profile_id, tab_name, can_view)
-- VALUES
--     (3, 'retornados', 1),
--     (3, 'amostragens', 1),
--     (3, 'fornecedores', 0),
--     (3, 'garantias', 1),
--     (3, 'melhorias', 0)
-- ON DUPLICATE KEY UPDATE can_view = VALUES(can_view);

-- Exemplo: Consultar abas permitidas para um perfil
-- SELECT tab_name 
-- FROM dashboard_tab_permissions 
-- WHERE profile_id = 1 AND can_view = 1;

-- Exemplo: Consultar se perfil tem acesso a uma aba específica
-- SELECT can_view 
-- FROM dashboard_tab_permissions 
-- WHERE profile_id = 1 AND tab_name = 'retornados';

-- ============================================
-- SCRIPT PARA DAR ACESSO TOTAL A TODOS OS PERFIS EXISTENTES
-- (Execute apenas se quiser liberar todas as abas para todos)
-- ============================================
-- INSERT INTO dashboard_tab_permissions (profile_id, tab_name, can_view)
-- SELECT p.id, t.tab_name, 1
-- FROM profiles p
-- CROSS JOIN (
--     SELECT 'retornados' as tab_name UNION ALL
--     SELECT 'amostragens' UNION ALL
--     SELECT 'fornecedores' UNION ALL
--     SELECT 'garantias' UNION ALL
--     SELECT 'melhorias'
-- ) t
-- ON DUPLICATE KEY UPDATE can_view = 1;

-- ============================================
-- FIM DO SCRIPT
-- ============================================
