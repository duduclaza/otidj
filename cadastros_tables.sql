-- =====================================================
-- MÓDULOS DE CADASTRO: MÁQUINAS E PEÇAS
-- Sistema SGQ OTI DJ - Novos módulos de cadastro
-- =====================================================

-- 1. TABELA DE CADASTRO DE MÁQUINAS
DROP TABLE IF EXISTS cadastro_maquinas;

CREATE TABLE cadastro_maquinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados Básicos da Máquina
    modelo VARCHAR(255) NOT NULL,
    cod_referencia VARCHAR(100) NOT NULL UNIQUE,
    
    -- Controle do Sistema
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relacionamentos
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Índices
    INDEX idx_modelo (modelo),
    INDEX idx_cod_referencia (cod_referencia),
    INDEX idx_created_at (created_at)
);

-- 2. TABELA DE CADASTRO DE PEÇAS
DROP TABLE IF EXISTS cadastro_pecas;

CREATE TABLE cadastro_pecas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Dados Básicos da Peça
    codigo_referencia VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT NOT NULL,
    
    -- Controle do Sistema
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relacionamentos
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Índices
    INDEX idx_codigo_referencia (codigo_referencia),
    INDEX idx_descricao (descricao(100)),
    INDEX idx_created_at (created_at)
);

-- =====================================================
-- DADOS DE EXEMPLO
-- =====================================================

-- Máquinas de exemplo
INSERT INTO cadastro_maquinas (modelo, cod_referencia, created_by) VALUES
('HP LaserJet Pro M404n', 'HP-M404N-001', 1),
('Canon PIXMA G3110', 'CANON-G3110-001', 1),
('Epson EcoTank L3150', 'EPSON-L3150-001', 1);

-- Peças de exemplo
INSERT INTO cadastro_pecas (codigo_referencia, descricao, created_by) VALUES
('CF276A', 'Toner HP 76A Preto Original', 1),
('CF276X', 'Toner HP 76X Preto Alto Rendimento', 1),
('PG-40', 'Cartucho Canon PG-40 Preto', 1),
('CL-41', 'Cartucho Canon CL-41 Colorido', 1),
('664-BK', 'Tinta Epson 664 Preta', 1),
('664-C', 'Tinta Epson 664 Ciano', 1),
('PAPEL-A4', 'Papel A4 75g Chamex', 1);

-- =====================================================
-- CONSULTAS ÚTEIS
-- =====================================================

-- Listar todas as máquinas
/*
SELECT 
    id,
    modelo,
    cod_referencia,
    created_at
FROM cadastro_maquinas 
ORDER BY modelo;
*/

-- Listar todas as peças
/*
SELECT 
    codigo_referencia,
    descricao,
    created_at
FROM cadastro_pecas
ORDER BY descricao;
*/

-- =====================================================
-- VERIFICAÇÃO DA ESTRUTURA
-- =====================================================

DESCRIBE cadastro_maquinas;
DESCRIBE cadastro_pecas;

-- Verificar dados inseridos
SELECT COUNT(*) as total_maquinas FROM cadastro_maquinas;
SELECT COUNT(*) as total_pecas FROM cadastro_pecas;
