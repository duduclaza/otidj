-- =====================================================
-- SCRIPT PARA CORRIGIR PERMISSÕES DE MELHORIA CONTÍNUA
-- E ADICIONAR COLUNA FIRST_ACCESS
-- Execute este script diretamente no banco de dados
-- =====================================================

-- Adicionar coluna first_access na tabela users (se não existir)
ALTER TABLE users ADD COLUMN first_access TINYINT(1) DEFAULT 0;

-- Atualizar usuários existentes para não serem primeiro acesso
UPDATE users SET first_access = 0 WHERE first_access IS NULL;

-- 1. Verificar perfis existentes
SELECT id, name, description FROM profiles ORDER BY name;

-- 2. Verificar permissões atuais de Melhoria Contínua
SELECT 
    p.name as perfil,
    pp.module,
    pp.can_view,
    pp.can_edit,
    pp.can_delete
FROM profiles p
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id
WHERE pp.module IN ('melhoria_continua', 'solicitacao_melhorias', 'melhorias_pendentes', 'historico_melhorias')
ORDER BY p.name, pp.module;

-- 3. LIMPAR permissões antigas de Melhoria Contínua
DELETE FROM profile_permissions 
WHERE module IN ('melhoria_continua', 'solicitacao_melhorias', 'melhorias_pendentes', 'historico_melhorias');

-- 4. INSERIR permissões corretas para cada perfil

-- ADMINISTRADOR (assumindo que o ID é 1, ajuste se necessário)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES
(1, 'melhoria_continua', 1, 1, 1, 1, 1),
(1, 'solicitacao_melhorias', 1, 1, 1, 1, 1),
(1, 'melhorias_pendentes', 1, 1, 1, 1, 1),
(1, 'historico_melhorias', 1, 1, 1, 1, 1);

-- USUÁRIO COMUM (assumindo que o ID é 2, ajuste se necessário)
-- Apenas visualização das solicitações
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES
(2, 'melhoria_continua', 1, 0, 0, 0, 0),
(2, 'solicitacao_melhorias', 1, 0, 0, 0, 0),
(2, 'melhorias_pendentes', 0, 0, 0, 0, 0),  -- SEM ACESSO
(2, 'historico_melhorias', 0, 0, 0, 0, 0);  -- SEM ACESSO

-- SUPERVISOR (assumindo que o ID é 3, ajuste se necessário)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES
(3, 'melhoria_continua', 1, 1, 0, 0, 1),
(3, 'solicitacao_melhorias', 1, 1, 0, 0, 1),
(3, 'melhorias_pendentes', 1, 1, 0, 0, 1),
(3, 'historico_melhorias', 1, 0, 0, 0, 1);

-- OPERADOR DE TONERS (assumindo que o ID é 4, ajuste se necessário)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES
(4, 'melhoria_continua', 1, 0, 0, 0, 0),
(4, 'solicitacao_melhorias', 1, 0, 0, 0, 0),
(4, 'melhorias_pendentes', 0, 0, 0, 0, 0),  -- SEM ACESSO
(4, 'historico_melhorias', 0, 0, 0, 0, 0);  -- SEM ACESSO

-- ANALISTA DE QUALIDADE (assumindo que o ID é 5, ajuste se necessário)
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES
(5, 'melhoria_continua', 1, 1, 0, 0, 1),
(5, 'solicitacao_melhorias', 1, 1, 0, 0, 1),
(5, 'melhorias_pendentes', 1, 1, 0, 0, 1),
(5, 'historico_melhorias', 1, 0, 0, 0, 1);

-- =====================================================
-- QUERIES PARA PERSONALIZAR PERMISSÕES ESPECÍFICAS
-- =====================================================

-- Para REMOVER acesso a "Melhorias Pendentes" de um perfil específico:
-- UPDATE profile_permissions SET can_view = 0 WHERE profile_id = [ID_DO_PERFIL] AND module = 'melhorias_pendentes';

-- Para REMOVER acesso a "Histórico de Melhorias" de um perfil específico:
-- UPDATE profile_permissions SET can_view = 0 WHERE profile_id = [ID_DO_PERFIL] AND module = 'historico_melhorias';

-- Para REMOVER acesso a "Solicitação de Melhorias" de um perfil específico:
-- UPDATE profile_permissions SET can_view = 0 WHERE profile_id = [ID_DO_PERFIL] AND module = 'solicitacao_melhorias';

-- =====================================================
-- VERIFICAÇÃO FINAL
-- =====================================================

-- Verificar se as permissões foram aplicadas corretamente
SELECT 
    p.name as perfil,
    pp.module,
    pp.can_view as visualizar,
    pp.can_edit as editar,
    pp.can_delete as excluir
FROM profiles p
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id
WHERE pp.module IN ('melhoria_continua', 'solicitacao_melhorias', 'melhorias_pendentes', 'historico_melhorias')
ORDER BY p.name, pp.module;

-- =====================================================
-- EXEMPLO DE USO PRÁTICO
-- =====================================================

-- Se você quiser que o "Usuário Comum" (ID 2) veja APENAS "Solicitação de Melhorias":
/*
UPDATE profile_permissions SET can_view = 1 WHERE profile_id = 2 AND module = 'solicitacao_melhorias';
UPDATE profile_permissions SET can_view = 0 WHERE profile_id = 2 AND module = 'melhorias_pendentes';
UPDATE profile_permissions SET can_view = 0 WHERE profile_id = 2 AND module = 'historico_melhorias';
*/

-- Se você quiser que um perfil veja APENAS "Histórico de Melhorias":
/*
UPDATE profile_permissions SET can_view = 0 WHERE profile_id = [ID] AND module = 'solicitacao_melhorias';
UPDATE profile_permissions SET can_view = 0 WHERE profile_id = [ID] AND module = 'melhorias_pendentes';
UPDATE profile_permissions SET can_view = 1 WHERE profile_id = [ID] AND module = 'historico_melhorias';
*/
