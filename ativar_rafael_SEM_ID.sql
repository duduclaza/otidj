-- =====================================================
-- ATIVAR NOTIFICAÇÕES RAFAEL - VERSÃO SEM ID
-- =====================================================
-- Esta versão NÃO usa a coluna "id" para evitar o erro
-- Use apenas EMAIL como referência
-- =====================================================

-- PASSO 1: Verificar se a coluna pode_aprovar_pops_its existe
-- =====================================================
SELECT COUNT(*) as coluna_existe
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'users'
AND COLUMN_NAME = 'pode_aprovar_pops_its';

-- Se retornar 0, execute:
-- ALTER TABLE users ADD COLUMN pode_aprovar_pops_its TINYINT(1) DEFAULT 0;


-- PASSO 2: Ver dados do Rafael (SEM usar id)
-- =====================================================
SELECT * 
FROM users 
WHERE email = 'rafael.camargo@djlocacao.com.br';


-- PASSO 3: ATIVAR notificações para Rafael
-- =====================================================
UPDATE users 
SET pode_aprovar_pops_its = 1
WHERE email = 'rafael.camargo@djlocacao.com.br'
AND role = 'admin';

-- Ver quantas linhas foram afetadas
SELECT ROW_COUNT() as linhas_atualizadas;


-- PASSO 4: Verificar se funcionou
-- =====================================================
SELECT 
    name,
    email,
    role,
    pode_aprovar_pops_its,
    status,
    'ATUALIZADO!' as resultado
FROM users 
WHERE email = 'rafael.camargo@djlocacao.com.br';


-- PASSO 5: Ver TODOS os admins (SEM id)
-- =====================================================
SELECT 
    name,
    email,
    role,
    pode_aprovar_pops_its,
    status,
    CASE 
        WHEN pode_aprovar_pops_its = 1 THEN '✅ Receberá notificações'
        ELSE '❌ NÃO receberá notificações'
    END as status_notificacao
FROM users 
WHERE role = 'admin' 
AND status = 'active'
ORDER BY pode_aprovar_pops_its DESC, name;


-- PASSO 6: OPCIONAL - Ativar TODOS os admins
-- =====================================================
-- Descomente se quiser ativar para todos:
/*
UPDATE users 
SET pode_aprovar_pops_its = 1
WHERE role = 'admin' 
AND status = 'active';

SELECT 
    name,
    email,
    pode_aprovar_pops_its,
    'TODOS ATIVADOS!' as resultado
FROM users 
WHERE role = 'admin' 
AND status = 'active';
*/


-- PASSO 7: Verificar últimas notificações (SEM id)
-- =====================================================
SELECT 
    n.title,
    n.message,
    n.type,
    n.created_at,
    u.name as usuario,
    u.email
FROM notifications n
LEFT JOIN users u ON n.user_id = u.email -- Ajuste aqui se necessário
WHERE n.type LIKE '%pops_its%'
ORDER BY n.created_at DESC
LIMIT 10;


-- =====================================================
-- RESUMO FINAL
-- =====================================================
SELECT 
    'Rafael Camargo' as usuario,
    email,
    role,
    status,
    pode_aprovar_pops_its,
    CASE 
        WHEN pode_aprovar_pops_its = 1 AND role = 'admin' AND status = 'active' 
        THEN '✅ CONFIGURADO - Receberá emails'
        WHEN pode_aprovar_pops_its = 0 OR pode_apovar_pops_its IS NULL
        THEN '❌ DESATIVADO - Execute PASSO 3'
        WHEN role != 'admin'
        THEN '⚠️ NÃO É ADMIN'
        WHEN status != 'active'
        THEN '⚠️ USUÁRIO INATIVO'
        ELSE '⚠️ VERIFICAR'
    END as diagnostico
FROM users 
WHERE email = 'rafael.camargo@djlocacao.com.br';
