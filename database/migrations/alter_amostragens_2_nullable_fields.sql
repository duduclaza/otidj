-- Migration: Permitir NULL nos campos de teste de amostragens_2
-- Data: 2025-10-07
-- Descrição: Os campos de quantidade testada/aprovada/reprovada agora são opcionais
-- pois uma pessoa pode cadastrar a amostragem e outra adicionar os resultados depois
-- Também adiciona campo de observações

-- Alterar campos para permitir NULL
ALTER TABLE amostragens_2 
    MODIFY COLUMN quantidade_testada INT NULL DEFAULT NULL,
    MODIFY COLUMN quantidade_aprovada INT NULL DEFAULT NULL,
    MODIFY COLUMN quantidade_reprovada INT NULL DEFAULT NULL;

-- Adicionar campo de observações se não existir
ALTER TABLE amostragens_2 
    ADD COLUMN IF NOT EXISTS observacoes TEXT NULL AFTER status_final;

-- Log da migration
INSERT INTO migrations_log (migration_name, executed_at) 
VALUES ('alter_amostragens_2_nullable_fields', NOW())
ON DUPLICATE KEY UPDATE executed_at = NOW();
