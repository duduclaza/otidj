-- ===== CORRIGIR: Dados órfãos em garantias_itens =====

-- PROBLEMA: garantias_itens tem registros, mas garantias está vazia
-- SOLUÇÃO: Criar registros na tabela garantias ou remover dados órfãos

-- PASSO 1: Ver registros órfãos (garantias_itens sem garantias)
SELECT 'Registros órfãos em garantias_itens' as diagnostico;
SELECT 
    gi.id,
    gi.garantia_id,
    gi.item,
    gi.tipo_produto,
    gi.quantidade,
    'Garantia não existe!' as problema
FROM garantias_itens gi
LEFT JOIN garantias g ON gi.garantia_id = g.id
WHERE g.id IS NULL;

-- PASSO 2: Ver IDs de garantias que deveriam existir
SELECT 'IDs de garantias que faltam' as diagnostico;
SELECT DISTINCT garantia_id 
FROM garantias_itens;

-- PASSO 3: Verificar estrutura da tabela garantias
SELECT 'Estrutura da tabela garantias' as diagnostico;
DESCRIBE garantias;

-- ===== OPÇÃO 1: CRIAR REGISTROS FALTANTES EM GARANTIAS =====
-- Execute isso SE você quer manter os dados de garantias_itens

-- Criar registros na tabela garantias para os IDs órfãos
-- ATENÇÃO: Ajuste fornecedor_id e created_by conforme seu sistema!
/*
INSERT INTO garantias (id, fornecedor_id, origem_garantia, status, created_by, created_at)
SELECT 
    gi.garantia_id,
    1 as fornecedor_id, -- ⚠️ AJUSTAR: ID do fornecedor correto
    'Em Campo' as origem_garantia,
    'Em andamento' as status,
    1 as created_by, -- ⚠️ AJUSTAR: ID do usuário correto
    gi.created_at
FROM garantias_itens gi
LEFT JOIN garantias g ON gi.garantia_id = g.id
WHERE g.id IS NULL
GROUP BY gi.garantia_id, gi.created_at;
*/

-- ===== OPÇÃO 2: REMOVER DADOS ÓRFÃOS =====
-- Execute isso SE os dados em garantias_itens são inválidos

/*
DELETE FROM garantias_itens 
WHERE garantia_id NOT IN (SELECT id FROM garantias);
*/

-- ===== VERIFICAÇÃO FINAL =====
-- Execute após escolher uma das opções acima

-- Contar registros
SELECT 
    'Garantias' as tabela,
    COUNT(*) as total
FROM garantias
UNION ALL
SELECT 
    'Garantias Itens' as tabela,
    COUNT(*) as total
FROM garantias_itens;

-- Ver join correto
SELECT 
    g.id as garantia_id,
    g.fornecedor_id,
    f.nome as fornecedor,
    g.origem_garantia,
    gi.item,
    gi.tipo_produto,
    gi.quantidade,
    g.created_at
FROM garantias g
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
ORDER BY g.id;
