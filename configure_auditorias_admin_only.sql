-- Script para configurar módulo Auditorias apenas para Administradores
-- Execute este script no banco de dados do sistema SGQ

-- 1. Remover todas as permissões existentes do módulo 'auditorias'
DELETE FROM profile_permissions WHERE module = 'auditorias';

-- 2. Adicionar permissões completas para o perfil Administrador (ID 1)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES (1, 'auditorias', 1, 1, 1, 1, 1);

-- 3. Verificar se existe perfil "Super Administrador" e adicionar permissões
-- (Ajuste o ID se necessário)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT id, 'auditorias', 1, 1, 1, 1, 1
FROM profiles
WHERE name LIKE '%Super%Admin%' OR name LIKE '%Super%Administrador%'
ON DUPLICATE KEY UPDATE 
    can_view = 1, 
    can_edit = 1, 
    can_delete = 1, 
    can_import = 1, 
    can_export = 1;

-- 4. Verificar as permissões configuradas
SELECT 
    p.name AS perfil,
    pp.module AS modulo,
    pp.can_view AS visualizar,
    pp.can_edit AS editar,
    pp.can_delete AS excluir,
    pp.can_import AS importar,
    pp.can_export AS exportar
FROM profile_permissions pp
JOIN profiles p ON pp.profile_id = p.id
WHERE pp.module = 'auditorias'
ORDER BY p.name;

-- Resultado esperado:
-- Apenas os perfis "Administrador" e "Super Administrador" devem ter permissões para o módulo 'auditorias'
