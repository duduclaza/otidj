-- ============================================
-- SCRIPT: Permissões do Módulo NPS (VERSÃO SEGURA)
-- Versão: 2.7.1 FIX
-- Data: 2024
-- Descrição: Adiciona o módulo NPS às permissões do sistema
--            Versão simplificada e segura
-- ============================================

-- 1. Deletar permissões NPS existentes (para limpar)
DELETE FROM profile_permissions WHERE module = 'nps';

-- 2. Inserir permissões do módulo NPS para TODOS os perfis existentes
-- Todos podem visualizar e exportar, apenas admins podem editar/excluir
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT 
    id AS profile_id,
    'nps' AS module,
    1 AS can_view,      -- Todos podem visualizar
    0 AS can_edit,      -- Inicialmente ninguém edita
    0 AS can_delete,    -- Inicialmente ninguém exclui
    0 AS can_import,    -- NPS não tem importação
    1 AS can_export     -- Todos podem exportar CSV
FROM profiles;

-- 3. Dar permissões completas para o perfil "Administrador" (se existir)
-- Versão compatível com MariaDB (sem LIMIT em subquery)
UPDATE profile_permissions 
SET can_edit = 1, can_delete = 1
WHERE module = 'nps' 
AND profile_id IN (
    SELECT id FROM profiles WHERE name = 'Administrador'
);

-- 4. Verificar resultado
SELECT 
    p.name AS Perfil,
    pp.can_view AS Ver,
    pp.can_edit AS Editar,
    pp.can_delete AS Excluir,
    pp.can_export AS Exportar
FROM profile_permissions pp
JOIN profiles p ON pp.profile_id = p.id
WHERE pp.module = 'nps'
ORDER BY p.name;
