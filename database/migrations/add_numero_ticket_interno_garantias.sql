-- Migration: Adicionar coluna numero_ticket_interno na tabela garantias
-- Data: 2025-10-08
-- Descrição: Adiciona campo para armazenar o número do ticket interno

ALTER TABLE garantias
ADD COLUMN numero_ticket_interno VARCHAR(100) NULL AFTER numero_ticket_os;

-- Adicionar índice para busca
ALTER TABLE garantias
ADD INDEX idx_numero_ticket_interno (numero_ticket_interno);
