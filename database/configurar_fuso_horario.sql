-- CONFIGURAR FUSO HORÁRIO DO BRASIL NO MYSQL
-- Execute estes comandos para ajustar o horário

-- 1. Verificar fuso horário atual
SELECT @@global.time_zone, @@session.time_zone;

-- 2. Configurar fuso horário para o Brasil (UTC-3)
SET time_zone = '-03:00';

-- 3. Verificar se mudou
SELECT NOW() as horario_atual;

-- 4. Para configurar permanentemente, adicione no arquivo my.cnf ou my.ini:
-- [mysqld]
-- default-time-zone = '-03:00'

-- 5. Atualizar registros existentes para o horário correto (OPCIONAL)
-- CUIDADO: Isso vai alterar todos os registros existentes
-- UPDATE homologacoes_historico 
-- SET created_at = CONVERT_TZ(created_at, '+00:00', '-03:00'),
--     data_acao = CONVERT_TZ(data_acao, '+00:00', '-03:00')
-- WHERE created_at IS NOT NULL;

SELECT 'Fuso horário configurado para o Brasil (UTC-3)' as resultado;
