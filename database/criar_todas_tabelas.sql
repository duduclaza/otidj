-- CRIAR TODAS AS TABELAS NECESSÁRIAS PARA O SISTEMA DE LOG
-- Execute este arquivo completo

-- 1. Criar tabela de histórico
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

-- 2. Criar tabela de dados por etapa (opcional)
CREATE TABLE IF NOT EXISTS homologacoes_etapas_dados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    etapa VARCHAR(50) NOT NULL,
    campo VARCHAR(100) NOT NULL,
    valor TEXT NULL,
    usuario_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_homologacao_etapa (homologacao_id, etapa),
    INDEX idx_usuario (usuario_id)
);

-- 3. Verificar se as tabelas foram criadas
SHOW TABLES LIKE 'homologacoes_%';

-- 4. Ver estrutura das tabelas
DESCRIBE homologacoes_historico;

-- 5. Inserir um registro de teste (opcional)
INSERT INTO homologacoes_historico (
    homologacao_id, 
    etapa_nova, 
    usuario_id, 
    usuario_nome, 
    acao_realizada
) VALUES (
    1, 
    'teste', 
    1, 
    'Sistema', 
    'Teste de criação da tabela'
);

-- 6. Verificar se o registro foi inserido
SELECT COUNT(*) as total_registros FROM homologacoes_historico;

SELECT 'Tabelas criadas com sucesso!' as resultado;
