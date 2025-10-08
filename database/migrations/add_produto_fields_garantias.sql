-- ===== MIGRATION: Adicionar campos de produto na tabela garantias =====
-- Data: 2025-10-08
-- Objetivo: Padronizar seleção de produtos igual ao módulo Amostragens 2.0
-- Autor: Sistema SGQ OTI DJ

-- Adicionar novas colunas para seleção padronizada de produtos
ALTER TABLE garantias
ADD COLUMN tipo_produto ENUM('Toner', 'Máquina', 'Peça') NULL AFTER fornecedor_id,
ADD COLUMN produto_id INT NULL AFTER tipo_produto,
ADD COLUMN codigo_produto VARCHAR(100) NULL AFTER produto_id,
ADD COLUMN nome_produto VARCHAR(255) NULL AFTER codigo_produto;

-- Adicionar índice para busca por tipo de produto
ALTER TABLE garantias
ADD INDEX idx_tipo_produto (tipo_produto),
ADD INDEX idx_produto_id (produto_id),
ADD INDEX idx_codigo_produto (codigo_produto);

-- Comentários descritivos
ALTER TABLE garantias
MODIFY COLUMN tipo_produto ENUM('Toner', 'Máquina', 'Peça') NULL
    COMMENT 'Tipo do produto: Toner, Máquina ou Peça',
MODIFY COLUMN produto_id INT NULL 
    COMMENT 'ID do produto na tabela correspondente (toners, maquinas ou pecas)',
MODIFY COLUMN codigo_produto VARCHAR(100) NULL 
    COMMENT 'Código do produto para referência rápida',
MODIFY COLUMN nome_produto VARCHAR(255) NULL
    COMMENT 'Nome/descrição do produto';

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
