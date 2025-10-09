/* ===== MIGRATION: Adicionar coluna notificacoes_ativadas na tabela users ===== */
/* Data: 09/10/2025 */
/* Descrição: Permite ativar/desativar o sino de notificações para cada usuário */
/* EXECUTAR ESTE ARQUIVO NO PHPMYADMIN */

/* Verificar se a coluna já existe antes de adicionar */
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'notificacoes_ativadas'
);

/* Adicionar coluna apenas se não existir */
SET @sql = IF(
    @column_exists = 0,
    'ALTER TABLE users ADD COLUMN notificacoes_ativadas TINYINT(1) NOT NULL DEFAULT 1 COMMENT ''1 = Notificações ativadas, 0 = Notificações desativadas'' AFTER status',
    'SELECT ''Coluna notificacoes_ativadas já existe'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

/* Verificar resultado */
SELECT 
    'Migration executada com sucesso!' as status,
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'users'
AND COLUMN_NAME = 'notificacoes_ativadas';
