-- ===== TESTE RÁPIDO: Verificar Garantias com tipo_produto =====
-- Execute este script no phpMyAdmin para diagnosticar o problema

-- 1. Verificar se o campo tipo_produto existe
SHOW COLUMNS FROM garantias_itens LIKE 'tipo_produto';

-- 2. Contar garantias COM tipo_produto preenchido
SELECT 
    'Garantias COM tipo_produto' as status,
    COUNT(*) as total
FROM garantias_itens 
WHERE tipo_produto IS NOT NULL;

-- 3. Contar garantias SEM tipo_produto (PROBLEMA!)
SELECT 
    'Garantias SEM tipo_produto' as status,
    COUNT(*) as total
FROM garantias_itens 
WHERE tipo_produto IS NULL;

-- 4. Ver exemplos de garantias COM tipo_produto
SELECT 
    g.id as garantia_id,
    f.nome as fornecedor,
    gi.item,
    gi.tipo_produto,
    gi.quantidade,
    g.created_at
FROM garantias g
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
INNER JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE gi.tipo_produto IS NOT NULL
ORDER BY g.created_at DESC
LIMIT 10;

-- 5. Ver exemplos de garantias SEM tipo_produto (precisam ser corrigidas!)
SELECT 
    g.id as garantia_id,
    f.nome as fornecedor,
    gi.item,
    gi.tipo_produto,
    gi.quantidade,
    g.created_at
FROM garantias g
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
INNER JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE gi.tipo_produto IS NULL
ORDER BY g.created_at DESC
LIMIT 10;

-- 6. Query EXATA usada no dashboard (período: janeiro a outubro 2025)
SELECT 
    f.id as fornecedor_id,
    f.nome as fornecedor_nome,
    gi.tipo_produto,
    COUNT(gi.id) as total_garantias
FROM garantias g
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
INNER JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE DATE(g.created_at) BETWEEN '2025-01-01' AND '2025-10-31'
AND gi.tipo_produto IS NOT NULL
GROUP BY f.id, f.nome, gi.tipo_produto
ORDER BY f.nome, gi.tipo_produto;

-- 7. SOLUÇÃO: Atualizar garantias sem tipo_produto (SE NECESSÁRIO)
-- ATENÇÃO: Só execute se você confirmou que há garantias sem tipo_produto!

/*
-- Atualizar baseado em palavras-chave no campo 'item'
UPDATE garantias_itens 
SET tipo_produto = 'Toner'
WHERE tipo_produto IS NULL 
AND (
    item LIKE '%toner%' 
    OR item LIKE '%cartucho%'
    OR item LIKE '%cartridge%'
);

UPDATE garantias_itens 
SET tipo_produto = 'Máquina'
WHERE tipo_produto IS NULL 
AND (
    item LIKE '%impressora%' 
    OR item LIKE '%multifuncional%'
    OR item LIKE '%printer%'
    OR item LIKE '%copiadora%'
);

UPDATE garantias_itens 
SET tipo_produto = 'Peça'
WHERE tipo_produto IS NULL;

-- Verificar resultado
SELECT 
    tipo_produto,
    COUNT(*) as total
FROM garantias_itens
GROUP BY tipo_produto;
*/
