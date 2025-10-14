/* ===================================================================
   MIGRATION: Tornar peso_cheio e peso_vazio opcionais na tabela toners
   
   Data: 14/10/2025
   Descrição: Permite cadastrar toners sem informar peso cheio e peso vazio
              para casos onde essa informação ainda não está disponível
   =================================================================== */

-- Alterar coluna peso_cheio para permitir NULL
ALTER TABLE toners 
MODIFY COLUMN peso_cheio DECIMAL(8,2) NULL 
COMMENT 'Peso em gramas do toner cheio (opcional)';

-- Alterar coluna peso_vazio para permitir NULL
ALTER TABLE toners 
MODIFY COLUMN peso_vazio DECIMAL(8,2) NULL 
COMMENT 'Peso em gramas do toner vazio (opcional)';

-- Alterar coluna gramatura para permitir NULL (calculado apenas quando pesos existem)
ALTER TABLE toners 
MODIFY COLUMN gramatura DECIMAL(8,2) NULL 
COMMENT 'Calculado automaticamente: peso_cheio - peso_vazio';

-- Alterar coluna gramatura_por_folha para permitir NULL
ALTER TABLE toners 
MODIFY COLUMN gramatura_por_folha DECIMAL(8,4) NULL 
COMMENT 'Calculado automaticamente: gramatura / capacidade_folhas';

-- Verificar estrutura atualizada
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'toners' AND TABLE_SCHEMA = DATABASE()
-- ORDER BY ORDINAL_POSITION;
