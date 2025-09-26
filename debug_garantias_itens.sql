-- =====================================================
-- DEBUG: VERIFICAR ESTRUTURA DA TABELA GARANTIAS_ITENS
-- =====================================================

-- 1. Verificar se a tabela existe
SHOW TABLES LIKE 'garantias_itens';

-- 2. Verificar estrutura da tabela
DESCRIBE garantias_itens;

-- 3. Verificar dados existentes
SELECT COUNT(*) as total_itens FROM garantias_itens;

-- 4. Verificar itens por garantia
SELECT 
    garantia_id,
    COUNT(*) as total_itens,
    SUM(quantidade) as total_quantidade,
    SUM(quantidade * valor_unitario) as valor_total
FROM garantias_itens 
GROUP BY garantia_id;

-- 5. Verificar garantias sem itens
SELECT g.id, g.fornecedor_id, g.total_itens, g.valor_total
FROM garantias g
LEFT JOIN garantias_itens gi ON g.id = gi.garantia_id
WHERE gi.garantia_id IS NULL;

-- 6. Verificar inconsistências nos totais
SELECT 
    g.id,
    g.total_itens as total_calculado,
    COUNT(gi.id) as total_real,
    g.valor_total as valor_calculado,
    COALESCE(SUM(gi.quantidade * gi.valor_unitario), 0) as valor_real
FROM garantias g
LEFT JOIN garantias_itens gi ON g.id = gi.garantia_id
GROUP BY g.id
HAVING (g.total_itens != COUNT(gi.id)) OR (g.valor_total != COALESCE(SUM(gi.quantidade * gi.valor_unitario), 0));

-- 7. Verificar triggers existentes
SHOW TRIGGERS LIKE 'garantias_itens%';

-- 8. Exemplo de inserção manual para teste
/*
INSERT INTO garantias_itens (garantia_id, descricao, quantidade, valor_unitario) 
VALUES (1, 'Teste Item', 2, 100.50);
*/

-- 9. Verificar últimos itens inseridos
SELECT * FROM garantias_itens ORDER BY id DESC LIMIT 10;

-- 10. Verificar se há problemas de encoding
SELECT 
    id,
    garantia_id,
    descricao,
    LENGTH(descricao) as tamanho_descricao,
    quantidade,
    valor_unitario,
    valor_total
FROM garantias_itens 
ORDER BY id DESC 
LIMIT 5;
