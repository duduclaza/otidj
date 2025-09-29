-- Criação da tabela pops_its_titulos
-- Sistema SGQ OTI DJ - Módulo POPs e ITs
-- Data: 29/09/2025

CREATE TABLE IF NOT EXISTS pops_its_titulos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL COMMENT 'Título do POP/IT',
  titulo_normalizado VARCHAR(255) NOT NULL COMMENT 'Título normalizado para evitar duplicidade',
  tipo ENUM('POP', 'IT') NOT NULL COMMENT 'Tipo: POP (Procedimento Operacional Padrão) ou IT (Instrução de Trabalho)',
  departamento_id INT NOT NULL COMMENT 'ID do departamento responsável',
  criado_por INT NOT NULL COMMENT 'ID do usuário que criou o título',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação',
  
  -- Índices para performance
  INDEX idx_departamento (departamento_id),
  INDEX idx_criado_por (criado_por),
  INDEX idx_tipo (tipo),
  INDEX idx_criado_em (criado_em),
  
  -- Constraint única para evitar duplicidade
  UNIQUE KEY uniq_titulo_tipo (tipo, titulo_normalizado),
  
  -- Foreign keys (se as tabelas existirem)
  FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (criado_por) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Títulos de POPs e ITs cadastrados no sistema';

-- Inserir alguns dados de exemplo (opcional)
INSERT INTO pops_its_titulos (titulo, titulo_normalizado, tipo, departamento_id, criado_por) VALUES
('Procedimento de Limpeza de Equipamentos', 'procedimento de limpeza de equipamentos', 'POP', 1, 1),
('Instrução para Troca de Toner', 'instrucao para troca de toner', 'IT', 2, 1),
('Procedimento de Backup de Dados', 'procedimento de backup de dados', 'POP', 3, 1)
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);
