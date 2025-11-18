-- Sistema de Log Detalhado para Homologações
-- Data: 17/11/2025
-- Melhoria do sistema de histórico para relatório completo

-- Verificar e criar tabela de homologações se não existir
CREATE TABLE IF NOT EXISTS homologacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cod_referencia VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    avisar_logistica BOOLEAN DEFAULT FALSE,
    status ENUM(
        'aguardando_recebimento',
        'recebido', 
        'em_analise',
        'em_homologacao',
        'aprovado',
        'reprovado'
    ) DEFAULT 'aguardando_recebimento',
    departamento_id INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Campos específicos por etapa
    data_recebimento DATETIME NULL,
    recebido_por INT NULL,
    observacoes_recebimento TEXT NULL,
    
    data_inicio_analise DATETIME NULL,
    analise_por INT NULL,
    observacoes_analise TEXT NULL,
    
    data_inicio_homologacao DATETIME NULL,
    homologacao_por INT NULL,
    observacoes_homologacao TEXT NULL,
    
    data_finalizacao DATETIME NULL,
    finalizado_por INT NULL,
    observacoes_finalizacao TEXT NULL,
    resultado_final TEXT NULL,
    
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_departamento (departamento_id)
);

-- Melhorar tabela de histórico para log detalhado
CREATE TABLE IF NOT EXISTS homologacoes_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    etapa_anterior VARCHAR(50) NULL,
    etapa_nova VARCHAR(50) NOT NULL,
    usuario_id INT NOT NULL,
    usuario_nome VARCHAR(255) NOT NULL,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Dados específicos da etapa
    observacoes TEXT NULL,
    dados_etapa JSON NULL, -- Para armazenar dados específicos de cada etapa
    tempo_etapa INT NULL, -- Tempo gasto na etapa anterior (em minutos)
    
    -- Campos para relatório
    acao_realizada VARCHAR(255) NOT NULL,
    detalhes_acao TEXT NULL,
    anexos_adicionados INT DEFAULT 0,
    
    INDEX idx_homologacao (homologacao_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_data (data_acao),
    
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE
);

-- Tabela para armazenar dados específicos de cada etapa
CREATE TABLE IF NOT EXISTS homologacoes_etapas_dados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    etapa VARCHAR(50) NOT NULL,
    campo VARCHAR(100) NOT NULL,
    valor TEXT NULL,
    usuario_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_homologacao_etapa (homologacao_id, etapa),
    UNIQUE KEY unique_campo_etapa (homologacao_id, etapa, campo),
    
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE
);

-- Tabela para responsáveis (se não existir)
CREATE TABLE IF NOT EXISTS homologacoes_responsaveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_responsavel (homologacao_id, user_id),
    INDEX idx_homologacao (homologacao_id),
    INDEX idx_user (user_id),
    
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE
);

-- Tabela para anexos (se não existir)
CREATE TABLE IF NOT EXISTS homologacoes_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    arquivo_blob LONGBLOB NOT NULL,
    tipo_arquivo VARCHAR(100) NOT NULL,
    tamanho_bytes INT NOT NULL,
    etapa_upload VARCHAR(50) NOT NULL,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_homologacao (homologacao_id),
    INDEX idx_etapa (etapa_upload),
    
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE
);

-- Inserir dados de exemplo para etapas (se não existirem)
INSERT IGNORE INTO homologacoes_etapas_dados (homologacao_id, etapa, campo, valor, usuario_id) VALUES
(1, 'aguardando_recebimento', 'observacoes_iniciais', 'Aguardando chegada do material', 1),
(1, 'recebido', 'condicoes_recebimento', 'Material recebido em boas condições', 1),
(1, 'em_analise', 'testes_realizados', 'Teste de qualidade inicial aprovado', 1);

-- Comentários explicativos
-- 
-- ESTRUTURA DO LOG DETALHADO:
-- 1. homologacoes: Dados principais + campos específicos por etapa
-- 2. homologacoes_historico: Log completo de todas as ações
-- 3. homologacoes_etapas_dados: Dados específicos salvos em cada etapa
-- 4. homologacoes_anexos: Arquivos por etapa
-- 
-- FLUXO DE ETAPAS:
-- aguardando_recebimento → recebido → em_analise → em_homologacao → aprovado/reprovado
-- 
-- DADOS SALVOS POR ETAPA:
-- - Recebimento: data, responsável, condições, observações
-- - Análise: testes realizados, resultados, observações técnicas
-- - Homologação: aprovação/reprovação, justificativa, recomendações
-- - Finalização: resultado final, relatório completo
