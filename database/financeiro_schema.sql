-- Tabela de Pagamentos Mensais
CREATE TABLE IF NOT EXISTS financeiro_pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mes INT NOT NULL,
    ano INT NOT NULL,
    status ENUM('Pago', 'Em Aberto', 'Atrasado') NOT NULL DEFAULT 'Em Aberto',
    comprovante MEDIUMBLOB,
    comprovante_nome VARCHAR(255),
    comprovante_tipo VARCHAR(100),
    comprovante_tamanho INT,
    data_vencimento DATE NOT NULL,
    data_pagamento DATETIME,
    anexado_por INT,
    anexado_em DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_mes_ano (mes, ano),
    INDEX idx_status (status),
    INDEX idx_vencimento (data_vencimento),
    
    -- Foreign Key
    FOREIGN KEY (anexado_por) REFERENCES users(id),
    
    -- Garantir um registro por mês/ano
    UNIQUE KEY unique_mes_ano (mes, ano)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Notificações de Pagamento
CREATE TABLE IF NOT EXISTS financeiro_notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pagamento_id INT NOT NULL,
    tipo ENUM('3_dias', '5_dias', 'bloqueio') NOT NULL,
    enviada BOOLEAN DEFAULT FALSE,
    enviada_em DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Key
    FOREIGN KEY (pagamento_id) REFERENCES financeiro_pagamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Log de Bloqueios
CREATE TABLE IF NOT EXISTS financeiro_bloqueios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pagamento_id INT NOT NULL,
    bloqueado_em DATETIME NOT NULL,
    desbloqueado_em DATETIME,
    motivo TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Key
    FOREIGN KEY (pagamento_id) REFERENCES financeiro_pagamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir permissões para o módulo
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export, created_at, updated_at)
SELECT 
    p.id,
    'financeiro',
    1,  -- view
    CASE WHEN p.name = 'Administrador' THEN 1 ELSE 0 END,  -- edit (só admin)
    CASE WHEN p.name = 'Administrador' THEN 1 ELSE 0 END,  -- delete (só admin)
    0,  -- import
    1,  -- export
    NOW(),
    NOW()
FROM profiles p
WHERE NOT EXISTS (
    SELECT 1 FROM profile_permissions 
    WHERE profile_id = p.id 
    AND module = 'financeiro'
);

-- Criar o pagamento do mês atual se não existir
INSERT INTO financeiro_pagamentos (mes, ano, status, data_vencimento)
SELECT 
    MONTH(LAST_DAY(CURDATE())),
    YEAR(LAST_DAY(CURDATE())),
    'Em Aberto',
    LAST_DAY(CURDATE())
WHERE NOT EXISTS (
    SELECT 1 FROM financeiro_pagamentos 
    WHERE mes = MONTH(CURDATE()) 
    AND ano = YEAR(CURDATE())
);
