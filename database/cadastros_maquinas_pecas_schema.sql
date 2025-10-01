-- Criar tabela de Cadastro de Máquinas
CREATE TABLE IF NOT EXISTS cadastros_maquinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modelo VARCHAR(255) NOT NULL,
    cod_referencia VARCHAR(100) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_modelo (modelo),
    INDEX idx_cod_referencia (cod_referencia),
    INDEX idx_created_by (created_by),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de Cadastro de Peças
CREATE TABLE IF NOT EXISTS cadastro_pecas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_referencia VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo_referencia (codigo_referencia),
    INDEX idx_created_by (created_by),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar permissões para Cadastro de Máquinas
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export, created_at, updated_at)
SELECT 
    p.id,
    'cadastro_maquinas',
    1,  -- view
    1,  -- edit
    1,  -- delete
    0,  -- import
    0,  -- export
    NOW(),
    NOW()
FROM profiles p
WHERE NOT EXISTS (
    SELECT 1 FROM profile_permissions 
    WHERE profile_id = p.id 
    AND module = 'cadastro_maquinas'
);

-- Adicionar permissões para Cadastro de Peças
INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export, created_at, updated_at)
SELECT 
    p.id,
    'cadastro_pecas',
    1,  -- view
    1,  -- edit
    1,  -- delete
    0,  -- import
    0,  -- export
    NOW(),
    NOW()
FROM profiles p
WHERE NOT EXISTS (
    SELECT 1 FROM profile_permissions 
    WHERE profile_id = p.id 
    AND module = 'cadastro_pecas'
);

-- Verificar se as tabelas foram criadas
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN ('cadastros_maquinas', 'cadastro_pecas');

-- Verificar se as permissões foram criadas
SELECT 
    p.name as perfil,
    pp.module,
    pp.can_view,
    pp.can_edit,
    pp.can_delete
FROM profile_permissions pp
JOIN profiles p ON pp.profile_id = p.id
WHERE pp.module IN ('cadastro_maquinas', 'cadastro_pecas')
ORDER BY p.name, pp.module;
