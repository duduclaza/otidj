/* TESTE 1: Verificar se coluna notificacoes_ativadas existe */
SELECT 
    '✅ TESTE 1: Verificar coluna' as teste,
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'users'
AND COLUMN_NAME = 'notificacoes_ativadas';
