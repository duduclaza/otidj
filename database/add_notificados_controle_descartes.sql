-- Adicionar coluna para armazenar usuários notificados
-- Data: 17/11/2025

-- Verificar e adicionar coluna notificar_usuarios apenas se não existir
SET @dbname = DATABASE();
SET @tablename = "controle_descartes";
SET @columnname = "notificar_usuarios";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE controle_descartes ADD COLUMN notificar_usuarios TEXT NULL AFTER observacoes"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Adicionar comentário na coluna (sempre executa, não dá erro se já existir)
ALTER TABLE controle_descartes 
MODIFY COLUMN notificar_usuarios TEXT NULL 
COMMENT 'IDs dos usuários separados por vírgula que devem ser notificados';

-- Comentário explicativo
-- A coluna armazena os IDs em formato: "1,5,12,23"
-- Isso permite selecionar manualmente quem deve receber email
