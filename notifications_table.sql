-- =====================================================
-- TABELA DE NOTIFICAÇÕES PARA O SISTEMA
-- Sistema de notificações em tempo real para administradores
-- =====================================================

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error', 'access_request', 'pops_its_pendente', 'pops_its_aprovado', 'pops_its_reprovado', 'pops_its_exclusao_pendente', 'pops_its_exclusao_aprovada', 'pops_its_exclusao_reprovada') DEFAULT 'info',
    related_type VARCHAR(50) NULL COMMENT 'Tipo do objeto relacionado (ex: access_request, user, etc)',
    related_id INT NULL COMMENT 'ID do objeto relacionado',
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relacionamentos
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Índices para performance
    INDEX idx_user_unread (user_id, read_at),
    INDEX idx_created_at (created_at),
    INDEX idx_related (related_type, related_id),
    INDEX idx_type (type)
);

-- =====================================================
-- DADOS DE EXEMPLO (OPCIONAL)
-- =====================================================

-- Exemplo de notificação de solicitação de acesso
/*
INSERT INTO notifications (user_id, title, message, type, related_type, related_id) VALUES 
(1, '🔔 Nova Solicitação de Acesso', 'O usuário João Silva (joao@empresa.com) solicitou acesso ao sistema. Clique para revisar e aprovar/rejeitar a solicitação.', 'access_request', 'access_request', 1);
*/

-- =====================================================
-- CONSULTAS ÚTEIS
-- =====================================================

-- Buscar notificações não lidas de um usuário
/*
SELECT id, title, message, type, related_type, related_id, created_at
FROM notifications 
WHERE user_id = ? AND read_at IS NULL 
ORDER BY created_at DESC 
LIMIT 20;
*/

-- Contar notificações não lidas
/*
SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL;
*/

-- Marcar notificação como lida
/*
UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?;
*/

-- Marcar todas como lidas para um usuário
/*
UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL;
*/

-- Buscar todos os administradores para notificação
/*
SELECT u.id 
FROM users u 
JOIN profiles p ON u.profile_id = p.id 
WHERE p.name = 'Administrador' AND u.status = 'active';
*/

-- =====================================================
-- VERIFICAÇÃO DA ESTRUTURA
-- =====================================================

DESCRIBE notifications;
