-- Adicionar coluna para armazenar usuários notificados
-- Data: 17/11/2025

-- Adicionar coluna para IDs dos usuários que devem ser notificados
ALTER TABLE controle_descartes 
ADD COLUMN notificar_usuarios TEXT NULL 
AFTER observacoes;

-- Adicionar comentário na coluna
ALTER TABLE controle_descartes 
MODIFY COLUMN notificar_usuarios TEXT NULL 
COMMENT 'IDs dos usuários separados por vírgula que devem ser notificados';

-- Comentário explicativo
-- A coluna armazena os IDs em formato: "1,5,12,23"
-- Isso permite selecionar manualmente quem deve receber email
