-- ============================================
-- SCRIPT: Permissões do Módulo NPS
-- Versão: 2.7.1
-- Data: 2024
-- Descrição: Adiciona o módulo NPS às permissões do sistema
-- ============================================

-- Inserir permissões do módulo NPS para todos os perfis existentes
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT 
    p.id AS profile_id,
    'nps' AS module,
    1 AS can_view,  -- Todos podem visualizar
    CASE WHEN p.is_admin = 1 THEN 1 ELSE 0 END AS can_edit,  -- Apenas admins podem editar
    CASE WHEN p.is_admin = 1 THEN 1 ELSE 0 END AS can_delete,  -- Apenas admins podem excluir
    0 AS can_import,  -- NPS não tem importação
    1 AS can_export  -- Todos podem exportar CSV
FROM profiles p
WHERE NOT EXISTS (
    SELECT 1 
    FROM profile_permissions pp 
    WHERE pp.profile_id = p.id 
    AND pp.module = 'nps'
);

-- Garantir que o perfil "Administrador" tem todas as permissões do NPS
UPDATE profile_permissions 
SET can_view = 1, can_edit = 1, can_delete = 1, can_export = 1
WHERE module = 'nps' 
AND profile_id IN (
    SELECT id FROM profiles WHERE name = 'Administrador' OR is_admin = 1
);

-- Mensagem de confirmação
SELECT 'Permissões do módulo NPS criadas/atualizadas com sucesso!' AS status;

-- Verificar permissões criadas
SELECT 
    p.name AS perfil,
    pp.module AS modulo,
    pp.can_view AS visualizar,
    pp.can_edit AS editar,
    pp.can_delete AS excluir,
    pp.can_export AS exportar
FROM profile_permissions pp
JOIN profiles p ON pp.profile_id = p.id
WHERE pp.module = 'nps'
ORDER BY p.name;
