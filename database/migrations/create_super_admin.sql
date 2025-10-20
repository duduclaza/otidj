-- =====================================================
-- CRIAR SUPER ADMINISTRADOR - ACESSO TOTAL
-- Usuário: du.claza@gmail.com
-- Data: 20/10/2024
-- =====================================================

-- 1. Criar perfil "Super Administrador" se não existir
INSERT IGNORE INTO profiles (name, description, created_at) 
VALUES (
    'Super Administrador',
    'Acesso total irrestrito ao sistema, incluindo edição de perfis administrativos',
    NOW()
);

-- 2. Obter ID do perfil Super Administrador
SET @super_admin_profile_id = (SELECT id FROM profiles WHERE name = 'Super Administrador' LIMIT 1);

-- 3. Inserir TODAS as permissões para Super Administrador
-- Lista completa de todos os módulos do sistema
INSERT IGNORE INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
VALUES
    -- Dashboard e Início
    (@super_admin_profile_id, 'dashboard', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'inicio', 1, 1, 1, 1, 1),
    
    -- Operacionais
    (@super_admin_profile_id, 'toners_cadastro', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'toners_retornados', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'amostragens', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'amostragens_2', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'homologacoes', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'garantias', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'controle_descartes', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'cadastro_maquinas', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'cadastro_pecas', 1, 1, 1, 1, 1),
    
    -- Gestão da Qualidade
    (@super_admin_profile_id, 'femea', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'pops_its_visualizacao', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'pops_its_cadastro_titulos', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'pops_its_meus_registros', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'pops_its_pendente_aprovacao', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'pops_its_logs_visualizacao', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'fluxogramas', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, '5w2h', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'auditorias', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'melhoria_continua', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'melhoria_continua_2', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'solicitacao_melhorias', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'controle_rc', 1, 1, 1, 1, 1),
    
    -- Registros
    (@super_admin_profile_id, 'registros_filiais', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'registros_departamentos', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'registros_fornecedores', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'registros_parametros', 1, 1, 1, 1, 1),
    
    -- Administrativo
    (@super_admin_profile_id, 'configuracoes_gerais', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'admin_usuarios', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'admin_perfis', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'admin_convites', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'admin_painel', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'api_powerbi', 1, 1, 1, 1, 1),
    
    -- Outros módulos
    (@super_admin_profile_id, 'profile', 1, 1, 1, 1, 1),
    (@super_admin_profile_id, 'financeiro', 1, 1, 1, 1, 1);

-- 4. Atualizar usuário du.claza@gmail.com para Super Administrador
UPDATE users 
SET 
    profile_id = @super_admin_profile_id,
    department = 'Compras',
    status = 'active'
WHERE email = 'du.claza@gmail.com';

-- 5. Verificar se o usuário foi atualizado
SELECT 
    u.id,
    u.name,
    u.email,
    p.name as perfil,
    u.department,
    u.status
FROM users u
LEFT JOIN profiles p ON u.profile_id = p.id
WHERE u.email = 'du.claza@gmail.com';

-- 6. Mostrar resumo das permissões do Super Administrador
SELECT 
    p.name as perfil,
    pp.module,
    pp.can_view,
    pp.can_edit,
    pp.can_delete,
    pp.can_import,
    pp.can_export
FROM profiles p
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id
WHERE p.name = 'Super Administrador'
ORDER BY pp.module;

-- =====================================================
-- FIM DA MIGRATION
-- =====================================================
