-- Migration: Adicionar coluna pode_aprovar_amostragens na tabela users
-- Data: 07/10/2025
-- Descrição: Adiciona campo para controlar quais admins recebem emails de amostragens pendentes

ALTER TABLE users 
ADD COLUMN pode_aprovar_amostragens TINYINT(1) DEFAULT 0 COMMENT 'Se 1, admin receberá emails de amostragens pendentes';

-- Criar índice para melhor performance
CREATE INDEX idx_pode_aprovar_amostragens ON users(pode_aprovar_amostragens);
