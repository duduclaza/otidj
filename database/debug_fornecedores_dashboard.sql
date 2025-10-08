-- ===== DEBUG: Dashboard de Qualidade de Fornecedores =====
-- Script para verificar se há dados disponíveis nas tabelas
-- Data: 2025-10-08

-- ========================================
-- 1. VERIFICAR ESTRUTURA DAS TABELAS
-- ========================================

-- Verificar se a tabela amostragens_2 existe e sua estrutura
SHOW TABLES LIKE 'amostragens_2';
DESCRIBE amostragens_2;

-- Verificar se a tabela garantias_itens tem o campo tipo_produto
SHOW COLUMNS FROM garantias_itens LIKE 'tipo_produto';

-- ========================================
-- 2. CONTAR REGISTROS DISPONÍVEIS
-- ========================================

-- Total de amostragens 2.0
SELECT 
    'Amostragens 2.0' as tabela,
    COUNT(*) as total_registros
FROM amostragens_2;

-- Amostragens por tipo de produto
SELECT 
    tipo_produto,
    COUNT(*) as total,
    SUM(quantidade_recebida) as total_comprados,
    MIN(created_at) as data_mais_antiga,
    MAX(created_at) as data_mais_recente
FROM amostragens_2
GROUP BY tipo_produto;

-- Total de garantias
SELECT 
    'Garantias' as tabela,
    COUNT(*) as total_registros
FROM garantias;

-- Garantias com tipo_produto preenchido
SELECT 
    gi.tipo_produto,
    COUNT(gi.id) as total_itens_garantia,
    COUNT(DISTINCT g.id) as total_garantias,
    MIN(g.created_at) as data_mais_antiga,
    MAX(g.created_at) as data_mais_recente
FROM garantias g
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
WHERE gi.tipo_produto IS NOT NULL
GROUP BY gi.tipo_produto;

-- Garantias SEM tipo_produto (problema!)
SELECT 
    COUNT(*) as garantias_sem_tipo_produto
FROM garantias_itens
WHERE tipo_produto IS NULL;

-- ========================================
-- 3. VERIFICAR FORNECEDORES
-- ========================================

-- Fornecedores com amostragens
SELECT 
    f.id,
    f.nome,
    COUNT(DISTINCT a.id) as total_amostragens,
    SUM(a.quantidade_recebida) as total_comprados
FROM fornecedores f
INNER JOIN amostragens_2 a ON f.id = a.fornecedor_id
GROUP BY f.id, f.nome
ORDER BY total_amostragens DESC
LIMIT 10;

-- Fornecedores com garantias
SELECT 
    f.id,
    f.nome,
    COUNT(DISTINCT g.id) as total_garantias,
    COUNT(gi.id) as total_itens_garantia
FROM fornecedores f
INNER JOIN garantias g ON f.id = g.fornecedor_id
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
WHERE gi.tipo_produto IS NOT NULL
GROUP BY f.id, f.nome
ORDER BY total_garantias DESC
LIMIT 10;

-- ========================================
-- 4. TESTE DA QUERY PRINCIPAL (COMPRADOS)
-- ========================================

-- Query exata usada no dashboard (últimos 90 dias)
SELECT 
    f.id as fornecedor_id,
    f.nome as fornecedor_nome,
    a.tipo_produto,
    SUM(a.quantidade_recebida) as total_comprados,
    COUNT(a.id) as total_amostragens
FROM amostragens_2 a
INNER JOIN fornecedores f ON a.fornecedor_id = f.id
INNER JOIN filiais fil ON a.filial_id = fil.id
WHERE DATE(a.created_at) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
GROUP BY f.id, f.nome, a.tipo_produto
ORDER BY f.nome, a.tipo_produto;

-- ========================================
-- 5. TESTE DA QUERY PRINCIPAL (GARANTIAS)
-- ========================================

