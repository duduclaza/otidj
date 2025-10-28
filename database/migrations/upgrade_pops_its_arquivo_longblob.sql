-- =====================================================
-- Migration: Upgrade POPs e ITs arquivo para LONGBLOB
-- Data: 27/10/2025
-- Descrição: Aumenta capacidade da coluna arquivo para suportar arquivos de até 50MB (PPT/PPTX)
-- =====================================================

-- MEDIUMBLOB: máximo 16MB (16.777.215 bytes)
-- LONGBLOB: máximo 4GB (4.294.967.295 bytes)

-- Alterar coluna arquivo para LONGBLOB
ALTER TABLE pops_its_registros 
MODIFY COLUMN arquivo LONGBLOB NOT NULL COMMENT 'Arquivo do documento (suporta até 50MB para PPT/PPTX)';

-- Verificar alteração
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH
FROM 
    INFORMATION_SCHEMA.COLUMNS
WHERE 
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'pops_its_registros'
    AND COLUMN_NAME = 'arquivo';

-- Mensagem de sucesso
SELECT 'Migration concluída com sucesso! Coluna arquivo agora suporta até 4GB (LONGBLOB)' as status;
