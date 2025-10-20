-- =====================================================
-- CRIAR TABELAS HOMOLOGAÇÕES - SEM FOREIGN KEYS
-- Copie TUDO e execute de uma vez no phpMyAdmin
-- =====================================================

-- Tabela 1: homologacoes
CREATE TABLE IF NOT EXISTS homologacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cod_referencia VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    avisar_logistica TINYINT(1) DEFAULT 0,
    observacao TEXT,
    status VARCHAR(50) DEFAULT 'aguardando_recebimento',
    local_homologacao VARCHAR(255),
    data_inicio_homologacao DATE,
    alerta_finalizacao DATE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela 2: homologacoes_responsaveis
CREATE TABLE IF NOT EXISTS homologacoes_responsaveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    user_id INT NOT NULL,
    notificado TINYINT(1) DEFAULT 0,
    notificado_em TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela 3: homologacoes_historico
CREATE TABLE IF NOT EXISTS homologacoes_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    status_anterior VARCHAR(50),
    status_novo VARCHAR(50) NOT NULL,
    usuario_id INT NOT NULL,
    observacao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela 4: homologacoes_anexos
CREATE TABLE IF NOT EXISTS homologacoes_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    arquivo_blob MEDIUMBLOB NOT NULL,
    tipo_arquivo VARCHAR(100) NOT NULL,
    tamanho_arquivo INT NOT NULL,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Adicionar coluna department se não existir
ALTER TABLE users ADD COLUMN IF NOT EXISTS department VARCHAR(100) NULL;

-- Atualizar seu usuário
UPDATE users SET department = 'Compras' WHERE email = 'du.claza@gmail.com';

-- VERIFICAÇÃO FINAL
SELECT 
    'SUCESSO!' as resultado,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'homologacoes') as tab_homologacoes,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'homologacoes_responsaveis') as tab_responsaveis,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'homologacoes_historico') as tab_historico,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'homologacoes_anexos') as tab_anexos;

-- Se todos mostrarem 1, está OK!
