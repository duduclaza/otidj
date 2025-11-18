-- Correção da tabela homologacoes_historico
-- Data: 17/11/2025
-- Adicionar colunas necessárias para o sistema de log detalhado

-- Verificar se as colunas existem e adicionar se necessário
SET @sql = '';

-- Adicionar coluna data_acao se não existir
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'homologacoes_historico' 
AND column_name = 'data_acao';

IF @col_exists = 0 THEN
    SET @sql = CONCAT(@sql, 'ALTER TABLE homologacoes_historico ADD COLUMN data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP;');
END IF;

-- Adicionar coluna usuario_nome se não existir
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'homologacoes_historico' 
AND column_name = 'usuario_nome';

IF @col_exists = 0 THEN
    SET @sql = CONCAT(@sql, 'ALTER TABLE homologacoes_historico ADD COLUMN usuario_nome VARCHAR(255) NULL;');
END IF;

-- Adicionar coluna etapa_anterior se não existir
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'homologacoes_historico' 
AND column_name = 'etapa_anterior';

IF @col_exists = 0 THEN
    SET @sql = CONCAT(@sql, 'ALTER TABLE homologacoes_historico ADD COLUMN etapa_anterior VARCHAR(50) NULL;');
END IF;

-- Adicionar coluna etapa_nova se não existir
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'homologacoes_historico' 
AND column_name = 'etapa_nova';

IF @col_exists = 0 THEN
    SET @sql = CONCAT(@sql, 'ALTER TABLE homologacoes_historico ADD COLUMN etapa_nova VARCHAR(50) NULL;');
END IF;

-- Adicionar coluna dados_etapa se não existir
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'homologacoes_historico' 
AND column_name = 'dados_etapa';

IF @col_exists = 0 THEN
    SET @sql = CONCAT(@sql, 'ALTER TABLE homologacoes_historico ADD COLUMN dados_etapa JSON NULL;');
END IF;

-- Adicionar coluna tempo_etapa se não existir
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'homologacoes_historico' 
AND column_name = 'tempo_etapa';

IF @col_exists = 0 THEN
    SET @sql = CONCAT(@sql, 'ALTER TABLE homologacoes_historico ADD COLUMN tempo_etapa INT NULL;');
END IF;

-- Adicionar coluna acao_realizada se não existir
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'homologacoes_historico' 
AND column_name = 'acao_realizada';

IF @col_exists = 0 THEN
    SET @sql = CONCAT(@sql, 'ALTER TABLE homologacoes_historico ADD COLUMN acao_realizada VARCHAR(255) NULL;');
END IF;

-- Adicionar coluna detalhes_acao se não existir
SELECT COUNT(*) INTO @col_exists 
FROM information_schema.columns 
WHERE table_schema = DATABASE() 
AND table_name = 'homologacoes_historico' 
AND column_name = 'detalhes_acao';

IF @col_exists = 0 THEN
    SET @sql = CONCAT(@sql, 'ALTER TABLE homologacoes_historico ADD COLUMN detalhes_acao TEXT NULL;');
END IF;

-- Executar as alterações se houver
IF LENGTH(@sql) > 0 THEN
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END IF;

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

-- Adicionar índices se não existirem
CREATE INDEX IF NOT EXISTS idx_homologacao_data ON homologacoes_historico(homologacao_id, COALESCE(data_acao, created_at));
CREATE INDEX IF NOT EXISTS idx_etapa_nova ON homologacoes_historico(etapa_nova);

-- Comentário de conclusão
SELECT 'Tabela homologacoes_historico atualizada com sucesso para suportar log detalhado' as resultado;
