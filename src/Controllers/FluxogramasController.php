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
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            // Verificar permissões específicas para cada aba
            // Usando módulo genérico 'fluxogramas' para simplificar
            $hasFluxogramasPermission = $isAdmin || \App\Services\PermissionService::hasPermission($user_id, 'fluxogramas', 'view');
            
            $canViewCadastroTitulos = $hasFluxogramasPermission;
            $canViewMeusRegistros = $hasFluxogramasPermission;
            $canViewPendenteAprovacao = $isAdmin; // Apenas admin pode ver pendente aprovação
            $canViewVisualizacao = $hasFluxogramasPermission;
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

            // Exibir detalhes somente se APP_DEBUG=true ou ?debug=1
            $appDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
            $reqDebug = isset($_GET['debug']) && $_GET['debug'] == '1';
            if ($appDebug || $reqDebug) {
                echo 'Erro: ' . htmlspecialchars($e->getMessage());
                echo '<br>Arquivo: ' . htmlspecialchars($e->getFile());
                echo '<br>Linha: ' . (int)$e->getLine();
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                exit;
            }
            // Caso contrário, lançar novamente para página 500 padrão
            throw $e;
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
        
        try {
            // Debug: Log da requisição
            error_log("=== FluxogramasController::createTitulo - INÍCIO ===");
            error_log("POST data: " . json_encode($_POST));
            
            // Verificar permissão
            if (!isset($_SESSION['user_id'])) {
                error_log("ERRO: Usuário não autenticado");
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            error_log("User ID: " . $user_id);
            
            // Verificar conexão com banco
            if (!$this->db) {
                error_log("ERRO: Conexão com banco de dados falhou");
                echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco de dados']);
                return;
            }
            
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            error_log("É Admin: " . ($isAdmin ? 'SIM' : 'NÃO'));
            
            if (!$isAdmin && !\App\Services\PermissionService::hasPermission($user_id, 'fluxogramas', 'edit')) {
                error_log("ERRO: Sem permissão");
                echo json_encode(['success' => false, 'message' => 'Sem permissão para criar títulos']);
                return;
            }
            
            // Verificar se a tabela existe
            try {
                $stmt = $this->db->query("SHOW TABLES LIKE 'fluxogramas_titulos'");
                $tableExists = $stmt->fetch();
                error_log("Tabela existe: " . ($tableExists ? 'SIM' : 'NÃO'));
                
                if (!$tableExists) {
                    echo json_encode(['success' => false, 'message' => 'Tabela fluxogramas_titulos não existe. Execute o script SQL primeiro.']);
                    return;
                }
            } catch (\Exception $e) {
                error_log("ERRO ao verificar tabela: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erro ao verificar tabela: ' . $e->getMessage()]);
                return;
            }
            
            // Validar dados
            $titulo = trim($_POST['titulo'] ?? '');
            $departamento_id = $_POST['departamento_id'] ?? '';
            
            error_log("Título: " . $titulo);
            error_log("Departamento ID: " . $departamento_id);
            
            if (empty($titulo) || empty($departamento_id)) {
                error_log("ERRO: Campos obrigatórios vazios");
                echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
                return;
            }
            
            // Normalizar título para verificação de duplicidade
            $titulo_normalizado = $this->normalizarTitulo($titulo);
            error_log("Título normalizado: " . $titulo_normalizado);
            
            // Verificar se já existe
            $stmt = $this->db->prepare("SELECT id FROM fluxogramas_titulos WHERE titulo_normalizado = ?");
            $stmt->execute([$titulo_normalizado]);
            
            if ($stmt->fetch()) {
                error_log("ERRO: Título já existe");
                echo json_encode(['success' => false, 'message' => 'Já existe um fluxograma com este título']);
                return;
            }
            
            // Inserir no banco
            error_log("Tentando inserir no banco...");
            $stmt = $this->db->prepare("
                INSERT INTO fluxogramas_titulos (titulo, titulo_normalizado, departamento_id, criado_por) 
                VALUES (?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([$titulo, $titulo_normalizado, $departamento_id, $user_id]);
            error_log("Resultado da inserção: " . ($result ? 'SUCESSO' : 'FALHA'));
            
            if ($result) {
                $lastId = $this->db->lastInsertId();
                error_log("ID inserido: " . $lastId);
                echo json_encode(['success' => true, 'message' => 'Título cadastrado com sucesso!']);
            } else {
                error_log("ERRO: Falha ao executar INSERT");
                echo json_encode(['success' => false, 'message' => 'Erro ao inserir no banco de dados']);
            }
            
        } catch (\Exception $e) {
            // Log detalhado do erro
            $errorMsg = "FluxogramasController::createTitulo - Erro: " . $e->getMessage() . 
                        " | File: " . $e->getFile() . 
                        " | Line: " . $e->getLine() . 
                        " | Trace: " . $e->getTraceAsString();
            error_log($errorMsg);
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }
    
    private function normalizarTitulo($titulo)
    {
        $titulo = mb_strtolower($titulo, 'UTF-8');
        $titulo = preg_replace('/\s+/', ' ', $titulo);
        return trim($titulo);
    }

    public function listTitulos()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar se a tabela existe
            $stmt = $this->db->query("SHOW TABLES LIKE 'fluxogramas_titulos'");
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Tabela fluxogramas_titulos não existe']);
                return;
            }
            
            // Buscar todos os títulos
            $stmt = $this->db->query("
                SELECT 
                    t.id,
                    t.titulo,
                    t.criado_em,
                    d.nome as departamento_nome,
                    u.name as criado_por_nome
                FROM fluxogramas_titulos t
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                LEFT JOIN users u ON t.criado_por = u.id
                ORDER BY t.criado_em DESC
            ");
            
            $titulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $titulos]);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::listTitulos - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao listar títulos: ' . $e->getMessage()]);
        }
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
