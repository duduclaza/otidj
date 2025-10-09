-- ===== DIAGNOSTICO E CORRECAO - Notificacoes POPs/ITs =====

-- 1. VERIFICAR SE A COLUNA EXISTE
SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users'
  AND COLUMN_NAME = 'pode_aprovar_pops_its';

-- Se retornar VAZIO, a coluna NÃO EXISTE - Execute o passo 2
-- Se retornar 1 linha, a coluna EXISTE - Pule para o passo 3

-- ============================================
-- 2. CRIAR A COLUNA (se não existir)
-- ============================================

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS pode_aprovar_pops_its TINYINT(1) DEFAULT 0 
COMMENT 'Indica se o administrador recebe emails de POPs/ITs pendentes';

-- Atualizar TODOS os admins para terem a permissão por padrão
UPDATE users 
SET pode_aprovar_pops_its = 1 
WHERE role = 'admin';

-- ============================================
-- 3. VERIFICAR QUAIS ADMINS TÊM A PERMISSÃO
-- ============================================

SELECT 
    id,
    name,
    email,
    role,
    pode_aprovar_pops_its,
    status,
    CASE 
        WHEN pode_aprovar_pops_its = 1 THEN '✅ SIM - Receberá notificações'
        ELSE '❌ NÃO - Não receberá notificações'
    END as 'Recebe Notificações?'
FROM users
WHERE role = 'admin'
ORDER BY pode_aprovar_pops_its DESC, name;

-- ============================================
-- 4. ATIVAR PERMISSÃO PARA ADMINISTRADOR ESPECÍFICO
-- ============================================

-- SUBSTITUA "Clayton" pelo nome do admin que deve receber notificações
-- UPDATE users 
-- SET pode_aprovar_pops_its = 1 
-- WHERE name LIKE '%Clayton%' AND role = 'admin';

-- OU ative para TODOS os admins de uma vez:
UPDATE users 
SET pode_aprovar_pops_its = 1 
WHERE role = 'admin';

-- ============================================
-- 5. VERIFICAR NOTIFICAÇÕES RECENTES
-- ============================================

SELECT 
    n.id,
    n.title,
    n.message,
    n.type,
    n.created_at,
    n.read_at,
    u.name as 'Admin',
    u.email,
    CASE 
        WHEN n.read_at IS NULL THEN '🔴 NÃO LIDA'
        ELSE '✅ LIDA'
    END as 'Status'
FROM notifications n
LEFT JOIN users u ON n.user_id = u.id
WHERE n.type = 'pops_its_pendente'
ORDER BY n.created_at DESC
LIMIT 20;

-- ============================================
-- 6. VERIFICAR POPs/ITs PENDENTES
-- ============================================

SELECT 
    r.id,
    r.versao,
    r.status,
    r.created_at,
    t.titulo,
    t.tipo,
    u.name as 'Criado Por',
    CASE 
        WHEN r.status = 'Pendente' THEN '⏳ PENDENTE - Deve notificar'
        WHEN r.status = 'Aprovado' THEN '✅ Aprovado'
        ELSE '❌ Reprovado'
    END as 'Status Detalhado'
FROM pops_its_registros r
LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
LEFT JOIN users u ON r.criado_por = u.id
WHERE r.status = 'Pendente'
ORDER BY r.created_at DESC;

-- ============================================
-- 7. TESTE: Verificar se admin receberia notificação
-- ============================================

-- Esta query mostra quem DEVERIA receber emails
SELECT 
    u.id,
    u.name,
    u.email,
    u.role,
    u.pode_aprovar_pops_its,
    u.status,
    '📧 Este admin RECEBERÁ emails de POPs/ITs pendentes' as resultado
FROM users u
WHERE u.role = 'admin'
  AND u.pode_aprovar_pops_its = 1
  AND u.status = 'active';

-- Se retornar VAZIO, NENHUM admin receberá emails!
-- Neste caso, execute o passo 4 novamente

-- ============================================
-- RESUMO DO PROBLEMA E SOLUÇÃO
-- ============================================

/*
PROBLEMA:
1. Coluna pode_aprovar_pops_its pode não existir
2. Admins podem ter valor 0 (desativado)
3. Checkbox não está salvando

SOLUÇÃO:
1. Executar passo 2 (criar coluna se não existe)
2. Executar passo 4 (ativar para todos admins)
3. Executar passo 3 (verificar quem tem permissão)

APÓS EXECUTAR:
- Todos admins receberão emails de POPs/ITs pendentes
- Checkbox funcionará corretamente na tela
- Sistema enviará notificações automaticamente
*/

-- ============================================
-- 8. LIMPAR CACHE (se necessário)
-- ============================================

-- Se mesmo após executar ainda não funcionar:
-- Desmarque e marque novamente o checkbox na tela do usuário
-- E salve
