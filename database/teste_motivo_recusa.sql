-- ========================================
-- SCRIPT DE TESTE - MOTIVO DE RECUSA
-- Melhoria Contínua 2.0
-- Data: 17/11/2025
-- ========================================

-- 1. VERIFICAR SE A COLUNA OBSERVACAO EXISTE
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    CHARACTER_MAXIMUM_LENGTH,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'melhoria_continua_2'
  AND COLUMN_NAME = 'observacao';

-- ========================================
-- 2. SE A COLUNA NÃO EXISTIR, ADICIONAR
-- ========================================

-- Descomentar se necessário:
-- ALTER TABLE `melhoria_continua_2` 
-- ADD COLUMN `observacao` TEXT NULL 
-- COMMENT 'Observações gerais. Quando status=Recusada, armazena RECUSADA: [motivo]'
-- AFTER `pontuacao`;

-- ========================================
-- 3. TESTE: INSERIR MELHORIA DE EXEMPLO
-- ========================================

-- Inserir uma melhoria de teste
INSERT INTO `melhoria_continua_2` (
    titulo,
    departamento_id,
    descricao,
    o_que,
    como,
    onde,
    porque,
    quando,
    quanto_custa,
    responsaveis,
    resultado_esperado,
    idealizador,
    status,
    observacao,
    criado_por
) VALUES (
    'Teste - Melhoria para Recusa',
    1,
    'Descrição de teste para validar sistema de recusa',
    'Implementar processo de teste',
    'Através de testes automatizados',
    'Departamento de TI',
    'Para validar funcionalidade',
    '2025-12-31',
    1000.00,
    '1,2',
    'Sistema funcionando 100%',
    'Administrador Teste',
    'Pendente análise',
    NULL,
    1
);

-- Pegar o ID da melhoria inserida
SET @teste_id = LAST_INSERT_ID();

-- ========================================
-- 4. TESTE: RECUSAR MELHORIA COM MOTIVO
-- ========================================

-- Simular recusa com motivo
UPDATE `melhoria_continua_2` 
SET 
    status = 'Recusada',
    observacao = 'RECUSADA: Esta melhoria não está alinhada com os objetivos estratégicos da empresa para 2025',
    updated_at = NOW()
WHERE id = @teste_id;

-- ========================================
-- 5. VERIFICAR RESULTADO
-- ========================================

SELECT 
    id,
    titulo,
    status,
    observacao as observacao_completa,
    REPLACE(observacao, 'RECUSADA: ', '') as motivo_recusa,
    created_at,
    updated_at
FROM `melhoria_continua_2`
WHERE id = @teste_id;

-- ========================================
-- 6. VERIFICAR TODAS AS RECUSADAS
-- ========================================

SELECT 
    id,
    titulo,
    departamento_id,
    status,
    REPLACE(observacao, 'RECUSADA: ', '') as motivo_recusa,
    criado_por,
    DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as data_criacao,
    DATE_FORMAT(updated_at, '%d/%m/%Y %H:%i') as data_atualizacao
FROM `melhoria_continua_2`
WHERE status = 'Recusada'
  AND observacao LIKE 'RECUSADA:%'
ORDER BY updated_at DESC;

-- ========================================
-- 7. ESTATÍSTICAS
-- ========================================

SELECT 
    status,
    COUNT(*) as total,
    COUNT(CASE WHEN observacao LIKE 'RECUSADA:%' THEN 1 END) as total_com_motivo,
    COUNT(CASE WHEN observacao IS NULL OR observacao NOT LIKE 'RECUSADA:%' THEN 1 END) as total_sem_motivo
FROM `melhoria_continua_2`
GROUP BY status
ORDER BY 
    FIELD(status, 'Pendente análise', 'Enviado para Aprovação', 'Em andamento', 'Concluída', 'Recusada', 'Pendente Adaptação');

-- ========================================
-- 8. LIMPAR TESTE (OPCIONAL)
-- ========================================

-- Descomentar para deletar a melhoria de teste:
-- DELETE FROM `melhoria_continua_2` WHERE id = @teste_id;

-- ========================================
-- RESULTADO ESPERADO
-- ========================================

-- A query deve retornar:
-- - observacao_completa: "RECUSADA: Esta melhoria não está alinhada..."
-- - motivo_recusa: "Esta melhoria não está alinhada..."
-- - status: "Recusada"

-- ========================================
-- NOTAS IMPORTANTES
-- ========================================

-- 1. O prefixo "RECUSADA: " é adicionado automaticamente pelo sistema
-- 2. O motivo é armazenado no campo 'observacao'
-- 3. Para exibir apenas o motivo, use REPLACE(observacao, 'RECUSADA: ', '')
-- 4. O campo aceita textos longos (TEXT = até 65,535 caracteres)
-- 5. Emails são enviados automaticamente quando status muda para Recusada
