-- ========================================
-- CORRIGIR PROBLEMA DO SUPER ADMIN NO SUPORTE
-- Data: 17/11/2025
-- ========================================

-- 1. DIAGNÓSTICO: Verificar o usuário du.claza@gmail.com
SELECT 
    id,
    name,
    email,
    user_role,
    CASE 
        WHEN user_role = 'super_admin' THEN '✅ CORRETO'
        ELSE '❌ PRECISA CORRIGIR'
    END as status
FROM users 
WHERE email = 'du.claza@gmail.com';

-- ========================================
-- 2. SOLUÇÃO: Atualizar para super_admin
-- ========================================

-- Execute esta linha para corrigir:
UPDATE users 
SET user_role = 'super_admin' 
WHERE email = 'du.claza@gmail.com';

-- Verificar se foi atualizado:
SELECT 
    id,
    name,
    email,
    user_role as role_atual
FROM users 
WHERE email = 'du.claza@gmail.com';

-- ========================================
-- 3. VERIFICAR TODOS OS ROLES
-- ========================================

-- Ver todos admins e super admins do sistema:
SELECT 
    id,
    name,
    email,
    user_role,
    created_at
FROM users 
WHERE user_role IN ('admin', 'super_admin')
ORDER BY 
    FIELD(user_role, 'super_admin', 'admin'),
    name;

-- ========================================
-- 4. VERIFICAR SOLICITAÇÕES DE SUPORTE
-- ========================================

-- Ver todas as solicitações existentes:
SELECT 
    s.id,
    s.titulo,
    s.status,
    u.name as solicitante,
    u.email as email_solicitante,
    s.created_at
FROM suporte_solicitacoes s
LEFT JOIN users u ON s.solicitante_id = u.id
ORDER BY s.created_at DESC;

-- ========================================
-- 5. TESTE: Simular query do super admin
-- ========================================

-- Esta é a query que o super admin deveria ver:
SELECT 
    s.*,
    u.name as solicitante_nome,
    u.email as solicitante_email,
    r.name as resolvido_por_nome
FROM suporte_solicitacoes s
LEFT JOIN users u ON s.solicitante_id = u.id
LEFT JOIN users r ON s.resolvido_por = r.id
ORDER BY 
    FIELD(s.status, 'Pendente', 'Em Análise', 'Concluído'),
    s.created_at DESC;

-- ========================================
-- PROBLEMAS COMUNS E SOLUÇÕES
-- ========================================

-- Problema 1: user_role diferente de 'super_admin'
-- Solução: Execute a query UPDATE acima

-- Problema 2: Sessão desatualizada
-- Solução: Faça LOGOUT e LOGIN novamente no sistema

-- Problema 3: Nenhuma solicitação aparece
-- Solução: Verifique se existe alguma solicitação na tabela:
-- SELECT COUNT(*) FROM suporte_solicitacoes;

-- Problema 4: Erro de permissão
-- Solução: Verifique se a tabela existe:
-- SHOW TABLES LIKE 'suporte_solicitacoes';

-- ========================================
-- AFTER FIX: CHECKLIST
-- ========================================

-- [ ] 1. Executar UPDATE do user_role
-- [ ] 2. Verificar que foi atualizado
-- [ ] 3. Fazer LOGOUT no sistema
-- [ ] 4. Fazer LOGIN novamente
-- [ ] 5. Acessar /suporte
-- [ ] 6. Verificar se vê todas as solicitações

-- ========================================
-- DEBUG AVANÇADO
-- ========================================

-- Se ainda não funcionar, acesse:
-- https://seusite.com/suporte/debug
-- 
-- Isso mostrará:
-- - Dados da sessão atual
-- - User_role no banco
-- - Todas as solicitações
-- - Diagnóstico completo

-- ========================================
-- OBSERVAÇÕES IMPORTANTES
-- ========================================

-- 1. Super Admin (super_admin):
--    - Vê TODAS as solicitações
--    - Pode alterar status
--    - Pode resolver solicitações

-- 2. Admin (admin):
--    - Vê apenas suas próprias solicitações
--    - Pode criar novas solicitações
--    - NÃO pode alterar status

-- 3. Diferença importante:
--    'admin' ≠ 'super_admin'
--    Certifique-se que está exatamente 'super_admin' (sem espaços)
