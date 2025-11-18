-- RESETAR FUSO HORÁRIO APENAS DA SESSÃO ATUAL
-- Não requer privilégios SUPER

-- Resetar apenas a sessão atual
SET time_zone = 'SYSTEM';

-- Verificar
SELECT @@session.time_zone;
SELECT NOW() as horario_atual;

SELECT 'Fuso horário da sessão resetado' as resultado;
