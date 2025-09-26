<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class CadastroPecasController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal do cadastro de peças
    public function index()
    {
        $title = 'Cadastro de Peças - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/cadastros/pecas.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    // Listar peças (API)
    public function list()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.*,
                    u.name as created_by_name
                FROM cadastro_pecas p
                LEFT JOIN users u ON p.created_by = u.id
                ORDER BY p.created_at DESC
            ");
            $stmt->execute();
            $pecas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $pecas]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao listar peças: ' . $e->getMessage()]);
        }
    }

    // Criar nova peça
    public function store()
    {
        header('Content-Type: application/json');
        
        try {
            $codigo_referencia = trim($_POST['codigo_referencia'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            // Validações
            if (empty($codigo_referencia)) {
                echo json_encode(['success' => false, 'message' => 'Código de referência é obrigatório']);
                return;
            }

            if (empty($descricao)) {
                echo json_encode(['success' => false, 'message' => 'Descrição é obrigatória']);
                return;
            }

            // Verificar se código já existe
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM cadastro_pecas WHERE codigo_referencia = ?");
            $stmt->execute([$codigo_referencia]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Código de referência já existe']);
                return;
            }

            // Inserir peça
            $stmt = $this->db->prepare("
                INSERT INTO cadastro_pecas (codigo_referencia, descricao, created_by) 
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([$codigo_referencia, $descricao, $_SESSION['user_id'] ?? null]);

            echo json_encode(['success' => true, 'message' => 'Peça cadastrada com sucesso!']);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar peça: ' . $e->getMessage()]);
        }
    }

    // Buscar peça por ID
    public function show($id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.*,
                    u.name as created_by_name
                FROM cadastro_pecas p
                LEFT JOIN users u ON p.created_by = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([(int)$id]);
            $peca = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$peca) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Peça não encontrada']);
                return;
            }

            echo json_encode(['success' => true, 'data' => $peca]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar peça: ' . $e->getMessage()]);
        }
    }

    // Atualizar peça
    public function update($id)
    {
        header('Content-Type: application/json');
        
        try {
            $codigo_referencia = trim($_POST['codigo_referencia'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            // Validações
            if (empty($codigo_referencia)) {
                echo json_encode(['success' => false, 'message' => 'Código de referência é obrigatório']);
                return;
            }

            if (empty($descricao)) {
                echo json_encode(['success' => false, 'message' => 'Descrição é obrigatória']);
                return;
            }

            // Verificar se código já existe (exceto para o registro atual)
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM cadastro_pecas WHERE codigo_referencia = ? AND id != ?");
            $stmt->execute([$codigo_referencia, (int)$id]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Código de referência já existe']);
                return;
            }

            // Atualizar peça
            $stmt = $this->db->prepare("
                UPDATE cadastro_pecas SET codigo_referencia = ?, descricao = ? WHERE id = ?
            ");
            
            $stmt->execute([$codigo_referencia, $descricao, (int)$id]);

            echo json_encode(['success' => true, 'message' => 'Peça atualizada com sucesso!']);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar peça: ' . $e->getMessage()]);
        }
    }

    // Deletar peça
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        try {
            // Deletar peça
            $stmt = $this->db->prepare("DELETE FROM cadastro_pecas WHERE id = ?");
            $stmt->execute([(int)$id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Peça não encontrada']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Peça excluída com sucesso!']);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir peça: ' . $e->getMessage()]);
        }
    }

}
