<?php

namespace App\Controllers;

use PDO;

class MasterController
{
    private $db;
    private const MASTER_EMAIL = 'du.claza@gmail.com';
    private const MASTER_PASSWORD = 'Pipoca@1989';

    public function __construct()
    {
        $this->db = \App\Config\Database::getInstance()->getConnection();
    }

    public function loginPage(): void
    {
        include __DIR__ . '/../../views/auth/master-login.php';
    }

    public function authenticate(): void
    {
        header('Content-Type: application/json');

        try {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Log de tentativa de acesso
            error_log("Tentativa de acesso Master - Email: $email - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

            if ($email === self::MASTER_EMAIL && $password === self::MASTER_PASSWORD) {
                $_SESSION['master_logged'] = true;
                $_SESSION['master_email'] = $email;
                $_SESSION['master_login_time'] = time();

                error_log("Acesso Master AUTORIZADO - Email: $email");

                echo json_encode([
                    'success' => true,
                    'message' => 'Acesso autorizado'
                ]);
            } else {
                error_log("Acesso Master NEGADO - Email: $email");

                echo json_encode([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ]);
            }

        } catch (\Exception $e) {
            error_log('Erro no login master: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro no servidor']);
        }
    }

    public function dashboard(): void
    {
        if (!$this->checkMasterAuth()) {
            header('Location: /master/login');
            exit;
        }

        try {
            // Buscar estatísticas
            $stats = $this->getStats();

            // Buscar pagamentos pendentes
            $stmt = $this->db->prepare('
                SELECT * FROM financeiro_pagamentos 
                WHERE status != "Pago"
                ORDER BY data_vencimento DESC
            ');
            $stmt->execute();
            $pagamentosPendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar todos os pagamentos
            $stmt = $this->db->prepare('
                SELECT * FROM financeiro_pagamentos 
                ORDER BY ano DESC, mes DESC
            ');
            $stmt->execute();
            $todosPagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            include __DIR__ . '/../../views/master/dashboard.php';

        } catch (\Exception $e) {
            error_log("Erro no dashboard master: " . $e->getMessage());
            echo "Erro ao carregar dashboard";
        }
    }

    public function aprovarPagamento(): void
    {
        header('Content-Type: application/json');

        if (!$this->checkMasterAuth()) {
            echo json_encode(['success' => false, 'message' => 'Não autorizado']);
            return;
        }

        try {
            $id = (int)($_POST['pagamento_id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }

            $stmt = $this->db->prepare('
                UPDATE financeiro_pagamentos 
                SET status = "Pago", 
                    data_pagamento = NOW(),
                    updated_at = NOW()
                WHERE id = :id
            ');
            $stmt->execute([':id' => $id]);

            // Desbloquear sistema se estava bloqueado
            $stmt = $this->db->prepare('
                UPDATE financeiro_bloqueios 
                SET desbloqueado_em = NOW()
                WHERE pagamento_id = :id AND desbloqueado_em IS NULL
            ');
            $stmt->execute([':id' => $id]);

            echo json_encode([
                'success' => true,
                'message' => 'Pagamento aprovado com sucesso!'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao aprovar pagamento: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao aprovar']);
        }
    }

    public function logout(): void
    {
        unset($_SESSION['master_logged']);
        unset($_SESSION['master_email']);
        unset($_SESSION['master_login_time']);
        
        header('Location: /login');
        exit;
    }

    private function checkMasterAuth(): bool
    {
        return isset($_SESSION['master_logged']) && $_SESSION['master_logged'] === true;
    }

    private function getStats(): array
    {
        try {
            // Total de usuários
            $stmt = $this->db->query('SELECT COUNT(*) as total FROM users');
            $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Pagamentos pendentes
            $stmt = $this->db->query('SELECT COUNT(*) as total FROM financeiro_pagamentos WHERE status != "Pago"');
            $pagamentosPendentes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Sistema bloqueado?
            $stmt = $this->db->query('SELECT COUNT(*) as total FROM financeiro_bloqueios WHERE desbloqueado_em IS NULL');
            $sistemaBloqueado = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

            // Total arrecadado
            $stmt = $this->db->query('SELECT COUNT(*) as total FROM financeiro_pagamentos WHERE status = "Pago"');
            $totalPagos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            return [
                'total_users' => $totalUsers,
                'pagamentos_pendentes' => $pagamentosPendentes,
                'sistema_bloqueado' => $sistemaBloqueado,
                'total_pagos' => $totalPagos
            ];

        } catch (\Exception $e) {
            error_log('Erro ao buscar stats: ' . $e->getMessage());
            return [
                'total_users' => 0,
                'pagamentos_pendentes' => 0,
                'sistema_bloqueado' => false,
                'total_pagos' => 0
            ];
        }
    }
}
