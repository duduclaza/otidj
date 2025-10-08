-- ===== QUERY EXATA DO DASHBOARD =====

-- 1. Verificar se o fornecedor existe
SELECT 'Passo 1: Verificar fornecedor' as etapa;
SELECT id, nome FROM fornecedores WHERE id = 1;

-- 2. Verificar data da garantia
SELECT 'Passo 2: Data da garantia' as etapa;
SELECT 
    id,
    DATE(created_at) as data_garantia,
    CURDATE() as hoje,
    '2025-01-01' as inicio_periodo,
    '2025-12-31' as fim_periodo,
    CASE 
        WHEN DATE(created_at) BETWEEN '2025-01-01' AND '2025-12-31' 
        THEN 'DENTRO DO PERÍODO ✅'
        ELSE 'FORA DO PERÍODO ❌'
    END as status_periodo
FROM garantias
WHERE id = 8;

-- 3. Query EXATA usada no backend (como está no AdminController.php)
SELECT 'Passo 3: Query do backend (período amplo 2025)' as etapa;
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

-- 4. Mesma query SEM filtro de data (para debug)
SELECT 'Passo 4: Query SEM filtro de data' as etapa;
SELECT 
    f.id as fornecedor_id,
    f.nome as fornecedor_nome,
    gi.tipo_produto,
    COUNT(gi.id) as total_garantias,
    g.created_at as data_criacao
FROM garantias g
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
INNER JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE gi.tipo_produto IS NOT NULL
GROUP BY f.id, f.nome, gi.tipo_produto
ORDER BY f.nome, gi.tipo_produto;

-- 5. Ver se o JOIN está funcionando
SELECT 'Passo 5: Detalhamento do JOIN' as etapa;
SELECT 
    g.id as garantia_id,
    g.fornecedor_id,
    f.nome as fornecedor_nome,
    gi.id as item_id,
    gi.tipo_produto,
    gi.quantidade,
    g.created_at
FROM garantias g
LEFT JOIN garantias_itens gi ON g.id = gi.garantia_id
LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE g.id = 8;

-- 6. Verificar se fornecedor_id = 1 existe
SELECT 'Passo 6: Fornecedores disponíveis' as etapa;
SELECT id, nome FROM fornecedores LIMIT 5;
