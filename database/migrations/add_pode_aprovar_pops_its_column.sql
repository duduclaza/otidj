-- Adicionar coluna para permissão de aprovar POPs e ITs
-- Esta coluna indica se um administrador deve receber emails de pendências

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS pode_aprovar_pops_its TINYINT(1) DEFAULT 0 
COMMENT 'Indica se o administrador recebe emails de POPs/ITs pendentes';

-- Atualizar admins existentes para terem a permissão por padrão
UPDATE users 
SET pode_aprovar_pops_its = 1 
WHERE role = 'admin';
