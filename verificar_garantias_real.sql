-- ===== VERIFICAR SE HÁ DADOS DE GARANTIAS NO SISTEMA =====

-- 1. Contar registros na tabela garantias
SELECT 
    'Tabela garantias' as tabela,
    COUNT(*) as total_registros,
    MIN(created_at) as data_mais_antiga,
    MAX(created_at) as data_mais_recente
FROM garantias;

-- 2. Contar registros na tabela garantias_itens
SELECT 
    'Tabela garantias_itens' as tabela,
    COUNT(*) as total_registros,
    MIN(created_at) as data_mais_antiga,
    MAX(created_at) as data_mais_recente
FROM garantias_itens;

-- 3. Se houver garantias, mostrar exemplos
SELECT 
    g.id,
    g.fornecedor_id,
    f.nome as fornecedor,
    g.origem_garantia,
    g.status,
    g.total_itens,
    g.created_at
FROM garantias g
LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
ORDER BY g.created_at DESC
LIMIT 10;

-- 4. Se houver garantias_itens, mostrar exemplos COM tipo_produto
SELECT 
    gi.id,
    gi.garantia_id,
    gi.item,
    gi.tipo_produto,
    gi.quantidade,
    gi.created_at
FROM garantias_itens gi
WHERE gi.tipo_produto IS NOT NULL
ORDER BY gi.created_at DESC
LIMIT 10;

-- 5. Mostrar garantias_itens SEM tipo_produto (problema!)
SELECT 
    gi.id,
    gi.garantia_id,
    gi.item,
    gi.tipo_produto,
    gi.quantidade,
    gi.created_at
FROM garantias_itens gi
WHERE gi.tipo_produto IS NULL
ORDER BY gi.created_at DESC
LIMIT 10;

-- 6. Verificar estrutura da tabela garantias_itens
DESCRIBE garantias_itens;

-- 7. Se NÃO houver dados de garantias, precisamos criar dados de exemplo
-- ou verificar se o módulo de garantias foi usado no sistema
