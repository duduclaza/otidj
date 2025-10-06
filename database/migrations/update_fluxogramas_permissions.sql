-- =====================================================
-- ATUALIZAR PERMISSÕES DO MÓDULO FLUXOGRAMAS
-- Data: 06/10/2025
-- =====================================================

-- Verificar se já existe permissão para fluxogramas no perfil Administrador (ID = 1)
SELECT * FROM profile_permissions WHERE profile_id = 1 AND module = 'fluxogramas';

-- Se não existir, inserir
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export, created_at)
VALUES (1, 'fluxogramas', 1, 1, 1, 1, 1, NOW())
ON DUPLICATE KEY UPDATE 
    can_view = 1,
    can_edit = 1,
    can_delete = 1,
    can_import = 1,
    can_export = 1;

-- Verificar se a permissão foi criada/atualizada
SELECT 
    pp.id,
    p.name as perfil,
    pp.module as modulo,
    pp.can_view,
    pp.can_edit,
    pp.can_delete,
    pp.can_import,
    pp.can_export
FROM profile_permissions pp
INNER JOIN profiles p ON pp.profile_id = p.id
WHERE pp.profile_id = 1 AND pp.module = 'fluxogramas';

-- Verificar todos os módulos do perfil Administrador
SELECT 
    pp.module,
    pp.can_view,
    pp.can_edit,
    pp.can_delete
FROM profile_permissions pp
WHERE pp.profile_id = 1
ORDER BY pp.module;

-- =====================================================
-- TESTE: Verificar se admin tem acesso
-- =====================================================
SELECT 
    u.id,
    u.name,
    u.email,
    p.name as perfil,
    pp.module,
    pp.can_view
FROM users u
INNER JOIN profiles p ON u.profile_id = p.id
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id AND pp.module = 'fluxogramas'
WHERE u.id = 1;
