-- ======================================================================
-- CRIAÇÃO DAS TABELAS DO MÓDULO FLUXOGRAMAS
-- Sistema SGQ OTI DJ
-- Data: 06/10/2025
-- Baseado na estrutura de POPs e ITs
-- ======================================================================

-- ======================================================================
-- 1. TABELA: fluxogramas_titulos
-- Cadastro de títulos de fluxogramas
-- ======================================================================

CREATE TABLE IF NOT EXISTS fluxogramas_titulos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL COMMENT 'Título do Fluxograma',
  titulo_normalizado VARCHAR(255) NOT NULL COMMENT 'Título normalizado para evitar duplicidade',
  departamento_id INT NOT NULL COMMENT 'ID do departamento responsável',
  criado_por INT NOT NULL COMMENT 'ID do usuário que criou o título',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação',
  
  -- Índices para performance
  INDEX idx_departamento (departamento_id),
  INDEX idx_criado_por (criado_por),
  INDEX idx_criado_em (criado_em),
  
  -- Constraint única para evitar duplicidade
  UNIQUE KEY uniq_titulo_normalizado (titulo_normalizado),
  
  -- Foreign keys (se as tabelas existirem)
  FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (criado_por) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Títulos de Fluxogramas cadastrados no sistema';


-- ======================================================================
-- 2. TABELA: fluxogramas_registros
-- Registros de arquivos de fluxogramas (versões)
-- ======================================================================

