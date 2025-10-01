-- Adicionar permissões para o módulo Melhoria Contínua 2.0
-- Execute este SQL no banco de dados

-- OPÇÃO 1: Se usar profile_permissions (permissões por perfil)
-- Verificar se a tabela existe
SELECT COUNT(*) FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name = 'profile_permissions';

-- Se existir, inserir permissões para cada perfil
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT 
    p.id,
    'melhoria_continua_2',
    1,  -- view (habilitado)
    1,  -- edit (habilitado)
    1,  -- delete (habilitado)
    0,  -- import (desabilitado)
    0   -- export (desabilitado)
FROM profiles p
WHERE NOT EXISTS (
    SELECT 1 FROM profile_permissions 
    WHERE profile_id = p.id 
    AND module = 'melhoria_continua_2'
);

-- OPÇÃO 2: Se usar user_permissions (permissões por usuário) - JÁ EXISTE!
-- Você já tem os registros na tabela user_permissions
-- Apenas verifique se o usuário que está tentando acessar tem permissão:

SELECT 
    u.id,
    u.name,
    u.email,
    up.module,
    up.can_view,
    up.can_edit,
    up.can_delete
FROM users u
LEFT JOIN user_permissions up ON u.id = up.user_id AND up.module = 'melhoria_continua_2'
WHERE u.status = 'active'
ORDER BY u.name;

-- Se algum usuário não tiver permissão, adicione:
-- INSERT INTO user_permissions (user_id, module, can_view, can_edit, can_delete, can_import, can_export, created_at, updated_at)
-- VALUES (ID_DO_USUARIO, 'melhoria_continua_2', 1, 1, 1, 0, 0, NOW(), NOW());
