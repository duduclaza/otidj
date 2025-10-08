-- ===== FIX: Verificar e corrigir dados de garantias =====

-- PASSO 1: Ver se há registros em garantias_itens
SELECT 'Passo 1: Contando garantias_itens' as etapa;
SELECT COUNT(*) as total_garantias_itens FROM garantias_itens;

-- PASSO 2: Ver estrutura de garantias_itens (verificar se tipo_produto existe)
SELECT 'Passo 2: Estrutura de garantias_itens' as etapa;
DESCRIBE garantias_itens;

-- PASSO 3: Ver os 7 registros de garantias_itens
SELECT 'Passo 3: Ver dados de garantias_itens' as etapa;
SELECT 
    gi.id,
    gi.garantia_id,
    gi.item,
    gi.tipo_produto,
    gi.produto_id,
    gi.codigo_produto,
    gi.nome_produto,
    gi.quantidade,
    gi.created_at
FROM garantias_itens gi
ORDER BY gi.id;

-- PASSO 4: Ver se há registros na tabela garantias (principal)
SELECT 'Passo 4: Contando garantias (principal)' as etapa;
SELECT COUNT(*) as total_garantias FROM garantias;

-- PASSO 5: Ver dados de garantias (se houver)
SELECT 'Passo 5: Ver dados de garantias' as etapa;
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
ORDER BY g.id;

-- PASSO 6: Join completo para ver relacionamento
SELECT 'Passo 6: Join garantias + garantias_itens' as etapa;
SELECT 
    g.id as garantia_id,
    g.fornecedor_id,
    f.nome as fornecedor,
    g.origem_garantia,
    g.created_at as data_garantia,
    gi.id as item_id,
    gi.item,
    gi.tipo_produto,
    gi.quantidade
FROM garantias g
LEFT JOIN garantias_itens gi ON g.id = gi.garantia_id
LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
ORDER BY g.id, gi.id;

-- PASSO 7: Se tipo_produto estiver NULL, precisamos preencher
-- Verificar quantos itens não têm tipo_produto
SELECT 'Passo 7: Itens SEM tipo_produto' as etapa;
SELECT 
    COUNT(*) as total_sem_tipo,
    COUNT(*) * 100.0 / (SELECT COUNT(*) FROM garantias_itens) as percentual
FROM garantias_itens 
WHERE tipo_produto IS NULL;
