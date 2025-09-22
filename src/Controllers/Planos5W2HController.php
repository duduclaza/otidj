<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class Planos5W2HController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal - Lista de planos
    public function index()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h_planos', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $departamentos = $this->getDepartamentos();
            $usuarios = $this->getUsuarios();
            
            // Usar o layout padrão com TailwindCSS
            $title = '5W2H - Planos de Ação - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/5w2h/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    // Listar planos com filtros
    public function listPlanos()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h_planos', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar planos']);
                return;
            }

            $user_id = $_SESSION['user_id'];
            $isAdmin = PermissionService::isAdmin($user_id);
            
            // Filtros
            $status = $_GET['status'] ?? '';
            $responsavel = $_GET['responsavel'] ?? '';
            $setor = $_GET['setor'] ?? '';
            $data_inicio = $_GET['data_inicio'] ?? '';
            $data_fim = $_GET['data_fim'] ?? '';
            $meus_planos = $_GET['meus_planos'] ?? '';

            // Construir query base
            $sql = "
                SELECT p.*, 
                       u.name as responsavel_nome,
                       d.nome as setor_nome,
                       uc.name as criado_por_nome,
                       ua.name as atualizado_por_nome
                FROM planos_5w2h p
                LEFT JOIN users u ON p.who_id = u.id
                LEFT JOIN departamentos d ON p.setor_id = d.id
                LEFT JOIN users uc ON p.created_by = uc.id
                LEFT JOIN users ua ON p.updated_by = ua.id
                WHERE 1=1
            ";
            
            $params = [];

            // Se não é admin, só vê planos do seu setor ou que criou
            if (!$isAdmin) {
                $userDept = $this->getUserDepartmentId($user_id);
                $sql .= " AND (p.setor_id = ? OR p.created_by = ?)";
                $params[] = $userDept;
                $params[] = $user_id;
            }

            // Filtros
            if ($status) {
                $sql .= " AND p.status = ?";
                $params[] = $status;
            }
            
            if ($responsavel) {
                $sql .= " AND p.who_id = ?";
                $params[] = $responsavel;
            }
            
            if ($setor) {
                $sql .= " AND p.setor_id = ?";
                $params[] = $setor;
            }
            
            if ($data_inicio) {
                $sql .= " AND p.when_inicio >= ?";
                $params[] = $data_inicio;
            }
            
            if ($data_fim) {
                $sql .= " AND p.when_fim <= ?";
                $params[] = $data_fim;
            }
            
            if ($meus_planos === '1') {
                $sql .= " AND p.created_by = ?";
                $params[] = $user_id;
            }

            $sql .= " ORDER BY p.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'planos' => $planos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar planos: ' . $e->getMessage()]);
        }
    }

    // Criar novo plano
    public function create()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h_planos', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para criar planos']);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validações
            $required = ['titulo', 'what', 'why'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    echo json_encode(['success' => false, 'message' => "Campo '{$field}' é obrigatório"]);
                    return;
                }
            }

            // Inserir plano
            $stmt = $this->db->prepare("
                INSERT INTO planos_5w2h (
                    titulo, what, why, where_local, when_inicio, when_fim, 
                    who_id, how, how_much, status, setor_id, observacoes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['titulo'],
                $data['what'],
                $data['why'],
                $data['where_local'] ?? null,
                $data['when_inicio'] ?? null,
                $data['when_fim'] ?? null,
                $data['who_id'] ?? null,
                $data['how'] ?? null,
                $data['how_much'] ?? 0.00,
                $data['status'] ?? 'Aberto',
                $data['setor_id'] ?? null,
                $data['observacoes'] ?? null,
                $_SESSION['user_id']
            ]);

            $plano_id = $this->db->lastInsertId();

            // Registrar no histórico
            $this->registrarHistorico($plano_id, 'criacao', null, 'Plano criado', $_SESSION['user_id']);

            echo json_encode(['success' => true, 'message' => 'Plano criado com sucesso!', 'plano_id' => $plano_id]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar plano: ' . $e->getMessage()]);
        }
    }

    // Atualizar plano
    public function update()
    {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $plano_id = $data['id'] ?? 0;

            if (!$plano_id) {
                echo json_encode(['success' => false, 'message' => 'ID do plano é obrigatório']);
                return;
            }

            // Verificar se o plano existe e se o usuário pode editá-lo
            $plano = $this->getPlanoById($plano_id);
            if (!$plano) {
                echo json_encode(['success' => false, 'message' => 'Plano não encontrado']);
                return;
            }

            $user_id = $_SESSION['user_id'];
            $isAdmin = PermissionService::isAdmin($user_id);
            $canEdit = PermissionService::hasPermission($user_id, '5w2h_planos', 'edit');

            if (!$canEdit) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para editar planos']);
                return;
            }

            // Se não é admin, só pode editar planos que criou
            if (!$isAdmin && $plano['created_by'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Você só pode editar planos que criou']);
                return;
            }

            // Preparar campos para atualização
            $campos = ['titulo', 'what', 'why', 'where_local', 'when_inicio', 'when_fim', 'who_id', 'how', 'how_much', 'status', 'setor_id', 'observacoes'];
            $updates = [];
            $params = [];
            
            foreach ($campos as $campo) {
                if (isset($data[$campo])) {
                    $updates[] = "{$campo} = ?";
                    $params[] = $data[$campo];
                    
                    // Registrar mudança no histórico
                    if ($plano[$campo] != $data[$campo]) {
                        $this->registrarHistorico($plano_id, $campo, $plano[$campo], $data[$campo], $user_id);
                    }
                }
            }

            if (empty($updates)) {
                echo json_encode(['success' => false, 'message' => 'Nenhum campo para atualizar']);
                return;
            }

            $updates[] = "updated_by = ?";
            $params[] = $user_id;
            $params[] = $plano_id;

            $sql = "UPDATE planos_5w2h SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            echo json_encode(['success' => true, 'message' => 'Plano atualizado com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar plano: ' . $e->getMessage()]);
        }
    }

    // Excluir plano
    public function delete()
    {
        header('Content-Type: application/json');
        
        try {
            $plano_id = $_POST['id'] ?? 0;

            if (!$plano_id) {
                echo json_encode(['success' => false, 'message' => 'ID do plano é obrigatório']);
                return;
            }

            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h_planos', 'delete')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para excluir planos']);
                return;
            }

            // Verificar se o plano existe
            $plano = $this->getPlanoById($plano_id);
            if (!$plano) {
                echo json_encode(['success' => false, 'message' => 'Plano não encontrado']);
                return;
            }

            $user_id = $_SESSION['user_id'];
            $isAdmin = PermissionService::isAdmin($user_id);

            // Se não é admin, só pode excluir planos que criou
            if (!$isAdmin && $plano['created_by'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Você só pode excluir planos que criou']);
                return;
            }

            // Excluir plano (CASCADE remove histórico e anexos)
            $stmt = $this->db->prepare("DELETE FROM planos_5w2h WHERE id = ?");
            $stmt->execute([$plano_id]);

            echo json_encode(['success' => true, 'message' => 'Plano excluído com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir plano: ' . $e->getMessage()]);
        }
    }

    // Obter detalhes de um plano
    public function getPlano($id)
    {
        header('Content-Type: application/json');
        
        try {
            $plano = $this->getPlanoById($id);
            if (!$plano) {
                echo json_encode(['success' => false, 'message' => 'Plano não encontrado']);
                return;
            }

            // Verificar permissão de visualização
            $user_id = $_SESSION['user_id'];
            $isAdmin = PermissionService::isAdmin($user_id);
            
            if (!$isAdmin) {
                $userDept = $this->getUserDepartmentId($user_id);
                if ($plano['setor_id'] != $userDept && $plano['created_by'] != $user_id) {
                    echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar este plano']);
                    return;
                }
            }

            // Buscar histórico
            $stmt = $this->db->prepare("
                SELECT h.*, u.name as alterado_por_nome
                FROM planos_5w2h_historico h
                LEFT JOIN users u ON h.alterado_por = u.id
                WHERE h.plano_id = ?
                ORDER BY h.alterado_em DESC
            ");
            $stmt->execute([$id]);
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar anexos
            $stmt = $this->db->prepare("
                SELECT a.*, u.name as uploaded_by_nome
                FROM planos_5w2h_anexos a
                LEFT JOIN users u ON a.uploaded_by = u.id
                WHERE a.plano_id = ?
                ORDER BY a.uploaded_at DESC
            ");
            $stmt->execute([$id]);
            $anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true, 
                'plano' => $plano, 
                'historico' => $historico, 
                'anexos' => $anexos
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar plano: ' . $e->getMessage()]);
        }
    }

    // Métodos auxiliares
    private function getPlanoById($id)
    {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   u.name as responsavel_nome,
                   d.nome as setor_nome,
                   uc.name as criado_por_nome,
                   ua.name as atualizado_por_nome
            FROM planos_5w2h p
            LEFT JOIN users u ON p.who_id = u.id
            LEFT JOIN departamentos d ON p.setor_id = d.id
            LEFT JOIN users uc ON p.created_by = uc.id
            LEFT JOIN users ua ON p.updated_by = ua.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getDepartamentos()
    {
        $stmt = $this->db->query("SELECT id, nome FROM departamentos ORDER BY nome");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUsuarios()
    {
        // Removido filtro por coluna 'active' (inexistente em alguns ambientes)
        $stmt = $this->db->query("SELECT id, name FROM users ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUserDepartmentId($user_id)
    {
        $stmt = $this->db->prepare("SELECT departamento_id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['departamento_id'] ?? null;
    }

    private function registrarHistorico($plano_id, $campo, $valor_anterior, $valor_novo, $user_id)
    {
        $stmt = $this->db->prepare("
            INSERT INTO planos_5w2h_historico (plano_id, campo_alterado, valor_anterior, valor_novo, alterado_por)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$plano_id, $campo, $valor_anterior, $valor_novo, $user_id]);
    }

    // Relatórios
    public function relatorios()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h_planos', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $title = '5W2H - Relatórios - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/5w2h/relatorios.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    // Kanban view
    public function kanban()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], '5w2h_planos', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $title = '5W2H - Kanban - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/5w2h/kanban.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }
}
