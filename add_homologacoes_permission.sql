-- ==========================================
-- ADICIONAR PERMISSÃO HOMOLOGAÇÕES
-- Execute no phpMyAdmin
-- ==========================================

-- 1. Ver perfis existentes
SELECT id, name FROM profiles;

-- 2. Adicionar permissão para Administrador (ajuste o ID se necessário)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT 
    p.id,
    'homologacoes' as module,
    1 as can_view,
    1 as can_edit,
    1 as can_delete,
    0 as can_import,
    1 as can_export
FROM profiles p
WHERE p.name IN ('Administrador', 'Super Admin', 'Admin')
AND NOT EXISTS (
    SELECT 1 FROM profile_permissions pp 
    WHERE pp.profile_id = p.id AND pp.module = 'homologacoes'
);

-- 3. Verificar se foi adicionado
SELECT 
    p.name as Perfil,
    pp.module as Modulo,
    pp.can_view as Ver,
    pp.can_edit as Editar,
    pp.can_delete as Excluir
FROM profile_permissions pp
JOIN profiles p ON p.id = pp.profile_id
WHERE pp.module = 'homologacoes';

-- 4. PRONTO! Faça logout e login novamente
SELECT '✅ Permissão adicionada! Faça LOGOUT e LOGIN para atualizar!' as Status;
