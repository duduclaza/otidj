/* ===== MIGRATION: Adicionar campo descricao_defeito em garantias ===== */
/* Data: 09/10/2025 */
/* Descrição: Campo para descrever o defeito reportado na garantia */

ALTER TABLE garantias 
ADD COLUMN descricao_defeito TEXT NULL 
COMMENT 'Descrição detalhada do defeito reportado' 
AFTER observacao;

/* Verificar se foi criado */
SELECT 
    'Campo criado com sucesso!' as status,
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'garantias'
AND COLUMN_NAME = 'descricao_defeito';
