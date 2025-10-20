-- =====================================================
-- CORREÇÃO URGENTE - HOMOLOGAÇÕES
-- Execute TODA esta query de uma vez
-- =====================================================

-- 1. Criar tabela homologacoes
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
    INDEX idx_status (status),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Criar tabela homologacoes_responsaveis
CREATE TABLE IF NOT EXISTS homologacoes_responsaveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    user_id INT NOT NULL,
    notificado BOOLEAN DEFAULT 0,
    notificado_em TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_homologacao (homologacao_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Criar tabela homologacoes_historico
CREATE TABLE IF NOT EXISTS homologacoes_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    status_anterior VARCHAR(50),
    status_novo VARCHAR(50) NOT NULL,
    usuario_id INT NOT NULL,
    observacao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_homologacao (homologacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Criar tabela homologacoes_anexos
CREATE TABLE IF NOT EXISTS homologacoes_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    arquivo_blob MEDIUMBLOB NOT NULL,
    tipo_arquivo VARCHAR(100) NOT NULL,
    tamanho_arquivo INT NOT NULL,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_homologacao (homologacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Adicionar coluna department na tabela users (se não existir)
ALTER TABLE users ADD COLUMN IF NOT EXISTS department VARCHAR(100) NULL;

-- 6. Atualizar seu usuário para departamento Compras
UPDATE users 
SET department = 'Compras'
WHERE email = 'du.claza@gmail.com';

-- 7. VERIFICAÇÃO - Execute e veja o resultado
SELECT 
    'Tabelas criadas com sucesso!' as mensagem,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'homologacoes') as homologacoes,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'homologacoes_responsaveis') as responsaveis,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'homologacoes_historico') as historico,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'homologacoes_anexos') as anexos;

-- Se retornar 1 em todas as colunas, está OK!
