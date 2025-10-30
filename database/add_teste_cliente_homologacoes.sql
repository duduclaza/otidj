-- Adicionar campo "teste_cliente" na tabela homologacoes
-- Data: 30/10/2025
-- Descrição: Campo de texto livre para registro de teste no cliente

ALTER TABLE homologacoes 
ADD COLUMN teste_cliente TEXT NULL COMMENT 'Observações sobre teste realizado no cliente' 
AFTER alerta_finalizacao;
