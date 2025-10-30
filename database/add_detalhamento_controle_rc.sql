-- Adicionar campo "detalhamento" na tabela controle_rc
-- Data: 30/10/2025
-- Descrição: Campo de texto para detalhamento adicional do registro de RC

ALTER TABLE controle_rc 
ADD COLUMN detalhamento TEXT NULL COMMENT 'Detalhamento adicional do registro de RC' 
AFTER categoria;
