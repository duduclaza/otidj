-- RESETAR FUSO HORÁRIO DO MYSQL PARA O PADRÃO
-- Execute este comando para voltar ao normal

-- Resetar para o fuso horário do sistema
SET GLOBAL time_zone = 'SYSTEM';
SET SESSION time_zone = 'SYSTEM';

-- Verificar
SELECT @@global.time_zone, @@session.time_zone;
SELECT NOW() as horario_atual;

SELECT 'Fuso horário resetado para SYSTEM' as resultado;
