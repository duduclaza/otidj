<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;

class ChecklistsController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Criar checklist
    public function create()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                return;
            }

            $user_id = $_SESSION['user_id'];
            
            // Verificar se é admin ou super admin
            if (!PermissionService::isAdmin($user_id) && !PermissionService::isSuperAdmin($user_id)) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            $titulo = trim($data['titulo'] ?? '');
            $descricao = trim($data['descricao'] ?? '');
            $itens = $data['itens'] ?? [];

            if (empty($titulo)) {
                echo json_encode(['success' => false, 'message' => 'Título obrigatório']);
                return;
            }

            if (empty($itens)) {
                echo json_encode(['success' => false, 'message' => 'Adicione pelo menos um item']);
                return;
            }

            // Iniciar transação
            $this->db->beginTransaction();

            // Inserir checklist
            $stmt = $this->db->prepare("
                INSERT INTO homologacao_checklists (titulo, descricao, criado_por)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$titulo, $descricao, $user_id]);
            $checklist_id = $this->db->lastInsertId();

            // Inserir itens
            $stmt = $this->db->prepare("
                INSERT INTO homologacao_checklist_itens 
                (checklist_id, titulo, ordem, tipo_resposta)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($itens as $item) {
                $stmt->execute([
                    $checklist_id,
                    $item['titulo'],
                    $item['ordem'],
                    $item['tipo_resposta']
                ]);
            }

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Checklist criado com sucesso',
                'checklist_id' => $checklist_id
            ]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erro ao criar checklist: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao criar checklist']);
        }
    }

    // Listar checklists
    public function list()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.id,
                    c.titulo,
                    c.descricao,
                    c.criado_em,
                    u.name as criado_por_nome,
                    COUNT(i.id) as total_itens
                FROM homologacao_checklists c
                LEFT JOIN users u ON c.criado_por = u.id
                LEFT JOIN homologacao_checklist_itens i ON c.id = i.checklist_id
                WHERE c.ativo = 1
                GROUP BY c.id
                ORDER BY c.criado_em DESC
            ");
            $stmt->execute();
            $checklists = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $checklists]);

        } catch (\Exception $e) {
            error_log("Erro ao listar checklists: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar checklists']);
        }
    }

    // Buscar checklist por ID
    public function show($id)
    {
        header('Content-Type: application/json');
        
        try {
            // Buscar checklist
            $stmt = $this->db->prepare("
                SELECT * FROM homologacao_checklists WHERE id = ? AND ativo = 1
            ");
            $stmt->execute([$id]);
            $checklist = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$checklist) {
                echo json_encode(['success' => false, 'message' => 'Checklist não encontrado']);
                return;
            }

            // Buscar itens
            $stmt = $this->db->prepare("
                SELECT * FROM homologacao_checklist_itens 
                WHERE checklist_id = ? 
                ORDER BY ordem
            ");
            $stmt->execute([$id]);
            $checklist['itens'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $checklist]);

        } catch (\Exception $e) {
            error_log("Erro ao buscar checklist: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar checklist']);
        }
    }

    // Excluir checklist (soft delete)
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                return;
            }

            $user_id = $_SESSION['user_id'];
            
            // Verificar se é admin ou super admin
            if (!PermissionService::isAdmin($user_id) && !PermissionService::isSuperAdmin($user_id)) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                return;
            }

            // Soft delete
            $stmt = $this->db->prepare("
                UPDATE homologacao_checklists 
                SET ativo = 0 
                WHERE id = ?
            ");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Checklist excluído']);

        } catch (\Exception $e) {
            error_log("Erro ao excluir checklist: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir checklist']);
        }
    }

    // Salvar respostas do checklist
    public function salvarRespostas()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                return;
            }

            $user_id = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            $homologacao_id = $data['homologacao_id'] ?? 0;
            $checklist_id = $data['checklist_id'] ?? 0;
            $respostas = $data['respostas'] ?? [];

            if (!$homologacao_id || !$checklist_id) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
                return;
            }

            // Iniciar transação
            $this->db->beginTransaction();

            // Limpar respostas anteriores
            $stmt = $this->db->prepare("
                DELETE FROM homologacao_checklist_respostas 
                WHERE homologacao_id = ? AND checklist_id = ?
            ");
            $stmt->execute([$homologacao_id, $checklist_id]);

            // Inserir novas respostas
            $stmt = $this->db->prepare("
                INSERT INTO homologacao_checklist_respostas 
                (homologacao_id, checklist_id, item_id, resposta, concluido, respondido_por)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($respostas as $resposta) {
                $stmt->execute([
                    $homologacao_id,
                    $checklist_id,
                    $resposta['item_id'],
                    $resposta['resposta'],
                    $resposta['concluido'] ? 1 : 0,
                    $user_id
                ]);
            }

            $this->db->commit();

            echo json_encode(['success' => true, 'message' => 'Respostas salvas com sucesso']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erro ao salvar respostas: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar respostas']);
        }
    }

    // Buscar respostas de uma homologação
    public function buscarRespostas($homologacao_id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    r.*,
                    i.titulo as item_titulo,
                    i.tipo_resposta
                FROM homologacao_checklist_respostas r
                JOIN homologacao_checklist_itens i ON r.item_id = i.id
                WHERE r.homologacao_id = ?
                ORDER BY i.ordem
            ");
            $stmt->execute([$homologacao_id]);
            $respostas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $respostas]);

        } catch (\Exception $e) {
            error_log("Erro ao buscar respostas: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar respostas']);
        }
    }
}
