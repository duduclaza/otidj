<?php

namespace App\Controllers;

use App\Services\PermissionService;
use PDO;

class FinanceiroController
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = \App\Config\Database::getInstance()->getConnection();
        } catch (\Exception $e) {
            error_log("Erro ao conectar DB no FinanceiroController: " . $e->getMessage());
            $this->db = null;
        }
    }

    public function index(): void
    {
        try {
            if ($this->db === null) {
                throw new \Exception("Erro de conexão com banco de dados");
            }

            $isAdmin = $_SESSION['user_role'] === 'admin';
            
            // Verificar se sistema está bloqueado
            $bloqueado = $this->verificarBloqueio();
            
            if ($bloqueado && !$isAdmin) {
                $title = 'Sistema Bloqueado - SGQ OTI DJ';
                $viewFile = __DIR__ . '/../../views/pages/financeiro/bloqueado.php';
                include __DIR__ . '/../../views/layouts/main.php';
                return;
            }
            
            if (!$isAdmin && !PermissionService::hasPermission($_SESSION['user_id'], 'financeiro', 'view')) {
                http_response_code(403);
                echo "Acesso negado";
                return;
            }

            // Buscar histórico de pagamentos
            $stmt = $this->db->prepare('
                SELECT p.*, u.name as anexado_por_nome
                FROM financeiro_pagamentos p
                LEFT JOIN users u ON p.anexado_por = u.id
                ORDER BY p.ano DESC, p.mes DESC
            ');
            $stmt->execute();
            $pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Verificar pagamento atual
            $mesAtual = (int)date('n');
            $anoAtual = (int)date('Y');
            
            $stmt = $this->db->prepare('
                SELECT * FROM financeiro_pagamentos 
                WHERE mes = :mes AND ano = :ano
            ');
            $stmt->execute([':mes' => $mesAtual, ':ano' => $anoAtual]);
            $pagamentoAtual = $stmt->fetch(PDO::FETCH_ASSOC);

            $title = 'Financeiro - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/financeiro/index.php';
            include __DIR__ . '/../../views/layouts/main.php';

        } catch (\Exception $e) {
            error_log("Erro em Financeiro: " . $e->getMessage());
            http_response_code(500);
            echo "Erro ao carregar o módulo: " . $e->getMessage();
        }
    }

    public function anexarComprovante(): void
    {
        header('Content-Type: application/json');

        try {
            $id = (int)($_POST['pagamento_id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }

            if (empty($_FILES['comprovante']['tmp_name'])) {
                echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado']);
                return;
            }

            $comprovante = file_get_contents($_FILES['comprovante']['tmp_name']);
            $nome = $_FILES['comprovante']['name'];
            $tipo = $_FILES['comprovante']['type'];
            $tamanho = $_FILES['comprovante']['size'];

            if ($tamanho > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo deve ter no máximo 10MB']);
                return;
            }

            $stmt = $this->db->prepare('
                UPDATE financeiro_pagamentos SET
                    comprovante = :comprovante,
                    comprovante_nome = :nome,
                    comprovante_tipo = :tipo,
                    comprovante_tamanho = :tamanho,
                    status = "Pago",
                    data_pagamento = NOW(),
                    anexado_por = :user_id,
                    anexado_em = NOW(),
                    updated_at = NOW()
                WHERE id = :id
            ');

            $stmt->execute([
                ':id' => $id,
                ':comprovante' => $comprovante,
                ':nome' => $nome,
                ':tipo' => $tipo,
                ':tamanho' => $tamanho,
                ':user_id' => $_SESSION['user_id']
            ]);

            // Desbloquear sistema se estava bloqueado
            $this->desbloquearSistema($id);

            echo json_encode([
                'success' => true,
                'message' => 'Comprovante anexado com sucesso!'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao anexar comprovante: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao anexar: ' . $e->getMessage()]);
        }
    }

    public function downloadComprovante($id = null): void
    {
        try {
            $id = (int)$id;
            
            $stmt = $this->db->prepare('
                SELECT comprovante, comprovante_nome, comprovante_tipo 
                FROM financeiro_pagamentos 
                WHERE id = :id
            ');
            $stmt->execute([':id' => $id]);
            $pagamento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pagamento || !$pagamento['comprovante']) {
                http_response_code(404);
                echo "Comprovante não encontrado";
                return;
            }
            
            header('Content-Type: ' . $pagamento['comprovante_tipo']);
            header('Content-Disposition: attachment; filename="' . $pagamento['comprovante_nome'] . '"');
            header('Content-Length: ' . strlen($pagamento['comprovante']));
            echo $pagamento['comprovante'];
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo "Erro ao baixar comprovante";
        }
    }

    private function verificarBloqueio(): bool
    {
        try {
            $stmt = $this->db->prepare('
                SELECT COUNT(*) as total
                FROM financeiro_bloqueios
                WHERE desbloqueado_em IS NULL
            ');
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'] > 0;
            
        } catch (\Exception $e) {
            error_log('Erro ao verificar bloqueio: ' . $e->getMessage());
            return false;
        }
    }

    private function desbloquearSistema($pagamentoId): void
    {
        try {
            $stmt = $this->db->prepare('
                UPDATE financeiro_bloqueios 
                SET desbloqueado_em = NOW()
                WHERE pagamento_id = :id AND desbloqueado_em IS NULL
            ');
            $stmt->execute([':id' => $pagamentoId]);
            
        } catch (\Exception $e) {
            error_log('Erro ao desbloquear sistema: ' . $e->getMessage());
        }
    }
}
