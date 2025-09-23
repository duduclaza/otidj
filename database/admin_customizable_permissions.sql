-- Tornar permissões do Administrador customizáveis
-- Este script garante que o perfil Administrador tenha todas as permissões explicitamente
-- definidas no banco, permitindo customização posterior

-- Primeiro, vamos garantir que o perfil Administrador tenha todas as permissões atuais
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT 
    p.id as profile_id,
    m.module_name as module,
    1 as can_view,
    1 as can_edit, 
    1 as can_delete,
    1 as can_import,
    1 as can_export
FROM profiles p
CROSS JOIN (
    SELECT 'dashboard' as module_name
    UNION ALL SELECT 'toners_cadastro'
    UNION ALL SELECT 'toners_retornados'
    UNION ALL SELECT 'homologacoes'
    UNION ALL SELECT 'amostragens'
    UNION ALL SELECT 'garantias'
    UNION ALL SELECT 'controle_descartes'
    UNION ALL SELECT 'fmea'
    UNION ALL SELECT 'pops_its'
    UNION ALL SELECT 'pops_its_cadastro_titulos'
    UNION ALL SELECT 'pops_its_meus_registros'
    UNION ALL SELECT 'pops_its_pendente_aprovacao'
    UNION ALL SELECT 'pops_its_visualizacao'
    UNION ALL SELECT 'pops_its_solicitacoes'
    UNION ALL SELECT '5w2h'
    UNION ALL SELECT 'fluxogramas'
    UNION ALL SELECT 'melhoria_continua'
    UNION ALL SELECT 'controle_rc'
    UNION ALL SELECT 'registros_filiais'
    UNION ALL SELECT 'registros_departamentos'
    UNION ALL SELECT 'registros_fornecedores'
    UNION ALL SELECT 'registros_parametros'
    UNION ALL SELECT 'configuracoes_gerais'
    UNION ALL SELECT 'admin_usuarios'
    UNION ALL SELECT 'admin_perfis'
    UNION ALL SELECT 'admin_convites'
    UNION ALL SELECT 'admin_painel'
    UNION ALL SELECT 'profile'
    UNION ALL SELECT 'email_config'
) m
WHERE p.name = 'Administrador'
ON DUPLICATE KEY UPDATE
    can_view = VALUES(can_view),
    can_edit = VALUES(can_edit),
    can_delete = VALUES(can_delete),
    can_import = VALUES(can_import),
    can_export = VALUES(can_export);

-- Criar um perfil "Super Administrador" que mantém acesso total via código
-- (para casos onde você precisa de um admin que sempre tem acesso total)
INSERT INTO profiles (name, description, is_active, created_at, updated_at)
VALUES ('Super Administrador', 'Administrador com acesso total irrestrito (não customizável)', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    description = VALUES(description),
    updated_at = VALUES(updated_at);

-- Dar todas as permissões ao Super Administrador também
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT 
    p.id as profile_id,
    m.module_name as module,
    1 as can_view,
    1 as can_edit, 
    1 as can_delete,
    1 as can_import,
    1 as can_export
FROM profiles p
CROSS JOIN (
    SELECT 'dashboard' as module_name
    UNION ALL SELECT 'toners_cadastro'
    UNION ALL SELECT 'toners_retornados'
    UNION ALL SELECT 'homologacoes'
    UNION ALL SELECT 'amostragens'
    UNION ALL SELECT 'garantias'
    UNION ALL SELECT 'controle_descartes'
    UNION ALL SELECT 'fmea'
    UNION ALL SELECT 'pops_its'
    UNION ALL SELECT 'pops_its_cadastro_titulos'
    UNION ALL SELECT 'pops_its_meus_registros'
    UNION ALL SELECT 'pops_its_pendente_aprovacao'
    UNION ALL SELECT 'pops_its_visualizacao'
    UNION ALL SELECT 'pops_its_solicitacoes'
    UNION ALL SELECT '5w2h'
    UNION ALL SELECT 'fluxogramas'
    UNION ALL SELECT 'melhoria_continua'
    UNION ALL SELECT 'controle_rc'
    UNION ALL SELECT 'registros_filiais'
    UNION ALL SELECT 'registros_departamentos'
    UNION ALL SELECT 'registros_fornecedores'
    UNION ALL SELECT 'registros_parametros'
    UNION ALL SELECT 'configuracoes_gerais'
    UNION ALL SELECT 'admin_usuarios'
    UNION ALL SELECT 'admin_perfis'
    UNION ALL SELECT 'admin_convites'
    UNION ALL SELECT 'admin_painel'
    UNION ALL SELECT 'profile'
    UNION ALL SELECT 'email_config'
) m
WHERE p.name = 'Super Administrador'
ON DUPLICATE KEY UPDATE
    can_view = VALUES(can_view),
    can_edit = VALUES(can_edit),
    can_delete = VALUES(can_delete),
    can_import = VALUES(can_import),
    can_export = VALUES(can_export);
