-- ============================================
-- FIX RÁPIDO - ERRO 500 DASHBOARD
-- Execute este SQL e faça logout/login
-- ============================================

-- 1. Adicionar permissão de dashboard para TODOS os perfis (1 a 5)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES 
    (1, 'dashboard', 1, 1, 1, 1, 1),
    (2, 'dashboard', 1, 0, 0, 0, 0),
    (3, 'dashboard', 1, 0, 0, 0, 0),
    (4, 'dashboard', 1, 0, 0, 0, 0),
    (5, 'dashboard', 1, 0, 0, 0, 0)
ON DUPLICATE KEY UPDATE can_view = 1;

-- 2. Verificar se funcionou
SELECT 
    p.name as perfil,
    pp.module,
    pp.can_view as 'pode_ver'
FROM profiles p
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id AND pp.module = 'dashboard'
ORDER BY p.id;

-- ============================================
-- DEPOIS DE EXECUTAR:
-- 1. Faça LOGOUT do sistema
-- 2. Faça LOGIN novamente  
-- 3. Tente acessar o dashboard
-- ============================================
