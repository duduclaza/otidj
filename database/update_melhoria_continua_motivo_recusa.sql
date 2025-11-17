-- ========================================
-- SQL PARA ADICIONAR SUPORTE A MOTIVO DE RECUSA
-- Módulo: Melhoria Contínua 2.0
-- Data: 17/11/2025
-- ========================================

-- Verificar se a coluna 'observacao' já existe
-- Se não existir, será criada. Se existir, será expandida.

-- 1. Adicionar ou modificar coluna observacao
ALTER TABLE `melhoria_continua_2` 
MODIFY COLUMN `observacao` TEXT NULL 
COMMENT 'Observações gerais. Quando status=Recusada, armazena o motivo com prefixo RECUSADA:';

-- 2. Verificar estrutura da tabela
DESCRIBE `melhoria_continua_2`;

-- ========================================
-- EXEMPLO DE USO
-- ========================================

-- Quando uma melhoria é recusada, o sistema salva assim:
-- UPDATE melhoria_continua_2 
-- SET status = 'Recusada', 
--     observacao = 'RECUSADA: Não está alinhado com os objetivos estratégicos',
--     updated_at = NOW()
-- WHERE id = 123;

-- ========================================
-- CONSULTA PARA VER MELHORIAS RECUSADAS
-- ========================================

SELECT 
    id,
    titulo,
    status,
    REPLACE(observacao, 'RECUSADA: ', '') as motivo_recusa,
    created_at,
    updated_at
FROM melhoria_continua_2
WHERE status = 'Recusada'
ORDER BY updated_at DESC;

-- ========================================
-- SCRIPT COMPLETO EXECUTADO
-- ========================================
