-- Verificar estrutura da tabela users
DESCRIBE users;

-- Verificar se a coluna email existe
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'users'
AND COLUMN_NAME = 'email';

-- Verificar usuários com email
SELECT id, name, email, status
FROM users
WHERE email IS NOT NULL AND email != ''
ORDER BY id;

-- Verificar usuários SEM email
SELECT id, name, email, status
FROM users
WHERE email IS NULL OR email = ''
ORDER BY id;

-- Contar total de usuários
SELECT 
    COUNT(*) as total_usuarios,
    SUM(CASE WHEN email IS NOT NULL AND email != '' THEN 1 ELSE 0 END) as com_email,
    SUM(CASE WHEN email IS NULL OR email = '' THEN 1 ELSE 0 END) as sem_email
FROM users;
