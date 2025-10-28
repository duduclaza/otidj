-- Adicionar coluna departamento_id na tabela homologacoes
-- Data: 28/10/2025

ALTER TABLE homologacoes 
ADD COLUMN departamento_id INT UNSIGNED NULL AFTER descricao,
ADD KEY idx_departamento (departamento_id);

-- Adicionar foreign key se a tabela departments existir
-- ALTER TABLE homologacoes 
-- ADD CONSTRAINT fk_homologacoes_departamento 
-- FOREIGN KEY (departamento_id) REFERENCES departments(id) ON DELETE SET NULL;
