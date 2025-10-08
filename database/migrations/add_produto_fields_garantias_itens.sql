-- ===== MIGRATION: Adicionar campos de produto na tabela garantias_itens =====
-- Data: 2025-10-08
-- Objetivo: Padronizar seleção de produtos igual ao módulo Amostragens 2.0
-- Autor: Sistema SGQ OTI DJ

-- Verificar se as colunas já existem antes de adicionar
SET @dbname = DATABASE();
SET @tablename = 'garantias_itens';

-- Adicionar novas colunas para seleção padronizada de produtos (SE NÃO EXISTIREM)
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = @dbname 
     AND TABLE_NAME = @tablename 
     AND COLUMN_NAME = 'tipo_produto') = 0,
    'ALTER TABLE garantias_itens 
     ADD COLUMN tipo_produto ENUM(''Toner'', ''Máquina'', ''Peça'') NULL AFTER descricao,
     ADD COLUMN produto_id INT NULL AFTER tipo_produto,
     ADD COLUMN codigo_produto VARCHAR(100) NULL AFTER produto_id,
     ADD COLUMN nome_produto VARCHAR(255) NULL AFTER codigo_produto',
    'SELECT ''Colunas já existem'' AS resultado'
));

PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Adicionar índices para busca por tipo de produto (SE NÃO EXISTIREM)
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = @dbname 
     AND TABLE_NAME = @tablename 
     AND INDEX_NAME = 'idx_tipo_produto') = 0,
    'ALTER TABLE garantias_itens 
     ADD INDEX idx_tipo_produto (tipo_produto),
     ADD INDEX idx_produto_id (produto_id),
     ADD INDEX idx_codigo_produto (codigo_produto)',
    'SELECT ''Índices já existem'' AS resultado'
));

PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ===== NOTAS DE IMPLEMENTAÇÃO =====
/*
FRONTEND (Formulário de Garantias):

1. Adicionar select para escolher tipo_produto (Toner, Máquina, Peça)
2. Baseado no tipo, carregar lista de produtos da API:
   - Toner: GET /api/toners
   - Máquina: GET /api/maquinas
   - Peça: GET /api/pecas

3. Ao selecionar produto, preencher automaticamente:
   - produto_id: ID do item selecionado
   - codigo_produto: Código do produto
   - nome_produto: Nome do produto

BACKEND (Controller de Garantias):

1. Validar tipo_produto ao criar garantia
2. Buscar dados do produto na tabela correspondente
3. Preencher campos automaticamente

EXEMPLO DE DADOS NA TABELA GARANTIAS:
- fornecedor_id: 45
- tipo_produto: 'Toner'
- produto_id: 123
- codigo_produto: 'TN-2370'
- nome_produto: 'Toner Brother TN-2370 Original'
- numero_nf_compras: '123456'
- status: 'Em andamento'
*/
