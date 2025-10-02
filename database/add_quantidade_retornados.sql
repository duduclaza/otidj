-- Adicionar coluna quantidade na tabela retornados
ALTER TABLE retornados 
ADD COLUMN quantidade INT NOT NULL DEFAULT 1 AFTER modelo;

-- Atualizar registros existentes para ter quantidade = 1
UPDATE retornados 
SET quantidade = 1 
WHERE quantidade IS NULL OR quantidade = 0;

-- Verificar a alteração
SELECT id, modelo, quantidade, codigo_cliente, destino, created_at 
FROM retornados 
ORDER BY created_at DESC 
LIMIT 10;
