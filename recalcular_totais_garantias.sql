-- =====================================================
-- SCRIPT PARA RECALCULAR TOTAIS DAS GARANTIAS
-- Execute este script se os valores não estiverem aparecendo no grid
-- =====================================================

-- Atualizar totais de todas as garantias existentes
UPDATE garantias g SET
    total_itens = (
        SELECT COALESCE(COUNT(*), 0) 
        FROM garantias_itens gi 
        WHERE gi.garantia_id = g.id
    ),
    valor_total = (
        SELECT COALESCE(SUM(quantidade * valor_unitario), 0) 
        FROM garantias_itens gi 
        WHERE gi.garantia_id = g.id
    );

-- Verificar os resultados
SELECT 
    id,
    total_itens,
    valor_total,
    created_at
FROM garantias 
ORDER BY created_at DESC;

-- Verificar se há itens sem garantia (dados órfãos)
SELECT 
    gi.*,
    'ITEM ÓRFÃO - Garantia não existe' as problema
FROM garantias_itens gi
LEFT JOIN garantias g ON gi.garantia_id = g.id
WHERE g.id IS NULL;

-- Verificar garantias com totais zerados mas que têm itens
SELECT 
    g.id,
    g.total_itens,
    g.valor_total,
    COUNT(gi.id) as itens_reais,
    SUM(gi.quantidade * gi.valor_unitario) as valor_real
FROM garantias g
LEFT JOIN garantias_itens gi ON g.id = gi.garantia_id
GROUP BY g.id
HAVING (g.total_itens != COUNT(gi.id)) OR (g.valor_total != COALESCE(SUM(gi.quantidade * gi.valor_unitario), 0));

-- =====================================================
-- VERIFICAR SE OS TRIGGERS ESTÃO FUNCIONANDO
-- =====================================================

-- Mostrar triggers existentes
SHOW TRIGGERS LIKE 'garantias_itens%';

-- Se os triggers não existirem, recriar:
/*
DELIMITER $$

DROP TRIGGER IF EXISTS garantias_itens_after_insert$$
CREATE TRIGGER garantias_itens_after_insert
AFTER INSERT ON garantias_itens
FOR EACH ROW
BEGIN
    UPDATE garantias 
    SET 
        total_itens = (
            SELECT COUNT(*) 
            FROM garantias_itens 
            WHERE garantia_id = NEW.garantia_id
        ),
        valor_total = (
            SELECT COALESCE(SUM(quantidade * valor_unitario), 0) 
            FROM garantias_itens 
            WHERE garantia_id = NEW.garantia_id
        )
    WHERE id = NEW.garantia_id;
END$$

DROP TRIGGER IF EXISTS garantias_itens_after_update$$
CREATE TRIGGER garantias_itens_after_update
AFTER UPDATE ON garantias_itens
FOR EACH ROW
BEGIN
    UPDATE garantias 
    SET 
        total_itens = (
            SELECT COUNT(*) 
            FROM garantias_itens 
            WHERE garantia_id = NEW.garantia_id
        ),
        valor_total = (
            SELECT COALESCE(SUM(quantidade * valor_unitario), 0) 
            FROM garantias_itens 
            WHERE garantia_id = NEW.garantia_id
        )
    WHERE id = NEW.garantia_id;
END$$

DROP TRIGGER IF EXISTS garantias_itens_after_delete$$
CREATE TRIGGER garantias_itens_after_delete
AFTER DELETE ON garantias_itens
FOR EACH ROW
BEGIN
    UPDATE garantias 
    SET 
        total_itens = (
            SELECT COUNT(*) 
            FROM garantias_itens 
            WHERE garantia_id = OLD.garantia_id
        ),
        valor_total = (
            SELECT COALESCE(SUM(quantidade * valor_unitario), 0) 
            FROM garantias_itens 
            WHERE garantia_id = OLD.garantia_id
        )
    WHERE id = OLD.garantia_id;
END$$

DELIMITER ;
*/
