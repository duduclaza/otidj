<?php

namespace App\Controllers;

use App\Config\Database;

class NotificationsController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Buscar notificações do usuário logado
    public function getNotifications()
    {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
                return;
            }
            
            // Buscar todas as notificações (lidas e não lidas) dos últimos 30 dias
            $stmt = $this->db->prepare("
                SELECT id, title, message, type, related_type, related_id, created_at, read_at,
                       CASE WHEN read_at IS NULL THEN 1 ELSE 0 END as is_unread
                FROM notifications 
                WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY is_unread DESC, created_at DESC 
                LIMIT 50
            ");
            $stmt->execute([$userId]);
            $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Contar total não lidas
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL");
            $stmt->execute([$userId]);
            $unreadCount = $stmt->fetchColumn();
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => (int)$unreadCount
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar notificações: ' . $e->getMessage()]);
        }
    }

    // Marcar notificação como lida
    public function markAsRead($id)
    {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $id = (int)$id;
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
                return;
            }
            
            $stmt = $this->db->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            
            echo json_encode(['success' => true, 'message' => 'Notificação marcada como lida']);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao marcar notificação: ' . $e->getMessage()]);
        }
    }

    // Marcar todas como lidas
    public function markAllAsRead()
    {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
                return;
            }
            
            $stmt = $this->db->prepare("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL");
            $stmt->execute([$userId]);
            
            echo json_encode(['success' => true, 'message' => 'Todas as notificações foram marcadas como lidas']);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao marcar notificações: ' . $e->getMessage()]);
        }
    }

    // Limpar histórico de notificações
    public function clearHistory()
    {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
                return;
            }
            
            // Deletar apenas notificações lidas (manter as não lidas)
            $stmt = $this->db->prepare("DELETE FROM notifications WHERE user_id = ? AND read_at IS NOT NULL");
            $stmt->execute([$userId]);
            
            $deletedCount = $stmt->rowCount();
            
            echo json_encode([
                'success' => true, 
                'message' => "Histórico limpo! {$deletedCount} notificações removidas.",
                'deleted_count' => $deletedCount
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao limpar histórico: ' . $e->getMessage()]);
        }
    }

    // Criar notificação (método estático para uso em outros controllers)
    public static function create($userId, $title, $message, $type = 'info', $relatedType = null, $relatedId = null)
    {
        try {
            $db = Database::getInstance();
            
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type, related_type, related_id) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$userId, $title, $message, $type, $relatedType, $relatedId]);
            
            return true;
        } catch (\Exception $e) {
            error_log("Erro ao criar notificação: " . $e->getMessage());
            return false;
        }
    }
}
