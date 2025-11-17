-- =====================================================
-- VERIFICAR USUÁRIOS PARA NC
-- =====================================================

-- 1. Ver estrutura da tabela users
DESCRIBE users;

-- 2. Verificar se coluna 'active' existe
SHOW COLUMNS FROM users LIKE 'active';

-- 3. Ver todos os usuários
SELECT id, name, email, active 
FROM users 
ORDER BY name;

-- 4. Ver quantos usuários ativos
SELECT COUNT(*) as total_ativos 
FROM users 
WHERE active = 1;

-- 5. Se coluna 'active' não existir, criar
-- ALTER TABLE users ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1;

-- 6. Se existir mas estiver zerada, ativar usuários
-- UPDATE users SET active = 1;

-- 7. Query que o sistema usa
SELECT id, name, email 
FROM users 
WHERE active = 1 
ORDER BY name;

-- =====================================================
-- CORREÇÃO SE NECESSÁRIO
-- =====================================================

-- Se a coluna 'active' não existir:
/*
ALTER TABLE users 
ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1 
AFTER email;
*/

-- Se todos usuários estiverem inativos:
/*
UPDATE users 
SET active = 1 
WHERE id IN (SELECT id FROM users);
*/

-- =====================================================
