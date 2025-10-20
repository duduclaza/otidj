<?php

namespace App\Services;

/**
 * Serviço para gerenciar o usuário Master (GOD MODE)
 * Este usuário tem acesso total ao sistema e é invisível para outros usuários
 */
class MasterUserService
{
    /**
     * Email do usuário Master (GOD MODE)
     * Este usuário tem poderes absolutos no sistema
     */
    private static $masterEmail = 'du.claza@gmail.com';
    
    /**
     * Verificar se o usuário atual é o Master
     */
    public static function isMasterUser(): bool
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Verificar por email na sessão
        if (isset($_SESSION['user_email'])) {
            return strtolower($_SESSION['user_email']) === strtolower(self::$masterEmail);
        }
        
        // Verificar por ID no banco (fallback)
        try {
            $db = \App\Config\Database::getInstance();
            $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                return strtolower($user['email']) === strtolower(self::$masterEmail);
            }
        } catch (\Exception $e) {
            error_log("Erro ao verificar Master User: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Verificar se um email é o Master
     */
    public static function isMasterEmail(string $email): bool
    {
        return strtolower($email) === strtolower(self::$masterEmail);
    }
    
    /**
     * Verificar se um ID de usuário é o Master
     */
    public static function isMasterUserId(int $userId): bool
    {
        try {
            $db = \App\Config\Database::getInstance();
            $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                return self::isMasterEmail($user['email']);
            }
        } catch (\Exception $e) {
            error_log("Erro ao verificar Master User ID: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Obter o email do Master
     */
    public static function getMasterEmail(): string
    {
        return self::$masterEmail;
    }
    
    /**
     * Verificar se tem permissão para visualizar usuário
     * Master User nunca deve aparecer nas listas para outros usuários
     */
    public static function canViewUser(int $targetUserId): bool
    {
        // Se o target é o Master, só o próprio Master pode ver
        if (self::isMasterUserId($targetUserId)) {
            return self::isMasterUser();
        }
        
        // Para outros usuários, permissão normal
        return true;
    }
    
    /**
     * Filtrar usuário Master das listas (para SQL)
     * Adiciona condição WHERE para ocultar o Master
     */
    public static function getSqlFilterForNonMaster(): string
    {
        return "users.email != '" . self::$masterEmail . "'";
    }
}
