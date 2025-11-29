-- =====================================================
-- MÓDULO: ÁREA TÉCNICA
-- Criado em: 2024
-- =====================================================

-- Tabela de controle de trial dos módulos
CREATE TABLE IF NOT EXISTS modulos_trial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modulo VARCHAR(100) NOT NULL UNIQUE,
    data_ativacao DATETIME NOT NULL,
    ativado_por INT NULL,
    pago TINYINT(1) DEFAULT 0,
    data_pagamento DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ativado_por) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de checklists da área técnica
CREATE TABLE IF NOT EXISTS area_tecnica_checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_serie VARCHAR(100) NOT NULL,
    manutencao_realizada TEXT NOT NULL,
    colaborador VARCHAR(255) NOT NULL,
    foto_contador VARCHAR(255) NULL,
    foto_equipamento VARCHAR(255) NULL,
    data_hora DATETIME NOT NULL,
    ip_origem VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_numero_serie (numero_serie),
    INDEX idx_data_hora (data_hora),
    INDEX idx_colaborador (colaborador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Para marcar um módulo como pago, execute:
-- UPDATE modulos_trial SET pago = 1, data_pagamento = NOW() WHERE modulo = 'area_tecnica';
-- =====================================================
