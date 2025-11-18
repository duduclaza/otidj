-- Correção básica da tabela homologacoes_historico
-- Execute linha por linha se necessário

-- 1. Adicionar colunas (ignore erros se já existirem)
ALTER TABLE homologacoes_historico ADD COLUMN data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE homologacoes_historico ADD COLUMN usuario_nome VARCHAR(255) NULL;
ALTER TABLE homologacoes_historico ADD COLUMN etapa_anterior VARCHAR(50) NULL;
ALTER TABLE homologacoes_historico ADD COLUMN etapa_nova VARCHAR(50) NULL;
ALTER TABLE homologacoes_historico ADD COLUMN dados_etapa TEXT NULL;
ALTER TABLE homologacoes_historico ADD COLUMN tempo_etapa INT NULL;
ALTER TABLE homologacoes_historico ADD COLUMN acao_realizada VARCHAR(255) NULL;
ALTER TABLE homologacoes_historico ADD COLUMN detalhes_acao TEXT NULL;

-- 2. Atualizar dados existentes
UPDATE homologacoes_historico 
SET data_acao = created_at 
WHERE data_acao IS NULL;

UPDATE homologacoes_historico 
SET etapa_nova = status_novo 
WHERE etapa_nova IS NULL AND status_novo IS NOT NULL;

UPDATE homologacoes_historico 
SET etapa_anterior = status_anterior 
WHERE etapa_anterior IS NULL AND status_anterior IS NOT NULL;

UPDATE homologacoes_historico 
SET acao_realizada = CONCAT('Mudança para ', COALESCE(status_novo, etapa_nova, 'nova etapa'))
WHERE acao_realizada IS NULL;

-- 3. Atualizar nomes de usuários
UPDATE homologacoes_historico h
LEFT JOIN users u ON h.usuario_id = u.id
SET h.usuario_nome = u.name
WHERE h.usuario_nome IS NULL AND u.name IS NOT NULL;

UPDATE homologacoes_historico 
SET usuario_nome = 'Usuário não identificado'
WHERE usuario_nome IS NULL;
