-- ============================================
-- ADICIONAR PERMISSÃO DE DASHBOARD PARA TODOS OS PERFIS
-- Data: 07/11/2025
-- ============================================

-- Verificar quais perfis existem
SELECT id, name FROM profiles ORDER BY id;

-- Adicionar permissão de DASHBOARD (VIEW) para cada perfil
-- Nota: Ajuste os IDs dos perfis conforme sua base de dados

-- PERFIL 1: Administrador (geralmente já tem tudo)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES (1, 'dashboard', 1, 1, 1, 1, 1)
ON DUPLICATE KEY UPDATE 
    can_view = 1, 
    can_edit = 1, 
    can_delete = 1, 
    can_import = 1, 
    can_export = 1;

-- PERFIL 2: Usuário Comum
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES (2, 'dashboard', 1, 0, 0, 0, 0)
ON DUPLICATE KEY UPDATE can_view = 1;

-- PERFIL 3: Supervisor
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES (3, 'dashboard', 1, 0, 0, 0, 0)
ON DUPLICATE KEY UPDATE can_view = 1;

-- PERFIL 4: Operador de Toners
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES (4, 'dashboard', 1, 0, 0, 0, 0)
ON DUPLICATE KEY UPDATE can_view = 1;

-- PERFIL 5: Analista de Qualidade
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES (5, 'dashboard', 1, 0, 0, 0, 0)
ON DUPLICATE KEY UPDATE can_view = 1;

-- Se houver mais perfis, adicione aqui
-- INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
-- VALUES (6, 'dashboard', 1, 0, 0, 0, 0)
-- ON DUPLICATE KEY UPDATE can_view = 1;

-- Verificar se as permissões foram adicionadas corretamente
SELECT 
    p.id,
    p.name as perfil,
    pp.module,
    pp.can_view,
    pp.can_edit,
    pp.can_delete
FROM profiles p
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id AND pp.module = 'dashboard'
ORDER BY p.id;

-- ============================================
-- ADICIONAR PERMISSÃO MELHORIAS (se ainda não existir)
-- ============================================

-- PERFIL 1: Administrador
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES (1, 'melhoria_continua_2', 1, 1, 1, 1, 1)
ON DUPLICATE KEY UPDATE 
    can_view = 1, 
    can_edit = 1, 
    can_delete = 1, 
    can_import = 1, 
    can_export = 1;

-- PERFIL 2: Usuário Comum
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES (2, 'melhoria_continua_2', 1, 1, 0, 0, 0)
ON DUPLICATE KEY UPDATE can_view = 1, can_edit = 1;

-- PERFIL 3: Supervisor
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES (3, 'melhoria_continua_2', 1, 1, 0, 0, 1)
ON DUPLICATE KEY UPDATE can_view = 1, can_edit = 1, can_export = 1;

-- PERFIL 4: Operador de Toners
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES (4, 'melhoria_continua_2', 1, 0, 0, 0, 0)
ON DUPLICATE KEY UPDATE can_view = 1;

-- PERFIL 5: Analista de Qualidade
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES (5, 'melhoria_continua_2', 1, 1, 0, 0, 1)
ON DUPLICATE KEY UPDATE can_view = 1, can_edit = 1, can_export = 1;

-- Verificar permissões de Melhoria Contínua 2.0
SELECT 
    p.id,
    p.name as perfil,
    pp.module,
    pp.can_view,
    pp.can_edit,
    pp.can_delete
FROM profiles p
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id AND pp.module = 'melhoria_continua_2'
ORDER BY p.id;

-- ============================================
-- LIMPAR CACHE DE PERMISSÕES (OPCIONAL)
-- Se houver sessões ativas, elas precisarão fazer logout/login
-- ============================================

-- Não há tabela de cache de sessões no seu sistema,
-- então os usuários precisarão fazer logout e login novamente
-- para as novas permissões entrarem em vigor

-- ============================================
-- FIM DO SCRIPT
-- ============================================
