-- =====================================================
-- CRIAR TABELA DE RECUPERAÇÃO DE SENHA
-- Data: 06/10/2025
-- =====================================================

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(6) NOT NULL COMMENT 'Código de 6 dígitos',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Códigos de recuperação de senha';

-- Limpar códigos expirados automaticamente (executar periodicamente)
DELETE FROM password_resets WHERE expires_at < NOW() OR used = TRUE;

-- Verificar estrutura
DESCRIBE password_resets;
