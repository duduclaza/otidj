-- Migration: Calcular e atualizar campos derivados nos toners existentes
-- Data: 28/10/2025
-- Descrição: Calcular gramatura, gramatura_por_folha e custo_por_folha para toners que já existem no banco

-- Atualizar campos calculados para todos os toners que têm os dados necessários
UPDATE toners 
SET 
    gramatura = peso_cheio - peso_vazio,
    gramatura_por_folha = (peso_cheio - peso_vazio) / capacidade_folhas,
    custo_por_folha = preco_toner / capacidade_folhas
WHERE 
    peso_cheio IS NOT NULL 
    AND peso_vazio IS NOT NULL 
    AND capacidade_folhas > 0
    AND preco_toner IS NOT NULL
    AND (gramatura IS NULL OR gramatura_por_folha IS NULL OR custo_por_folha IS NULL);

-- Verificar resultados
SELECT 
    COUNT(*) as total_toners,
    SUM(CASE WHEN gramatura IS NOT NULL THEN 1 ELSE 0 END) as com_gramatura,
    SUM(CASE WHEN gramatura_por_folha IS NOT NULL THEN 1 ELSE 0 END) as com_gram_folha,
    SUM(CASE WHEN custo_por_folha IS NOT NULL THEN 1 ELSE 0 END) as com_custo_folha
FROM toners;