-- Query exata usada no dashboard (últimos 90 dias)
SELECT 
    f.id as fornecedor_id,
    f.nome as fornecedor_nome,
    gi.tipo_produto,
    COUNT(gi.id) as total_garantias,
    COUNT(DISTINCT g.id) as total_garantias_distintas
FROM garantias g
INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
INNER JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE DATE(g.created_at) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
AND gi.tipo_produto IS NOT NULL
GROUP BY f.id, f.nome, gi.tipo_produto
ORDER BY f.nome, gi.tipo_produto;

-- ========================================
-- 6. TESTE COMPLETO SIMULANDO DASHBOARD
-- ========================================

-- Simular cálculo de qualidade (ano de 2025)
SELECT 
    f.nome as fornecedor,
    a.tipo_produto,
    SUM(a.quantidade_recebida) as comprados,
    COALESCE(g.garantias, 0) as garantias,
    ROUND(((SUM(a.quantidade_recebida) - COALESCE(g.garantias, 0)) / SUM(a.quantidade_recebida)) * 100, 2) as qualidade_pct
FROM amostragens_2 a
INNER JOIN fornecedores f ON a.fornecedor_id = f.id
LEFT JOIN (
    SELECT 
        g.fornecedor_id,
        gi.tipo_produto,
        COUNT(gi.id) as garantias
    FROM garantias g
    INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
    WHERE YEAR(g.created_at) = 2025
    AND gi.tipo_produto IS NOT NULL
    GROUP BY g.fornecedor_id, gi.tipo_produto
) g ON f.id = g.fornecedor_id AND a.tipo_produto = g.tipo_produto
WHERE YEAR(a.created_at) = 2025
GROUP BY f.id, f.nome, a.tipo_produto, g.garantias
ORDER BY qualidade_pct ASC;

-- ========================================
-- 7. DIAGNÓSTICO DE PROBLEMAS
-- ========================================

-- Verificar amostragens sem fornecedor (FK inválido)
SELECT COUNT(*) as amostragens_sem_fornecedor
FROM amostragens_2 a
LEFT JOIN fornecedores f ON a.fornecedor_id = f.id
WHERE f.id IS NULL;

-- Verificar garantias sem fornecedor (FK inválido)
SELECT COUNT(*) as garantias_sem_fornecedor
FROM garantias g
LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
WHERE f.id IS NULL;

-- Verificar amostragens sem filial (FK inválido)
SELECT COUNT(*) as amostragens_sem_filial
FROM amostragens_2 a
LEFT JOIN filiais fil ON a.filial_id = fil.id
WHERE fil.id IS NULL;

-- ========================================
-- 8. ESTATÍSTICAS GERAIS
-- ========================================

SELECT 
    'Resumo Geral' as informacao,
    (SELECT COUNT(*) FROM fornecedores) as total_fornecedores,
    (SELECT COUNT(*) FROM amostragens_2) as total_amostragens,
    (SELECT COUNT(*) FROM garantias) as total_garantias,
    (SELECT COUNT(*) FROM garantias_itens WHERE tipo_produto IS NOT NULL) as garantias_com_tipo_produto,
    (SELECT COUNT(*) FROM garantias_itens WHERE tipo_produto IS NULL) as garantias_sem_tipo_produto;

-- ========================================
-- 9. SUGESTÃO DE CORREÇÃO
-- ========================================

-- Se existirem garantias_itens sem tipo_produto, 
-- você pode atualizar manualmente ou criar uma lógica para preencher

-- Exemplo: Definir como 'Toner' itens que contenham palavras-chave
/*
UPDATE garantias_itens 
SET tipo_produto = 'Toner'
WHERE tipo_produto IS NULL 
AND (item LIKE '%toner%' OR item LIKE '%cartucho%');

UPDATE garantias_itens 
SET tipo_produto = 'Máquina'
WHERE tipo_produto IS NULL 
AND (item LIKE '%impressora%' OR item LIKE '%multifuncional%');

UPDATE garantias_itens 
SET tipo_produto = 'Peça'
WHERE tipo_produto IS NULL 
AND tipo_produto IS NULL;
*/
