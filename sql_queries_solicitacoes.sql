-- ========================================
-- QUERIES SQL PARA SOLICITAÇÃO DE MELHORIAS
-- Execute essas queries no seu banco de dados
-- ========================================

-- 1. TABELA PRINCIPAL: solicitacoes_melhorias
CREATE TABLE IF NOT EXISTS solicitacoes_melhorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    usuario_nome VARCHAR(255) NOT NULL,
    data_solicitacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    setor VARCHAR(255) NOT NULL,
    processo TEXT NOT NULL,
    descricao_melhoria TEXT NOT NULL,
    status ENUM('pendente', 'em_analise', 'aprovado', 'rejeitado', 'implementado') NOT NULL DEFAULT 'pendente',
    observacoes TEXT,
    resultado_esperado TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_status (status),
    INDEX idx_data_solicitacao (data_solicitacao)
);

-- 2. TABELA DE RESPONSÁVEIS: solicitacoes_melhorias_responsaveis (many-to-many)
CREATE TABLE IF NOT EXISTS solicitacoes_melhorias_responsaveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT NOT NULL,
    usuario_id INT NOT NULL,
    usuario_nome VARCHAR(255) NOT NULL,
    usuario_email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_melhorias(id) ON DELETE CASCADE,
    UNIQUE KEY unique_solicitacao_responsavel (solicitacao_id, usuario_id),
    INDEX idx_solicitacao_id (solicitacao_id),
    INDEX idx_usuario_id (usuario_id)
);

-- 3. TABELA DE ANEXOS: solicitacoes_melhorias_anexos
CREATE TABLE IF NOT EXISTS solicitacoes_melhorias_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitacao_id INT NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    tipo_arquivo VARCHAR(100) NOT NULL,
    tamanho_arquivo INT NOT NULL,
    caminho_arquivo VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes_melhorias(id) ON DELETE CASCADE,
    INDEX idx_solicitacao_id (solicitacao_id)
);

-- 4. ADICIONAR PERMISSÕES PARA O MÓDULO (execute apenas se você tem as tabelas de perfis)
-- Remover módulo antigo se existir
DELETE FROM profile_permissions WHERE module = 'melhoria_continua';

-- Adicionar permissões para Administrador (assumindo que existe)
INSERT IGNORE INTO profile_permissions 
(profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
SELECT id, 'solicitacao_melhorias', 1, 1, 1, 0, 1 
FROM profiles WHERE name = 'Administrador';

-- Adicionar permissões para Supervisor (assumindo que existe)
INSERT IGNORE INTO profile_permissions 
(profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
SELECT id, 'solicitacao_melhorias', 1, 1, 0, 0, 1 
FROM profiles WHERE name = 'Supervisor';

-- Adicionar permissões para Analista de Qualidade (assumindo que existe)
INSERT IGNORE INTO profile_permissions 
(profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
SELECT id, 'solicitacao_melhorias', 1, 1, 0, 0, 1 
FROM profiles WHERE name = 'Analista de Qualidade';

-- Adicionar permissões básicas para outros perfis (só visualização)
INSERT IGNORE INTO profile_permissions 
(profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
SELECT id, 'solicitacao_melhorias', 1, 0, 0, 0, 0 
FROM profiles WHERE name NOT IN ('Administrador', 'Supervisor', 'Analista de Qualidade');

-- 5. CRIAR DIRETÓRIO PARA UPLOADS (execute no sistema de arquivos)
-- mkdir -p storage/uploads/melhorias

-- ========================================
-- QUERIES DE TESTE (opcional)
-- ========================================

-- Verificar se as tabelas foram criadas
SHOW TABLES LIKE '%solicitacoes_melhorias%';

-- Verificar estrutura das tabelas
DESCRIBE solicitacoes_melhorias;
DESCRIBE solicitacoes_melhorias_responsaveis;
DESCRIBE solicitacoes_melhorias_anexos;

-- Verificar permissões adicionadas
SELECT p.name as perfil, pp.module, pp.can_view, pp.can_edit, pp.can_delete 
FROM profiles p 
LEFT JOIN profile_permissions pp ON p.id = pp.profile_id 
WHERE pp.module = 'solicitacao_melhorias'
ORDER BY p.name;

-- ========================================
-- DADOS DE EXEMPLO (opcional)
-- ========================================

-- Inserir uma solicitação de exemplo (ajuste os IDs conforme seu banco)
/*
INSERT INTO solicitacoes_melhorias 
(usuario_id, usuario_nome, setor, processo, descricao_melhoria, resultado_esperado, observacoes) 
VALUES 
(1, 'Administrador', 'TI', 'Processo de backup', 'Implementar backup automático diário', 'Reduzir risco de perda de dados', 'Urgente para compliance');

-- Adicionar responsável para a solicitação
INSERT INTO solicitacoes_melhorias_responsaveis 
(solicitacao_id, usuario_id, usuario_nome, usuario_email) 
VALUES 
(1, 1, 'Administrador', 'admin@empresa.com');
*/
