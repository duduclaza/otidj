-- EXECUTE ESTES COMANDOS UM POR VEZ
-- Copie e cole cada comando separadamente no seu MySQL

-- 1. PRIMEIRO: Verificar se a tabela existe
SHOW TABLES LIKE 'homologacoes_historico';

-- 2. Ver a estrutura atual da tabela
DESCRIBE homologacoes_historico;

-- 3. Adicionar colunas uma por vez (ignore erros se já existirem)

-- Adicionar usuario_nome
ALTER TABLE homologacoes_historico ADD COLUMN usuario_nome VARCHAR(255) NULL;

-- Adicionar etapa_nova  
ALTER TABLE homologacoes_historico ADD COLUMN etapa_nova VARCHAR(50) NULL;

-- Adicionar etapa_anterior
ALTER TABLE homologacoes_historico ADD COLUMN etapa_anterior VARCHAR(50) NULL;

-- Adicionar acao_realizada
ALTER TABLE homologacoes_historico ADD COLUMN acao_realizada VARCHAR(255) NULL;

-- Adicionar dados_etapa
ALTER TABLE homologacoes_historico ADD COLUMN dados_etapa TEXT NULL;

-- Adicionar tempo_etapa
ALTER TABLE homologacoes_historico ADD COLUMN tempo_etapa INT NULL;

-- Adicionar detalhes_acao
ALTER TABLE homologacoes_historico ADD COLUMN detalhes_acao TEXT NULL;

-- 4. Atualizar dados existentes

-- Copiar status_novo para etapa_nova
UPDATE homologacoes_historico 
SET etapa_nova = status_novo 
WHERE etapa_nova IS NULL AND status_novo IS NOT NULL;

-- Copiar status_anterior para etapa_anterior  
UPDATE homologacoes_historico 
SET etapa_anterior = status_anterior 
WHERE etapa_anterior IS NULL AND status_anterior IS NOT NULL;

-- Criar acao_realizada baseada no status
UPDATE homologacoes_historico 
SET acao_realizada = CONCAT('Mudança para ', COALESCE(status_novo, etapa_nova, 'nova etapa'))
WHERE acao_realizada IS NULL;

-- Buscar nomes de usuários
UPDATE homologacoes_historico h
LEFT JOIN users u ON h.usuario_id = u.id
SET h.usuario_nome = u.name
WHERE h.usuario_nome IS NULL AND u.name IS NOT NULL;

-- Definir nome padrão para usuários não encontrados
UPDATE homologacoes_historico 
SET usuario_nome = 'Usuário não identificado'
WHERE usuario_nome IS NULL;

-- 5. VERIFICAR se deu certo
SELECT COUNT(*) as total_registros FROM homologacoes_historico;
SELECT * FROM homologacoes_historico LIMIT 3;
