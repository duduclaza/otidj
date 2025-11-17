-- Adicionar coluna de status para Controle de RC (Reclamação do Cliente)
-- Data: 17/11/2025

-- Primeiro, verificar e adicionar coluna observacoes se não existir
SET @dbname = DATABASE();
SET @tablename = "controle_rc";
SET @columnname = "observacoes";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE controle_rc ADD COLUMN observacoes TEXT NULL AFTER conclusao"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar e adicionar coluna status apenas se não existir
SET @columnname = "status";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE controle_rc ADD COLUMN status VARCHAR(100) NOT NULL DEFAULT 'Em analise' AFTER observacoes"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Adicionar colunas de auditoria para controle de alteração de status
SET @columnname2 = "status_alterado_por";
SET @preparedStatement2 = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname2)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE controle_rc ADD COLUMN status_alterado_por INT NULL AFTER status"
));
PREPARE alterIfNotExists2 FROM @preparedStatement2;
EXECUTE alterIfNotExists2;
DEALLOCATE PREPARE alterIfNotExists2;

SET @columnname3 = "status_alterado_em";
SET @preparedStatement3 = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname3)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE controle_rc ADD COLUMN status_alterado_em DATETIME NULL AFTER status_alterado_por"
));
PREPARE alterIfNotExists3 FROM @preparedStatement3;
EXECUTE alterIfNotExists3;
DEALLOCATE PREPARE alterIfNotExists3;

SET @columnname4 = "justificativa_status";
SET @preparedStatement4 = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname4)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE controle_rc ADD COLUMN justificativa_status TEXT NULL AFTER status_alterado_em"
));
PREPARE alterIfNotExists4 FROM @preparedStatement4;
EXECUTE alterIfNotExists4;
DEALLOCATE PREPARE alterIfNotExists4;

-- Adicionar comentários nas colunas
ALTER TABLE controle_rc 
MODIFY COLUMN observacoes TEXT NULL
COMMENT 'Observações gerais sobre a reclamação';

ALTER TABLE controle_rc 
MODIFY COLUMN status VARCHAR(100) NOT NULL DEFAULT 'Em analise'
COMMENT 'Status da reclamação: Em analise, Aguardando ações do fornecedor, Aguardando retorno do produto, Finalizado, Concluída';

ALTER TABLE controle_rc 
MODIFY COLUMN status_alterado_por INT NULL
COMMENT 'ID do usuário que alterou o status';

ALTER TABLE controle_rc 
MODIFY COLUMN status_alterado_em DATETIME NULL
COMMENT 'Data e hora da alteração do status';

ALTER TABLE controle_rc 
MODIFY COLUMN justificativa_status TEXT NULL
COMMENT 'Justificativa para alteração do status';

-- Comentário explicativo
-- Status disponíveis:
-- 1. Em analise (padrão)
-- 2. Aguardando ações do fornecedor
-- 3. Aguardando retorno do produto
-- 4. Finalizado
-- 5. Concluída

-- Apenas usuários com perfil admin, super_admin ou qualidade podem alterar status
