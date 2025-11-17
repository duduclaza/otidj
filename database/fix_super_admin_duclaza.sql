-- ========================================
-- CORREÇÃO: Definir du.claza@gmail.com como ÚNICO SUPER ADMIN
-- Data: 17/11/2025
-- ========================================

-- 1. VERIFICAR SITUAÇÃO ATUAL
-- ========================================
SELECT 
    id,
    name,
    email,
    role as role_atual,
    CASE 
        WHEN role = 'super_admin' THEN '✅ CORRETO'
        WHEN role = 'admin' THEN '⚠️ PRECISA ATUALIZAR'
        ELSE '❌ INCORRETO'
    END as status
FROM users 
WHERE email = 'du.claza@gmail.com';

-- ========================================
-- 2. CORRIGIR PARA SUPER_ADMIN
-- ========================================

-- Atualizar du.claza@gmail.com para super_admin
UPDATE users 
SET role = 'super_admin' 
WHERE email = 'du.claza@gmail.com';

-- ========================================
-- 3. VERIFICAR SE FOI ATUALIZADO
-- ========================================

SELECT 
    id,
    name,
    email,
    role,
    '✅ Atualizado com sucesso!' as resultado
FROM users 
WHERE email = 'du.claza@gmail.com';

-- ========================================
-- 4. GARANTIR QUE É O ÚNICO SUPER ADMIN
-- ========================================

-- Ver TODOS os super admins no sistema
SELECT 
    id,
    name,
    email,
    role,
    created_at
FROM users 
WHERE role = 'super_admin'
ORDER BY created_at;

-- ========================================
-- 5. (OPCIONAL) REMOVER OUTROS SUPER ADMINS
-- ========================================

-- Se encontrar outros super_admins que NÃO sejam du.claza@gmail.com,
-- execute este comando para transformá-los em admins comuns:

-- UPDATE users 
-- SET role = 'admin' 
-- WHERE role = 'super_admin' 
-- AND email != 'du.claza@gmail.com';

-- ========================================
-- 6. VERIFICAÇÃO FINAL
-- ========================================

-- Ver todos admins e super admins
SELECT 
    id,
    name,
    email,
    role,
    CASE 
        WHEN role = 'super_admin' THEN '🔑 SUPER ADMIN'
        WHEN role = 'admin' THEN '👤 ADMIN'
        ELSE '❓ OUTRO'
    END as tipo
FROM users 
WHERE role IN ('admin', 'super_admin')
ORDER BY 
    FIELD(role, 'super_admin', 'admin'),
    name;

-- ========================================
-- 7. APÓS EXECUTAR, FAÇA LOGOUT E LOGIN
-- ========================================

-- IMPORTANTE:
-- 1. Execute este script no phpMyAdmin ou MySQL
-- 2. Faça LOGOUT no sistema SGQ
-- 3. Faça LOGIN novamente com du.claza@gmail.com
-- 4. A sessão será atualizada com role = 'super_admin'
-- 5. Acesse /suporte para testar

-- ========================================
-- RESULTADO ESPERADO
-- ========================================

-- du.claza@gmail.com deve ter:
-- ✅ role = 'super_admin'
-- ✅ Deve ser o ÚNICO super_admin
-- ✅ Pode acessar menu Suporte
-- ✅ Pode ver TODAS as solicitações
-- ✅ Pode gerenciar status e observações
-- ✅ NÃO vê botão "Nova Solicitação"
