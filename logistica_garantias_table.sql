-- =====================================================
-- TABELA DE LOGÍSTICA PARA GARANTIAS
-- Informações complementares de transporte e dimensões
-- =====================================================

CREATE TABLE logistica_garantias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    garantia_id INT NOT NULL,
    
    -- Dados da Transportadora
    nome_transportadora VARCHAR(255) NULL,
    cnpj_transportadora VARCHAR(18) NULL,
    
    -- Dimensões e Peso
    peso_total DECIMAL(10,3) NULL COMMENT 'Peso em kg com 3 casas decimais',
    altura DECIMAL(10,2) NULL COMMENT 'Altura em cm',
    largura DECIMAL(10,2) NULL COMMENT 'Largura em cm', 
    profundidade DECIMAL(10,2) NULL COMMENT 'Profundidade em cm',
    
    -- Campos calculados
    volume_total DECIMAL(15,3) GENERATED ALWAYS AS (
        CASE 
            WHEN altura IS NOT NULL AND largura IS NOT NULL AND profundidade IS NOT NULL 
            THEN (altura * largura * profundidade) / 1000000  -- Converte cm³ para m³
            ELSE NULL 
        END
    ) STORED COMMENT 'Volume em m³ calculado automaticamente',
    
    -- Observações adicionais
    observacoes_logistica TEXT NULL,
    
    -- Controle
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relacionamentos e Índices
    FOREIGN KEY (garantia_id) REFERENCES garantias(id) ON DELETE CASCADE,
    INDEX idx_garantia (garantia_id),
    INDEX idx_transportadora (nome_transportadora),
    INDEX idx_cnpj (cnpj_transportadora)
);

-- =====================================================
-- COMENTÁRIOS E EXEMPLOS
-- =====================================================

-- Exemplo de inserção:
/*
INSERT INTO logistica_garantias (
    garantia_id, 
    nome_transportadora, 
    cnpj_transportadora,
    peso_total,
    altura,
    largura,
    profundidade,
    observacoes_logistica
) VALUES (
    1,
    'Transportadora ABC Ltda',
    '12.345.678/0001-90',
    2.500,
    30.00,
    40.00,
    20.00,
    'Produto frágil - manuseio cuidadoso'
);
*/

-- Consulta com dados de logística:
/*
SELECT 
    g.*,
    l.nome_transportadora,
    l.cnpj_transportadora,
    l.peso_total,
    l.altura,
    l.largura,
    l.profundidade,
    l.volume_total,
    l.observacoes_logistica
FROM garantias g
LEFT JOIN logistica_garantias l ON g.id = l.garantia_id
WHERE g.id = 1;
*/

-- =====================================================
-- VERIFICAÇÃO DA ESTRUTURA
-- =====================================================

DESCRIBE logistica_garantias;
