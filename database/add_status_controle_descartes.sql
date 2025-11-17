-- Adicionar coluna de status na tabela controle_descartes
-- Data: 17/11/2025

-- Adicionar coluna status
ALTER TABLE controle_descartes 
ADD COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Aguardando Descarte' 
AFTER observacoes;

-- Adicionar colunas de auditoria para mudança de status
ALTER TABLE controle_descartes
ADD COLUMN status_alterado_por INT NULL AFTER status,
ADD COLUMN status_alterado_em DATETIME NULL AFTER status_alterado_por,
ADD COLUMN justificativa_status TEXT NULL AFTER status_alterado_em;

-- Adicionar chave estrangeira
ALTER TABLE controle_descartes
ADD CONSTRAINT fk_status_alterado_por 
FOREIGN KEY (status_alterado_por) REFERENCES users(id) ON DELETE SET NULL;

-- Criar índice para melhorar performance
CREATE INDEX idx_status ON controle_descartes(status);
CREATE INDEX idx_status_alterado_em ON controle_descartes(status_alterado_em);

-- Atualizar registros existentes para status padrão (se houver)
UPDATE controle_descartes 
SET status = 'Aguardando Descarte' 
WHERE status IS NULL OR status = '';

-- Comentários nas colunas
ALTER TABLE controle_descartes 
MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Aguardando Descarte' 
COMMENT 'Status do descarte: Aguardando Descarte, Itens Descartados, Descartes Reprovados';

ALTER TABLE controle_descartes
MODIFY COLUMN status_alterado_por INT NULL 
COMMENT 'ID do usuário que alterou o status';

ALTER TABLE controle_descartes
MODIFY COLUMN status_alterado_em DATETIME NULL 
COMMENT 'Data e hora da alteração do status';

ALTER TABLE controle_descartes
MODIFY COLUMN justificativa_status TEXT NULL 
COMMENT 'Justificativa para alteração do status';
