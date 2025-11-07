-- ============================================
-- FIX AUTOMÁTICO - ERRO 500 DASHBOARD
-- Este SQL funciona independente dos IDs dos perfis
-- ============================================

-- PASSO 1: Ver quais perfis existem no seu banco
SELECT id, name, description FROM profiles ORDER BY id;

-- ============================================
-- PASSO 2: Execute este bloco após ver os IDs acima
-- Adiciona permissão de dashboard para TODOS os perfis existentes
-- ============================================

-- Inserir permissão de dashboard para CADA perfil que existe
-- O ON DUPLICATE KEY UPDATE evita erros se já existir

-- Para TODOS os perfis (versão dinâmica)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT 
    id,
    'dashboard',
    1,  -- can_view
    CASE WHEN name LIKE '%Admin%' OR name LIKE '%Administrador%' THEN 1 ELSE 0 END,  -- can_edit
    CASE WHEN name LIKE '%Admin%' OR name LIKE '%Administrador%' THEN 1 ELSE 0 END,  -- can_delete
    CASE WHEN name LIKE '%Admin%' OR name LIKE '%Administrador%' THEN 1 ELSE 0 END,  -- can_import
    CASE WHEN name LIKE '%Admin%' OR name LIKE '%Administrador%' THEN 1 ELSE 0 END   -- can_export
FROM profiles
WHERE NOT EXISTS (
    SELECT 1 FROM profile_permissions pp 
    WHERE pp.profile_id = profiles.id AND pp.module = 'dashboard'
);

-- Atualizar perfis que já têm permissão de dashboard
UPDATE profile_permissions pp
INNER JOIN profiles p ON pp.profile_id = p.id
SET pp.can_view = 1
WHERE pp.module = 'dashboard';

-- ============================================
-- PASSO 3: Verificar se funcionou
-- ============================================

SELECT 
    p.id,
    p.name as perfil,
    CASE 
        WHEN pp.can_view = 1 THEN '✅ SIM'
        ELSE '❌ NÃO'
    END as 'Pode Ver Dashboard'
FROM profiles p
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id AND pp.module = 'dashboard'
ORDER BY p.id;

-- ============================================
-- DEPOIS DE EXECUTAR:
-- 1. Faça LOGOUT do sistema
-- 2. Faça LOGIN novamente  
-- 3. Tente acessar o dashboard
-- ============================================
