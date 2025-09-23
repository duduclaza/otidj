-- ===== SISTEMA DE SOLICITAÇÕES DE ACESSO =====

-- Tabela para armazenar solicitações de acesso
CREATE TABLE IF NOT EXISTS access_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    setor VARCHAR(100),
    filial VARCHAR(100),
    justificativa TEXT,
    status ENUM('pendente', 'aprovado', 'rejeitado') DEFAULT 'pendente',
    profile_id INT NULL, -- Será preenchido quando aprovado
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Configurações de email (se não existir)
CREATE TABLE IF NOT EXISTS email_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    smtp_host VARCHAR(255) NOT NULL DEFAULT 'smtp.gmail.com',
    smtp_port INT NOT NULL DEFAULT 587,
    smtp_username VARCHAR(255) NOT NULL,
    smtp_password VARCHAR(255) NOT NULL,
    smtp_encryption ENUM('tls', 'ssl', 'none') DEFAULT 'tls',
    from_email VARCHAR(255) NOT NULL,
    from_name VARCHAR(255) NOT NULL DEFAULT 'SGQ OTI DJ',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir configuração padrão de email (ajuste conforme necessário)
INSERT INTO email_config (smtp_username, smtp_password, from_email, from_name) 
VALUES ('seu-email@gmail.com', 'sua-senha-app', 'noreply@sgqoti.com.br', 'SGQ OTI DJ')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;
