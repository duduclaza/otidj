-- Adicionar coluna quantidade na tabela toners_retornados
ALTER TABLE toners_retornados 
ADD COLUMN quantidade INT NOT NULL DEFAULT 1 AFTER modelo;

-- Atualizar registros existentes para ter quantidade = 1
UPDATE toners_retornados 
SET quantidade = 1 
WHERE quantidade IS NULL OR quantidade = 0;

-- Verificar a alteração
SELECT id, modelo, quantidade, motivo_retorno, created_at 
FROM toners_retornados 
ORDER BY created_at DESC 
LIMIT 10;
