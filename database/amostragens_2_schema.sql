-- Tabela principal de Amostragens 2.0
CREATE TABLE IF NOT EXISTS amostragens_2 (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Informações automáticas
    user_id INT NOT NULL,
    filial_id INT NOT NULL,
    
    -- Dados da NF
    numero_nf VARCHAR(100) NOT NULL,
    anexo_nf MEDIUMBLOB,
    anexo_nf_nome VARCHAR(255),
    anexo_nf_tipo VARCHAR(100),
    anexo_nf_tamanho INT,
    
    -- Tipo e Produto
    tipo_produto ENUM('Toner', 'Peça', 'Máquina') NOT NULL,
    produto_id INT NOT NULL,
    codigo_produto VARCHAR(100) NOT NULL,
    nome_produto VARCHAR(255) NOT NULL,
    
    -- Quantidades
    quantidade_recebida INT NOT NULL,
    quantidade_testada INT NULL DEFAULT NULL,
    quantidade_aprovada INT NULL DEFAULT NULL,
    quantidade_reprovada INT NULL DEFAULT NULL,
    
    -- Fornecedor
    fornecedor_id INT NOT NULL,
    fornecedor_nome VARCHAR(255),
    
    -- Responsáveis (IDs separados por vírgula)
    responsaveis TEXT,
    
    -- Status
    status_final ENUM('Aprovado', 'Aprovado Parcialmente', 'Reprovado', 'Pendente') DEFAULT 'Pendente',
    
    -- Observações
    observacoes TEXT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_user (user_id),
    INDEX idx_filial (filial_id),
    INDEX idx_tipo_produto (tipo_produto),
    INDEX idx_produto (produto_id),
    INDEX idx_fornecedor (fornecedor_id),
    INDEX idx_status (status_final),
    INDEX idx_created (created_at),
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE RESTRICT,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Evidências (Fotos)
CREATE TABLE IF NOT EXISTS amostragens_2_evidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amostragem_id INT NOT NULL,
    
    -- Dados da evidência
    evidencia MEDIUMBLOB NOT NULL,
    nome VARCHAR(255) NOT NULL,
    tipo VARCHAR(100) NOT NULL,
    tamanho INT NOT NULL,
    ordem INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_amostragem (amostragem_id),
    
    -- Foreign Key
    FOREIGN KEY (amostragem_id) REFERENCES amostragens_2(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir permissões para o módulo
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export, created_at, updated_at)
SELECT 
    p.id,
    'amostragens_2',
    1,  -- view
    1,  -- edit
    1,  -- delete
    0,  -- import
    1,  -- export
    NOW(),
    NOW()
FROM profiles p
WHERE NOT EXISTS (
    SELECT 1 FROM profile_permissions 
    WHERE profile_id = p.id 
    AND module = 'amostragens_2'
);
