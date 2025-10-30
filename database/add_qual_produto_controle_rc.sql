-- Adicionar campo "qual_produto" na tabela controle_rc
-- Data: 30/10/2025
-- Descrição: Campo para identificar tipo de produto (Suprimentos, Atendimento, Atendimento Técnico, Equipamento)

ALTER TABLE controle_rc 
ADD COLUMN qual_produto VARCHAR(50) NULL COMMENT 'Tipo de produto: Suprimentos, Atendimento, Atendimento Técnico, Equipamento' 
AFTER detalhamento;
