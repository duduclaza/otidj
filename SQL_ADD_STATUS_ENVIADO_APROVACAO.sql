-- ============================================
-- ADICIONAR STATUS "Enviado para Aprovação"
-- Tabela: melhoria_continua_2
-- Data: 05/11/2025
-- ============================================

-- Alterar o ENUM do campo status para incluir o novo valor
ALTER TABLE melhoria_continua_2 
MODIFY COLUMN status ENUM(
    'Pendente análise',
    'Enviado para Aprovação',
    'Em andamento',
    'Concluída',
    'Recusada',
    'Pendente Adaptação'
) DEFAULT 'Pendente análise';

-- Verificar a alteração
DESCRIBE melhoria_continua_2;

-- Testar inserção (opcional)
-- SELECT * FROM melhoria_continua_2 WHERE status = 'Enviado para Aprovação';
