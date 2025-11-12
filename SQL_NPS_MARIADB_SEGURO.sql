-- ============================================
-- SCRIPT: Permissões NPS - MariaDB COMPATÍVEL
-- Versão: 2.7.1 MARIADB
-- Data: 2024
-- Descrição: Adiciona permissões NPS sem usar LIMIT em subqueries
-- ============================================

-- 1. Limpar permissões NPS existentes
DELETE FROM profile_permissions WHERE module = 'nps';

-- 2. Inserir permissões básicas para TODOS os perfis
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT 
    id AS profile_id,
    'nps' AS module,
    1 AS can_view,      -- Todos podem visualizar
    0 AS can_edit,      -- Inicialmente ninguém edita
    0 AS can_delete,    -- Inicialmente ninguém exclui
    0 AS can_import,    -- NPS não tem importação
    1 AS can_export     -- Todos podem exportar CSV
FROM profiles;

-- 3. Dar permissões completas para Administrador (método alternativo)
-- Usando JOIN em vez de subquery com LIMIT
UPDATE profile_permissions pp
JOIN profiles p ON pp.profile_id = p.id
SET pp.can_edit = 1, pp.can_delete = 1
WHERE pp.module = 'nps' 
AND p.name = 'Administrador';

-- 4. Verificar resultado
SELECT 
    p.name AS Perfil,
    pp.module AS Modulo,
    pp.can_view AS Ver,
    pp.can_edit AS Editar,
    pp.can_delete AS Excluir,
    pp.can_export AS Exportar
FROM profile_permissions pp
JOIN profiles p ON pp.profile_id = p.id
WHERE pp.module = 'nps'
ORDER BY p.name;

-- 5. Mensagem final
SELECT 'Permissões NPS criadas com sucesso! Versão MariaDB compatível.' AS status;
