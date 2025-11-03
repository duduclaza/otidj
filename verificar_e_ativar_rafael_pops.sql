-- =====================================================
-- VERIFICAR E ATIVAR NOTIFICAÇÕES POPs/ITs PARA RAFAEL
-- =====================================================
-- Usuário: rafael.camargo@djlocacao.com.br
-- Data: 03/11/2024
-- Problema: Não está recebendo notificações de POPs/ITs pendentes
-- =====================================================

-- PASSO 1: Verificar se a coluna pode_aprovar_pops_its existe
-- =====================================================
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'users'
AND COLUMN_NAME = 'pode_aprovar_pops_its';

-- Se a consulta acima NÃO retornar nenhuma linha, execute:
-- ALTER TABLE users ADD COLUMN pode_aprovar_pops_its TINYINT(1) DEFAULT 0 AFTER role;


-- PASSO 2: Verificar dados do Rafael Camargo
-- =====================================================
SELECT 
    id,
    name,
    email,
    role,
    pode_aprovar_pops_its,
    status,
    created_at
FROM users 
WHERE email = 'rafael.camargo@djlocacao.com.br';


-- PASSO 3: Verificar TODOS os admins com permissão
-- =====================================================
SELECT 
    id,
    name,
    email,
    role,
    pode_aprovar_pops_its,
    status
FROM users 
WHERE role = 'admin' 
AND status = 'active'
ORDER BY pode_aprovar_pops_its DESC, name;


-- PASSO 4: ATIVAR notificações para Rafael
-- =====================================================
-- EXECUTE ESTE COMANDO APENAS SE:
-- 1. A coluna pode_aprovar_pops_its existe
-- 2. O Rafael tem role = 'admin'
-- 3. O valor atual de pode_aprovar_pops_its for 0 ou NULL

UPDATE users 
SET pode_aprovar_pops_its = 1
WHERE email = 'rafael.camargo@djlocacao.com.br'
AND role = 'admin';


-- PASSO 5: Verificar se a atualização funcionou
-- =====================================================
SELECT 
    id,
    name,
    email,
    role,
    pode_aprovar_pops_its,
    status,
    'ATUALIZADO!' as resultado
FROM users 
WHERE email = 'rafael.camargo@djlocacao.com.br';


-- PASSO 6: Verificar configurações de email
-- =====================================================
SELECT 
    id,
    name,
    email,
    role,
    pode_aprovar_pops_its,
    status,
    CASE 
        WHEN email IS NULL OR email = '' THEN '❌ Email vazio'
        WHEN email NOT LIKE '%@%' THEN '❌ Email inválido'
        WHEN status != 'active' THEN '❌ Usuário inativo'
        WHEN role != 'admin' THEN '⚠️ Não é admin'
        WHEN pode_aprovar_pops_its != 1 THEN '❌ Permissão desativada'
        ELSE '✅ Configurado corretamente'
    END as diagnostico
FROM users 
WHERE email = 'rafael.camargo@djlocacao.com.br';


-- =====================================================
-- DIAGNÓSTICO COMPLETO DE NOTIFICAÇÕES
-- =====================================================

-- Verificar se há registros pendentes
SELECT COUNT(*) as total_pendentes
FROM pops_its_registros
WHERE status = 'PENDENTE';

-- Verificar últimas notificações criadas
SELECT 
    n.id,
    n.user_id,
    u.name,
    u.email,
    n.title,
    n.message,
    n.type,
    n.is_read,
    n.created_at
FROM notifications n
LEFT JOIN users u ON n.user_id = u.id
WHERE n.type LIKE '%pops_its%'
ORDER BY n.created_at DESC
LIMIT 10;


-- =====================================================
-- SCRIPT DE EMERGÊNCIA: Ativar TODOS os admins ativos
-- =====================================================
-- Use este script se quiser ativar notificações para TODOS os administradores:
/*
UPDATE users 
SET pode_aprovar_pops_its = 1
WHERE role = 'admin' 
AND status = 'active';

SELECT 
    id,
    name,
    email,
    pode_aprovar_pops_its,
    'ATIVADO!' as status_atualizacao
FROM users 
WHERE role = 'admin' 
AND status = 'active';
*/


-- =====================================================
-- TESTE: Criar notificação manualmente para Rafael
-- =====================================================
-- Execute apenas para testar se o sistema de notificações está funcionando:
/*
INSERT INTO notifications (user_id, title, message, type, related_type, related_id, created_at)
SELECT 
    id,
    '📋 TESTE - Notificação POPs/ITs',
    'Este é um teste manual de notificação. Se você receber este email, o sistema está funcionando!',
    'pops_its_pendente',
    'teste',
    999,
    NOW()
FROM users 
WHERE email = 'rafael.camargo@djlocacao.com.br';
*/


-- =====================================================
-- RESUMO DE VERIFICAÇÃO
-- =====================================================
SELECT 
    'RESUMO DO DIAGNÓSTICO' as secao,
    '' as detalhe
UNION ALL
SELECT 
    '1. Coluna existe?',
    CASE 
        WHEN EXISTS(
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'users'
            AND COLUMN_NAME = 'pode_aprovar_pops_its'
        ) THEN '✅ SIM'
        ELSE '❌ NÃO - Precisa criar'
    END
UNION ALL
SELECT 
    '2. Rafael é admin?',
    CASE 
        WHEN EXISTS(
            SELECT 1 FROM users 
            WHERE email = 'rafael.camargo@djlocacao.com.br' 
            AND role = 'admin'
        ) THEN '✅ SIM'
        ELSE '❌ NÃO'
    END
UNION ALL
SELECT 
    '3. Rafael está ativo?',
    CASE 
        WHEN EXISTS(
            SELECT 1 FROM users 
            WHERE email = 'rafael.camargo@djlocacao.com.br' 
            AND status = 'active'
        ) THEN '✅ SIM'
        ELSE '❌ NÃO'
    END
UNION ALL
SELECT 
    '4. Permissão ativada?',
    CASE 
        WHEN EXISTS(
            SELECT 1 FROM users 
            WHERE email = 'rafael.camargo@djlocacao.com.br' 
            AND pode_aprovar_pops_its = 1
        ) THEN '✅ SIM'
        ELSE '❌ NÃO - EXECUTAR PASSO 4'
    END;
