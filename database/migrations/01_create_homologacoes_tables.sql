-- ==========================================
-- MIGRATION PARTE 1: Criar Tabelas Homologações
-- Data: 2024-10-16
-- Descrição: Cria apenas as tabelas (DDL)
-- ==========================================

-- 1. VERIFICAR E ADICIONAR COLUNA DEPARTMENT (se não existir)
SET @exist := (SELECT COUNT(*) 
               FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE table_schema = DATABASE() 
               AND table_name = 'users' 
               AND column_name = 'department');

SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE users ADD COLUMN department VARCHAR(100) DEFAULT NULL AFTER email',
    'SELECT "Coluna department já existe" AS info');

PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. CRIAR TABELA PRINCIPAL DE HOMOLOGAÇÕES
CREATE TABLE IF NOT EXISTS homologacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_produto VARCHAR(100) NOT NULL COMMENT 'Código do produto/serviço no ERP',
    descricao TEXT NOT NULL,
    fornecedor VARCHAR(255) NOT NULL,
    motivo_homologacao VARCHAR(100) NOT NULL COMMENT 'novo_item, troca_fornecedor, atualizacao_tecnica, etc',
    data_solicitacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    avisar_logistica BOOLEAN DEFAULT FALSE COMMENT 'Se TRUE, envia email para todo departamento Logística',
    status VARCHAR(50) NOT NULL DEFAULT 'pendente_recebimento' COMMENT 'pendente_recebimento, em_analise, aprovado, reprovado',
    ordem INT DEFAULT 0 COMMENT 'Ordem do cartão na coluna do Kanban',
    observacoes TEXT,
    
    -- Auditoria
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_status (status),
    INDEX idx_data_solicitacao (data_solicitacao),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Tabela principal de homologações (Kanban)';

-- 3. CRIAR TABELA DE RESPONSÁVEIS (many-to-many)
CREATE TABLE IF NOT EXISTS homologacoes_responsaveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    user_id INT NOT NULL,
    notificado BOOLEAN DEFAULT FALSE COMMENT 'Se já foi enviado email de notificação',
    data_notificacao DATETIME DEFAULT NULL,
    
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_homologacao_user (homologacao_id, user_id),
    INDEX idx_user (user_id),
    INDEX idx_homologacao (homologacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Múltiplos responsáveis por homologação';

-- 4. CRIAR TABELA DE HISTÓRICO DE MOVIMENTAÇÕES
CREATE TABLE IF NOT EXISTS homologacoes_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    status_anterior VARCHAR(50) COMMENT 'Status antes da movimentação',
    status_novo VARCHAR(50) NOT NULL COMMENT 'Status após a movimentação',
    user_id INT NOT NULL COMMENT 'Usuário que moveu o cartão no Kanban',
    observacao TEXT COMMENT 'Observação sobre a movimentação',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_homologacao (homologacao_id),
    INDEX idx_created_at (created_at),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Histórico de mudanças de status no Kanban';

-- 5. CRIAR TABELA DE ANEXOS (BLOB storage)
CREATE TABLE IF NOT EXISTS homologacoes_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    tipo_arquivo VARCHAR(50) COMMENT 'MIME type do arquivo',
    tamanho_arquivo INT COMMENT 'Tamanho em bytes',
    arquivo MEDIUMBLOB NOT NULL COMMENT 'Conteúdo do arquivo (até 16MB)',
    uploaded_by INT NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_homologacao (homologacao_id),
    INDEX idx_uploaded_by (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Anexos das homologações armazenados em BLOB';

-- 6. VERIFICAÇÃO DE SUCESSO
SELECT 
    CASE 
        WHEN COUNT(*) = 4 THEN '✅ SUCESSO! Todas as 4 tabelas criadas!'
        ELSE CONCAT('⚠️ Apenas ', COUNT(*), ' de 4 tabelas criadas')
    END as Status
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name IN ('homologacoes', 'homologacoes_responsaveis', 'homologacoes_historico', 'homologacoes_anexos');

SELECT '📋 Execute agora: 02_add_homologacoes_permissions.sql' as 'Próximo Passo';
