<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class CadastroPecasController
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
            if (!$isAdmin && !PermissionService::hasPermission($_SESSION['user_id'], 'cadastro_pecas', 'view')) {
                http_response_code(403);
                echo "Acesso negado";
                return;
            }

            // Buscar peças
            $stmt = $this->db->prepare('
                SELECT p.*, u.name as criador_nome
                FROM cadastro_pecas p
                LEFT JOIN users u ON p.created_by = u.id
                ORDER BY p.created_at DESC
            ');
            $stmt->execute();
            $pecas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $title = 'Cadastro de Peças - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/cadastro-pecas/index.php';
            include __DIR__ . '/../../views/layouts/main.php';

        } catch (\Exception $e) {
            error_log("Erro em Cadastro de Peças: " . $e->getMessage());
            http_response_code(500);
            echo "Erro ao carregar o módulo: " . $e->getMessage();
        }
    }

    public function store(): void
    {
        header('Content-Type: application/json');

        try {
            $userId = $_SESSION['user_id'];
            $codigoReferencia = trim($_POST['codigo_referencia'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            if (empty($codigoReferencia) || empty($descricao)) {
                echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
                return;
            }

            $stmt = $this->db->prepare('
                INSERT INTO cadastro_pecas (codigo_referencia, descricao, created_by, created_at, updated_at)
                VALUES (:codigo_referencia, :descricao, :created_by, NOW(), NOW())
            ');

            $stmt->execute([
                ':codigo_referencia' => $codigoReferencia,
                ':descricao' => $descricao,
                ':created_by' => $userId
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Peça cadastrada com sucesso!',
                'redirect' => '/cadastro-pecas'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao salvar peça: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    public function update(): void
    {
        header('Content-Type: application/json');

        try {
            $id = (int)($_POST['id'] ?? 0);
            $codigoReferencia = trim($_POST['codigo_referencia'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            if ($id <= 0 || empty($codigoReferencia) || empty($descricao)) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
                return;
            }

            $stmt = $this->db->prepare('
                UPDATE cadastro_pecas 
                SET codigo_referencia = :codigo_referencia, descricao = :descricao, updated_at = NOW()
                WHERE id = :id
            ');

            $stmt->execute([
                ':id' => $id,
                ':codigo_referencia' => $codigoReferencia,
                ':descricao' => $descricao
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Peça atualizada com sucesso!',
                'redirect' => '/cadastro-pecas'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao atualizar peça: ' . $e->getMessage());
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

            $stmt = $this->db->prepare('DELETE FROM cadastro_pecas WHERE id = :id');
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Peça excluída com sucesso!']);

        } catch (\Exception $e) {
            error_log('Erro ao excluir peça: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir: ' . $e->getMessage()]);
        }
    }

    public function import(): void
    {
        header('Content-Type: application/json');

        try {
            $userId = $_SESSION['user_id'];
            
            // Receber dados JSON do frontend
            $pecasData = json_decode($_POST['pecas_data'] ?? '[]', true);
            
            if (empty($pecasData)) {
                echo json_encode(['success' => false, 'message' => 'Nenhum dado válido encontrado']);
                return;
            }

            $imported = 0;
            $errors = [];

            // Iniciar transação
            $this->db->beginTransaction();

            foreach ($pecasData as $index => $row) {
                try {
                    // Validar dados
                    $codigoReferencia = trim($row[0] ?? '');
                    $descricao = trim($row[1] ?? '');

                    if (empty($codigoReferencia) || empty($descricao)) {
                        $errors[] = "Linha " . ($index + 10) . ": Campos obrigatórios não preenchidos";
                        continue;
                    }

                    // Verificar se código já existe
                    $stmtCheck = $this->db->prepare('SELECT id FROM cadastro_pecas WHERE codigo_referencia = :codigo');
                    $stmtCheck->execute([':codigo' => $codigoReferencia]);
                    
                    if ($stmtCheck->fetch()) {
                        $errors[] = "Linha " . ($index + 10) . ": Código '$codigoReferencia' já cadastrado";
                        continue;
                    }

                    // Inserir peça
                    $stmt = $this->db->prepare('
                        INSERT INTO cadastro_pecas (codigo_referencia, descricao, created_by, created_at, updated_at)
                        VALUES (:codigo_referencia, :descricao, :created_by, NOW(), NOW())
                    ');

                    $stmt->execute([
                        ':codigo_referencia' => $codigoReferencia,
                        ':descricao' => $descricao,
                        ':created_by' => $userId
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Linha " . ($index + 10) . ": " . $e->getMessage();
                }
            }

            // Commit da transação
            $this->db->commit();

            // Preparar mensagem de resposta
            $message = "$imported peças importadas com sucesso!";
            if (!empty($errors)) {
                $message .= " (" . count($errors) . " erros encontrados)";
                error_log("Erros na importação de peças: " . implode('; ', $errors));
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            error_log('Erro ao importar peças: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao processar importação: ' . $e->getMessage()
            ]);
        }
    }
}
