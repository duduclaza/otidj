-- =====================================================
-- TABELAS DO MÓDULO FLUXOGRAMAS
-- Baseado no módulo POPs e ITs
-- =====================================================

-- Tabela de títulos de fluxogramas
CREATE TABLE IF NOT EXISTS fluxogramas_titulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    tipo ENUM('FLUXOGRAMA', 'PROCESSO') DEFAULT 'FLUXOGRAMA',
    descricao TEXT,
    criado_por INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (criado_por) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_titulo (titulo),
    INDEX idx_tipo (tipo),
    INDEX idx_criado_por (criado_por)
);

-- Tabela de registros de fluxogramas
CREATE TABLE IF NOT EXISTS fluxogramas_registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo_id INT NOT NULL,
    versao INT NOT NULL DEFAULT 1,
    arquivo LONGBLOB NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    extensao VARCHAR(10) NOT NULL,
    tamanho_arquivo INT NOT NULL,
    publico TINYINT(1) DEFAULT 0,
    status ENUM('PENDENTE', 'APROVADO', 'REPROVADO') DEFAULT 'PENDENTE',
    observacao_reprovacao TEXT NULL,
    criado_por INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    aprovado_por INT NULL,
    aprovado_em TIMESTAMP NULL,
    
    FOREIGN KEY (titulo_id) REFERENCES fluxogramas_titulos(id) ON DELETE CASCADE,
    FOREIGN KEY (criado_por) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (aprovado_por) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_titulo_versao (titulo_id, versao),
    INDEX idx_status (status),
    INDEX idx_criado_por (criado_por),
    INDEX idx_aprovado_por (aprovado_por),
    INDEX idx_publico (publico),
    
    UNIQUE KEY unique_titulo_versao (titulo_id, versao)
);

-- Tabela de departamentos para fluxogramas
CREATE TABLE IF NOT EXISTS fluxogramas_registros_departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registro_id INT NOT NULL,
    departamento_id INT NOT NULL,
    
    FOREIGN KEY (registro_id) REFERENCES fluxogramas_registros(id) ON DELETE CASCADE,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_registro_departamento (registro_id, departamento_id),
    INDEX idx_registro_id (registro_id),
    INDEX idx_departamento_id (departamento_id)
);

-- Tabela de logs de visualização de fluxogramas
CREATE TABLE IF NOT EXISTS fluxogramas_logs_visualizacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registro_id INT NOT NULL,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    visualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (registro_id) REFERENCES fluxogramas_registros(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_registro_id (registro_id),
    INDEX idx_user_id (user_id),
    INDEX idx_visualizado_em (visualizado_em)
);

-- Tabela de solicitações de exclusão de fluxogramas
CREATE TABLE IF NOT EXISTS fluxogramas_solicitacoes_exclusao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registro_id INT NOT NULL,
    solicitante_id INT NOT NULL,
    motivo TEXT NOT NULL,
    status ENUM('PENDENTE', 'APROVADA', 'REPROVADA') DEFAULT 'PENDENTE',
    avaliado_por INT NULL,
    avaliado_em TIMESTAMP NULL,
    observacoes_avaliacao TEXT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (registro_id) REFERENCES fluxogramas_registros(id) ON DELETE CASCADE,
    FOREIGN KEY (solicitante_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (avaliado_por) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_registro_id (registro_id),
    INDEX idx_solicitante_id (solicitante_id),
    INDEX idx_status (status),
    INDEX idx_avaliado_por (avaliado_por)
);

-- =====================================================
-- DADOS INICIAIS (OPCIONAL)
-- =====================================================

-- Inserir alguns títulos de exemplo
INSERT IGNORE INTO fluxogramas_titulos (titulo, tipo, descricao, criado_por) VALUES
('Processo de Atendimento ao Cliente', 'PROCESSO', 'Fluxograma do processo de atendimento ao cliente', 1),
('Fluxo de Aprovação de Documentos', 'FLUXOGRAMA', 'Fluxograma para aprovação de documentos internos', 1),
('Processo de Compras', 'PROCESSO', 'Fluxograma do processo de compras da empresa', 1),
('Fluxo de Controle de Qualidade', 'FLUXOGRAMA', 'Fluxograma do controle de qualidade dos produtos', 1);

-- =====================================================
-- COMENTÁRIOS E OBSERVAÇÕES
-- =====================================================

/*
ESTRUTURA DAS TABELAS:

1. fluxogramas_titulos:
   - Armazena os títulos/categorias dos fluxogramas
   - Tipos: FLUXOGRAMA ou PROCESSO
   - Permite descrição opcional

2. fluxogramas_registros:
   - Armazena os arquivos dos fluxogramas (LONGBLOB)
   - Sistema de versionamento (versao)
   - Status de aprovação (PENDENTE, APROVADO, REPROVADO)
   - Controle de visibilidade (publico ou por departamentos)

3. fluxogramas_registros_departamentos:
   - Relacionamento many-to-many entre registros e departamentos
   - Define quais departamentos podem visualizar cada fluxograma

4. fluxogramas_logs_visualizacao:
   - Log de todas as visualizações dos fluxogramas
   - Rastreamento por usuário, IP e user agent

5. fluxogramas_solicitacoes_exclusao:
   - Sistema de solicitação de exclusão
   - Workflow de aprovação para exclusões
   - Motivos e observações da avaliação

FUNCIONALIDADES SUPORTADAS:
- ✅ Upload de arquivos (PDF, imagens)
- ✅ Sistema de versionamento
- ✅ Aprovação/reprovação por administradores
- ✅ Controle de visibilidade por departamentos
- ✅ Log de visualizações para auditoria
- ✅ Sistema de solicitação de exclusão
- ✅ Notificações integradas
- ✅ Permissões granulares
*/
