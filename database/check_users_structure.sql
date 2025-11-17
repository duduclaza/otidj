-- ========================================
-- VERIFICAR ESTRUTURA DA TABELA USERS
-- ========================================

-- Mostrar todas as colunas da tabela users
DESCRIBE users;

-- OU use esta alternativa:
SHOW COLUMNS FROM users;

-- OU ainda:
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'users';

-- ========================================
-- Ver dados de exemplo
-- ========================================

-- Ver as primeiras linhas para entender a estrutura
SELECT * FROM users LIMIT 5;

-- Ver usuário específico
SELECT * FROM users WHERE email = 'du.claza@gmail.com';
