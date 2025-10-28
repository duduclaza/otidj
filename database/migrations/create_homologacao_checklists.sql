-- Tabela de Checklists (Templates)
CREATE TABLE IF NOT EXISTS homologacao_checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    ativo TINYINT(1) DEFAULT 1,
    criado_por INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (criado_por) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Itens do Checklist (Opções)
CREATE TABLE IF NOT EXISTS homologacao_checklist_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    ordem INT DEFAULT 0,
    obrigatorio TINYINT(1) DEFAULT 0,
    tipo_resposta ENUM('checkbox', 'sim_nao', 'texto', 'numero') DEFAULT 'checkbox',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (checklist_id) REFERENCES homologacao_checklists(id) ON DELETE CASCADE,
    INDEX idx_checklist (checklist_id),
    INDEX idx_ordem (ordem)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Respostas do Checklist (Preenchimento)
CREATE TABLE IF NOT EXISTS homologacao_checklist_respostas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    checklist_id INT NOT NULL,
    item_id INT NOT NULL,
    resposta TEXT NULL,
    concluido TINYINT(1) DEFAULT 0,
    respondido_por INT NOT NULL,
    respondido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (checklist_id) REFERENCES homologacao_checklists(id),
    FOREIGN KEY (item_id) REFERENCES homologacao_checklist_itens(id),
    FOREIGN KEY (respondido_por) REFERENCES users(id),
    INDEX idx_homologacao (homologacao_id),
    INDEX idx_checklist (checklist_id),
    UNIQUE KEY unique_resposta (homologacao_id, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar campo checklist_id na tabela homologacoes
ALTER TABLE homologacoes 
ADD COLUMN checklist_id INT NULL AFTER status,
ADD FOREIGN KEY (checklist_id) REFERENCES homologacao_checklists(id) ON DELETE SET NULL;
