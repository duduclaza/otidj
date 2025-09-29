-- Criação das tabelas para registros de POPs e ITs
-- Sistema SGQ OTI DJ - Módulo POPs e ITs - Aba 2
-- Data: 29/09/2025

-- Tabela principal de registros
CREATE TABLE IF NOT EXISTS pops_its_registros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo_id INT NOT NULL COMMENT 'ID do título cadastrado',
  versao INT NOT NULL COMMENT 'Versão do documento (v1, v2, etc)',
  arquivo MEDIUMBLOB NOT NULL COMMENT 'Arquivo do documento',
  nome_arquivo VARCHAR(255) NOT NULL COMMENT 'Nome original do arquivo',
  extensao VARCHAR(10) NOT NULL COMMENT 'Extensão do arquivo (pdf, ppt, jpg, png)',
  tamanho_arquivo INT NOT NULL COMMENT 'Tamanho do arquivo em bytes',
  publico BOOLEAN DEFAULT FALSE COMMENT 'Se é público (todos veem) ou restrito',
  status ENUM('PENDENTE','APROVADO','REPROVADO') DEFAULT 'PENDENTE' COMMENT 'Status do registro',
  criado_por INT NOT NULL COMMENT 'ID do usuário que criou',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
  aprovado_por INT NULL COMMENT 'ID do admin que aprovou/reprovou',
  aprovado_em TIMESTAMP NULL COMMENT 'Data de aprovação/reprovação',
  observacao_reprovacao TEXT NULL COMMENT 'Observação em caso de reprovação',
  
  -- Índices para performance
  INDEX idx_titulo_id (titulo_id),
  INDEX idx_criado_por (criado_por),
  INDEX idx_status (status),
  INDEX idx_publico (publico),
  INDEX idx_criado_em (criado_em),
  
  -- Constraint única para versão por título
  UNIQUE KEY uniq_titulo_versao (titulo_id, versao),
  
  -- Foreign keys
  FOREIGN KEY (titulo_id) REFERENCES pops_its_titulos(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (criado_por) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (aprovado_por) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registros de documentos POPs e ITs';

-- Tabela de departamentos permitidos (para registros não públicos)
CREATE TABLE IF NOT EXISTS pops_its_registros_departamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  registro_id INT NOT NULL COMMENT 'ID do registro',
  departamento_id INT NOT NULL COMMENT 'ID do departamento permitido',
  
  -- Índices
  INDEX idx_registro_id (registro_id),
  INDEX idx_departamento_id (departamento_id),
  
  -- Constraint única para evitar duplicatas
  UNIQUE KEY uniq_registro_departamento (registro_id, departamento_id),
  
  -- Foreign keys
  FOREIGN KEY (registro_id) REFERENCES pops_its_registros(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Departamentos permitidos para visualizar registros não públicos';

-- Inserir alguns dados de exemplo (opcional)
-- Nota: Só funciona se já existirem títulos e usuários
/*
INSERT INTO pops_its_registros (titulo_id, versao, arquivo, nome_arquivo, extensao, tamanho_arquivo, publico, criado_por) VALUES
(1, 1, 0x255044462D312E34, 'exemplo.pdf', 'pdf', 1024, TRUE, 1),
(2, 1, 0x255044462D312E34, 'instrucao.pdf', 'pdf', 2048, FALSE, 1)
ON DUPLICATE KEY UPDATE versao = VALUES(versao);
*/
