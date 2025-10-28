-- ===== ADICIONAR COLUNA FILIAL NA TABELA GARANTIAS =====
-- Data: 27/10/2024
-- Descrição: Adiciona campo filial_id para identificar a filial da garantia

-- Adicionar coluna filial_id
ALTER TABLE garantias 
ADD COLUMN filial_id INT NULL AFTER fornecedor_id,
ADD CONSTRAINT fk_garantias_filial 
    FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE RESTRICT;

-- Criar índice para melhor performance
CREATE INDEX idx_filial ON garantias(filial_id);

-- Comentário descritivo
ALTER TABLE garantias 
MODIFY COLUMN filial_id INT NULL COMMENT 'FK para tabela filiais - identifica a filial da garantia';
