<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class FluxogramasController
{
    private $db;

    public function __construct()
    {
        try {
            $this->db = Database::getInstance();
        } catch (\Exception $e) {
            error_log("FluxogramasController - Erro de conexão: " . $e->getMessage());
            $this->db = null;
        }
    }

    public function index()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }

            $user_id = $_SESSION['user_id'];
            
            // Permissões básicas
            $canViewCadastroTitulos = true;
            $canViewMeusRegistros = true;
            $canViewPendenteAprovacao = false;
            $canViewVisualizacao = true;
            $canViewLogsVisualizacao = false;
            
            // Departamentos
            $departamentos = $this->getDepartamentos();
            
            $title = 'Fluxogramas - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/fluxogramas/index.php';
            
            if (file_exists(__DIR__ . '/../../views/layouts/main.php')) {
                include __DIR__ . '/../../views/layouts/main.php';
            } else {
                include $viewFile;
            }
            
        } catch (\Throwable $e) {
            error_log("FluxogramasController::index - ERRO: " . $e->getMessage());
            echo "<h1>🚧 Módulo em Desenvolvimento</h1><p><a href='/inicio'>← Voltar</a></p>";
        }
    }

    private function getDepartamentos()
    {
        if (!$this->db) return [];
        
        try {
            $stmt = $this->db->prepare("SELECT id, nome FROM departamentos ORDER BY nome ASC");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function createTitulo()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Em desenvolvimento']);
    }

    public function listTitulos()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => []]);
    }

    public function searchTitulos()
    {
        header('Content-Type: application/json');
        echo json_encode([]);
    }

    public function deleteTitulo()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Em desenvolvimento']);
    }

    public function createRegistro()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Em desenvolvimento']);
    }

    public function editarRegistro()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Em desenvolvimento']);
    }

    public function listMeusRegistros()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => []]);
    }

    public function downloadArquivo($id)
    {
        http_response_code(404);
        echo "Arquivo não encontrado";
    }
}
