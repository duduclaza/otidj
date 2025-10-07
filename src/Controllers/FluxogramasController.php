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
        
        try {
            $query = $_GET['q'] ?? '';
            
            if (strlen($query) < 2) {
                echo json_encode(['success' => true, 'data' => []]);
                return;
            }
            
            $searchTerm = '%' . $query . '%';
            
            $stmt = $this->db->prepare("
                SELECT id, titulo
                FROM fluxogramas_titulos
                WHERE titulo LIKE ? OR titulo_normalizado LIKE ?
                ORDER BY titulo ASC
                LIMIT 10
            ");
            
            $stmt->execute([$searchTerm, $searchTerm]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $resultados]);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::searchTitulos - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar títulos']);
        }
    }

    public function deleteTitulo()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores podem excluir títulos']);
                return;
            }
            
            $titulo_id = $_POST['titulo_id'] ?? '';
            
            if (empty($titulo_id)) {
                echo json_encode(['success' => false, 'message' => 'ID do título não informado']);
                return;
            }
            
            // Verificar se existem registros vinculados
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM fluxogramas_registros WHERE titulo_id = ?");
            $stmt->execute([$titulo_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Não é possível excluir. Existem ' . $result['total'] . ' registro(s) vinculado(s) a este título.']);
                return;
            }
            
            // Excluir título
            $stmt = $this->db->prepare("DELETE FROM fluxogramas_titulos WHERE id = ?");
            $stmt->execute([$titulo_id]);
            
            echo json_encode(['success' => true, 'message' => 'Título excluído com sucesso!']);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::deleteTitulo - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir título: ' . $e->getMessage()]);
        }
    }

    public function createRegistro()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar autenticação
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            
            // Validar dados obrigatórios
            $titulo_id = $_POST['titulo_id'] ?? '';
            $visibilidade = $_POST['visibilidade'] ?? '';
            
            if (empty($titulo_id) || empty($visibilidade)) {
                echo json_encode(['success' => false, 'message' => 'Campos obrigatórios não preenchidos']);
                return;
            }
            
            // Validar arquivo
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Arquivo não foi enviado corretamente']);
                return;
            }
            
            $arquivo = $_FILES['arquivo'];
            $nome_arquivo = $arquivo['name'];
            $tamanho = $arquivo['size'];
            $tmp_name = $arquivo['tmp_name'];
            
            // Validar tamanho (max 10MB)
            if ($tamanho > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo: 10MB']);
                return;
            }
            
            // Validar extensão
            $extensao = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
            $extensoes_permitidas = ['pdf', 'png', 'jpg', 'jpeg', 'ppt', 'pptx'];
            
            if (!in_array($extensao, $extensoes_permitidas)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Apenas: PDF, PNG, JPEG, PPT, PPTX']);
                return;
            }
            
            // Ler conteúdo do arquivo
            $conteudo_arquivo = file_get_contents($tmp_name);
            
            if ($conteudo_arquivo === false) {
                echo json_encode(['success' => false, 'message' => 'Erro ao ler arquivo']);
                return;
            }
            
            // Determinar próxima versão
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(versao), 0) + 1 as proxima_versao FROM fluxogramas_registros WHERE titulo_id = ?");
            $stmt->execute([$titulo_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $versao = $result['proxima_versao'];
            
            // Determinar se é público
            $publico = ($visibilidade === 'publico') ? 1 : 0;
            
            // Inserir registro
            $stmt = $this->db->prepare("
                INSERT INTO fluxogramas_registros 
                (titulo_id, versao, arquivo, nome_arquivo, extensao, tamanho_arquivo, publico, status, criado_por) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDENTE', ?)
            ");
            
            $stmt->execute([
                $titulo_id,
                $versao,
                $conteudo_arquivo,
                $nome_arquivo,
                $extensao,
                $tamanho,
                $publico,
                $user_id
            ]);
            
            $registro_id = $this->db->lastInsertId();
            
            // Se não for público, vincular departamentos
            if ($publico == 0 && isset($_POST['departamentos_permitidos']) && is_array($_POST['departamentos_permitidos'])) {
                $stmt_dept = $this->db->prepare("
                    INSERT INTO fluxogramas_registros_departamentos (registro_id, departamento_id) 
                    VALUES (?, ?)
                ");
                
                foreach ($_POST['departamentos_permitidos'] as $dept_id) {
                    $stmt_dept->execute([$registro_id, $dept_id]);
                }
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Registro criado com sucesso! Versão: v' . $versao . '. Aguardando aprovação do administrador.'
            ]);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::createRegistro - Erro: " . $e->getMessage() . " | Line: " . $e->getLine());
            echo json_encode(['success' => false, 'message' => 'Erro ao criar registro: ' . $e->getMessage()]);
        }
    }

    public function editarRegistro()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $registro_id = $_POST['registro_id'] ?? '';
            
            if (empty($registro_id)) {
                echo json_encode(['success' => false, 'message' => 'ID do registro não informado']);
                return;
            }
            
            // Verificar se o registro pertence ao usuário e está reprovado
            $stmt = $this->db->prepare("
                SELECT id, status, criado_por 
                FROM fluxogramas_registros 
                WHERE id = ?
            ");
            $stmt->execute([$registro_id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$registro) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado']);
                return;
            }
            
            if ($registro['criado_por'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Você não tem permissão para editar este registro']);
                return;
            }
            
            if ($registro['status'] != 'REPROVADO') {
                echo json_encode(['success' => false, 'message' => 'Apenas registros reprovados podem ser editados']);
                return;
            }
            
            // Validar arquivo
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Arquivo não foi enviado corretamente']);
                return;
            }
            
            $arquivo = $_FILES['arquivo'];
            $nome_arquivo = $arquivo['name'];
            $tamanho = $arquivo['size'];
            $tmp_name = $arquivo['tmp_name'];
            
            // Validar tamanho
            if ($tamanho > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo: 10MB']);
                return;
            }
            
            // Validar extensão
            $extensao = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
            $extensoes_permitidas = ['pdf', 'png', 'jpg', 'jpeg', 'ppt', 'pptx'];
            
            if (!in_array($extensao, $extensoes_permitidas)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido']);
                return;
            }
            
            // Ler arquivo
            $conteudo_arquivo = file_get_contents($tmp_name);
            
            if ($conteudo_arquivo === false) {
                echo json_encode(['success' => false, 'message' => 'Erro ao ler arquivo']);
                return;
            }
            
            // Atualizar registro
            $stmt = $this->db->prepare("
                UPDATE fluxogramas_registros 
                SET arquivo = ?, 
                    nome_arquivo = ?, 
                    extensao = ?, 
                    tamanho_arquivo = ?,
                    status = 'PENDENTE',
                    observacao_reprovacao = NULL,
                    aprovado_por = NULL,
                    aprovado_em = NULL
                WHERE id = ?
            ");
            
            $stmt->execute([
                $conteudo_arquivo,
                $nome_arquivo,
                $extensao,
                $tamanho,
                $registro_id
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Registro atualizado com sucesso! Aguardando nova aprovação.']);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::editarRegistro - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao editar registro: ' . $e->getMessage()]);
        }
    }
    
    public function aprovarRegistro()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores podem aprovar registros']);
                return;
            }
            
            $registro_id = $_POST['registro_id'] ?? '';
            
            if (empty($registro_id)) {
                echo json_encode(['success' => false, 'message' => 'ID do registro não informado']);
                return;
            }
            
            // Verificar se registro existe e está pendente
            $stmt = $this->db->prepare("SELECT id, status FROM fluxogramas_registros WHERE id = ?");
            $stmt->execute([$registro_id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$registro) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado']);
                return;
            }
            
            if ($registro['status'] != 'PENDENTE') {
                echo json_encode(['success' => false, 'message' => 'Este registro não está pendente de aprovação']);
                return;
            }
            
            // Aprovar registro
            $stmt = $this->db->prepare("
                UPDATE fluxogramas_registros 
                SET status = 'APROVADO',
                    aprovado_por = ?,
                    aprovado_em = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([$user_id, $registro_id]);
            
            echo json_encode(['success' => true, 'message' => 'Registro aprovado com sucesso!']);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::aprovarRegistro - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao aprovar registro: ' . $e->getMessage()]);
        }
    }
    
    public function reprovarRegistro()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores podem reprovar registros']);
                return;
            }
            
            $registro_id = $_POST['registro_id'] ?? '';
            $observacao = trim($_POST['observacao'] ?? '');
            
            if (empty($registro_id)) {
                echo json_encode(['success' => false, 'message' => 'ID do registro não informado']);
                return;
            }
            
            if (empty($observacao)) {
                echo json_encode(['success' => false, 'message' => 'Observação é obrigatória para reprovação']);
                return;
            }
            
            // Verificar se registro existe e está pendente
            $stmt = $this->db->prepare("SELECT id, status FROM fluxogramas_registros WHERE id = ?");
            $stmt->execute([$registro_id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$registro) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado']);
                return;
            }
            
            if ($registro['status'] != 'PENDENTE') {
                echo json_encode(['success' => false, 'message' => 'Este registro não está pendente de aprovação']);
                return;
            }
            
            // Reprovar registro
            $stmt = $this->db->prepare("
                UPDATE fluxogramas_registros 
                SET status = 'REPROVADO',
                    aprovado_por = ?,
                    aprovado_em = NOW(),
                    observacao_reprovacao = ?
                WHERE id = ?
            ");
            
            $stmt->execute([$user_id, $observacao, $registro_id]);
            
            echo json_encode(['success' => true, 'message' => 'Registro reprovado. O autor será notificado.']);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::reprovarRegistro - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao reprovar registro: ' . $e->getMessage()]);
        }
    }

    public function listMeusRegistros()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            
            // Verificar se a tabela existe
            $stmt = $this->db->query("SHOW TABLES LIKE 'fluxogramas_registros'");
            if (!$stmt->fetch()) {
                echo json_encode(['success' => true, 'data' => [], 'message' => 'Nenhum registro encontrado']);
                return;
            }
            
            // Buscar registros do usuário
            $stmt = $this->db->prepare("
                SELECT 
                    r.id,
                    r.versao,
                    r.status,
                    r.nome_arquivo,
                    r.extensao,
                    r.tamanho_arquivo,
                    r.publico,
                    r.criado_em,
                    r.observacao_reprovacao,
                    t.titulo,
                    GROUP_CONCAT(d.nome SEPARATOR ', ') as departamentos_permitidos
                FROM fluxogramas_registros r
                INNER JOIN fluxogramas_titulos t ON r.titulo_id = t.id
                LEFT JOIN fluxogramas_registros_departamentos rd ON r.id = rd.registro_id
                LEFT JOIN departamentos d ON rd.departamento_id = d.id
                WHERE r.criado_por = ?
                GROUP BY r.id
                ORDER BY r.criado_em DESC
            ");
            
            $stmt->execute([$user_id]);
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $registros]);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::listMeusRegistros - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao listar registros: ' . $e->getMessage()]);
        }
    }

    public function downloadArquivo($id)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(403);
                echo "Acesso negado";
                return;
            }
            
            $stmt = $this->db->prepare("
                SELECT arquivo, nome_arquivo, extensao 
                FROM fluxogramas_registros 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$registro) {
                http_response_code(404);
                echo "Arquivo não encontrado";
                return;
            }
            
            // Definir headers para download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $registro['nome_arquivo'] . '"');
            header('Content-Length: ' . strlen($registro['arquivo']));
            
            echo $registro['arquivo'];
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::downloadArquivo - Erro: " . $e->getMessage());
            http_response_code(500);
            echo "Erro ao baixar arquivo";
        }
    }
    
    public function listPendentes()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }
            
            $stmt = $this->db->query("
                SELECT 
                    r.id,
                    r.versao,
                    r.nome_arquivo,
                    r.criado_em,
                    t.titulo,
                    u.name as autor_nome,
                    u.email as autor_email
                FROM fluxogramas_registros r
                INNER JOIN fluxogramas_titulos t ON r.titulo_id = t.id
                INNER JOIN users u ON r.criado_por = u.id
                WHERE r.status = 'PENDENTE'
                ORDER BY r.criado_em ASC
            ");
            
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $registros]);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::listPendentes - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao listar pendentes']);
        }
    }
    
    public function createSolicitacaoExclusao()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $registro_id = $_POST['registro_id'] ?? '';
            $motivo = trim($_POST['motivo'] ?? '');
            
            if (empty($registro_id) || empty($motivo)) {
                echo json_encode(['success' => false, 'message' => 'Registro e motivo são obrigatórios']);
                return;
            }
            
            // Verificar se o registro existe e pertence ao usuário
            $stmt = $this->db->prepare("
                SELECT id, criado_por, status 
                FROM fluxogramas_registros 
                WHERE id = ?
            ");
            $stmt->execute([$registro_id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$registro) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado']);
                return;
            }
            
            if ($registro['criado_por'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Você não tem permissão para solicitar exclusão deste registro']);
                return;
            }
            
            // Verificar se já existe solicitação pendente
            $stmt = $this->db->prepare("
                SELECT id 
                FROM fluxogramas_solicitacoes_exclusao 
                WHERE registro_id = ? AND status = 'PENDENTE'
            ");
            $stmt->execute([$registro_id]);
            
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Já existe uma solicitação de exclusão pendente para este registro']);
                return;
            }
            
            // Criar solicitação
            $stmt = $this->db->prepare("
                INSERT INTO fluxogramas_solicitacoes_exclusao 
                (registro_id, solicitante_id, motivo, status) 
                VALUES (?, ?, ?, 'PENDENTE')
            ");
            
            $stmt->execute([$registro_id, $user_id, $motivo]);
            $solicitacao_id = $this->db->lastInsertId();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Solicitação enviada com sucesso!',
                'solicitacao_id' => $solicitacao_id
            ]);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::createSolicitacaoExclusao - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao criar solicitação: ' . $e->getMessage()]);
        }
    }
    
    public function listSolicitacoes()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }
            
            // Verificar se tabela existe
            $stmt = $this->db->query("SHOW TABLES LIKE 'fluxogramas_solicitacoes_exclusao'");
            if (!$stmt->fetch()) {
                echo json_encode(['success' => true, 'data' => []]);
                return;
            }
            
            // Buscar todas as solicitações
            $stmt = $this->db->query("
                SELECT 
                    s.id,
                    s.motivo,
                    s.status,
                    s.solicitado_em,
                    s.avaliado_em,
                    s.observacoes_avaliacao,
                    r.versao,
                    r.nome_arquivo,
                    t.titulo,
                    u_solicitante.name as solicitante_nome,
                    u_solicitante.email as solicitante_email,
                    u_avaliador.name as avaliado_por_nome
                FROM fluxogramas_solicitacoes_exclusao s
                INNER JOIN fluxogramas_registros r ON s.registro_id = r.id
                INNER JOIN fluxogramas_titulos t ON r.titulo_id = t.id
                INNER JOIN users u_solicitante ON s.solicitante_id = u_solicitante.id
                LEFT JOIN users u_avaliador ON s.avaliado_por = u_avaliador.id
                ORDER BY 
                    CASE s.status 
                        WHEN 'PENDENTE' THEN 1 
                        WHEN 'APROVADA' THEN 2 
                        WHEN 'REPROVADA' THEN 3 
                    END,
                    s.solicitado_em DESC
            ");
            
            $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $solicitacoes]);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::listSolicitacoes - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao listar solicitações: ' . $e->getMessage()]);
        }
    }
    
    public function aprovarSolicitacao()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores podem aprovar solicitações']);
                return;
            }
            
            $solicitacao_id = $_POST['solicitacao_id'] ?? '';
            $observacoes = trim($_POST['observacoes'] ?? '');
            
            if (empty($solicitacao_id)) {
                echo json_encode(['success' => false, 'message' => 'ID da solicitação não informado']);
                return;
            }
            
            // Buscar solicitação
            $stmt = $this->db->prepare("
                SELECT s.id, s.registro_id, s.status 
                FROM fluxogramas_solicitacoes_exclusao s 
                WHERE s.id = ?
            ");
            $stmt->execute([$solicitacao_id]);
            $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$solicitacao) {
                echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
                return;
            }
            
            if ($solicitacao['status'] != 'PENDENTE') {
                echo json_encode(['success' => false, 'message' => 'Esta solicitação já foi processada']);
                return;
            }
            
            // Iniciar transação
            $this->db->beginTransaction();
            
            try {
                // Atualizar solicitação
                $stmt = $this->db->prepare("
                    UPDATE fluxogramas_solicitacoes_exclusao 
                    SET status = 'APROVADA',
                        avaliado_por = ?,
                        avaliado_em = NOW(),
                        observacoes_avaliacao = ?
                    WHERE id = ?
                ");
                $stmt->execute([$user_id, $observacoes, $solicitacao_id]);
                
                // Excluir registro
                $stmt = $this->db->prepare("DELETE FROM fluxogramas_registros WHERE id = ?");
                $stmt->execute([$solicitacao['registro_id']]);
                
                $this->db->commit();
                
                echo json_encode(['success' => true, 'message' => 'Solicitação aprovada e registro excluído com sucesso!']);
                
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::aprovarSolicitacao - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao aprovar solicitação: ' . $e->getMessage()]);
        }
    }
    
    public function reprovarSolicitacao()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores podem reprovar solicitações']);
                return;
            }
            
            $solicitacao_id = $_POST['solicitacao_id'] ?? '';
            $observacoes = trim($_POST['observacoes'] ?? '');
            
            if (empty($solicitacao_id) || empty($observacoes)) {
                echo json_encode(['success' => false, 'message' => 'ID da solicitação e observações são obrigatórios']);
                return;
            }
            
            // Buscar solicitação
            $stmt = $this->db->prepare("
                SELECT id, status 
                FROM fluxogramas_solicitacoes_exclusao 
                WHERE id = ?
            ");
            $stmt->execute([$solicitacao_id]);
            $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$solicitacao) {
                echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
                return;
            }
            
            if ($solicitacao['status'] != 'PENDENTE') {
                echo json_encode(['success' => false, 'message' => 'Esta solicitação já foi processada']);
                return;
            }
            
            // Reprovar solicitação
            $stmt = $this->db->prepare("
                UPDATE fluxogramas_solicitacoes_exclusao 
                SET status = 'REPROVADA',
                    avaliado_por = ?,
                    avaliado_em = NOW(),
                    observacoes_avaliacao = ?
                WHERE id = ?
            ");
            
            $stmt->execute([$user_id, $observacoes, $solicitacao_id]);
            
            echo json_encode(['success' => true, 'message' => 'Solicitação reprovada. O solicitante será notificado.']);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::reprovarSolicitacao - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao reprovar solicitação: ' . $e->getMessage()]);
        }
    }
    
    public function listVisualizacao()
    {
        header('Content-Type: application/json');
        
        try {
            error_log("=== FluxogramasController::listVisualizacao - INÍCIO ===");
            
            if (!isset($_SESSION['user_id'])) {
                error_log("ERRO: Usuário não autenticado");
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            error_log("User ID: " . $user_id . " | É Admin: " . ($isAdmin ? 'SIM' : 'NÃO'));
            
            // Verificar se tabelas existem
            $stmt = $this->db->query("SHOW TABLES LIKE 'fluxogramas_registros'");
            $tableExists = $stmt->fetch();
            error_log("Tabela fluxogramas_registros existe: " . ($tableExists ? 'SIM' : 'NÃO'));
            
            if (!$tableExists) {
                echo json_encode(['success' => true, 'data' => [], 'debug' => 'Tabela não existe']);
                return;
            }
            
            // Contar registros aprovados
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM fluxogramas_registros WHERE status = 'APROVADO'");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Total de registros APROVADOS no banco: " . $count['total']);
            
            if ($isAdmin) {
                // ADMIN VÊ TUDO - não precisa verificar departamento
                error_log("Executando query para ADMIN");
                
                $query = "
                    SELECT 
                        r.id,
                        r.versao,
                        r.nome_arquivo,
                        r.extensao,
                        r.publico,
                        r.aprovado_em,
                        t.titulo,
                        u.name as autor_nome,
                        u.email as autor_email,
                        GROUP_CONCAT(DISTINCT d.nome SEPARATOR ', ') as departamentos_permitidos
                    FROM fluxogramas_registros r
                    INNER JOIN fluxogramas_titulos t ON r.titulo_id = t.id
                    INNER JOIN users u ON r.criado_por = u.id
                    LEFT JOIN fluxogramas_registros_departamentos rd ON r.id = rd.registro_id
                    LEFT JOIN departamentos d ON rd.departamento_id = d.id
                    WHERE r.status = 'APROVADO'
                    GROUP BY r.id
                    ORDER BY r.aprovado_em DESC
                ";
                
                error_log("Query SQL: " . $query);
                $stmt = $this->db->query($query);
                error_log("Query executada com sucesso");
                
            } else {
                // USUÁRIO COMUM VÊ: PÚBLICO + DO SEU DEPARTAMENTO
                error_log("Executando query para USUÁRIO COMUM");
                
                // Buscar departamento do usuário
                $user_departamento_id = null;
                try {
                    $stmt = $this->db->prepare("SELECT department_id FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $user_departamento_id = $user_data['department_id'] ?? null;
                    error_log("Departamento do usuário: " . ($user_departamento_id ?? 'NULL'));
                } catch (\Exception $e) {
                    error_log("Aviso: Coluna de departamento não encontrada");
                }
                
                $query = "
                    SELECT DISTINCT
                        r.id,
                        r.versao,
                        r.nome_arquivo,
                        r.extensao,
                        r.publico,
                        r.aprovado_em,
                        t.titulo,
                        u.name as autor_nome,
                        u.email as autor_email,
                        GROUP_CONCAT(DISTINCT d.nome SEPARATOR ', ') as departamentos_permitidos
                    FROM fluxogramas_registros r
                    INNER JOIN fluxogramas_titulos t ON r.titulo_id = t.id
                    INNER JOIN users u ON r.criado_por = u.id
                    LEFT JOIN fluxogramas_registros_departamentos rd ON r.id = rd.registro_id
                    LEFT JOIN departamentos d ON rd.departamento_id = d.id
                    WHERE r.status = 'APROVADO'
                    AND (
                        r.publico = 1
                        OR (
                            r.publico = 0 
                            AND EXISTS (
                                SELECT 1 
                                FROM fluxogramas_registros_departamentos rd2 
                                WHERE rd2.registro_id = r.id 
                                AND rd2.departamento_id = ?
                            )
                        )
                    )
                    GROUP BY r.id
                    ORDER BY r.aprovado_em DESC
                ";
                
                $stmt = $this->db->prepare($query);
                $stmt->execute([$user_departamento_id]);
            }
            
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Total de registros retornados pela query: " . count($registros));
            
            if (count($registros) > 0) {
                error_log("Primeiro registro: " . json_encode($registros[0]));
            } else {
                error_log("ATENÇÃO: Query não retornou nenhum registro!");
            }
            
            echo json_encode(['success' => true, 'data' => $registros, 'debug' => [
                'total' => count($registros),
                'is_admin' => $isAdmin,
                'user_id' => $user_id
            ]]);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::listVisualizacao - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao listar registros: ' . $e->getMessage()]);
        }
    }
    
    public function visualizarArquivo($id)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(403);
                echo "Acesso negado";
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            // Buscar departamento do usuário (usar setor como departamento)
            $user_departamento_id = null;
            try {
                $stmt = $this->db->prepare("SELECT setor FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Tentar encontrar departamento pelo nome do setor
                if ($user_data && !empty($user_data['setor'])) {
                    $stmt = $this->db->prepare("SELECT id FROM departamentos WHERE nome = ?");
                    $stmt->execute([$user_data['setor']]);
                    $dept = $stmt->fetch(PDO::FETCH_ASSOC);
                    $user_departamento_id = $dept['id'] ?? null;
                }
            } catch (\Exception $e) {
                error_log("Erro ao buscar departamento do usuário: " . $e->getMessage());
            }
            
            // Buscar registro
            $stmt = $this->db->prepare("
                SELECT 
                    r.id,
                    r.arquivo,
                    r.nome_arquivo,
                    r.extensao,
                    r.publico,
                    r.status
                FROM fluxogramas_registros r
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$registro) {
                http_response_code(404);
                echo "Arquivo não encontrado";
                return;
            }
            
            // Verificar se está aprovado
            if ($registro['status'] != 'APROVADO') {
                http_response_code(403);
                echo "Arquivo não aprovado para visualização";
                return;
            }
            
            // Verificar permissões de visualização
            $tem_acesso = false;
            
            if ($isAdmin) {
                $tem_acesso = true; // Admin vê tudo
            } elseif ($registro['publico'] == 1) {
                $tem_acesso = true; // Público todos veem
            } else {
                // Verificar se o departamento do usuário tem acesso
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as tem_acesso
                    FROM fluxogramas_registros_departamentos
                    WHERE registro_id = ? AND departamento_id = ?
                ");
                $stmt->execute([$id, $user_departamento_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $tem_acesso = ($result['tem_acesso'] > 0);
            }
            
            if (!$tem_acesso) {
                http_response_code(403);
                echo "Você não tem permissão para visualizar este arquivo";
                return;
            }
            
            // Registrar log de visualização
            try {
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $stmt = $this->db->prepare("
                    INSERT INTO fluxogramas_logs_visualizacao 
                    (registro_id, usuario_id, user_agent) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$id, $user_id, $user_agent]);
            } catch (\Exception $e) {
                error_log("Erro ao registrar log: " . $e->getMessage());
            }
            
            // Exibir arquivo
            $mime_types = [
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ];
            
            $content_type = $mime_types[$registro['extensao']] ?? 'application/octet-stream';
            
            header('Content-Type: ' . $content_type);
            header('Content-Disposition: inline; filename="' . $registro['nome_arquivo'] . '"');
            header('Content-Length: ' . strlen($registro['arquivo']));
            header('X-Frame-Options: SAMEORIGIN');
            
            echo $registro['arquivo'];
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::visualizarArquivo - Erro: " . $e->getMessage());
            http_response_code(500);
            echo "Erro ao visualizar arquivo";
        }
    }
    
    public function getRegistro($id)
    {
        // Limpar qualquer output anterior
        if (ob_get_level()) ob_clean();
        
        header('Content-Type: application/json');
        
        try {
            error_log("=== getRegistro INICIADO ===");
            error_log("getRegistro - ID recebido: " . var_export($id, true));
            error_log("getRegistro - REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
            
            if (!isset($_SESSION['user_id'])) {
                error_log("getRegistro - Usuário não autenticado");
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                exit;
            }
            
            $user_id = $_SESSION['user_id'];
            $registro_id = (int)$id;
            
            error_log("getRegistro - User ID: {$user_id}, Registro ID: {$registro_id}");
            
            if ($registro_id <= 0) {
                error_log("getRegistro - ID inválido");
                echo json_encode(['success' => false, 'message' => 'ID do registro inválido']);
                exit;
            }
            
            if (!$this->db) {
                error_log("getRegistro - Database não inicializado");
                echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco']);
                exit;
            }
            
            error_log("getRegistro - Preparando query...");
            
            // Verificar se é admin
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            error_log("getRegistro - User é admin: " . ($isAdmin ? 'SIM' : 'NÃO'));
            
            // Buscar registro com departamentos permitidos
            // Admin pode ver todos, usuário comum apenas os próprios
            if ($isAdmin) {
                $stmt = $this->db->prepare("
                    SELECT 
                        r.*,
                        t.titulo,
                        GROUP_CONCAT(rd.departamento_id) as departamentos_ids
                    FROM fluxogramas_registros r
                    INNER JOIN fluxogramas_titulos t ON r.titulo_id = t.id
                    LEFT JOIN fluxogramas_registros_departamentos rd ON r.id = rd.registro_id
                    WHERE r.id = ?
                    GROUP BY r.id
                ");
                error_log("getRegistro - Executando query ADMIN com params: [{$registro_id}]");
                $stmt->execute([$registro_id]);
            } else {
                $stmt = $this->db->prepare("
                    SELECT 
                        r.*,
                        t.titulo,
                        GROUP_CONCAT(rd.departamento_id) as departamentos_ids
                    FROM fluxogramas_registros r
                    INNER JOIN fluxogramas_titulos t ON r.titulo_id = t.id
                    LEFT JOIN fluxogramas_registros_departamentos rd ON r.id = rd.registro_id
                    WHERE r.id = ? AND r.criado_por = ?
                    GROUP BY r.id
                ");
                error_log("getRegistro - Executando query USER com params: [{$registro_id}, {$user_id}]");
                $stmt->execute([$registro_id, $user_id]);
            }
            
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("getRegistro - Registro encontrado: " . ($registro ? 'SIM' : 'NÃO'));
            
            if (!$registro) {
                error_log("getRegistro - Registro não encontrado para user {$user_id}");
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado ou você não tem permissão']);
                exit;
            }
            
            error_log("getRegistro - Retornando sucesso");
            $result = json_encode(['success' => true, 'data' => $registro]);
            error_log("getRegistro - JSON gerado: " . substr($result, 0, 200) . "...");
            echo $result;
            exit;
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::getRegistro - EXCEÇÃO: " . $e->getMessage());
            error_log("FluxogramasController::getRegistro - Stack: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar registro: ' . $e->getMessage()]);
            exit;
        }
    }
    
    public function atualizarVisibilidade()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $registro_id = (int)($_POST['registro_id'] ?? 0);
            $publico = (int)($_POST['publico'] ?? 0);
            $departamentos = $_POST['departamentos'] ?? [];
            
            if ($registro_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID do registro inválido']);
                return;
            }
            
            // Verificar se é o criador do registro
            $stmt = $this->db->prepare("SELECT criado_por FROM fluxogramas_registros WHERE id = ?");
            $stmt->execute([$registro_id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$registro) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado']);
                return;
            }
            
            if ($registro['criado_por'] != $user_id && !\App\Services\PermissionService::isAdmin($user_id)) {
                echo json_encode(['success' => false, 'message' => 'Você não tem permissão para editar este registro']);
                return;
            }
            
            // Atualizar visibilidade SEM alterar o status
            $stmt = $this->db->prepare("
                UPDATE fluxogramas_registros 
                SET publico = ?,
                    atualizado_em = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$publico, $registro_id]);
            
            // Atualizar departamentos se for restrito
            if ($publico == 0) {
                // Remover departamentos antigos
                $stmt = $this->db->prepare("DELETE FROM fluxogramas_registros_departamentos WHERE registro_id = ?");
                $stmt->execute([$registro_id]);
                
                // Adicionar novos departamentos
                if (!empty($departamentos)) {
                    $stmt = $this->db->prepare("
                        INSERT INTO fluxogramas_registros_departamentos (registro_id, departamento_id)
                        VALUES (?, ?)
                    ");
                    
                    foreach ($departamentos as $dept_id) {
                        $stmt->execute([$registro_id, (int)$dept_id]);
                    }
                }
            } else {
                // Se público, remover todas as restrições de departamento
                $stmt = $this->db->prepare("DELETE FROM fluxogramas_registros_departamentos WHERE registro_id = ?");
                $stmt->execute([$registro_id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Visibilidade atualizada com sucesso']);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::atualizarVisibilidade - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar visibilidade: ' . $e->getMessage()]);
        }
    }
    
    public function listLogs()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            if (!$isAdmin) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }
            
            // Parâmetros de filtro
            $search = $_GET['search'] ?? '';
            $data_inicio = $_GET['data_inicio'] ?? '';
            $data_fim = $_GET['data_fim'] ?? '';
            
            $query = "
                SELECT 
                    l.id,
                    l.visualizado_em,
                    u.name as usuario_nome,
                    u.email as usuario_email,
                    t.titulo,
                    r.versao,
                    r.nome_arquivo
                FROM fluxogramas_logs_visualizacao l
                INNER JOIN users u ON l.usuario_id = u.id
                INNER JOIN fluxogramas_registros r ON l.registro_id = r.id
                INNER JOIN fluxogramas_titulos t ON r.titulo_id = t.id
                WHERE 1=1
            ";
            
            $params = [];
            
            if (!empty($search)) {
                $query .= " AND (u.name LIKE ? OR t.titulo LIKE ? OR r.nome_arquivo LIKE ?)";
                $search_term = '%' . $search . '%';
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
            }
            
            if (!empty($data_inicio)) {
                $query .= " AND DATE(l.visualizado_em) >= ?";
                $params[] = $data_inicio;
            }
            
            if (!empty($data_fim)) {
                $query .= " AND DATE(l.visualizado_em) <= ?";
                $params[] = $data_fim;
            }
            
            $query .= " ORDER BY l.visualizado_em DESC LIMIT 500";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $logs]);
            
        } catch (\Exception $e) {
            error_log("FluxogramasController::listLogs - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao listar logs']);
        }
    }
}
