<?php

namespace App\Controllers;

use App\Config\Database;

class ClientesController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Verificar se é admin
     */
    private function isAdmin(): bool
    {
        return isset($_SESSION['user_role']) && 
               in_array($_SESSION['user_role'], ['admin', 'super_admin', 'superadmin']);
    }

    /**
     * Página principal - Listar clientes
     */
    public function index()
    {
        // Verificar se está logado
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        if (!$this->isAdmin()) {
            $_SESSION['error'] = 'Acesso restrito a administradores.';
            header('Location: /');
            exit;
        }

        try {
            $stmt = $this->db->query("SELECT * FROM clientes ORDER BY nome ASC");
            $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $clientes = [];
            error_log('Erro ao carregar clientes: ' . $e->getMessage());
        }

        $title = 'Cadastro de Clientes - SGQ OTI';
        $viewFile = __DIR__ . '/../../views/pages/cadastros/clientes.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    /**
     * Listar clientes (API)
     */
    public function listar()
    {
        header('Content-Type: application/json');

        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }

        try {
            $stmt = $this->db->query("SELECT * FROM clientes ORDER BY nome ASC");
            $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $clientes]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Criar cliente
     */
    public function criar()
    {
        header('Content-Type: application/json');

        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }

        try {
            $codigo = trim($_POST['codigo'] ?? '');
            $nome = trim($_POST['nome'] ?? '');

            if (empty($codigo) || empty($nome)) {
                echo json_encode(['success' => false, 'message' => 'Código e nome são obrigatórios']);
                exit;
            }

            // Verificar se código já existe
            $stmt = $this->db->prepare("SELECT id FROM clientes WHERE codigo = ?");
            $stmt->execute([$codigo]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Este código de cliente já existe']);
                exit;
            }

            $stmt = $this->db->prepare("INSERT INTO clientes (codigo, nome, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$codigo, $nome]);

            echo json_encode([
                'success' => true, 
                'message' => 'Cliente cadastrado com sucesso!',
                'id' => $this->db->lastInsertId()
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Atualizar cliente
     */
    public function atualizar()
    {
        header('Content-Type: application/json');

        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }

        try {
            $id = $_POST['id'] ?? '';
            $codigo = trim($_POST['codigo'] ?? '');
            $nome = trim($_POST['nome'] ?? '');

            if (empty($id) || empty($codigo) || empty($nome)) {
                echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
                exit;
            }

            // Verificar se código já existe em outro cliente
            $stmt = $this->db->prepare("SELECT id FROM clientes WHERE codigo = ? AND id != ?");
            $stmt->execute([$codigo, $id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Este código já está em uso por outro cliente']);
                exit;
            }

            $stmt = $this->db->prepare("UPDATE clientes SET codigo = ?, nome = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$codigo, $nome, $id]);

            echo json_encode(['success' => true, 'message' => 'Cliente atualizado com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Excluir cliente
     */
    public function excluir()
    {
        header('Content-Type: application/json');

        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }

        try {
            $id = $_POST['id'] ?? '';

            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID não informado']);
                exit;
            }

            $stmt = $this->db->prepare("DELETE FROM clientes WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Cliente excluído com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Importar clientes via Excel/CSV
     */
    public function importar()
    {
        header('Content-Type: application/json');

        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }

        try {
            $dados = json_decode(file_get_contents('php://input'), true);
            
            if (empty($dados) || !is_array($dados)) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
                exit;
            }

            $importados = 0;
            $erros = 0;
            $duplicados = 0;

            foreach ($dados as $item) {
                $codigo = trim($item['codigo'] ?? '');
                $nome = trim($item['nome'] ?? '');

                if (empty($codigo) || empty($nome)) {
                    $erros++;
                    continue;
                }

                // Verificar duplicado
                $stmt = $this->db->prepare("SELECT id FROM clientes WHERE codigo = ?");
                $stmt->execute([$codigo]);
                
                if ($stmt->fetch()) {
                    // Atualizar existente
                    $stmt = $this->db->prepare("UPDATE clientes SET nome = ?, updated_at = NOW() WHERE codigo = ?");
                    $stmt->execute([$nome, $codigo]);
                    $duplicados++;
                } else {
                    // Inserir novo
                    $stmt = $this->db->prepare("INSERT INTO clientes (codigo, nome, created_at) VALUES (?, ?, NOW())");
                    $stmt->execute([$codigo, $nome]);
                    $importados++;
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "Importação concluída! {$importados} novos, {$duplicados} atualizados, {$erros} erros.",
                'importados' => $importados,
                'atualizados' => $duplicados,
                'erros' => $erros
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro na importação: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Download do template Excel
     */
    public function template()
    {
        if (!$this->isAdmin()) {
            header('Location: /');
            exit;
        }

        // Gerar CSV simples como template
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="template_clientes.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para Excel reconhecer UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalho
        fputcsv($output, ['Código do Cliente', 'Nome do Cliente'], ';');
        
        // Exemplos
        fputcsv($output, ['00001234', 'Empresa Exemplo Ltda'], ';');
        fputcsv($output, ['00005678', 'Cliente Teste S.A.'], ';');
        
        fclose($output);
        exit;
    }
}
