<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class CadastroMaquinasController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        try {
            // Verificar permissão
            $isAdmin = $_SESSION['user_role'] === 'admin';
            if (!$isAdmin && !PermissionService::hasPermission($_SESSION['user_id'], 'cadastro_maquinas', 'view')) {
                http_response_code(403);
                echo "Acesso negado";
                return;
            }

            // Buscar máquinas
            $stmt = $this->db->prepare('
                SELECT m.*, u.name as criador_nome
                FROM cadastro_maquinas m
                LEFT JOIN users u ON m.created_by = u.id
                ORDER BY m.created_at DESC
            ');
            $stmt->execute();
            $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $title = 'Cadastro de Máquinas - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/cadastro-maquinas/index.php';
            include __DIR__ . '/../../views/layouts/main.php';

        } catch (\Exception $e) {
            error_log("Erro em Cadastro de Máquinas: " . $e->getMessage());
            http_response_code(500);
            echo "Erro ao carregar o módulo: " . $e->getMessage();
        }
    }

    public function store(): void
    {
        header('Content-Type: application/json');

        try {
            $userId = $_SESSION['user_id'];
            $modelo = trim($_POST['modelo'] ?? '');
            $codReferencia = trim($_POST['cod_referencia'] ?? '');

            if (empty($modelo) || empty($codReferencia)) {
                echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
                return;
            }

            $stmt = $this->db->prepare('
                INSERT INTO cadastro_maquinas (modelo, cod_referencia, created_by, created_at, updated_at)
                VALUES (:modelo, :cod_referencia, :created_by, NOW(), NOW())
            ');

            $stmt->execute([
                ':modelo' => $modelo,
                ':cod_referencia' => $codReferencia,
                ':created_by' => $userId
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Máquina cadastrada com sucesso!',
                'redirect' => '/cadastro-maquinas'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao salvar máquina: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    public function update(): void
    {
        header('Content-Type: application/json');

        try {
            $id = (int)($_POST['id'] ?? 0);
            $modelo = trim($_POST['modelo'] ?? '');
            $codReferencia = trim($_POST['cod_referencia'] ?? '');

            if ($id <= 0 || empty($modelo) || empty($codReferencia)) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
                return;
            }

            $stmt = $this->db->prepare('
                UPDATE cadastro_maquinas 
                SET modelo = :modelo, cod_referencia = :cod_referencia, updated_at = NOW()
                WHERE id = :id
            ');

            $stmt->execute([
                ':id' => $id,
                ':modelo' => $modelo,
                ':cod_referencia' => $codReferencia
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Máquina atualizada com sucesso!',
                'redirect' => '/cadastro-maquinas'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao atualizar máquina: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    public function delete(): void
    {
        header('Content-Type: application/json');

        try {
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }

            $stmt = $this->db->prepare('DELETE FROM cadastro_maquinas WHERE id = :id');
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Máquina excluída com sucesso!']);

        } catch (\Exception $e) {
            error_log('Erro ao excluir máquina: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir: ' . $e->getMessage()]);
        }
    }
}