CREATE TABLE IF NOT EXISTS fluxogramas_registros (
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
  FOREIGN KEY (titulo_id) REFERENCES fluxogramas_titulos(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (criado_por) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (aprovado_por) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registros de documentos de Fluxogramas';


-- ======================================================================
-- 3. TABELA: fluxogramas_registros_departamentos
-- Departamentos permitidos para visualizar fluxogramas não públicos
-- ======================================================================

CREATE TABLE IF NOT EXISTS fluxogramas_registros_departamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  registro_id INT NOT NULL COMMENT 'ID do registro',
  departamento_id INT NOT NULL COMMENT 'ID do departamento permitido',
  
  -- Índices
  INDEX idx_registro_id (registro_id),
  INDEX idx_departamento_id (departamento_id),
  
  -- Constraint única para evitar duplicatas
  UNIQUE KEY uniq_registro_departamento (registro_id, departamento_id),
  
  -- Foreign keys
  FOREIGN KEY (registro_id) REFERENCES fluxogramas_registros(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Departamentos permitidos para visualizar fluxogramas não públicos';


-- ======================================================================
-- 4. TABELA: fluxogramas_logs_visualizacao
-- Logs de visualização de fluxogramas para auditoria
-- ======================================================================

CREATE TABLE IF NOT EXISTS fluxogramas_logs_visualizacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registro_id INT NOT NULL COMMENT 'ID do registro visualizado',
    usuario_id INT NOT NULL COMMENT 'ID do usuário que visualizou',
    visualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora da visualização',
    user_agent TEXT NULL COMMENT 'User agent do navegador',
    
    -- Foreign keys
    FOREIGN KEY (registro_id) REFERENCES fluxogramas_registros(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Índices
    INDEX idx_registro_id (registro_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_visualizado_em (visualizado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Logs de visualização de fluxogramas';


-- ======================================================================
-- 5. TABELA: fluxogramas_solicitacoes_exclusao
-- Solicitações de exclusão de fluxogramas (Aba 4)
-- ======================================================================

CREATE TABLE IF NOT EXISTS fluxogramas_solicitacoes_exclusao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registro_id INT NOT NULL COMMENT 'ID do registro a ser excluído',
    solicitante_id INT NOT NULL COMMENT 'ID do usuário que solicitou',
    motivo TEXT NOT NULL COMMENT 'Motivo da solicitação de exclusão',
    status ENUM('PENDENTE', 'APROVADA', 'REPROVADA') DEFAULT 'PENDENTE' COMMENT 'Status da solicitação',
    avaliado_por INT NULL COMMENT 'ID do admin que avaliou',
    avaliado_em TIMESTAMP NULL COMMENT 'Data de avaliação',
    observacoes_avaliacao TEXT NULL COMMENT 'Observações do avaliador',
    solicitado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data da solicitação',
    
    -- Foreign keys
    FOREIGN KEY (registro_id) REFERENCES fluxogramas_registros(id) ON DELETE CASCADE,
    FOREIGN KEY (solicitante_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (avaliado_por) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Índices
    INDEX idx_registro_id (registro_id),
    INDEX idx_solicitante_id (solicitante_id),
    INDEX idx_status (status),
    INDEX idx_solicitado_em (solicitado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitações de exclusão de fluxogramas';


-- ======================================================================
-- 6. DADOS DE EXEMPLO (OPCIONAL - COMENTADO)
-- ======================================================================

-- Inserir alguns títulos de exemplo (descomente se desejar)
/*
INSERT INTO fluxogramas_titulos (titulo, titulo_normalizado, departamento_id, criado_por) VALUES
('Fluxograma de Processo de Compras', 'fluxograma de processo de compras', 1, 1),
('Fluxograma de Manutenção Preventiva', 'fluxograma de manutencao preventiva', 2, 1),
('Fluxograma de Atendimento ao Cliente', 'fluxograma de atendimento ao cliente', 3, 1)
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);
*/


-- ======================================================================
-- 7. VERIFICAÇÃO DAS TABELAS CRIADAS
-- ======================================================================

-- Verificar estrutura das tabelas
SHOW TABLES LIKE 'fluxogramas_%';

-- Verificar estrutura detalhada da tabela principal
DESCRIBE fluxogramas_titulos;
DESCRIBE fluxogramas_registros;

-- Contar registros (deve ser 0 inicialmente)
SELECT COUNT(*) as total_titulos FROM fluxogramas_titulos;
SELECT COUNT(*) as total_registros FROM fluxogramas_registros;


-- ======================================================================
-- 8. NOTAS DE IMPLEMENTAÇÃO
-- ======================================================================

/*
ESTRUTURA CRIADA:

✅ fluxogramas_titulos
   - Cadastro de títulos (Aba 1: Cadastro de Títulos)
   - Sem campo "tipo" (diferente de POPs e ITs)
   - Foreign keys para departamentos e users

✅ fluxogramas_registros
   - Armazenamento de arquivos em MEDIUMBLOB
   - Versionamento automático (v1, v2, v3...)
   - Sistema de aprovação/reprovação
   - Status: PENDENTE, APROVADO, REPROVADO
   - Visibilidade: Público ou Restrito por departamento

✅ fluxogramas_registros_departamentos
   - Controle de acesso por departamento
   - Para fluxogramas não públicos

✅ fluxogramas_logs_visualizacao
   - Auditoria de visualizações (Aba 5: Logs)
   - User agent para rastreamento

✅ fluxogramas_solicitacoes_exclusao
   - Workflow de exclusão (Aba 4: Solicitações)
   - Aprovação por administrador
   - Motivo e observações

PRÓXIMOS PASSOS:
1. Executar este SQL no banco de dados
2. Criar FluxogramasController baseado em PopItsController
3. Criar view fluxogramas/index.php com as 5 abas
4. Configurar rotas em public/index.php
5. Adicionar permissões no sistema (fluxogramas_cadastro_titulos, fluxogramas_visualizacao, etc.)
6. Integrar EmailService para notificações

DIFERENÇAS COM POPs e ITs:
- Não há campo "tipo" em fluxogramas_titulos (POPs tem tipo='POP'/'IT')
- Tabelas renomeadas: fluxogramas_* ao invés de pops_its_*
- Mesma estrutura de aprovação, logs e solicitações

COMPATIBILIDADE:
- MySQL 5.7+
- InnoDB Engine
- UTF-8 (utf8mb4_unicode_ci)
- Foreign keys com CASCADE
- Índices otimizados para performance
*/
