-- ============================================
-- FIX DASHBOARD - PASSO A PASSO
-- Execute cada etapa separadamente
-- ============================================

-- ========== ETAPA 1: VER PERFIS EXISTENTES ==========
-- Execute SOMENTE esta query primeiro
-- ====================================================

SELECT id, name FROM profiles ORDER BY id;

-- IMPORTANTE: Anote os IDs que aparecerem!
-- Exemplo de resultado:
-- id | name
-- 7  | Administrador
-- 8  | Usuário Comum
-- 9  | Supervisor


-- ========== ETAPA 2: ADICIONAR PERMISSÕES ==========
-- SUBSTITUA os números X, Y, Z pelos IDs REAIS que você viu na Etapa 1
-- Depois execute este bloco
-- ====================================================

-- EXEMPLO: Se seus perfis são IDs 7, 8, 9
-- Descomente e ajuste as linhas abaixo:

/*
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES 
    (7, 'dashboard', 1, 1, 1, 1, 1),  -- Substitua 7 pelo ID do Administrador
    (8, 'dashboard', 1, 0, 0, 0, 0),  -- Substitua 8 pelo ID do Usuário Comum
    (9, 'dashboard', 1, 0, 0, 0, 0)   -- Substitua 9 pelo ID do Supervisor
    -- Adicione mais linhas se houver mais perfis
ON DUPLICATE KEY UPDATE can_view = 1;
*/


-- ========== ETAPA 3: VERIFICAR ==========
-- Execute esta query para confirmar
-- ========================================

SELECT 
    p.id,
    p.name as perfil,
    pp.module,
    CASE WHEN pp.can_view = 1 THEN '✅' ELSE '❌' END as 'Ver'
FROM profiles p
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id AND pp.module = 'dashboard'
ORDER BY p.id;


-- ============================================
-- DEPOIS:
-- 1. Faça LOGOUT
-- 2. Faça LOGIN
-- 3. Acesse o dashboard
-- ============================================
