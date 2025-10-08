-- ===== TESTE RÁPIDO: Ver dados de garantias_itens =====

-- 1. Ver estrutura da tabela (verificar se tipo_produto existe)
DESCRIBE garantias_itens;

-- 2. Ver todos os 7 registros
SELECT * FROM garantias_itens;

-- 3. Contar quantos têm tipo_produto preenchido
SELECT 
    COUNT(*) as total,
    COUNT(tipo_produto) as com_tipo_produto,
    COUNT(*) - COUNT(tipo_produto) as sem_tipo_produto
FROM garantias_itens;

-- 4. Ver quais tipos de produto existem
SELECT 
    tipo_produto,
    COUNT(*) as quantidade
FROM garantias_itens
GROUP BY tipo_produto;
