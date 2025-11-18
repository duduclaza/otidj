-- Correção da tabela homologacoes_historico
-- Data: 17/11/2025
-- Adicionar colunas necessárias para o sistema de log detalhado

-- Adicionar colunas se não existirem (usando ALTER TABLE com IF NOT EXISTS onde possível)

-- Adicionar coluna data_acao
ALTER TABLE homologacoes_historico 
ADD COLUMN IF NOT EXISTS data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Adicionar coluna usuario_nome
ALTER TABLE homologacoes_historico 
ADD COLUMN IF NOT EXISTS usuario_nome VARCHAR(255) NULL;

-- Adicionar coluna etapa_anterior
ALTER TABLE homologacoes_historico 
ADD COLUMN IF NOT EXISTS etapa_anterior VARCHAR(50) NULL;

-- Adicionar coluna etapa_nova
ALTER TABLE homologacoes_historico 
ADD COLUMN IF NOT EXISTS etapa_nova VARCHAR(50) NULL;

-- Adicionar coluna dados_etapa
ALTER TABLE homologacoes_historico 
ADD COLUMN IF NOT EXISTS dados_etapa JSON NULL;

-- Adicionar coluna tempo_etapa
ALTER TABLE homologacoes_historico 
ADD COLUMN IF NOT EXISTS tempo_etapa INT NULL;

-- Adicionar coluna acao_realizada
ALTER TABLE homologacoes_historico 
ADD COLUMN IF NOT EXISTS acao_realizada VARCHAR(255) NULL;

-- Adicionar coluna detalhes_acao
ALTER TABLE homologacoes_historico 
ADD COLUMN IF NOT EXISTS detalhes_acao TEXT NULL;

-- Atualizar registros existentes para compatibilidade
UPDATE homologacoes_historico 
SET 
    data_acao = COALESCE(data_acao, created_at),
    etapa_nova = COALESCE(etapa_nova, status_novo),
    etapa_anterior = COALESCE(etapa_anterior, status_anterior),
    acao_realizada = COALESCE(acao_realizada, CONCAT('Mudança para ', COALESCE(status_novo, etapa_nova, 'nova etapa')))
WHERE acao_realizada IS NULL OR acao_realizada = '';

-- Atualizar usuario_nome baseado no usuario_id
UPDATE homologacoes_historico h
LEFT JOIN users u ON h.usuario_id = u.id
SET h.usuario_nome = COALESCE(h.usuario_nome, u.name, 'Usuário não identificado')
WHERE h.usuario_nome IS NULL OR h.usuario_nome = '';

-- Adicionar índices
CREATE INDEX IF NOT EXISTS idx_homologacao_data ON homologacoes_historico(homologacao_id, created_at);
CREATE INDEX IF NOT EXISTS idx_etapa_nova ON homologacoes_historico(etapa_nova);

-- Comentário de conclusão
SELECT 'Tabela homologacoes_historico atualizada com sucesso!' as resultado;
