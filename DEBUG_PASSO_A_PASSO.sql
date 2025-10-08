-- ===== DEBUG PASSO A PASSO =====

-- PASSO 1: Ver a data exata da garantia
SELECT 
    'PASSO 1: Data da garantia' as teste,
    id,
    created_at,
    DATE(created_at) as data_formatada,
    YEAR(created_at) as ano,
    MONTH(created_at) as mes,
    DAY(created_at) as dia
FROM garantias 
WHERE id = 8;

-- PASSO 2: Ver se a data está no período
SELECT 
    'PASSO 2: Verificar período' as teste,
    DATE(created_at) as data_garantia,
    '2025-01-01' as inicio,
    '2025-12-31' as fim,
    CASE 
        WHEN DATE(created_at) >= '2025-01-01' AND DATE(created_at) <= '2025-12-31' 
        THEN '✅ DENTRO'
        ELSE '❌ FORA'
    END as esta_no_periodo
FROM garantias
WHERE id = 8;

-- PASSO 3: Ver o garantias_itens.tipo_produto
SELECT 
    'PASSO 3: tipo_produto' as teste,
    id,
    garantia_id,
    descricao,
    tipo_produto,
    CASE 
        WHEN tipo_produto IS NULL THEN '❌ NULL'
        ELSE '✅ PREENCHIDO'
    END as status_tipo
FROM garantias_itens
WHERE garantia_id = 8;

-- PASSO 4: Ver se o fornecedor existe
SELECT 
    'PASSO 4: Fornecedor' as teste,
    g.id as garantia_id,
    g.fornecedor_id,
    f.nome as fornecedor_nome,
    CASE 
        WHEN f.id IS NULL THEN '❌ NÃO EXISTE'
        ELSE '✅ EXISTE'
    END as status_fornecedor
FROM garantias g
LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE g.id = 8;

-- PASSO 5: JOIN simples (SEM WHERE)
SELECT 
    'PASSO 5: JOIN sem filtros' as teste,
    g.id as garantia_id,
    g.fornecedor_id,
    f.nome as fornecedor_nome,
    gi.tipo_produto,
    COUNT(gi.id) as total
FROM garantias g
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
INNER JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE g.id = 8
GROUP BY g.id, f.id, f.nome, gi.tipo_produto;

-- PASSO 6: Adicionar filtro de data
SELECT 
    'PASSO 6: JOIN com filtro de data' as teste,
    g.id as garantia_id,
    DATE(g.created_at) as data_garantia,
    g.fornecedor_id,
    f.nome as fornecedor_nome,
    gi.tipo_produto,
    COUNT(gi.id) as total
FROM garantias g
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
INNER JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE g.id = 8
AND DATE(g.created_at) BETWEEN '2025-01-01' AND '2025-12-31'
GROUP BY g.id, f.id, f.nome, gi.tipo_produto;

-- PASSO 7: Adicionar filtro de tipo_produto
SELECT 
    'PASSO 7: JOIN com TODOS os filtros' as teste,
    g.id as garantia_id,
    DATE(g.created_at) as data_garantia,
    g.fornecedor_id,
    f.nome as fornecedor_nome,
    gi.tipo_produto,
    COUNT(gi.id) as total
FROM garantias g
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
INNER JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE g.id = 8
AND DATE(g.created_at) BETWEEN '2025-01-01' AND '2025-12-31'
AND gi.tipo_produto IS NOT NULL
GROUP BY g.id, f.id, f.nome, gi.tipo_produto;
