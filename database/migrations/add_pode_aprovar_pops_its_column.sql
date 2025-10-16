-- Adicionar coluna para permissão de aprovar POPs e ITs
-- Esta coluna indica se um administrador deve receber emails de pendências

-- Verificar e adicionar coluna apenas se não existir
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'users' 
               AND COLUMN_NAME = 'pode_aprovar_pops_its');

SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE users ADD COLUMN pode_aprovar_pops_its TINYINT(1) DEFAULT 0 COMMENT ''Indica se o administrador recebe emails de POPs/ITs pendentes''',
    'SELECT ''Coluna pode_aprovar_pops_its já existe'' AS mensagem');

PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Atualizar admins existentes para terem a permissão por padrão
UPDATE users 
SET pode_aprovar_pops_its = 1 
WHERE role = 'admin' AND pode_aprovar_pops_its = 0;
