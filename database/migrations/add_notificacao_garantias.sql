-- Migration: Adicionar sistema de notificações e histórico de status
-- Data: 2025-10-08
-- Descrição: Adiciona campo para notificar usuário e tabela de histórico de status

-- Adicionar campo de notificação
ALTER TABLE garantias
ADD COLUMN usuario_notificado_id INT NULL AFTER observacao,
ADD CONSTRAINT fk_garantias_usuario_notificado 
    FOREIGN KEY (usuario_notificado_id) REFERENCES users(id) ON DELETE SET NULL;

-- Criar tabela de histórico de status para rastreamento real
CREATE TABLE IF NOT EXISTS garantias_historico_status (
    id INT PRIMARY KEY AUTO_INCREMENT,
    garantia_id INT NOT NULL,
    status_anterior VARCHAR(50) NULL,
    status_novo VARCHAR(50) NOT NULL,
    usuario_id INT NOT NULL,
    data_mudanca TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observacao TEXT NULL,
    FOREIGN KEY (garantia_id) REFERENCES garantias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users(id),
    INDEX idx_garantia_status (garantia_id, data_mudanca)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar índice para campo de notificação
ALTER TABLE garantias
ADD INDEX idx_usuario_notificado (usuario_notificado_id);
