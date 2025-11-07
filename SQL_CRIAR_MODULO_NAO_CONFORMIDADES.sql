-- ============================================
-- MÓDULO NÃO CONFORMIDADES (NC)
-- Criação das tabelas
-- Data: 07/11/2025
-- ============================================

-- Tabela principal de Não Conformidades
CREATE TABLE IF NOT EXISTS nao_conformidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT NOT NULL,
    
    -- Usuários envolvidos
    usuario_criador_id INT NOT NULL,
    usuario_responsavel_id INT NOT NULL,
    
    -- Status da NC
    status ENUM('pendente', 'em_andamento', 'solucionada') DEFAULT 'pendente',
    
    -- Ação corretiva
    acao_corretiva TEXT NULL,
    usuario_acao_id INT NULL,
    data_acao DATETIME NULL,
    
    -- Solução
    usuario_solucao_id INT NULL,
    data_solucao DATETIME NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (usuario_criador_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_responsavel_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_acao_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_solucao_id) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Índices
    INDEX idx_status (status),
    INDEX idx_criador (usuario_criador_id),
    INDEX idx_responsavel (usuario_responsavel_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de anexos
CREATE TABLE IF NOT EXISTS nao_conformidades_anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nc_id INT NOT NULL,
    
    -- Informações do arquivo
    nome_arquivo VARCHAR(255) NOT NULL,
    tipo_arquivo VARCHAR(100) NOT NULL,
    tamanho_bytes INT NOT NULL,
    caminho_arquivo VARCHAR(500) NOT NULL,
    
    -- Tipo de anexo
    tipo_anexo ENUM('evidencia_inicial', 'evidencia_acao') NOT NULL,
    
    -- Usuário que enviou
    usuario_id INT NOT NULL,
    
    -- Timestamp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (nc_id) REFERENCES nao_conformidades(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE RESTRICT,
    
    -- Índices
    INDEX idx_nc (nc_id),
    INDEX idx_tipo (tipo_anexo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PERMISSÕES PARA O MÓDULO
-- ============================================
-- Adicionar permissão para visualizar o módulo de Não Conformidades
-- Substitua {PROFILE_ID} pelo ID do perfil que deve ter acesso (ex: Supervisor de Qualidade, Admin, etc.)
-- 
-- Para descobrir os IDs dos perfis:
-- SELECT id, name FROM profiles;
--
-- Exemplo para dar permissão ao perfil com ID 1:
-- INSERT INTO profile_permissions (profile_id, module_name, can_view, can_create, can_edit, can_delete)
-- VALUES (1, 'nao_conformidades', 1, 1, 1, 1)
-- ON DUPLICATE KEY UPDATE can_view=1, can_create=1, can_edit=1, can_delete=1;
--
-- Ou para múltiplos perfis de uma vez:
-- INSERT INTO profile_permissions (profile_id, module_name, can_view, can_create, can_edit, can_delete)
-- SELECT id, 'nao_conformidades', 1, 1, 1, 1
-- FROM profiles 
-- WHERE name IN ('Admin', 'Supervisor Qualidade', 'Gerente')
-- ON DUPLICATE KEY UPDATE can_view=1, can_create=1, can_edit=1, can_delete=1;

-- ============================================
-- FIM DO SCRIPT
-- ============================================
