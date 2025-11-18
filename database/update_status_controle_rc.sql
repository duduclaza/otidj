-- Atualização da coluna status para Controle de RC
-- Data: 17/11/2025
-- Adiciona os novos status solicitados

-- Atualizar a coluna status com os novos valores
ALTER TABLE controle_rc 
MODIFY COLUMN status ENUM(
    'Em analise',
    'Aguardando ações do fornecedor', 
    'Aguardando retorno do produto',
    'Finalizado',
    'Concluída'
) NOT NULL DEFAULT 'Em analise'
COMMENT 'Status da reclamação: Em analise (padrão), Aguardando ações do fornecedor, Aguardando retorno do produto, Finalizado, Concluída';

-- Garantir que registros existentes tenham status válido
UPDATE controle_rc 
SET status = 'Em analise' 
WHERE status NOT IN (
    'Em analise',
    'Aguardando ações do fornecedor', 
    'Aguardando retorno do produto',
    'Finalizado',
    'Concluída'
);

-- Comentário explicativo dos status
-- 1. Em analise (padrão) - Status inicial quando RC é criado
-- 2. Aguardando ações do fornecedor - Aguardando providências do fornecedor
-- 3. Aguardando retorno do produto - Produto enviado para análise/troca
-- 4. Finalizado - RC resolvido mas aguardando confirmação final
-- 5. Concluída - RC totalmente concluído e fechado

-- Apenas usuários com permissão de edição podem alterar status
