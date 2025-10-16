-- Tabela de Homologações (Kanban)
CREATE TABLE IF NOT EXISTS homologacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_produto VARCHAR(100) NOT NULL COMMENT 'Código do produto/serviço no ERP',
    descricao TEXT NOT NULL,
    fornecedor VARCHAR(255) NOT NULL,
    motivo_homologacao VARCHAR(100) NOT NULL COMMENT 'novo_item, troca_fornecedor, atualizacao_tecnica, etc',
    data_solicitacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    avisar_logistica BOOLEAN DEFAULT FALSE,
    status VARCHAR(50) NOT NULL DEFAULT 'pendente_recebimento' COMMENT 'pendente_recebimento, em_analise, aprovado, reprovado',
    ordem INT DEFAULT 0 COMMENT 'Ordem do cartão na coluna do Kanban',
    observacoes TEXT,
    
    -- Auditoria
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_status (status),
    INDEX idx_data_solicitacao (data_solicitacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Responsáveis pela Homologação (múltiplos responsáveis por homologação)
CREATE TABLE IF NOT EXISTS homologacoes_responsaveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    user_id INT NOT NULL,
    notificado BOOLEAN DEFAULT FALSE,
    data_notificacao DATETIME DEFAULT NULL,
    
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_homologacao_user (homologacao_id, user_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Histórico de Movimentações no Kanban
CREATE TABLE IF NOT EXISTS homologacoes_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    status_anterior VARCHAR(50),
    status_novo VARCHAR(50) NOT NULL,
    user_id INT NOT NULL COMMENT 'Quem moveu o cartão',
    observacao TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_homologacao (homologacao_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Anexos/Documentos
CREATE TABLE IF NOT EXISTS homologacoes_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    tipo_arquivo VARCHAR(50),
    tamanho_arquivo INT,
    arquivo MEDIUMBLOB NOT NULL,
    uploaded_by INT NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_homologacao (homologacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir permissões para o módulo de Homologações
INSERT IGNORE INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT 
    p.id,
    'homologacoes' as module,
    CASE WHEN p.name IN ('Administrador', 'Super Admin') THEN 1 ELSE 0 END as can_view,
    CASE WHEN p.name IN ('Administrador', 'Super Admin') THEN 1 ELSE 0 END as can_edit,
    CASE WHEN p.name IN ('Administrador', 'Super Admin') THEN 1 ELSE 0 END as can_delete,
    0 as can_import,
    CASE WHEN p.name IN ('Administrador', 'Super Admin') THEN 1 ELSE 0 END as can_export
FROM profiles p
WHERE NOT EXISTS (
    SELECT 1 FROM profile_permissions pp 
    WHERE pp.profile_id = p.id AND pp.module = 'homologacoes'
);
