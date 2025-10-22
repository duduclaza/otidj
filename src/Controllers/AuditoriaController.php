<?php

namespace App\Controllers;

use App\Config\Database;

class AuditoriaController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Página principal de auditoria
     */
    public function index()
    {
        // Verificar se usuário está autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Buscar logs de auditoria (últimos 100 registros)
        $logs = $this->getAuditLogs(100);

        // Buscar estatísticas
        $stats = $this->getAuditStats();

        // Renderizar view
        require_once __DIR__ . '/../../views/pages/auditoria/index.php';
    }

    /**
     * Buscar logs de auditoria
     */
    private function getAuditLogs($limit = 100, $filters = [])
    {
        try {
            $sql = "SELECT 
                        al.*,
                        u.name as user_name,
                        u.email as user_email
                    FROM audit_logs al
                    LEFT JOIN users u ON al.user_id = u.id
                    WHERE 1=1";

            $params = [];

            // Filtros opcionais
            if (!empty($filters['user_id'])) {
                $sql .= " AND al.user_id = :user_id";
                $params[':user_id'] = $filters['user_id'];
            }

            if (!empty($filters['action'])) {
                $sql .= " AND al.action = :action";
                $params[':action'] = $filters['action'];
            }

            if (!empty($filters['module'])) {
                $sql .= " AND al.module = :module";
                $params[':module'] = $filters['module'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(al.created_at) >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(al.created_at) <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }

            $sql .= " ORDER BY al.created_at DESC LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            error_log("Erro ao buscar logs de auditoria: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar estatísticas de auditoria
     */
    private function getAuditStats()
    {
        try {
            $stats = [];

            // Total de ações hoje
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM audit_logs 
                WHERE DATE(created_at) = CURDATE()
            ");
            $stats['today'] = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            // Total de ações esta semana
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM audit_logs 
                WHERE YEARWEEK(created_at) = YEARWEEK(NOW())
            ");
            $stats['week'] = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            // Total de ações este mês
            $stmt = $this->db->query("
                SELECT COUNT(*) as total 
                FROM audit_logs 
                WHERE MONTH(created_at) = MONTH(NOW()) 
                AND YEAR(created_at) = YEAR(NOW())
            ");
            $stats['month'] = $stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;

            // Usuários mais ativos (top 5)
            $stmt = $this->db->query("
                SELECT 
                    u.name,
                    u.email,
                    COUNT(*) as total_actions
                FROM audit_logs al
                JOIN users u ON al.user_id = u.id
                WHERE DATE(al.created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY al.user_id
                ORDER BY total_actions DESC
                LIMIT 5
            ");
            $stats['top_users'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Ações mais comuns
            $stmt = $this->db->query("
                SELECT 
                    action,
                    COUNT(*) as total
                FROM audit_logs
                WHERE DATE(created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY action
                ORDER BY total DESC
                LIMIT 5
            ");
            $stats['top_actions'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $stats;

        } catch (\PDOException $e) {
            error_log("Erro ao buscar estatísticas de auditoria: " . $e->getMessage());
            return [
                'today' => 0,
                'week' => 0,
                'month' => 0,
                'top_users' => [],
                'top_actions' => []
            ];
        }
    }

    /**
     * Registrar ação de auditoria
     */
    public static function log($action, $module, $details = null, $userId = null)
    {
        try {
            $db = Database::getInstance()->getConnection();
            
            $userId = $userId ?? $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                return false;
            }

            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, module, details, ip_address, user_agent, created_at)
                VALUES (:user_id, :action, :module, :details, :ip_address, :user_agent, NOW())
            ");

            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':module' => $module,
                ':details' => $details ? json_encode($details) : null,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            return true;

        } catch (\PDOException $e) {
            error_log("Erro ao registrar log de auditoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exportar logs para CSV
     */
    public function export()
    {
        // Verificar permissão
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $logs = $this->getAuditLogs(1000);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="auditoria_' . date('Y-m-d_His') . '.csv"');

        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Cabeçalhos
        fputcsv($output, ['ID', 'Data/Hora', 'Usuário', 'Email', 'Ação', 'Módulo', 'Detalhes', 'IP', 'User Agent']);

        // Dados
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['created_at'],
                $log['user_name'] ?? 'N/A',
                $log['user_email'] ?? 'N/A',
                $log['action'],
                $log['module'],
                $log['details'] ?? '',
                $log['ip_address'] ?? '',
                $log['user_agent'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }
}
