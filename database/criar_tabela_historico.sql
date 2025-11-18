-- CRIAR TABELA homologacoes_historico
-- Execute este comando primeiro

CREATE TABLE IF NOT EXISTS homologacoes_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    status_anterior VARCHAR(50) NULL,
    status_novo VARCHAR(50) NULL,
    etapa_anterior VARCHAR(50) NULL,
    etapa_nova VARCHAR(50) NULL,
    usuario_id INT NOT NULL,
    usuario_nome VARCHAR(255) NULL,
    observacao TEXT NULL,
    observacoes TEXT NULL,
    dados_etapa TEXT NULL,
    tempo_etapa INT NULL,
    acao_realizada VARCHAR(255) NULL,
    detalhes_acao TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_homologacao (homologacao_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_data (created_at)
);

-- Verificar se a tabela foi criada
SHOW TABLES LIKE 'homologacoes_historico';

-- Ver a estrutura da tabela
DESCRIBE homologacoes_historico;
