-- Script SQL para adicionar colunas de aprovação na tabela pops_its_registros
-- Execute este script no banco de dados para habilitar as funcionalidades das Abas 3 e 4

-- Adicionar colunas de aprovação
ALTER TABLE pops_its_registros
  ADD COLUMN IF NOT EXISTS aprovado_por INT NULL,
  ADD COLUMN IF NOT EXISTS aprovado_em TIMESTAMP NULL,
  ADD FOREIGN KEY IF NOT EXISTS (aprovado_por) REFERENCES users(id);

-- Atualizar status padrão para PENDENTE se não especificado
ALTER TABLE pops_its_registros 
  MODIFY COLUMN status ENUM('PENDENTE', 'APROVADO', 'REPROVADO') DEFAULT 'PENDENTE';

-- Verificar estrutura da tabela
DESCRIBE pops_its_registros;

-- Exemplo de consulta para verificar registros pendentes
SELECT 
    r.id,
    r.status,
    r.versao,
    r.nome_arquivo,
    r.criado_em,
    r.aprovado_por,
    r.aprovado_em,
    t.titulo,
    u.name as autor_nome
FROM pops_its_registros r
LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
LEFT JOIN users u ON r.criado_por = u.id
WHERE r.status = 'PENDENTE'
ORDER BY r.criado_em ASC;
