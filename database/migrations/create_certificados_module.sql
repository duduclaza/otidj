-- =====================================================
-- MÓDULO CERTIFICADOS
-- Tabelas: certificados, certificados_titulos
-- =====================================================

CREATE TABLE IF NOT EXISTS certificados_titulos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL UNIQUE,
  uso_count INT DEFAULT 0,
  last_used TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS certificados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo_id INT NULL,
  titulo_text VARCHAR(255) NOT NULL,
  arquivo_blob MEDIUMBLOB NOT NULL,
  nome_arquivo VARCHAR(255) NOT NULL,
  tipo_arquivo VARCHAR(100) NOT NULL,
  tamanho_arquivo INT NOT NULL,
  data_registro DATE NOT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_titulo (titulo_id),
  INDEX idx_data_registro (data_registro),
  INDEX idx_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissões (se existir sistema)
INSERT IGNORE INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, 'certificados', CASE WHEN p.name = 'Administrador' THEN 1 ELSE 1 END,
       CASE WHEN p.name = 'Administrador' THEN 1 ELSE 1 END,
       CASE WHEN p.name = 'Administrador' THEN 1 ELSE 0 END,
       0, 1
FROM profiles p
WHERE NOT EXISTS (
  SELECT 1 FROM profile_permissions pp WHERE pp.profile_id = p.id AND pp.module = 'certificados'
);
