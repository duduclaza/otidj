<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class PopItsController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal com abas
    public function index()
    {
        try {
            // Verificar permissões para cada aba
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            // Verificar permissões específicas para cada aba
            $canViewCadastroTitulos = \App\Services\PermissionService::hasPermission($user_id, 'pops_its_cadastro_titulos', 'view');
            $canViewMeusRegistros = \App\Services\PermissionService::hasPermission($user_id, 'pops_its_meus_registros', 'view');
            $canViewPendenteAprovacao = $isAdmin; // Apenas admin pode ver pendente aprovação
            $canViewVisualizacao = \App\Services\PermissionService::hasPermission($user_id, 'pops_its_visualizacao', 'view');
            $canViewLogsVisualizacao = $isAdmin; // Apenas admin pode ver logs
            
            // Carregar departamentos para o formulário
            $departamentos = $this->getDepartamentos();
            
            // Usar o layout padrão com TailwindCSS
            $title = 'POPs e ITs - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/pops-its/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            // Logar erro para diagnóstico
            try {
                $logDir = __DIR__ . '/../../logs';
                if (!is_dir($logDir)) { @mkdir($logDir, 0777, true); }
                $msg = date('Y-m-d H:i:s') . ' POPs-ITs index ERRO: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine() . "\n";
                file_put_contents($logDir . '/pops_its_debug.log', $msg, FILE_APPEND);
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


    // ===== ABA 1: CADASTRO DE TÍTULOS =====
    // Método createTitulo() implementado no final do arquivo

    // ===== MÉTODOS IMPLEMENTADOS NO FINAL DO ARQUIVO =====
    // createTitulo(), listTitulos(), searchTitulos(), deleteTitulo()
    // createRegistro(), listMeusRegistros(), downloadArquivo()
    // Outros métodos auxiliares

    private function getNextVersion($titulo_id): string
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(MAX(CAST(SUBSTRING(versao, 2) AS UNSIGNED)), 0) + 1 as next_version
            FROM pops_its_registros 
            WHERE titulo_id = ?
        ");
        $stmt->execute([$titulo_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return 'v' . $result['next_version'];
    }

    // ===== MÉTODOS IMPLEMENTADOS CORRETAMENTE =====

    private function getDepartamentos(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM departamentos ORDER BY nome");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Se tabela não existe, retorna array vazio
            return [];
        }
    }

    // ===== MÉTODOS IMPLEMENTADOS CORRETAMENTE NO FINAL =====

    // Criar título
    public function createTitulo()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            if (!\App\Services\PermissionService::hasPermission($user_id, 'pops_its_cadastro_titulos', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para criar títulos']);
                return;
            }
            
            // Verificar se a tabela existe
            try {
                $stmt = $this->db->query("SHOW TABLES LIKE 'pops_its_titulos'");
                if (!$stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Tabela pops_its_titulos não existe. Execute o script SQL primeiro.']);
                    return;
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erro ao verificar tabela: ' . $e->getMessage()]);
                return;
            }
            
            // Validar dados
            $tipo = $_POST['tipo'] ?? '';
            $titulo = trim($_POST['titulo'] ?? '');
            $departamento_id = $_POST['departamento_id'] ?? '';
            
            if (empty($tipo) || empty($titulo) || empty($departamento_id)) {
                echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
                return;
            }
            
            if (!in_array($tipo, ['POP', 'IT'])) {
                echo json_encode(['success' => false, 'message' => 'Tipo inválido']);
                return;
            }
            
            // Normalizar título para verificação de duplicidade
            $titulo_normalizado = $this->normalizarTitulo($titulo);
            
            // Verificar se já existe
            $stmt = $this->db->prepare("SELECT id FROM pops_its_titulos WHERE tipo = ? AND titulo_normalizado = ?");
            $stmt->execute([$tipo, $titulo_normalizado]);
            
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Já existe um ' . $tipo . ' com este título']);
                return;
            }
            
            // Inserir no banco
            $stmt = $this->db->prepare("
                INSERT INTO pops_its_titulos (tipo, titulo, titulo_normalizado, departamento_id, criado_por) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$tipo, $titulo, $titulo_normalizado, $departamento_id, $user_id]);
            
            echo json_encode(['success' => true, 'message' => 'Título cadastrado com sucesso!']);
            
        } catch (\Exception $e) {
            // Log detalhado do erro
            error_log("PopItsController::createTitulo - Erro: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }
    
    private function normalizarTitulo($titulo)
    {
        $titulo = mb_strtolower($titulo, 'UTF-8');
        $titulo = preg_replace('/\s+/', ' ', $titulo);
        return trim($titulo);
    }

    // Listar títulos
    public function listTitulos()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar se a tabela existe
            $stmt = $this->db->query("SHOW TABLES LIKE 'pops_its_titulos'");
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Tabela pops_its_titulos não existe']);
                return;
            }
            
            // Buscar todos os títulos
            $stmt = $this->db->query("
                SELECT 
                    t.id,
                    t.tipo,
                    t.titulo,
                    t.criado_em,
                    d.nome as departamento_nome,
                    u.name as criador_nome
                FROM pops_its_titulos t
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                LEFT JOIN users u ON t.criado_por = u.id
                ORDER BY t.criado_em DESC
            ");
            
            $titulos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $titulos]);
            
        } catch (\Exception $e) {
            error_log("PopIts listTitulos ERRO: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar títulos: ' . $e->getMessage()]);
        }
    }

    // Buscar títulos para autocomplete
    public function searchTitulos()
    {
        header('Content-Type: application/json');
        
        try {
            $query = $_GET['q'] ?? '';
            $tipo = $_GET['tipo'] ?? '';
            
            if (strlen($query) < 2) {
                echo json_encode(['success' => true, 'data' => []]);
                return;
            }
            
            $sql = "SELECT DISTINCT titulo, tipo FROM pops_its_titulos WHERE titulo LIKE ?";
            $params = ['%' . $query . '%'];
            
            if (!empty($tipo)) {
                $sql .= " AND tipo = ?";
                $params[] = $tipo;
            }
            
            $sql .= " ORDER BY titulo LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $resultados]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro na busca: ' . $e->getMessage()]);
        }
    }

    // Excluir título (apenas admin)
    public function deleteTitulo()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            if (!\App\Services\PermissionService::isAdmin($user_id)) {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores podem excluir títulos']);
                return;
            }
            
            $titulo_id = (int)($_POST['titulo_id'] ?? 0);
            
            if ($titulo_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID do título é obrigatório']);
                return;
            }
            
            // Verificar se existem registros vinculados a este título
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM pops_its_registros WHERE titulo_id = ?");
            $stmt->execute([$titulo_id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totalRegistros = $result['total'];
            
            if ($totalRegistros > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => "❌ Não é possível excluir este título!\n\nExistem {$totalRegistros} registro(s) vinculado(s) a este título.\n\nPara excluir o título, primeiro exclua todos os registros relacionados."
                ]);
                return;
            }
            
            // Buscar informações do título para log
            $stmt = $this->db->prepare("SELECT titulo, tipo FROM pops_its_titulos WHERE id = ?");
            $stmt->execute([$titulo_id]);
            $titulo = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$titulo) {
                echo json_encode(['success' => false, 'message' => 'Título não encontrado']);
                return;
            }
            
            // Excluir o título
            $stmt = $this->db->prepare("DELETE FROM pops_its_titulos WHERE id = ?");
            $stmt->execute([$titulo_id]);
            
            if ($stmt->rowCount() > 0) {
                // Log da exclusão
                error_log("TÍTULO EXCLUÍDO: {$titulo['tipo']} - {$titulo['titulo']} (ID: {$titulo_id}) por usuário {$user_id}");
                echo json_encode(['success' => true, 'message' => "✅ Título '{$titulo['titulo']}' excluído com sucesso!"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro inesperado ao excluir o título']);
            }
            
        } catch (\Exception $e) {
            error_log("PopItsController::deleteTitulo - Erro: " . $e->getMessage());
            
            // Verificar se é erro de constraint de foreign key
            if (strpos($e->getMessage(), '1451') !== false || strpos($e->getMessage(), 'foreign key constraint') !== false) {
                echo json_encode([
                    'success' => false, 
                    'message' => "❌ Não é possível excluir este título!\n\nExistem registros vinculados a este título que impedem sua exclusão.\n\nPara excluir o título, primeiro exclua todos os registros relacionados."
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro inesperado ao excluir título. Tente novamente.']);
            }
        }
    }

    // Listar registros do usuário (Aba 2)
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
            $stmt = $this->db->query("SHOW TABLES LIKE 'pops_its_registros'");
            if (!$stmt->fetch()) {
                echo json_encode(['success' => true, 'data' => [], 'message' => 'Tabela pops_its_registros não existe ainda']);
                return;
            }
            
            // Buscar registros do usuário
            $stmt = $this->db->prepare("
                SELECT 
                    r.id,
                    r.versao,
                    r.nome_arquivo,
                    r.extensao,
                    r.tamanho_arquivo,
                    r.publico,
                    r.status,
                    r.criado_em,
                    r.observacao_reprovacao,
                    t.titulo,
                    t.tipo
                FROM pops_its_registros r
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                WHERE r.criado_por = ?
                ORDER BY r.criado_em DESC
            ");
            
            $stmt->execute([$user_id]);
            $registros = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $registros]);
            
        } catch (\Exception $e) {
            error_log("PopItsController::listMeusRegistros - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar registros: ' . $e->getMessage()]);
        }
    }

    // Criar registro (Aba 2)
    public function createRegistro()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            if (!\App\Services\PermissionService::hasPermission($user_id, 'pops_its_meus_registros', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para criar registros']);
                return;
            }
            
            // Validar dados básicos
            $titulo_id = (int)($_POST['titulo_id'] ?? 0);
            $visibilidade = $_POST['visibilidade'] ?? '';
            
            if ($titulo_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Título é obrigatório']);
                return;
            }
            
            if (!in_array($visibilidade, ['publico', 'departamentos'])) {
                echo json_encode(['success' => false, 'message' => 'Visibilidade inválida']);
                return;
            }
            
            // Validar arquivo
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Arquivo é obrigatório']);
                return;
            }
            
            $file = $_FILES['arquivo'];
            
            // Validar tipo de arquivo
            $allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use PDF, PNG ou JPEG']);
                return;
            }
            
            // Validar tamanho (10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 10MB']);
                return;
            }
            
            // Determinar próxima versão
            $stmt = $this->db->prepare("SELECT MAX(versao) as max_versao FROM pops_its_registros WHERE titulo_id = ?");
            $stmt->execute([$titulo_id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $proxima_versao = ($result['max_versao'] ?? 0) + 1;
            
            // Ler arquivo
            $arquivo_conteudo = file_get_contents($file['tmp_name']);
            $nome_arquivo = $file['name'];
            $extensao = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
            $tamanho_arquivo = $file['size'];
            
            // Inserir registro
            $stmt = $this->db->prepare("
                INSERT INTO pops_its_registros 
                (titulo_id, versao, arquivo, nome_arquivo, extensao, tamanho_arquivo, publico, criado_por, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDENTE')
            ");
            
            $publico = ($visibilidade === 'publico') ? 1 : 0;
            $stmt->execute([
                $titulo_id, $proxima_versao, $arquivo_conteudo, $nome_arquivo, 
                $extensao, $tamanho_arquivo, $publico, $user_id
            ]);
            
            echo json_encode(['success' => true, 'message' => "Registro criado com sucesso! Versão v{$proxima_versao} está pendente de aprovação."]);
            
        } catch (\Exception $e) {
            error_log("PopItsController::createRegistro - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    // Download de arquivo
    public function downloadArquivo($id)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo "Acesso negado";
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $registro_id = (int)$id;
            
            // Buscar o registro
            $stmt = $this->db->prepare("
                SELECT r.*, t.titulo 
                FROM pops_its_registros r
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                WHERE r.id = ?
            ");
            $stmt->execute([$registro_id]);
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$registro) {
                http_response_code(404);
                echo "Arquivo não encontrado";
                return;
            }
            
            // Verificar permissões
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            $isOwner = ($registro['criado_por'] == $user_id);
            
            // Se não é admin nem dono, verificar se tem acesso
            if (!$isAdmin && !$isOwner) {
                // Se é público, pode acessar
                if (!$registro['publico']) {
                    http_response_code(403);
                    echo "Acesso negado a este arquivo";
                    return;
                }
            }
            
            // Definir headers para download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $registro['nome_arquivo'] . '"');
            header('Content-Length: ' . $registro['tamanho_arquivo']);
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            // Enviar o arquivo (usando o nome correto da coluna)
            echo $registro['arquivo'];
            
        } catch (\Exception $e) {
            error_log("PopItsController::downloadArquivo - Erro: " . $e->getMessage());
            http_response_code(500);
            echo "Erro interno do servidor";
        }
    }

    // Método de debug para verificar arquivos no banco
    public function debugArquivo($id)
    {
        try {
            $registro_id = (int)$id;
            
            // Buscar o registro
            $stmt = $this->db->prepare("
                SELECT id, nome_arquivo, tamanho_arquivo, extensao, 
                       LENGTH(arquivo) as tamanho_blob, publico, status
                FROM pops_its_registros 
                WHERE id = ?
            ");
            $stmt->execute([$registro_id]);
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            
            if (!$registro) {
                echo json_encode(['error' => 'Registro não encontrado', 'id' => $registro_id]);
                return;
            }
            
            echo json_encode([
                'success' => true,
                'registro' => $registro,
                'arquivo_existe' => !empty($registro['tamanho_blob']),
                'tamanho_original' => $registro['tamanho_arquivo'],
                'tamanho_blob' => $registro['tamanho_blob']
            ]);
            
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // ===== ABA 3: PENDENTE APROVAÇÃO =====

    // Listar registros pendentes de aprovação (apenas admins)
    public function listPendentesAprovacao()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            
            // Verificar se tem permissão para aprovar
            if (!\App\Services\PermissionService::hasPermission($user_id, 'pops_its_pendente_aprovacao', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar pendências']);
                return;
            }
            
            // Buscar registros pendentes
            $stmt = $this->db->prepare("
                SELECT 
                    r.id,
                    r.versao,
                    r.nome_arquivo,
                    r.extensao,
                    r.tamanho_arquivo,
                    r.publico,
                    r.criado_em,
                    t.titulo,
                    t.tipo,
                    u.name as autor_nome,
                    u.email as autor_email
                FROM pops_its_registros r
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                LEFT JOIN users u ON r.criado_por = u.id
                WHERE r.status = 'PENDENTE'
                ORDER BY r.criado_em ASC
            ");
            
            $stmt->execute();
            $registros = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $registros]);
            
        } catch (\Exception $e) {
            error_log("PopItsController::listPendentesAprovacao - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar pendências: ' . $e->getMessage()]);
        }
    }

    // Aprovar registro
    public function aprovarRegistro()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            
            // Verificar se tem permissão para aprovar
            if (!\App\Services\PermissionService::hasPermission($user_id, 'pops_its_pendente_aprovacao', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para aprovar registros']);
                return;
            }
            
            $registro_id = (int)($_POST['registro_id'] ?? 0);
            
            if ($registro_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID do registro é obrigatório']);
                return;
            }
            
            // Verificar se o registro existe e está pendente
            $stmt = $this->db->prepare("SELECT id, status FROM pops_its_registros WHERE id = ? AND status = 'PENDENTE'");
            $stmt->execute([$registro_id]);
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$registro) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado ou já processado']);
                return;
            }
            
            // Aprovar o registro
            $stmt = $this->db->prepare("
                UPDATE pops_its_registros 
                SET status = 'APROVADO', aprovado_por = ?, aprovado_em = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$user_id, $registro_id]);
            
            echo json_encode(['success' => true, 'message' => 'Registro aprovado com sucesso!']);
            
        } catch (\Exception $e) {
            error_log("PopItsController::aprovarRegistro - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao aprovar registro: ' . $e->getMessage()]);
        }
    }

    // Reprovar registro
    public function reprovarRegistro()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            
            // Verificar se tem permissão para reprovar
            if (!\App\Services\PermissionService::hasPermission($user_id, 'pops_its_pendente_aprovacao', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para reprovar registros']);
                return;
            }
            
            $registro_id = (int)($_POST['registro_id'] ?? 0);
            $observacao = trim($_POST['observacao'] ?? '');
            
            if ($registro_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID do registro é obrigatório']);
                return;
            }
            
            if (empty($observacao)) {
                echo json_encode(['success' => false, 'message' => 'Observação de reprovação é obrigatória']);
                return;
            }
            
            // Verificar se o registro existe e está pendente
            $stmt = $this->db->prepare("SELECT id, status FROM pops_its_registros WHERE id = ? AND status = 'PENDENTE'");
            $stmt->execute([$registro_id]);
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$registro) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado ou já processado']);
                return;
            }
            
            // Reprovar o registro
            $stmt = $this->db->prepare("
                UPDATE pops_its_registros 
                SET status = 'REPROVADO', observacao_reprovacao = ?, aprovado_por = ?, aprovado_em = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$observacao, $user_id, $registro_id]);
            
            echo json_encode(['success' => true, 'message' => 'Registro reprovado com sucesso!']);
            
        } catch (\Exception $e) {
            error_log("PopItsController::reprovarRegistro - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao reprovar registro: ' . $e->getMessage()]);
        }
    }

    // ===== ABA 4: VISUALIZAÇÃO =====

    // Listar registros aprovados para visualização
    public function listVisualizacao()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            
            // Verificar se é admin - admin vê tudo
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            if ($isAdmin) {
                // Admin vê todos os registros aprovados
                $stmt = $this->db->prepare("
                    SELECT 
                        r.id,
                        r.versao,
                        r.nome_arquivo,
                        r.extensao,
                        r.tamanho_arquivo,
                        r.publico,
                        r.criado_em,
                        r.aprovado_em,
                        t.titulo,
                        t.tipo,
                        u.name as autor_nome,
                        ua.name as aprovado_por_nome
                    FROM pops_its_registros r
                    LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                    LEFT JOIN users u ON r.criado_por = u.id
                    LEFT JOIN users ua ON r.aprovado_por = ua.id
                    WHERE r.status = 'APROVADO'
                    AND r.versao = (
                        SELECT MAX(r2.versao) 
                        FROM pops_its_registros r2 
                        WHERE r2.titulo_id = r.titulo_id 
                        AND r2.status = 'APROVADO'
                    )
                    ORDER BY r.aprovado_em DESC
                ");
                
                $stmt->execute();
            } else {
                // Usuário comum - controle de acesso por departamento
                $user_dept_id = $this->getUserDepartmentId($user_id);
                
                $stmt = $this->db->prepare("
                    SELECT 
                        r.id,
                        r.versao,
                        r.nome_arquivo,
                        r.extensao,
                        r.tamanho_arquivo,
                        r.publico,
                        r.criado_em,
                        r.aprovado_em,
                        t.titulo,
                        t.tipo,
                        u.name as autor_nome,
                        ua.name as aprovado_por_nome
                    FROM pops_its_registros r
                    LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                    LEFT JOIN users u ON r.criado_por = u.id
                    LEFT JOIN users ua ON r.aprovado_por = ua.id
                    LEFT JOIN pops_its_registros_departamentos rd ON r.id = rd.registro_id
                    WHERE r.status = 'APROVADO'
                    AND (
                        r.publico = 1 
                        OR rd.departamento_id = ?
                        OR r.criado_por = ?
                    )
                    AND r.versao = (
                        SELECT MAX(r2.versao) 
                        FROM pops_its_registros r2 
                        WHERE r2.titulo_id = r.titulo_id 
                        AND r2.status = 'APROVADO'
                    )
                    ORDER BY r.aprovado_em DESC
                ");
                
                $stmt->execute([$user_dept_id, $user_id]);
            }
            
            $registros = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $registros]);
            
        } catch (\Exception $e) {
            error_log("PopItsController::listVisualizacao - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar registros: ' . $e->getMessage()]);
        }
    }

    // Visualizar arquivo (PDF em iframe com log de segurança)
    public function visualizarArquivo($id)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo "Acesso negado";
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $registro_id = (int)$id;
            
            // Verificar se é admin - admin vê tudo
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            if ($isAdmin) {
                // Admin vê todos os registros aprovados
                $stmt = $this->db->prepare("
                    SELECT r.*, t.titulo 
                    FROM pops_its_registros r
                    LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                    WHERE r.id = ? AND r.status = 'APROVADO'
                ");
                $stmt->execute([$registro_id]);
            } else {
                // Usuário comum - controle de acesso
                $user_dept_id = $this->getUserDepartmentId($user_id);
                
                $stmt = $this->db->prepare("
                    SELECT r.*, t.titulo 
                    FROM pops_its_registros r
                    LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                    LEFT JOIN pops_its_registros_departamentos rd ON r.id = rd.registro_id
                    WHERE r.id = ? 
                    AND r.status = 'APROVADO'
                    AND (
                        r.publico = 1 
                        OR rd.departamento_id = ?
                        OR r.criado_por = ?
                    )
                ");
                $stmt->execute([$registro_id, $user_dept_id, $user_id]);
            }
            
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$registro) {
                http_response_code(404);
                echo "Arquivo não encontrado ou sem permissão";
                return;
            }
            
            // REGISTRAR LOG DE VISUALIZAÇÃO
            error_log("INICIANDO LOG: Usuário $user_id vai visualizar registro $registro_id");
            $this->registrarLogVisualizacao($registro_id, $user_id);
            error_log("LOG FINALIZADO para registro $registro_id");
            
            // Apenas PDFs podem ser visualizados em iframe por segurança
            if (strtolower($registro['extensao']) !== 'pdf') {
                http_response_code(403);
                echo "Apenas arquivos PDF podem ser visualizados por segurança";
                return;
            }
            
            // Headers para visualização segura em iframe
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $registro['nome_arquivo'] . '"');
            header('Content-Length: ' . $registro['tamanho_arquivo']);
            header('X-Frame-Options: SAMEORIGIN'); // Permite iframe apenas do mesmo domínio
            header('Cache-Control: private, no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Enviar o arquivo PDF
            echo $registro['arquivo'];
            
        } catch (\Exception $e) {
            error_log("PopItsController::visualizarArquivo - Erro: " . $e->getMessage());
            http_response_code(500);
            echo "Erro interno do servidor";
        }
    }

    // Registrar log de visualização
    private function registrarLogVisualizacao($registro_id, $user_id)
    {
        try {
            // Verificar se a tabela existe, se não, criar
            $this->criarTabelaLogsSeNaoExistir();
            
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt = $this->db->prepare("
                INSERT INTO pops_its_logs_visualizacao 
                (registro_id, usuario_id, user_agent, visualizado_em) 
                VALUES (?, ?, ?, NOW())
            ");
            $result = $stmt->execute([$registro_id, $user_id, $user_agent]);
            
            if ($result) {
                error_log("LOG REGISTRADO: Usuário $user_id visualizou registro $registro_id em " . date('Y-m-d H:i:s'));
            } else {
                error_log("ERRO: Falha ao registrar log de visualização");
            }
            
        } catch (\Exception $e) {
            error_log("ERRO ao registrar log de visualização: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            // Não falha a visualização se o log der erro
        }
    }

    // Criar tabela de logs se não existir
    private function criarTabelaLogsSeNaoExistir()
    {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS pops_its_logs_visualizacao (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    registro_id INT NOT NULL,
                    usuario_id INT NOT NULL,
                    visualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    user_agent TEXT NULL,
                    INDEX idx_registro_id (registro_id),
                    INDEX idx_usuario_id (usuario_id),
                    INDEX idx_visualizado_em (visualizado_em)
                )
            ";
            $this->db->exec($sql);
            
        } catch (\Exception $e) {
            error_log("Erro ao criar tabela de logs: " . $e->getMessage());
        }
    }

    // Método auxiliar para obter departamento do usuário
    private function getUserDepartmentId($user_id)
    {
        try {
            // Simplificado - assumindo que existe uma relação user->departamento
            // Ajustar conforme a estrutura real do banco
            $stmt = $this->db->prepare("SELECT departamento_id FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $result['departamento_id'] ?? 0;
        } catch (\Exception $e) {
            return 0; // Fallback
        }
    }

    // Método auxiliar para obter content-type correto
    private function getContentType($extensao)
    {
        $types = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];
        
        return $types[strtolower($extensao)] ?? 'application/octet-stream';
    }

    // ===== ABA 5: LOG DE VISUALIZAÇÕES =====

    // Listar logs de visualização (apenas admin)
    public function listLogsVisualizacao()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            
            // Verificar se é admin
            if (!\App\Services\PermissionService::isAdmin($user_id)) {
                echo json_encode(['success' => false, 'message' => 'Acesso restrito a administradores']);
                return;
            }
            
            // Filtros de busca
            $search = $_GET['search'] ?? '';
            $data_inicio = $_GET['data_inicio'] ?? '';
            $data_fim = $_GET['data_fim'] ?? '';
            
            $sql = "
                SELECT 
                    l.id,
                    l.visualizado_em,
                    u.name as usuario_nome,
                    u.email as usuario_email,
                    r.versao,
                    r.nome_arquivo,
                    t.titulo,
                    t.tipo
                FROM pops_its_logs_visualizacao l
                LEFT JOIN users u ON l.usuario_id = u.id
                LEFT JOIN pops_its_registros r ON l.registro_id = r.id
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                WHERE 1=1
            ";
            
            $params = [];
            
            // Filtro de busca
            if (!empty($search)) {
                $sql .= " AND (u.name LIKE ? OR t.titulo LIKE ? OR r.nome_arquivo LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            // Filtro de data início
            if (!empty($data_inicio)) {
                $sql .= " AND DATE(l.visualizado_em) >= ?";
                $params[] = $data_inicio;
            }
            
            // Filtro de data fim
            if (!empty($data_fim)) {
                $sql .= " AND DATE(l.visualizado_em) <= ?";
                $params[] = $data_fim;
            }
            
            $sql .= " ORDER BY l.visualizado_em DESC LIMIT 500";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $logs]);
            
        } catch (\Exception $e) {
            error_log("PopItsController::listLogsVisualizacao - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar logs: ' . $e->getMessage()]);
        }
    }

    // Método de teste para verificar logs
    public function testeLogs()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar se a tabela existe
            $stmt = $this->db->query("SHOW TABLES LIKE 'pops_its_logs_visualizacao'");
            $tabelaExiste = $stmt->fetch() !== false;
            
            // Contar registros na tabela
            $totalLogs = 0;
            if ($tabelaExiste) {
                $stmt = $this->db->query("SELECT COUNT(*) as total FROM pops_its_logs_visualizacao");
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                $totalLogs = $result['total'];
            }
            
            // Buscar últimos 5 logs
            $ultimosLogs = [];
            if ($tabelaExiste && $totalLogs > 0) {
                $stmt = $this->db->query("
                    SELECT l.*, u.name as usuario_nome, r.nome_arquivo, t.titulo
                    FROM pops_its_logs_visualizacao l
                    LEFT JOIN users u ON l.usuario_id = u.id
                    LEFT JOIN pops_its_registros r ON l.registro_id = r.id
                    LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                    ORDER BY l.visualizado_em DESC 
                    LIMIT 5
                ");
                $ultimosLogs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            
            echo json_encode([
                'success' => true,
                'tabela_existe' => $tabelaExiste,
                'total_logs' => $totalLogs,
                'ultimos_logs' => $ultimosLogs,
                'timestamp_atual' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

}
