-- =====================================================
-- CORREÇÃO ERRO 500 - MÓDULO HOMOLOGAÇÕES
-- Execute esta query para criar as tabelas necessárias
-- =====================================================

-- 1. VERIFICAR se as tabelas existem
SELECT 
    COUNT(*) as tabelas_existem
FROM information_schema.tables 
WHERE table_schema = DATABASE()
AND table_name IN ('homologacoes', 'homologacoes_responsaveis', 'homologacoes_historico', 'homologacoes_anexos');

-- Se retornar 0, as tabelas não existem. Execute tudo abaixo:
-- Se retornar 4, as tabelas já existem. Pule para o final.

-- =====================================================
-- CRIAR TABELAS DE HOMOLOGAÇÕES
-- =====================================================

-- Tabela principal de homologações
CREATE TABLE IF NOT EXISTS homologacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cod_referencia VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    avisar_logistica BOOLEAN DEFAULT 0,
    observacao TEXT,
    status ENUM(
        'aguardando_recebimento',
        'recebido', 
        'em_analise',
        'em_homologacao',
        'aprovado',
        'reprovado'
    ) DEFAULT 'aguardando_recebimento',
    local_homologacao VARCHAR(255),
    data_inicio_homologacao DATE,
    alerta_finalizacao DATE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de responsáveis (many-to-many)
CREATE TABLE IF NOT EXISTS homologacoes_responsaveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    user_id INT NOT NULL,
    notificado BOOLEAN DEFAULT 0,
    notificado_em TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_homologacao_user (homologacao_id, user_id),
    INDEX idx_homologacao (homologacao_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de histórico de mudanças de status
CREATE TABLE IF NOT EXISTS homologacoes_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    status_anterior VARCHAR(50),
    status_novo VARCHAR(50) NOT NULL,
    usuario_id INT NOT NULL,
    observacao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_homologacao (homologacao_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de evidências/anexos
CREATE TABLE IF NOT EXISTS homologacoes_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    arquivo_blob MEDIUMBLOB NOT NULL,
    tipo_arquivo VARCHAR(100) NOT NULL,
    tamanho_arquivo INT NOT NULL,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (homologacao_id) REFERENCES homologacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_homologacao (homologacao_id),
    INDEX idx_uploaded_by (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar coluna department na tabela users se não existir
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname = 'department';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL AFTER profile_id')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =====================================================
-- VERIFICAÇÃO FINAL
-- =====================================================

-- Verificar se todas as tabelas foram criadas
SELECT 
    table_name,
    'CRIADA' as status
FROM information_schema.tables 
WHERE table_schema = DATABASE()
AND table_name IN ('homologacoes', 'homologacoes_responsaveis', 'homologacoes_historico', 'homologacoes_anexos')
ORDER BY table_name;

-- Deve retornar 4 tabelas

-- Verificar se a coluna department foi adicionada
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    'COLUNA OK' as status
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'users'
AND COLUMN_NAME = 'department';

-- =====================================================
-- ATUALIZAR SEU USUÁRIO PARA DEPARTAMENTO COMPRAS
-- (Necessário para poder criar homologações)
-- =====================================================

UPDATE users 
SET department = 'Compras'
WHERE email = 'du.claza@gmail.com';

-- Verificar
SELECT 
    name,
    email,
    department,
    'USUÁRIO ATUALIZADO' as status
FROM users 
WHERE email = 'du.claza@gmail.com';

-- =====================================================
-- PRONTO! Agora acesse: /homologacoes
-- =====================================================
