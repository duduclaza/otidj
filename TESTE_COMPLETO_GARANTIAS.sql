-- ===== TESTE COMPLETO: Verificar todo o fluxo de garantias =====

-- 1. Ver o registro em garantias_itens
SELECT '1. Registro em garantias_itens' as passo;
SELECT * FROM garantias_itens WHERE id = 11;

-- 2. Verificar se existe garantia com id = 8
SELECT '2. Verificar garantia id = 8' as passo;
SELECT * FROM garantias WHERE id = 8;

-- 3. JOIN completo (como o backend faz)
SELECT '3. JOIN completo (query do dashboard)' as passo;
SELECT 
    f.id as fornecedor_id,
    f.nome as fornecedor_nome,
    gi.tipo_produto,
    COUNT(gi.id) as total_garantias
FROM garantias g
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
INNER JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE DATE(g.created_at) BETWEEN '2025-01-01' AND '2025-12-31'
AND gi.tipo_produto IS NOT NULL
GROUP BY f.id, f.nome, gi.tipo_produto
ORDER BY f.nome, gi.tipo_produto;

-- 4. Verificar se o fornecedor existe
SELECT '4. Verificar fornecedor' as passo;
SELECT g.fornecedor_id, f.nome 
FROM garantias g
LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE g.id = 8;

-- 5. Ver TODAS as garantias
SELECT '5. Todas as garantias' as passo;
SELECT COUNT(*) as total FROM garantias;

-- 6. Ver TODOS os itens
SELECT '6. Todos os itens de garantias' as passo;
SELECT COUNT(*) as total FROM garantias_itens;

-- 7. Query exata que o PHP está executando (com logs)
SELECT '7. Query exata do backend (período: hoje)' as passo;
SELECT 
    f.id as fornecedor_id,
    f.nome as fornecedor_nome,
    gi.tipo_produto,
    COUNT(gi.id) as total_garantias,
    g.created_at as data_garantia
FROM garantias g
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
INNER JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE DATE(g.created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 90 DAY) AND CURDATE()
AND gi.tipo_produto IS NOT NULL
GROUP BY f.id, f.nome, gi.tipo_produto
ORDER BY f.nome, gi.tipo_produto;

-- 8. Verificar data da garantia (pode estar fora do período)
SELECT '8. Data da garantia id = 8' as passo;
SELECT 
    id,
    created_at,
    DATE(created_at) as data,
    CURDATE() as hoje,
    DATEDIFF(CURDATE(), DATE(created_at)) as dias_atras
FROM garantias 
WHERE id = 8;
