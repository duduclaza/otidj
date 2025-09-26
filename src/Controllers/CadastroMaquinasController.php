<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class CadastroMaquinasController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal do cadastro de máquinas
    public function index()
    {
        $title = 'Cadastro de Máquinas - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/cadastros/maquinas.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    // Listar máquinas (API)
    public function list()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    m.*,
                    u.name as created_by_name
                FROM cadastro_maquinas m
                LEFT JOIN users u ON m.created_by = u.id
                ORDER BY m.created_at DESC
            ");
            $stmt->execute();
            $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $maquinas]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao listar máquinas: ' . $e->getMessage()]);
        }
    }

    // Criar nova máquina
    public function store()
    {
        header('Content-Type: application/json');
        
        try {
            $modelo = trim($_POST['modelo'] ?? '');
            $cod_referencia = trim($_POST['cod_referencia'] ?? '');

            // Validações
            if (empty($modelo)) {
                echo json_encode(['success' => false, 'message' => 'Modelo é obrigatório']);
                return;
            }

            if (empty($cod_referencia)) {
                echo json_encode(['success' => false, 'message' => 'Código de referência é obrigatório']);
                return;
            }

            // Verificar se código já existe
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM cadastro_maquinas WHERE cod_referencia = ?");
            $stmt->execute([$cod_referencia]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Código de referência já existe']);
                return;
            }

            // Inserir máquina
            $stmt = $this->db->prepare("
                INSERT INTO cadastro_maquinas (modelo, cod_referencia, created_by) 
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([$modelo, $cod_referencia, $_SESSION['user_id'] ?? null]);

            echo json_encode(['success' => true, 'message' => 'Máquina cadastrada com sucesso!']);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar máquina: ' . $e->getMessage()]);
        }
    }

    // Buscar máquina por ID
    public function show($id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    m.*,
                    u.name as created_by_name
                FROM cadastro_maquinas m
                LEFT JOIN users u ON m.created_by = u.id
                WHERE m.id = ?
            ");
            $stmt->execute([(int)$id]);
            $maquina = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$maquina) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Máquina não encontrada']);
                return;
            }


            echo json_encode(['success' => true, 'data' => $maquina]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar máquina: ' . $e->getMessage()]);
        }
    }

    // Atualizar máquina
    public function update($id)
    {
        header('Content-Type: application/json');
        
        try {
            $modelo = trim($_POST['modelo'] ?? '');
            $cod_referencia = trim($_POST['cod_referencia'] ?? '');

            // Validações
            if (empty($modelo)) {
                echo json_encode(['success' => false, 'message' => 'Modelo é obrigatório']);
                return;
            }

            if (empty($cod_referencia)) {
                echo json_encode(['success' => false, 'message' => 'Código de referência é obrigatório']);
                return;
            }

            // Verificar se código já existe (exceto para o registro atual)
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM cadastro_maquinas WHERE cod_referencia = ? AND id != ?");
            $stmt->execute([$cod_referencia, (int)$id]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Código de referência já existe']);
                return;
            }

            // Atualizar máquina
            $stmt = $this->db->prepare("
                UPDATE cadastro_maquinas SET modelo = ?, cod_referencia = ? WHERE id = ?
            ");
            
            $stmt->execute([$modelo, $cod_referencia, (int)$id]);

            echo json_encode(['success' => true, 'message' => 'Máquina atualizada com sucesso!']);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar máquina: ' . $e->getMessage()]);
        }
    }

    // Deletar máquina
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        try {
            // Deletar máquina
            $stmt = $this->db->prepare("DELETE FROM cadastro_maquinas WHERE id = ?");
            $stmt->execute([(int)$id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Máquina não encontrada']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Máquina excluída com sucesso!']);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir máquina: ' . $e->getMessage()]);
        }
    }
}
