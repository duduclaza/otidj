<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class FluxogramasController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal com abas
    public function index()
    {
        try {
            // Verificar permissões para cada aba
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            // Verificar permissões específicas para cada aba
            $canViewCadastroTitulos = \App\Services\PermissionService::hasPermission($user_id, 'fluxogramas_cadastro_titulos', 'view');
            $canViewMeusRegistros = \App\Services\PermissionService::hasPermission($user_id, 'fluxogramas_meus_registros', 'view');
            $canViewPendenteAprovacao = $isAdmin; // Apenas admin pode ver pendente aprovação
            $canViewVisualizacao = \App\Services\PermissionService::hasPermission($user_id, 'fluxogramas_visualizacao', 'view');
            $canViewLogsVisualizacao = $isAdmin; // Apenas admin pode ver logs
            
            // Carregar departamentos para o formulário
            $departamentos = $this->getDepartamentos();
            
            // Usar o layout padrão com TailwindCSS
            $title = 'Fluxogramas - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/fluxogramas/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            // Logar erro para diagnóstico
            try {
                $logDir = __DIR__ . '/../../logs';
                if (!is_dir($logDir)) { @mkdir($logDir, 0777, true); }
                $msg = date('Y-m-d H:i:s') . ' Fluxogramas index ERRO: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine() . "\n";
                file_put_contents($logDir . '/fluxogramas_debug.log', $msg, FILE_APPEND);
            } catch (\Throwable $ignored) {}

            echo "<h1>Erro no Módulo Fluxogramas</h1>";
            echo "<p><strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // Buscar departamentos
    private function getDepartamentos()
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nome FROM departamentos ORDER BY nome ASC");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erro ao buscar departamentos: " . $e->getMessage());
            return [];
        }
    }

    // ===== ABA 1: CADASTRO DE TÍTULOS =====

    // Criar título
    public function createTitulo()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            
            // Verificar permissão
            if (!\App\Services\PermissionService::hasPermission($user_id, 'fluxogramas_cadastro_titulos', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para criar títulos']);
                return;
            }
            
            $titulo = trim($_POST['titulo'] ?? '');
            $tipo = trim($_POST['tipo'] ?? 'FLUXOGRAMA');
            $descricao = trim($_POST['descricao'] ?? '');
            
            if (empty($titulo)) {
                echo json_encode(['success' => false, 'message' => 'Título é obrigatório']);
                return;
            }
            
            // Verificar se já existe
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM fluxogramas_titulos WHERE titulo = ?");
            $stmt->execute([$titulo]);
            
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Já existe um título com este nome']);
                return;
            }
            
            // Inserir título
            $stmt = $this->db->prepare("
                INSERT INTO fluxogramas_titulos (titulo, tipo, descricao, criado_por) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$titulo, $tipo, $descricao, $user_id]);
            
            echo json_encode(['success' => true, 'message' => 'Título criado com sucesso']);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::createTitulo - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    // Listar títulos
    public function listTitulos()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, u.name as criado_por_nome 
                FROM fluxogramas_titulos t
                LEFT JOIN users u ON t.criado_por = u.id
                ORDER BY t.titulo ASC
            ");
            $stmt->execute();
            $titulos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $titulos]);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::listTitulos - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar títulos: ' . $e->getMessage()]);
        }
    }

    // Buscar títulos para autocomplete
    public function searchTitulos()
    {
        header('Content-Type: application/json');
        
        try {
            $query = $_GET['q'] ?? '';
            
            if (strlen($query) < 2) {
                echo json_encode([]);
                return;
            }
            
            $stmt = $this->db->prepare("
                SELECT id, titulo, tipo 
                FROM fluxogramas_titulos 
                WHERE titulo LIKE ? 
                ORDER BY titulo ASC 
                LIMIT 10
            ");
            $stmt->execute(['%' . $query . '%']);
            $titulos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode($titulos);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::searchTitulos - Erro: " . $e->getMessage());
            echo json_encode([]);
        }
    }

    // Excluir título
    public function deleteTitulo()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            
            // Verificar permissão
            if (!\App\Services\PermissionService::hasPermission($user_id, 'fluxogramas_cadastro_titulos', 'delete')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para excluir títulos']);
                return;
            }
            
            $titulo_id = (int)($_POST['id'] ?? 0);
            
            if ($titulo_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }
            
            // Verificar se tem registros associados
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM fluxogramas_registros WHERE titulo_id = ?");
            $stmt->execute([$titulo_id]);
            
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Não é possível excluir: existem registros associados a este título']);
                return;
            }
            
            // Excluir título
            $stmt = $this->db->prepare("DELETE FROM fluxogramas_titulos WHERE id = ?");
            $stmt->execute([$titulo_id]);
            
            echo json_encode(['success' => true, 'message' => 'Título excluído com sucesso']);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::deleteTitulo - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    // Notificar todos os administradores
    private function notificarAdministradores($titulo, $mensagem, $tipo, $related_type = null, $related_id = null)
    {
        try {
            error_log("=== NOTIFICAÇÃO FLUXOGRAMAS ===");
            error_log("TÍTULO: $titulo");
            
            $stmt = $this->db->prepare("SELECT id, name FROM users WHERE is_admin = 1");
            $stmt->execute();
            $admins = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $notificacoes_criadas = 0;
            foreach ($admins as $admin) {
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO notifications (user_id, title, message, type, related_type, related_id) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $resultado = $stmt->execute([$admin['id'], $titulo, $mensagem, $tipo, $related_type, $related_id]);
                    
                    if ($resultado) {
                        $notificacoes_criadas++;
                    }
                } catch (\Exception $e) {
                    error_log("Erro ao notificar admin {$admin['name']}: " . $e->getMessage());
                }
            }
            
            error_log("NOTIFICAÇÕES CRIADAS: $notificacoes_criadas de " . count($admins));
            return $notificacoes_criadas > 0;
            
        } catch (\Exception $e) {
            error_log("Erro geral ao notificar administradores: " . $e->getMessage());
            return false;
        }
    }
}
