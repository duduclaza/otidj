-- Adicionar coluna descricao na tabela melhoria_continua_2
ALTER TABLE melhoria_continua_2 
ADD COLUMN descricao TEXT NULL AFTER titulo;

-- Verificar a alteração
DESCRIBE melhoria_continua_2;

-- Se você quiser copiar o conteúdo de resultado_esperado para descricao (opcional)
-- UPDATE melhoria_continua_2 SET descricao = resultado_esperado WHERE descricao IS NULL;
