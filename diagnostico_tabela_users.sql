-- =====================================================
-- DIAGNÓSTICO: Estrutura da Tabela USERS
-- =====================================================
-- Use este script para descobrir a estrutura exata da tabela
-- =====================================================

-- PASSO 1: Ver TODAS as colunas da tabela users
-- =====================================================
DESCRIBE users;

-- OU alternativamente:
SHOW COLUMNS FROM users;


-- PASSO 2: Ver estrutura completa
-- =====================================================
SHOW CREATE TABLE users;


-- PASSO 3: Listar todas as colunas disponíveis
-- =====================================================
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_KEY,
    COLUMN_DEFAULT,
    EXTRA
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'users'
ORDER BY ORDINAL_POSITION;


-- PASSO 4: Buscar Rafael sem usar "id"
-- =====================================================
-- Tente esta query primeiro para ver quais colunas existem:
SELECT * 
FROM users 
WHERE email = 'rafael.camargo@djlocacao.com.br'
LIMIT 1;


-- PASSO 5: Se a coluna de ID tiver outro nome
-- =====================================================
-- Possíveis nomes alternativos:
-- user_id, userId, ID, User_ID, pk_id, etc.

-- Tente descobrir qual é a primary key:
SELECT 
    COLUMN_NAME,
    COLUMN_KEY
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'users'
AND COLUMN_KEY = 'PRI';
