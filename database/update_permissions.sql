-- Atualizar permissões conforme solicitado
-- Remover permissões antigas de melhoria contínua
-- Adicionar permissões granulares para POPs e ITs
-- Adicionar permissão para 5W2H

-- 1. Remover permissões antigas (se existirem)
DELETE FROM profile_permissions WHERE module IN ('solicitacao_melhorias', 'melhorias_pendentes', 'historico_melhorias');

-- 2. Adicionar novas permissões granulares para POPs e ITs
-- Para perfil Administrador (assumindo id = 1)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES
(1, 'pops_its_cadastro_titulos', 1, 1, 1, 1, 1),
(1, 'pops_its_meus_registros', 1, 1, 1, 1, 1),
(1, 'pops_its_pendente_aprovacao', 1, 1, 1, 1, 1),
(1, 'pops_its_visualizacao', 1, 1, 1, 1, 1),
(1, 'pops_its_solicitacoes', 1, 1, 1, 1, 1),
(1, '5w2h', 1, 1, 1, 1, 1)
ON DUPLICATE KEY UPDATE 
    can_view = VALUES(can_view),
    can_edit = VALUES(can_edit),
    can_delete = VALUES(can_delete),
    can_import = VALUES(can_import),
    can_export = VALUES(can_export);

-- Para outros perfis existentes, dar permissões básicas
-- Analista de Qualidade
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, 'pops_its_cadastro_titulos', 1, 1, 0, 0, 1
FROM profiles p WHERE p.name = 'Analista de Qualidade'
ON DUPLICATE KEY UPDATE can_view = 1, can_edit = 1, can_export = 1;

INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, 'pops_its_meus_registros', 1, 1, 0, 0, 1
FROM profiles p WHERE p.name = 'Analista de Qualidade'
ON DUPLICATE KEY UPDATE can_view = 1, can_edit = 1, can_export = 1;

INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, 'pops_its_visualizacao', 1, 0, 0, 0, 1
FROM profiles p WHERE p.name = 'Analista de Qualidade'
ON DUPLICATE KEY UPDATE can_view = 1, can_export = 1;

INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, '5w2h', 1, 1, 0, 0, 1
FROM profiles p WHERE p.name = 'Analista de Qualidade'
ON DUPLICATE KEY UPDATE can_view = 1, can_edit = 1, can_export = 1;

-- Supervisor
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, 'pops_its_cadastro_titulos', 1, 1, 0, 0, 1
FROM profiles p WHERE p.name = 'Supervisor'
ON DUPLICATE KEY UPDATE can_view = 1, can_edit = 1, can_export = 1;

INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, 'pops_its_meus_registros', 1, 1, 0, 0, 1
FROM profiles p WHERE p.name = 'Supervisor'
ON DUPLICATE KEY UPDATE can_view = 1, can_edit = 1, can_export = 1;

INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, 'pops_its_visualizacao', 1, 0, 0, 0, 1
FROM profiles p WHERE p.name = 'Supervisor'
ON DUPLICATE KEY UPDATE can_view = 1, can_export = 1;

INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, '5w2h', 1, 1, 0, 0, 1
FROM profiles p WHERE p.name = 'Supervisor'
ON DUPLICATE KEY UPDATE can_view = 1, can_edit = 1, can_export = 1;

-- Usuário Comum - apenas visualização
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, 'pops_its_visualizacao', 1, 0, 0, 0, 0
FROM profiles p WHERE p.name = 'Usuário Comum'
ON DUPLICATE KEY UPDATE can_view = 1;

INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, '5w2h', 1, 0, 0, 0, 0
FROM profiles p WHERE p.name = 'Usuário Comum'
ON DUPLICATE KEY UPDATE can_view = 1;
