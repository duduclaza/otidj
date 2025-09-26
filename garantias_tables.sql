-- =====================================================
-- MÓDULO GARANTIAS - ESTRUTURA COMPLETA
-- Suporte ao formulário inline com anexos em MEDIUMBLOB
-- =====================================================

-- 1. TABELA PRINCIPAL DE GARANTIAS
DROP TABLE IF EXISTS garantias_anexos;
DROP TABLE IF EXISTS garantias_itens;
DROP TABLE IF EXISTS garantias;

CREATE TABLE garantias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Informações Básicas
    fornecedor_id INT NOT NULL,
    origem_garantia ENUM('Amostragem', 'Homologação', 'Em Campo') NOT NULL,
    
    -- Números das Notas Fiscais
    numero_nf_compras VARCHAR(100) NULL,
    numero_nf_remessa_simples VARCHAR(100) NULL,
    numero_nf_remessa_devolucao VARCHAR(100) NULL,
    
    -- Campos Opcionais
    numero_serie VARCHAR(100) NULL,
    numero_lote VARCHAR(100) NULL,
    numero_ticket_os VARCHAR(100) NULL,
    
    -- Status e Observação
    status ENUM(
        'Em andamento',
        'Aguardando Fornecedor', 
        'Aguardando Recebimento',
        'Aguardando Item Chegar ao laboratório',
        'Aguardando Emissão de NF',
        'Aguardando Despache',
        'Aguardando Testes',
        'Finalizado',
        'Garantia Expirada',
        'Garantia não coberta'
    ) DEFAULT 'Em andamento',
    observacao TEXT NULL,
    
    -- Totais Calculados (atualizados por triggers)
    total_itens INT DEFAULT 0,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    
    -- Controle
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices
    INDEX idx_fornecedor (fornecedor_id),
    INDEX idx_origem (origem_garantia),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- 2. TABELA DE ITENS DA GARANTIA
CREATE TABLE garantias_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    garantia_id INT NOT NULL,
    
    -- Dados do Item
    descricao VARCHAR(500) NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_total DECIMAL(10,2) GENERATED ALWAYS AS (quantidade * valor_unitario) STORED,
    
    -- Controle
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Relacionamentos
    FOREIGN KEY (garantia_id) REFERENCES garantias(id) ON DELETE CASCADE,
    
    -- Índices
    INDEX idx_garantia (garantia_id)
);

-- 3. TABELA DE ANEXOS (TODOS EM MEDIUMBLOB)
CREATE TABLE garantias_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    garantia_id INT NOT NULL,
    
    -- Tipo de Anexo
    tipo_anexo ENUM(
        'nf_compras',
        'nf_remessa_simples', 
        'nf_remessa_devolucao',
        'laudo_tecnico',
        'evidencia'
    ) NOT NULL,
    
    -- Dados do Arquivo
    nome_arquivo VARCHAR(255) NOT NULL,
    tipo_mime VARCHAR(100) NOT NULL,
    tamanho_bytes INT NOT NULL,
    
    -- Conteúdo do Arquivo em MEDIUMBLOB (até 16MB)
    conteudo_arquivo MEDIUMBLOB NOT NULL,
    
    -- Controle
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Relacionamentos
    FOREIGN KEY (garantia_id) REFERENCES garantias(id) ON DELETE CASCADE,
    
    -- Índices
    INDEX idx_garantia_tipo (garantia_id, tipo_anexo),
    INDEX idx_tipo (tipo_anexo)
);

-- =====================================================
-- TRIGGERS PARA ATUALIZAR TOTAIS AUTOMATICAMENTE
-- =====================================================

-- Trigger para INSERT de itens
DELIMITER $$
CREATE TRIGGER garantias_itens_after_insert
AFTER INSERT ON garantias_itens
FOR EACH ROW
BEGIN
    UPDATE garantias 
    SET 
        total_itens = (
            SELECT COUNT(*) 
            FROM garantias_itens 
            WHERE garantia_id = NEW.garantia_id
        ),
        valor_total = (
            SELECT COALESCE(SUM(valor_total), 0) 
            FROM garantias_itens 
            WHERE garantia_id = NEW.garantia_id
        )
    WHERE id = NEW.garantia_id;
END$$

-- Trigger para UPDATE de itens
CREATE TRIGGER garantias_itens_after_update
AFTER UPDATE ON garantias_itens
FOR EACH ROW
BEGIN
    UPDATE garantias 
    SET 
        total_itens = (
            SELECT COUNT(*) 
            FROM garantias_itens 
            WHERE garantia_id = NEW.garantia_id
        ),
        valor_total = (
            SELECT COALESCE(SUM(valor_total), 0) 
            FROM garantias_itens 
            WHERE garantia_id = NEW.garantia_id
        )
    WHERE id = NEW.garantia_id;
END$$

-- Trigger para DELETE de itens
CREATE TRIGGER garantias_itens_after_delete
AFTER DELETE ON garantias_itens
FOR EACH ROW
BEGIN
    UPDATE garantias 
    SET 
        total_itens = (
            SELECT COUNT(*) 
            FROM garantias_itens 
            WHERE garantia_id = OLD.garantia_id
        ),
        valor_total = (
            SELECT COALESCE(SUM(valor_total), 0) 
            FROM garantias_itens 
            WHERE garantia_id = OLD.garantia_id
        )
    WHERE id = OLD.garantia_id;
END$$
DELIMITER ;

-- =====================================================
-- DADOS DE EXEMPLO (OPCIONAL)
-- =====================================================

-- Inserir uma garantia de exemplo
INSERT INTO garantias (
    fornecedor_id, 
    origem_garantia, 
    numero_nf_compras,
    numero_nf_remessa_simples,
    status,
    observacao
) VALUES (
    1, 
    'Amostragem', 
    'NF001234',
    'RS001234',
    'Em andamento',
    'Garantia de exemplo para testes'
);

-- Inserir itens de exemplo
INSERT INTO garantias_itens (garantia_id, descricao, quantidade, valor_unitario) VALUES
(1, 'Toner HP CF280A Preto', 2, 150.00),
(1, 'Cartucho HP 664 Colorido', 1, 89.90);

-- =====================================================
-- CONSULTAS ÚTEIS PARA VERIFICAÇÃO
-- =====================================================

-- Verificar estrutura das tabelas
DESCRIBE garantias;
DESCRIBE garantias_itens;
DESCRIBE garantias_anexos;

-- Consulta completa de garantias com totais
SELECT 
    g.id,
    g.origem_garantia,
    g.numero_nf_compras,
    g.status,
    g.total_itens,
    g.valor_total,
    g.created_at,
    f.nome as fornecedor_nome
FROM garantias g
LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
ORDER BY g.created_at DESC;

-- Consulta de itens por garantia
SELECT 
    gi.descricao,
    gi.quantidade,
    gi.valor_unitario,
    gi.valor_total
FROM garantias_itens gi
WHERE gi.garantia_id = 1;

-- Consulta de anexos por garantia
SELECT 
    ga.tipo_anexo,
    ga.nome_arquivo,
    ga.tipo_mime,
    ga.tamanho_bytes,
    ga.created_at
FROM garantias_anexos ga
WHERE ga.garantia_id = 1;

-- =====================================================
-- COMANDOS PARA LIMPEZA (SE NECESSÁRIO)
-- =====================================================

-- Para deletar todas as tabelas antigas:
-- DROP TABLE IF EXISTS garantias_anexos;
-- DROP TABLE IF EXISTS garantias_itens; 
-- DROP TABLE IF EXISTS garantias;
