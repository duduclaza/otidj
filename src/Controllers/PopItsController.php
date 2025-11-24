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
            $isSuperAdmin = \App\Services\PermissionService::isSuperAdmin($user_id);
            
            // Verificar permissões específicas para cada aba
            $canViewCadastroTitulos = \App\Services\PermissionService::hasPermission($user_id, 'pops_its_cadastro_titulos', 'view');
            $canViewMeusRegistros = \App\Services\PermissionService::hasPermission($user_id, 'pops_its_meus_registros', 'view');
            $canViewPendenteAprovacao = $isAdmin || $isSuperAdmin || \App\Services\PermissionService::hasPermission($user_id, 'pops_its_pendente_aprovacao', 'view');
            $canViewVisualizacao = \App\Services\PermissionService::hasPermission($user_id, 'pops_its_visualizacao', 'view');
            $canViewLogsVisualizacao = $isAdmin || $isSuperAdmin; // Admin ou super admin podem ver logs
            
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
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            $isSuperAdmin = \App\Services\PermissionService::isSuperAdmin($user_id);
            
            if (!$isAdmin && !$isSuperAdmin) {
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
            $allowedTypes = [
                'application/pdf',
                'image/png',
                'image/jpeg',
                'image/jpg',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ];
            
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use PDF, PNG, JPEG ou PPT/PPTX']);
                return;
            }
            
            // Validar tamanho - PPT/PPTX: 50MB, Outros: 10MB
            $isPowerPoint = in_array($file['type'], [
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ]);
            
            $maxSize = $isPowerPoint ? 50 * 1024 * 1024 : 10 * 1024 * 1024;
            $maxSizeText = $isPowerPoint ? '50MB' : '10MB';
            
            if ($file['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => "Arquivo muito grande. Máximo {$maxSizeText} para este tipo"]);
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
            
            $registro_id = $this->db->lastInsertId();
            
            // Se não for público, salvar departamentos permitidos
            if ($visibilidade === 'departamentos') {
                $departamentos_permitidos = $_POST['departamentos_permitidos'] ?? [];
                
                if (empty($departamentos_permitidos)) {
                    echo json_encode(['success' => false, 'message' => 'Selecione pelo menos um departamento para visibilidade restrita']);
                    return;
                }
                
                // Criar tabela se não existir
                $this->criarTabelaDepartamentosSeNaoExistir();
                
                // Inserir departamentos permitidos
                $stmt_dept = $this->db->prepare("
                    INSERT INTO pops_its_registros_departamentos (registro_id, departamento_id) 
                    VALUES (?, ?)
                ");
                
                foreach ($departamentos_permitidos as $dept_id) {
                    $dept_id = (int)$dept_id;
                    if ($dept_id > 0) {
                        $stmt_dept->execute([$registro_id, $dept_id]);
                        error_log("DEPARTAMENTO SALVO: Registro $registro_id -> Departamento $dept_id");
                    }
                }
            }
            
            // Buscar informações do título para notificação
            $stmt_titulo = $this->db->prepare("SELECT titulo, tipo FROM pops_its_titulos WHERE id = ?");
            $stmt_titulo->execute([$titulo_id]);
            $titulo_info = $stmt_titulo->fetch(\PDO::FETCH_ASSOC);
            
            // Notificar administradores sobre novo registro pendente
            error_log("========================================");
            error_log("🔔 INICIANDO PROCESSO DE NOTIFICAÇÃO");
            error_log("Tipo: Novo {$titulo_info['tipo']}");
            error_log("Título: {$titulo_info['titulo']}");
            error_log("Versão: v{$proxima_versao}");
            error_log("Registro ID: {$registro_id}");
            error_log("========================================");
            
            $notificacao_enviada = $this->notificarAdministradores(
                "📋 Novo " . $titulo_info['tipo'] . " Pendente",
                "Um novo registro '{$titulo_info['titulo']}' v{$proxima_versao} foi criado e aguarda aprovação.",
                "pops_its_pendente",
                "pops_its_registro",
                $registro_id
            );
            
            error_log("========================================");
            error_log("🔔 RESULTADO FINAL DA NOTIFICAÇÃO: " . ($notificacao_enviada ? '✅ SUCESSO' : '❌ FALHA'));
            error_log("========================================");
            
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
            
            // NOVA LÓGICA SIMPLES: Notificar sobre registros muito recentes (últimos 2 minutos)
            $this->notificarRegistrosRecentes($registros);
            
            echo json_encode(['success' => true, 'data' => $registros]);
            
        } catch (\Exception $e) {
            error_log("PopItsController::listPendentesAprovacao - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar pendências: ' . $e->getMessage()]);
        }
    }

    // Método simples para notificar sobre registros recentes
    private function notificarRegistrosRecentes($registros)
    {
        try {
            error_log("🔍 VERIFICANDO REGISTROS RECENTES...");
            
            foreach ($registros as $registro) {
                // Verificar se foi criado nos últimos 2 minutos
                $criado_em = strtotime($registro['criado_em']);
                $agora = time();
                $diferenca_minutos = ($agora - $criado_em) / 60;
                
                if ($diferenca_minutos <= 2) {
                    error_log("📋 REGISTRO RECENTE ENCONTRADO: {$registro['titulo']} (criado há " . round($diferenca_minutos, 1) . " min)");
                    
                    // Verificar se já foi notificado
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) FROM notifications 
                        WHERE related_type = 'pops_its_registro' 
                        AND related_id = ? 
                        AND type = 'pops_its_pendente'
                        AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                    ");
                    $stmt->execute([$registro['id']]);
                    $ja_notificado = $stmt->fetchColumn() > 0;
                    
                    if (!$ja_notificado) {
                        // Criar notificação simples
                        $titulo = "🔔 Novo {$registro['tipo']} Pendente";
                        $mensagem = "'{$registro['titulo']}' v{$registro['versao']} por {$registro['autor_nome']} aguarda aprovação.";
                        
                        error_log("📤 ENVIANDO NOTIFICAÇÃO: $titulo");
                        
                        $sucesso = $this->notificarAdministradores(
                            $titulo,
                            $mensagem,
                            'pops_its_pendente',
                            'pops_its_registro',
                            $registro['id']
                        );
                        
                        if ($sucesso) {
                            error_log("✅ NOTIFICAÇÃO ENVIADA COM SUCESSO para registro {$registro['id']}");
                        } else {
                            error_log("❌ FALHA ao enviar notificação para registro {$registro['id']}");
                        }
                    } else {
                        error_log("⏭️ REGISTRO {$registro['id']} já foi notificado recentemente");
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("❌ ERRO ao verificar registros recentes: " . $e->getMessage());
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
            
            // Buscar informações do registro para notificação
            $stmt_info = $this->db->prepare("
                SELECT r.criado_por, r.versao, t.titulo, t.tipo 
                FROM pops_its_registros r 
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id 
                WHERE r.id = ?
            ");
            $stmt_info->execute([$registro_id]);
            $registro_info = $stmt_info->fetch(\PDO::FETCH_ASSOC);
            
            // Notificar o autor sobre aprovação
            if ($registro_info) {
                $this->criarNotificacao(
                    $registro_info['criado_por'],
                    "✅ " . $registro_info['tipo'] . " Aprovado!",
                    "Seu registro '{$registro_info['titulo']}' v{$registro_info['versao']} foi aprovado e está disponível para visualização.",
                    "pops_its_aprovado",
                    "pops_its_registro",
                    $registro_id
                );
                
                // Enviar email para o criador
                try {
                    $stmt_user = $this->db->prepare("SELECT email FROM users WHERE id = ?");
                    $stmt_user->execute([$registro_info['criado_por']]);
                    $user_email = $stmt_user->fetchColumn();
                    
                    if ($user_email) {
                        error_log("📧 Enviando email de aprovação para: $user_email");
                        $emailService = new \App\Services\EmailService();
                        $emailEnviado = $emailService->sendPopItsAprovadoNotification(
                            $user_email,
                            $registro_info['tipo'],
                            $registro_info['titulo'],
                            $registro_info['versao'],
                            $registro_id
                        );
                        
                        if ($emailEnviado) {
                            error_log("✅ Email de aprovação enviado com sucesso");
                        }
                    }
                } catch (\Exception $e) {
                    error_log("⚠️ Erro ao enviar email de aprovação: " . $e->getMessage());
                }
            }
            
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
            
            // Buscar informações do registro para notificação
            $stmt_info = $this->db->prepare("
                SELECT r.criado_por, r.versao, t.titulo, t.tipo 
                FROM pops_its_registros r 
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id 
                WHERE r.id = ?
            ");
            $stmt_info->execute([$registro_id]);
            $registro_info = $stmt_info->fetch(\PDO::FETCH_ASSOC);
            
            // Notificar o autor sobre reprovação
            if ($registro_info) {
                $this->criarNotificacao(
                    $registro_info['criado_por'],
                    "❌ " . $registro_info['tipo'] . " Reprovado",
                    "Seu registro '{$registro_info['titulo']}' v{$registro_info['versao']} foi reprovado. Motivo: {$observacao}",
                    "pops_its_reprovado",
                    "pops_its_registro",
                    $registro_id
                );
                
                // Enviar email para o criador
                try {
                    $stmt_user = $this->db->prepare("SELECT email FROM users WHERE id = ?");
                    $stmt_user->execute([$registro_info['criado_por']]);
                    $user_email = $stmt_user->fetchColumn();
                    
                    if ($user_email) {
                        error_log("📧 Enviando email de reprovação para: $user_email");
                        $emailService = new \App\Services\EmailService();
                        $emailEnviado = $emailService->sendPopItsReprovadoNotification(
                            $user_email,
                            $registro_info['tipo'],
                            $registro_info['titulo'],
                            $registro_info['versao'],
                            $observacao,
                            $registro_id
                        );
                        
                        if ($emailEnviado) {
                            error_log("✅ Email de reprovação enviado com sucesso");
                        }
                    }
                } catch (\Exception $e) {
                    error_log("⚠️ Erro ao enviar email de reprovação: " . $e->getMessage());
                }
            }
            
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
            
            // Verificar se as tabelas existem, se não, criar
            $this->criarTabelaDepartamentosSeNaoExistir();
            
            // Verificar se é admin - admin vê tudo
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            if ($isAdmin) {
                // Admin vê todos os registros aprovados com departamentos permitidos
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
                        ua.name as aprovado_por_nome,
                        GROUP_CONCAT(d.nome ORDER BY d.nome SEPARATOR ', ') as departamentos_permitidos
                    FROM pops_its_registros r
                    LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                    LEFT JOIN users u ON r.criado_por = u.id
                    LEFT JOIN users ua ON r.aprovado_por = ua.id
                    LEFT JOIN pops_its_registros_departamentos rd ON r.id = rd.registro_id
                    LEFT JOIN departamentos d ON rd.departamento_id = d.id
                    WHERE r.status = 'APROVADO'
                    AND r.versao = (
                        SELECT MAX(r2.versao) 
                        FROM pops_its_registros r2 
                        WHERE r2.titulo_id = r.titulo_id 
                        AND r2.status = 'APROVADO'
                    )
                    GROUP BY r.id, r.versao, r.nome_arquivo, r.extensao, r.tamanho_arquivo, 
                             r.publico, r.criado_em, r.aprovado_em, t.titulo, t.tipo, 
                             u.name, ua.name
                    ORDER BY r.aprovado_em DESC
                ");
                
                $stmt->execute();
            } else {
                // NOVA LÓGICA: Buscar setor do usuário diretamente
                $user_setor = $this->getUserSetor($user_id);
                error_log("NOVA LÓGICA - Usuário $user_id -> Setor: '$user_setor'");
                
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
                        ua.name as aprovado_por_nome,
                        GROUP_CONCAT(d.nome ORDER BY d.nome SEPARATOR ', ') as departamentos_permitidos
                    FROM pops_its_registros r
                    LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                    LEFT JOIN users u ON r.criado_por = u.id
                    LEFT JOIN users ua ON r.aprovado_por = ua.id
                    LEFT JOIN pops_its_registros_departamentos rd ON r.id = rd.registro_id
                    LEFT JOIN departamentos d ON rd.departamento_id = d.id
                    WHERE r.status = 'APROVADO'
                    AND r.versao = (
                        SELECT MAX(r2.versao) 
                        FROM pops_its_registros r2 
                        WHERE r2.titulo_id = r.titulo_id 
                        AND r2.status = 'APROVADO'
                    )
                    AND (
                        r.publico = 1 
                        OR r.criado_por = ?
                        OR EXISTS (
                            SELECT 1 FROM pops_its_registros_departamentos rd3
                            INNER JOIN departamentos d3 ON rd3.departamento_id = d3.id
                            WHERE rd3.registro_id = r.id 
                            AND (
                                LOWER(TRIM(d3.nome)) = LOWER(TRIM(?))
                                OR d3.nome LIKE CONCAT('%', ?, '%')
                                OR ? LIKE CONCAT('%', d3.nome, '%')
                            )
                        )
                    )
                    GROUP BY r.id, r.versao, r.nome_arquivo, r.extensao, r.tamanho_arquivo, 
                             r.publico, r.criado_em, r.aprovado_em, t.titulo, t.tipo, 
                             u.name, ua.name
                    ORDER BY r.aprovado_em DESC
                ");
                
                error_log("NOVA QUERY PARAMS: user_id=$user_id, user_setor='$user_setor'");
                
                // Debug: testar se o registro 4 tem acesso para o setor RH
                if ($user_setor) {
                    $debug_stmt = $this->db->prepare("
                        SELECT r.id, r.publico, r.criado_por, t.titulo,
                               EXISTS (
                                   SELECT 1 FROM pops_its_registros_departamentos rd3
                                   INNER JOIN departamentos d3 ON rd3.departamento_id = d3.id
                                   WHERE rd3.registro_id = r.id 
                                   AND (
                                       LOWER(TRIM(d3.nome)) = LOWER(TRIM(?))
                                       OR d3.nome LIKE CONCAT('%', ?, '%')
                                       OR ? LIKE CONCAT('%', d3.nome, '%')
                                   )
                               ) as tem_acesso_setor
                        FROM pops_its_registros r
                        LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                        WHERE r.status = 'APROVADO' AND r.id = 4
                    ");
                    $debug_stmt->execute([$user_setor, $user_setor, $user_setor]);
                    $debug_result = $debug_stmt->fetch(\PDO::FETCH_ASSOC);
                    error_log("DEBUG REGISTRO 4 - NOVA LÓGICA: " . json_encode($debug_result));
                }
                
                $stmt->execute([$user_id, $user_setor, $user_setor, $user_setor]);
            }
            
            $registros = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Debug: Log para verificar dados
            error_log("VISUALIZAÇÃO - Total registros: " . count($registros));
            foreach ($registros as $reg) {
                error_log("REGISTRO: {$reg['titulo']} - Público: {$reg['publico']} - Departamentos: " . ($reg['departamentos_permitidos'] ?? 'NULL'));
                
                // Debug adicional: verificar se existem departamentos na tabela de relacionamento
                if (!$reg['publico'] && empty($reg['departamentos_permitidos'])) {
                    $debugStmt = $this->db->prepare("
                        SELECT rd.registro_id, rd.departamento_id, d.nome 
                        FROM pops_its_registros_departamentos rd 
                        LEFT JOIN departamentos d ON rd.departamento_id = d.id 
                        WHERE rd.registro_id = ?
                    ");
                    $debugStmt->execute([$reg['id']]);
                    $depts = $debugStmt->fetchAll(\PDO::FETCH_ASSOC);
                    error_log("DEBUG DEPARTAMENTOS para registro {$reg['id']}: " . json_encode($depts));
                }
            }
            
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
            
            // Verificar se é PDF, imagem ou PowerPoint
            $extensao = strtolower($registro['extensao']);
            $tiposPermitidos = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'ppt', 'pptx'];
            
            if (!in_array($extensao, $tiposPermitidos)) {
                http_response_code(403);
                echo "Tipo de arquivo não suportado para visualização";
                return;
            }
            
            // Verificar se é imagem para criar wrapper HTML
            $tiposImagem = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'];
            $tiposPowerPoint = ['ppt', 'pptx'];
            $isImagem = in_array($extensao, $tiposImagem);
            $isPowerPoint = in_array($extensao, $tiposPowerPoint);
            
            if ($isImagem) {
                // Para imagens, criar um HTML wrapper para melhor exibição
                header('Content-Type: text/html; charset=utf-8');
                header('X-Frame-Options: SAMEORIGIN');
                header('Cache-Control: private, no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                header('X-Content-Type-Options: nosniff');
                header('Referrer-Policy: no-referrer');
                
                // Criar base64 da imagem
                $base64 = base64_encode($registro['arquivo']);
                $content_type = $this->getContentType($registro['extensao']);
                
                echo $this->gerarHtmlImagem($base64, $content_type, $registro['nome_arquivo']);
            } else if ($isPowerPoint) {
                // Para PowerPoint, servir com tipo correto para visualizadores online
                $content_type = $this->getContentType($registro['extensao']);
                header('Content-Type: ' . $content_type);
                header('Content-Disposition: inline; filename="' . $registro['nome_arquivo'] . '"');
                header('Content-Length: ' . $registro['tamanho_arquivo']);
                header('Access-Control-Allow-Origin: *'); // Permitir acesso dos visualizadores online
                header('Cache-Control: public, max-age=3600'); // Cache de 1 hora para visualizadores
                header('X-Content-Type-Options: nosniff');
                
                echo $registro['arquivo'];
            } else {
                // Para PDFs, servir diretamente
                $content_type = $this->getContentType($registro['extensao']);
                header('Content-Type: ' . $content_type);
                header('Content-Disposition: inline; filename="' . $registro['nome_arquivo'] . '"');
                header('Content-Length: ' . $registro['tamanho_arquivo']);
                header('X-Frame-Options: SAMEORIGIN');
                header('Cache-Control: private, no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                header('X-Content-Type-Options: nosniff');
                header('Referrer-Policy: no-referrer');
                
                echo $registro['arquivo'];
            }
            
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

    // Criar tabela de departamentos se não existir
    private function criarTabelaDepartamentosSeNaoExistir()
    {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS pops_its_registros_departamentos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    registro_id INT NOT NULL,
                    departamento_id INT NOT NULL,
                    
                    INDEX idx_registro_id (registro_id),
                    INDEX idx_departamento_id (departamento_id),
                    UNIQUE KEY uniq_registro_departamento (registro_id, departamento_id),
                    
                    FOREIGN KEY (registro_id) REFERENCES pops_its_registros(id) ON DELETE CASCADE ON UPDATE CASCADE,
                    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $this->db->exec($sql);
            
        } catch (\Exception $e) {
            error_log("Erro ao criar tabela de departamentos: " . $e->getMessage());
        }
    }

    // Método auxiliar para obter setor do usuário (NOVA LÓGICA)
    private function getUserSetor($user_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT setor, name FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $setor = $result['setor'] ?? null;
            error_log("SETOR DO USUÁRIO: {$result['name']} (ID: $user_id) -> Setor: '$setor'");
            
            return $setor;
        } catch (\Exception $e) {
            error_log("Erro ao obter setor do usuário: " . $e->getMessage());
            return null;
        }
    }

    // Método auxiliar para obter departamento do usuário (MÉTODO ANTIGO)
    private function getUserDepartmentId($user_id)
    {
        try {
            // Buscar o setor do usuário e encontrar o departamento correspondente
            // Usando comparação case-insensitive e flexível
            $stmt = $this->db->prepare("
                SELECT u.setor, u.name, d.id as departamento_id 
                FROM users u 
                LEFT JOIN departamentos d ON (
                    LOWER(TRIM(u.setor)) = LOWER(TRIM(d.nome))
                    OR d.nome LIKE CONCAT('%', u.setor, '%')
                    OR u.setor LIKE CONCAT('%', d.nome, '%')
                )
                WHERE u.id = ?
            ");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $dept_id = $result['departamento_id'] ?? null;
            $setor = $result['setor'] ?? 'N/A';
            error_log("SETOR DO USUÁRIO: {$result['name']} (ID: $user_id) -> Setor: '$setor' -> Departamento ID: $dept_id");
            
            // Debug adicional: verificar se o departamento existe
            if (!$dept_id && $setor !== 'N/A') {
                $stmt2 = $this->db->prepare("SELECT id, nome FROM departamentos WHERE nome = ?");
                $stmt2->execute([$setor]);
                $dept_result = $stmt2->fetch(\PDO::FETCH_ASSOC);
                error_log("BUSCA DEPARTAMENTO '$setor': " . json_encode($dept_result));
                
                // Se encontrou o departamento, retornar o ID
                if ($dept_result) {
                    $dept_id = $dept_result['id'];
                    error_log("DEPARTAMENTO ENCONTRADO: '$setor' -> ID: $dept_id");
                }
            }
            
            return $dept_id;
        } catch (\Exception $e) {
            error_log("Erro ao obter departamento do usuário: " . $e->getMessage());
            return null;
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
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml'
        ];
        
        return $types[strtolower($extensao)] ?? 'application/octet-stream';
    }

    // Gerar HTML otimizado para exibição de imagens
    private function gerarHtmlImagem($base64, $content_type, $nome_arquivo)
    {
        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualização Protegida - ' . htmlspecialchars($nome_arquivo) . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            overflow: hidden;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            
            /* Proteções contra seleção e interação */
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            -webkit-touch-callout: none;
            -webkit-tap-highlight-color: transparent;
        }
        
        .container {
            max-width: 100%;
            max-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .image-wrapper {
            position: relative;
            max-width: 100%;
            max-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background: white;
            padding: 10px;
        }
        
        img {
            max-width: 100%;
            max-height: calc(100vh - 100px);
            object-fit: contain;
            border-radius: 4px;
            
            /* Proteções específicas para imagem */
            pointer-events: none;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            -webkit-user-drag: none;
            -khtml-user-drag: none;
            -moz-user-drag: none;
            -o-user-drag: none;
            user-drag: none;
        }
        
        .watermark {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            z-index: 10;
        }
        
        /* Proteções adicionais */
        ::selection {
            background: transparent;
        }
        
        ::-moz-selection {
            background: transparent;
        }
    </style>
</head>
<body oncontextmenu="return false;" ondragstart="return false;" onselectstart="return false;">
    <div class="container">
        <div class="image-wrapper">
            <div class="watermark">🔒 PROTEGIDO</div>
            <img src="data:' . $content_type . ';base64,' . $base64 . '" 
                 alt="' . htmlspecialchars($nome_arquivo) . '"
                 oncontextmenu="return false;"
                 ondragstart="return false;"
                 onselectstart="return false;"
                 onmousedown="return false;">
        </div>
    </div>
    
    <script>
        // Proteções JavaScript
        document.addEventListener("keydown", function(e) {
            // Bloquear Ctrl+S, Ctrl+P, Ctrl+A, F12, Print Screen
            if ((e.ctrlKey && (e.key === "s" || e.key === "p" || e.key === "a")) || 
                e.key === "F12" || e.key === "PrintScreen" ||
                (e.ctrlKey && e.shiftKey && e.key === "I") ||
                (e.ctrlKey && e.key === "u")) {
                e.preventDefault();
                return false;
            }
        });
        
        // Bloquear menu de contexto
        document.addEventListener("contextmenu", function(e) {
            e.preventDefault();
            return false;
        });
        
        // Bloquear seleção
        document.addEventListener("selectstart", function(e) {
            e.preventDefault();
            return false;
        });
        
        // Bloquear arrastar
        document.addEventListener("dragstart", function(e) {
            e.preventDefault();
            return false;
        });
        
        // Bloquear print
        window.addEventListener("beforeprint", function(e) {
            e.preventDefault();
            return false;
        });
        
        // Detectar tentativas de DevTools
        let devtools = {open: false, orientation: null};
        setInterval(function() {
            if (window.outerHeight - window.innerHeight > 200 || 
                window.outerWidth - window.innerWidth > 200) {
                if (!devtools.open) {
                    devtools.open = true;
                    console.clear();
                    console.log("%c🔒 ACESSO NEGADO", "color: red; font-size: 20px; font-weight: bold;");
                    console.log("%cEste conteúdo é protegido por direitos autorais.", "color: red; font-size: 14px;");
                }
            } else {
                devtools.open = false;
            }
        }, 500);
        
        // Limpar console periodicamente
        setInterval(function() {
            console.clear();
        }, 1000);
    </script>
</body>
</html>';
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
            
            // Verificar se é admin ou super admin
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            $isSuperAdmin = \App\Services\PermissionService::isSuperAdmin($user_id);
            
            if (!$isAdmin && !$isSuperAdmin) {
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

    // Diagnóstico rápido de permissões
    public function diagnosticoPermissoes()
    {
        header('Content-Type: application/json');
        
        try {
            $user_id = $_SESSION['user_id'] ?? null;
            
            if (!$user_id) {
                echo json_encode(['success' => false, 'message' => 'Usuário não logado']);
                return;
            }
            
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            $isSuperAdmin = \App\Services\PermissionService::isSuperAdmin($user_id);
            
            // Buscar informações do usuário
            $stmt = $this->db->prepare("
                SELECT u.name, u.email, p.name as profile_name 
                FROM users u 
                LEFT JOIN profiles p ON u.profile_id = p.id 
                WHERE u.id = ?
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'user_id' => $user_id,
                'user_name' => $user['name'] ?? 'N/A',
                'user_email' => $user['email'] ?? 'N/A',
                'profile_name' => $user['profile_name'] ?? 'N/A',
                'is_admin' => $isAdmin,
                'is_super_admin' => $isSuperAdmin,
                'can_view_logs' => $isAdmin,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
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

    // Editar registro reprovado
    public function editarRegistro()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $registro_id = (int)($_POST['registro_id'] ?? 0);
            
            if ($registro_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID do registro é obrigatório']);
                return;
            }
            
            // Verificar se o registro existe, está reprovado e pertence ao usuário
            $stmt = $this->db->prepare("
                SELECT r.id, r.status, r.criado_por, r.titulo_id, r.versao, r.publico
                FROM pops_its_registros r 
                WHERE r.id = ? AND r.status = 'REPROVADO' AND r.criado_por = ?
            ");
            $stmt->execute([$registro_id, $user_id]);
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$registro) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado, não está reprovado ou não pertence a você']);
                return;
            }
            
            // Validar novo arquivo
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Novo arquivo é obrigatório']);
                return;
            }
            
            $file = $_FILES['arquivo'];
            
            // Validar tipo de arquivo
            $allowedTypes = [
                'application/pdf',
                'image/png',
                'image/jpeg',
                'image/jpg',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use PDF, PNG, JPEG ou PPT/PPTX']);
                return;
            }
            
            // Validar tamanho - PPT/PPTX: 50MB, Outros: 10MB
            $isPowerPoint = in_array($file['type'], [
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ]);
            
            $maxSize = $isPowerPoint ? 50 * 1024 * 1024 : 10 * 1024 * 1024;
            $maxSizeText = $isPowerPoint ? '50MB' : '10MB';
            
            if ($file['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => "Arquivo muito grande. Máximo {$maxSizeText} para este tipo"]);
                return;
            }
            
            $novo_arquivo = file_get_contents($file['tmp_name']);
            $nome_arquivo = $file['name'];
            $extensao = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
            $tamanho_arquivo = $file['size'];
            
            // Atualizar registro
            $stmt = $this->db->prepare("
                UPDATE pops_its_registros 
                SET arquivo = ?, nome_arquivo = ?, extensao = ?, tamanho_arquivo = ?, 
                    status = 'PENDENTE', observacao_reprovacao = NULL
                WHERE id = ?
            ");
            $stmt->execute([$novo_arquivo, $nome_arquivo, $extensao, $tamanho_arquivo, $registro_id]);
            
            // Buscar informações do título para notificação
            $stmt_titulo = $this->db->prepare("
                SELECT t.titulo, t.tipo 
                FROM pops_its_registros r
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                WHERE r.id = ?
            ");
            $stmt_titulo->execute([$registro_id]);
            $titulo_info = $stmt_titulo->fetch(\PDO::FETCH_ASSOC);
            
            // Notificar administradores sobre registro reeditado
            $this->notificarAdministradores(
                "📝 " . $titulo_info['tipo'] . " Reeditado",
                "O registro '{$titulo_info['titulo']}' v{$registro['versao']} foi reeditado após reprovação e aguarda nova aprovação.",
                "pops_its_pendente",
                "pops_its_registro",
                $registro_id
            );
            
            echo json_encode(['success' => true, 'message' => 'Registro reeditado com sucesso! Aguarda nova aprovação.']);
            
        } catch (\Exception $e) {
            error_log("PopItsController::editarRegistro - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    // ===== SISTEMA DE NOTIFICAÇÕES =====

    // Criar notificação para usuários
    private function criarNotificacao($user_id, $titulo, $mensagem, $tipo, $related_type = null, $related_id = null)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, title, message, type, related_type, related_id) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $titulo, $mensagem, $tipo, $related_type, $related_id]);
            
            error_log("NOTIFICAÇÃO CRIADA: $titulo para usuário $user_id");
            return true;
        } catch (\Exception $e) {
            error_log("Erro ao criar notificação: " . $e->getMessage());
            return false;
        }
    }

    // Notificar administradores COM PERMISSÃO de aprovar POPs e ITs + ENVIAR EMAIL
    private function notificarAdministradores($titulo, $mensagem, $tipo, $related_type = null, $related_id = null)
    {
        try {
            error_log("┌─────────────────────────────────────────────────────────┐");
            error_log("│ 🔔 SISTEMA DE NOTIFICAÇÕES POPs e ITs                   │");
            error_log("└─────────────────────────────────────────────────────────┘");
            error_log("📋 Título: $titulo");
            error_log("💬 Mensagem: $mensagem");
            error_log("🏷️  Tipo: $tipo");
            error_log("🔗 Related Type: " . ($related_type ?? 'N/A'));
            error_log("🔗 Related ID: " . ($related_id ?? 'N/A'));
            error_log("");
            
            // Buscar administradores com permissão específica para aprovar POPs e ITs
            $admins = [];
            
            // Verificar se coluna pode_aprovar_pops_its existe
            error_log("🔍 Verificando se coluna pode_aprovar_pops_its existe...");
            $hasColumn = false;
            try {
                $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_pops_its'");
                $hasColumn = $checkColumn->rowCount() > 0;
                error_log($hasColumn ? "✅ Coluna existe!" : "❌ Coluna NÃO existe!");
            } catch (\Exception $e) {
                error_log("❌ ERRO ao verificar coluna: " . $e->getMessage());
            }
            
            if ($hasColumn) {
                // Buscar apenas admins com permissão específica
                error_log("🔍 Buscando administradores com pode_aprovar_pops_its = 1...");
                $stmt = $this->db->prepare("
                    SELECT id, name, email, pode_aprovar_pops_its, status
                    FROM users 
                    WHERE role = 'admin' 
                    AND pode_aprovar_pops_its = 1
                    AND status = 'active'
                ");
                $stmt->execute();
                $admins = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                error_log("✅ ADMINS COM PERMISSÃO ENCONTRADOS: " . count($admins));
                
                foreach ($admins as $admin) {
                    error_log("   👤 {$admin['name']} (ID: {$admin['id']}, Email: {$admin['email']})");
                }
            } else {
                // Fallback: buscar todos os admins se coluna não existir
                error_log("⚠️ Coluna não existe - buscando TODOS administradores ativos...");
                $stmt = $this->db->prepare("SELECT id, name, email, status FROM users WHERE role = 'admin' AND status = 'active'");
                $stmt->execute();
                $admins = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                error_log("⚠️ TODOS ADMINS ATIVOS: " . count($admins));
            }
            
            if (empty($admins)) {
                error_log("❌ PROBLEMA CRÍTICO: NENHUM ADMINISTRADOR ENCONTRADO!");
                error_log("❌ Possíveis causas:");
                error_log("   1. Nenhum admin com pode_aprovar_pops_its = 1");
                error_log("   2. Todos admins estão inativos");
                error_log("   3. Erro na consulta SQL");
                return false;
            }
            
            // Criar notificações no sistema para cada admin
            $notificacoes_criadas = 0;
            $emails = [];
            
            foreach ($admins as $admin) {
                error_log("--- CRIANDO NOTIFICAÇÃO PARA {$admin['name']} (ID: {$admin['id']}) ---");
                
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO notifications (user_id, title, message, type, related_type, related_id) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $resultado = $stmt->execute([$admin['id'], $titulo, $mensagem, $tipo, $related_type, $related_id]);
                    
                    if ($resultado) {
                        $notificacoes_criadas++;
                        if (!empty($admin['email'])) {
                            $emails[] = $admin['email'];
                        }
                        error_log("✅ NOTIFICAÇÃO CRIADA COM SUCESSO para {$admin['name']}");
                    }
                } catch (\Exception $e) {
                    error_log("❌ ERRO ao criar notificação para {$admin['name']}: " . $e->getMessage());
                }
            }
            
            // Enviar EMAIL para todos os admins com permissão
            if (!empty($emails)) {
                try {
                    error_log("📧 ENVIANDO EMAIL PARA " . count($emails) . " ADMINISTRADORES");
                    
                    $emailService = new \App\Services\EmailService();
                    $emailEnviado = $emailService->sendPopItsPendenteNotification(
                        $emails,
                        $titulo,
                        $mensagem,
                        $related_id
                    );
                    
                    if ($emailEnviado) {
                        error_log("✅ EMAIL ENVIADO COM SUCESSO PARA ADMINS");
                    } else {
                        error_log("⚠️ FALHA AO ENVIAR EMAIL (não crítico)");
                    }
                } catch (\Exception $e) {
                    error_log("⚠️ ERRO AO ENVIAR EMAIL: " . $e->getMessage());
                }
            }
            
            error_log("=== RESULTADO FINAL ===");
            error_log("NOTIFICAÇÕES CRIADAS: $notificacoes_criadas de " . count($admins));
            error_log("EMAILS ENVIADOS: " . count($emails));
            error_log("=== FIM NOTIFICAÇÃO ADMINS ===");
            
            return $notificacoes_criadas > 0;
            
        } catch (\Exception $e) {
            error_log("❌ ERRO GERAL ao notificar administradores: " . $e->getMessage());
            error_log("STACK TRACE: " . $e->getTraceAsString());
            return false;
        }
    }

    // Método de teste para notificações - FOCO EM ADMINISTRADORES
    public function testeNotificacoes()
    {
        header('Content-Type: application/json');
        
        try {
            $user_id = $_SESSION['user_id'] ?? null;
            
            // 1. Verificar administradores por múltiplas estratégias
            $admins_perfil = [];
            $admins_flag = [];
            $erro_perfil = null;
            $erro_flag = null;
            
            // Estratégia 1: Por perfil "Administrador"
            try {
                $stmt = $this->db->prepare("
                    SELECT DISTINCT u.id, u.name, u.email, 'perfil' as fonte
                    FROM users u
                    INNER JOIN user_profiles up ON u.id = up.user_id
                    INNER JOIN profiles p ON up.profile_id = p.id
                    WHERE p.name = 'Administrador'
                ");
                $stmt->execute();
                $admins_perfil = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $erro_perfil = $e->getMessage();
            }
            
            // Estratégia 2: Por campo is_admin
            try {
                $stmt = $this->db->prepare("
                    SELECT id, name, email, 'flag' as fonte
                    FROM users 
                    WHERE is_admin = 1
                ");
                $stmt->execute();
                $admins_flag = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $erro_flag = $e->getMessage();
            }
            
            // 2. Verificar estrutura das tabelas
            $tabelas_existem = [];
            $tabelas_check = ['users', 'profiles', 'user_profiles', 'notifications'];
            
            foreach ($tabelas_check as $tabela) {
                $stmt = $this->db->query("SHOW TABLES LIKE '$tabela'");
                $tabelas_existem[$tabela] = $stmt->fetch() !== false;
            }
            
            // 3. Verificar perfil "Administrador"
            $perfil_admin = null;
            if ($tabelas_existem['profiles']) {
                $stmt = $this->db->prepare("SELECT * FROM profiles WHERE name = 'Administrador'");
                $stmt->execute();
                $perfil_admin = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
            
            // 4. Teste manual de criação de notificação
            $teste_manual = false;
            if ($tabelas_existem['notifications'] && $user_id) {
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO notifications (user_id, title, message, type) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $user_id,
                        "🧪 Teste Manual Admin",
                        "Teste criado em " . date('Y-m-d H:i:s') . " para verificar notificações",
                        "info"
                    ]);
                    $teste_manual = true;
                } catch (\Exception $e) {
                    $teste_manual = "ERRO: " . $e->getMessage();
                }
            }
            
            echo json_encode([
                'success' => true,
                'tabelas_existem' => $tabelas_existem,
                'admins_por_perfil' => [
                    'count' => count($admins_perfil),
                    'dados' => $admins_perfil,
                    'erro' => $erro_perfil
                ],
                'admins_por_flag' => [
                    'count' => count($admins_flag),
                    'dados' => $admins_flag,
                    'erro' => $erro_flag
                ],
                'perfil_administrador' => $perfil_admin,
                'teste_manual_notificacao' => $teste_manual,
                'user_id_atual' => $user_id,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Teste manual de notificação - MÉTODO SUPER SIMPLES
    public function testeNotificacaoManual()
    {
        header('Content-Type: application/json');
        
        try {
            $user_id = $_SESSION['user_id'] ?? null;
            
            if (!$user_id) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            error_log("🧪 === TESTE MANUAL DE NOTIFICAÇÃO INICIADO ===");
            error_log("👤 Usuário testando: ID $user_id");
            
            // 1. Testar inserção direta na tabela notifications
            $titulo_teste = "🧪 TESTE MANUAL - " . date('H:i:s');
            $mensagem_teste = "Esta é uma notificação de teste criada manualmente em " . date('Y-m-d H:i:s');
            
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO notifications (user_id, title, message, type, related_type, related_id) 
                    VALUES (?, ?, ?, 'info', 'teste', 999)
                ");
                $resultado = $stmt->execute([$user_id, $titulo_teste, $mensagem_teste]);
                
                if ($resultado) {
                    error_log("✅ NOTIFICAÇÃO TESTE CRIADA COM SUCESSO");
                    
                    // 2. Testar busca de administradores
                    $stmt_admins = $this->db->prepare("SELECT id, name, email FROM users WHERE is_admin = 1");
                    $stmt_admins->execute();
                    $admins = $stmt_admins->fetchAll(\PDO::FETCH_ASSOC);
                    
                    error_log("👥 ADMINISTRADORES ENCONTRADOS: " . count($admins));
                    foreach ($admins as $admin) {
                        error_log("   - {$admin['name']} (ID: {$admin['id']})");
                    }
                    
                    // 3. Criar notificação para cada admin
                    $notificacoes_admin = 0;
                    foreach ($admins as $admin) {
                        $stmt_admin = $this->db->prepare("
                            INSERT INTO notifications (user_id, title, message, type) 
                            VALUES (?, ?, ?, 'pops_its_pendente')
                        ");
                        $resultado_admin = $stmt_admin->execute([
                            $admin['id'], 
                            "🔔 Teste para Admin", 
                            "Notificação de teste para {$admin['name']} às " . date('H:i:s')
                        ]);
                        
                        if ($resultado_admin) {
                            $notificacoes_admin++;
                            error_log("✅ Notificação criada para {$admin['name']}");
                        } else {
                            error_log("❌ Falha ao criar notificação para {$admin['name']}");
                        }
                    }
                    
                    error_log("🧪 === TESTE MANUAL CONCLUÍDO ===");
                    
                    echo json_encode([
                        'success' => true,
                        'message' => "Teste concluído!\n\n" .
                                   "✅ Notificação pessoal criada\n" .
                                   "👥 {$notificacoes_admin} notificações para admins\n" .
                                   "📊 Total admins: " . count($admins) . "\n\n" .
                                   "Verifique o sininho agora!"
                    ]);
                    
                } else {
                    error_log("❌ FALHA ao criar notificação teste");
                    echo json_encode(['success' => false, 'message' => 'Falha ao criar notificação teste']);
                }
                
            } catch (\Exception $e) {
                error_log("❌ ERRO SQL: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erro SQL: ' . $e->getMessage()]);
            }
            
        } catch (\Exception $e) {
            error_log("❌ ERRO GERAL: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ===== SISTEMA DE SOLICITAÇÕES DE EXCLUSÃO =====

    // Criar tabela de solicitações se não existir
    private function criarTabelaSolicitacoesSeNaoExistir()
    {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS pops_its_solicitacoes_exclusao (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    registro_id INT NOT NULL,
                    solicitante_id INT NOT NULL,
                    motivo TEXT NOT NULL,
                    status ENUM('PENDENTE', 'APROVADA', 'REPROVADA') DEFAULT 'PENDENTE',
                    avaliado_por INT NULL,
                    avaliado_em TIMESTAMP NULL,
                    observacoes_avaliacao TEXT NULL,
                    solicitado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    
                    FOREIGN KEY (registro_id) REFERENCES pops_its_registros(id) ON DELETE CASCADE,
                    FOREIGN KEY (solicitante_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (avaliado_por) REFERENCES users(id) ON DELETE SET NULL,
                    
                    INDEX idx_registro_id (registro_id),
                    INDEX idx_solicitante_id (solicitante_id),
                    INDEX idx_status (status),
                    INDEX idx_solicitado_em (solicitado_em)
                )
            ";
            $this->db->exec($sql);
            
        } catch (\Exception $e) {
            error_log("Erro ao criar tabela de solicitações: " . $e->getMessage());
        }
    }

    // Criar solicitação de exclusão
    public function createSolicitacao()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            // Verificar se a tabela existe, se não, criar
            $this->criarTabelaSolicitacoesSeNaoExistir();
            
            $user_id = $_SESSION['user_id'];
            $registro_id = (int)($_POST['registro_id'] ?? 0);
            $motivo = trim($_POST['motivo'] ?? '');
            
            if ($registro_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID do registro é obrigatório']);
                return;
            }
            
            if (empty($motivo)) {
                echo json_encode(['success' => false, 'message' => 'Motivo da exclusão é obrigatório']);
                return;
            }
            
            // Verificar se o registro existe e pertence ao usuário
            $stmt = $this->db->prepare("
                SELECT r.id, r.criado_por, t.titulo, r.nome_arquivo 
                FROM pops_its_registros r 
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                WHERE r.id = ?
            ");
            $stmt->execute([$registro_id]);
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$registro) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado']);
                return;
            }
            
            if ($registro['criado_por'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Você só pode solicitar exclusão de seus próprios registros']);
                return;
            }
            
            // Verificar se já existe solicitação pendente para este registro
            $stmt = $this->db->prepare("
                SELECT id FROM pops_its_solicitacoes_exclusao 
                WHERE registro_id = ? AND status = 'PENDENTE'
            ");
            $stmt->execute([$registro_id]);
            
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Já existe uma solicitação de exclusão pendente para este registro']);
                return;
            }
            
            // Criar a solicitação
            $stmt = $this->db->prepare("
                INSERT INTO pops_its_solicitacoes_exclusao 
                (registro_id, solicitante_id, motivo) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$registro_id, $user_id, $motivo]);
            
            $solicitacao_id = $this->db->lastInsertId();
            
            // Log da ação
            error_log("SOLICITAÇÃO DE EXCLUSÃO: Usuário $user_id solicitou exclusão do registro $registro_id (Protocolo: #$solicitacao_id)");
            
            // Notificar administradores sobre nova solicitação de exclusão
            $this->notificarAdministradores(
                "🗑️ Nova Solicitação de Exclusão",
                "Solicitação #$solicitacao_id para exclusão do registro '{$registro['titulo']}' aguarda aprovação. Motivo: $motivo",
                "pops_its_exclusao_pendente",
                "pops_its_solicitacao",
                $solicitacao_id
            );
            
            echo json_encode([
                'success' => true, 
                'message' => 'Solicitação de exclusão criada com sucesso',
                'solicitacao_id' => $solicitacao_id
            ]);
            
        } catch (\Exception $e) {
            error_log("PopItsController::createSolicitacao - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    // Listar solicitações de exclusão (para Aba 3 - Pendente Aprovação)
    public function listSolicitacoes()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            
            // Verificar se tem permissão para aprovar (admin ou permissão específica)
            if (!\App\Services\PermissionService::hasPermission($user_id, 'pops_its_pendente_aprovacao', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar solicitações']);
                return;
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    s.id,
                    s.registro_id,
                    s.motivo,
                    s.status,
                    s.solicitado_em,
                    s.avaliado_em,
                    s.observacoes_avaliacao,
                    u.name as solicitante_nome,
                    u.email as solicitante_email,
                    t.titulo,
                    t.tipo,
                    r.nome_arquivo,
                    r.versao,
                    ua.name as avaliado_por_nome
                FROM pops_its_solicitacoes_exclusao s
                LEFT JOIN users u ON s.solicitante_id = u.id
                LEFT JOIN pops_its_registros r ON s.registro_id = r.id
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                LEFT JOIN users ua ON s.avaliado_por = ua.id
                ORDER BY s.solicitado_em DESC
            ");
            
            $stmt->execute();
            $solicitacoes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $solicitacoes]);
            
        } catch (\Exception $e) {
            error_log("PopItsController::listSolicitacoes - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar solicitações: ' . $e->getMessage()]);
        }
    }

    // Aprovar solicitação de exclusão
    public function aprovarSolicitacao()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $solicitacao_id = (int)($_POST['solicitacao_id'] ?? 0);
            $observacoes = trim($_POST['observacoes'] ?? '');
            
            // Verificar permissão
            if (!\App\Services\PermissionService::hasPermission($user_id, 'pops_its_pendente_aprovacao', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para aprovar solicitações']);
                return;
            }
            
            if ($solicitacao_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID da solicitação é obrigatório']);
                return;
            }
            
            // Buscar a solicitação
            $stmt = $this->db->prepare("
                SELECT s.*, r.nome_arquivo, t.titulo 
                FROM pops_its_solicitacoes_exclusao s
                LEFT JOIN pops_its_registros r ON s.registro_id = r.id
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                WHERE s.id = ? AND s.status = 'PENDENTE'
            ");
            $stmt->execute([$solicitacao_id]);
            $solicitacao = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitacao) {
                echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada ou já processada']);
                return;
            }
            
            // Iniciar transação
            $this->db->beginTransaction();
            
            try {
                // Atualizar status da solicitação
                $stmt = $this->db->prepare("
                    UPDATE pops_its_solicitacoes_exclusao 
                    SET status = 'APROVADA', avaliado_por = ?, avaliado_em = NOW(), observacoes_avaliacao = ?
                    WHERE id = ?
                ");
                $stmt->execute([$user_id, $observacoes, $solicitacao_id]);
                
                // Excluir o registro
                $stmt = $this->db->prepare("DELETE FROM pops_its_registros WHERE id = ?");
                $stmt->execute([$solicitacao['registro_id']]);
                
                $this->db->commit();
                
                // Log da ação
                error_log("EXCLUSÃO APROVADA: Usuário $user_id aprovou exclusão do registro {$solicitacao['registro_id']} (Protocolo: #$solicitacao_id)");
                
                // Notificar o solicitante sobre aprovação da exclusão
                $this->criarNotificacao(
                    $solicitacao['solicitante_id'],
                    "✅ Solicitação de Exclusão Aprovada",
                    "Sua solicitação #$solicitacao_id para exclusão do registro '{$solicitacao['titulo']}' foi aprovada e o registro foi removido do sistema.",
                    "pops_its_exclusao_aprovada",
                    "pops_its_solicitacao",
                    $solicitacao_id
                );
                
                // Enviar email para o solicitante
                try {
                    $stmt_user = $this->db->prepare("SELECT email FROM users WHERE id = ?");
                    $stmt_user->execute([$solicitacao['solicitante_id']]);
                    $user_email = $stmt_user->fetchColumn();
                    
                    if ($user_email) {
                        error_log("📧 Enviando email de exclusão aprovada para: $user_email");
                        $emailService = new \App\Services\EmailService();
                        $emailEnviado = $emailService->sendExclusaoAprovadaNotification(
                            $user_email,
                            $solicitacao['titulo'],
                            $solicitacao_id,
                            $observacoes
                        );
                        
                        if ($emailEnviado) {
                            error_log("✅ Email de exclusão aprovada enviado com sucesso");
                        }
                    }
                } catch (\Exception $e) {
                    error_log("⚠️ Erro ao enviar email: " . $e->getMessage());
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => "Solicitação aprovada e registro '{$solicitacao['titulo']}' excluído com sucesso"
                ]);
                
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            error_log("PopItsController::aprovarSolicitacao - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    // Reprovar solicitação de exclusão
    public function reprovarSolicitacao()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $solicitacao_id = (int)($_POST['solicitacao_id'] ?? 0);
            $observacoes = trim($_POST['observacoes'] ?? '');
            
            // Verificar permissão
            if (!\App\Services\PermissionService::hasPermission($user_id, 'pops_its_pendente_aprovacao', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para reprovar solicitações']);
                return;
            }
            
            if ($solicitacao_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID da solicitação é obrigatório']);
                return;
            }
            
            if (empty($observacoes)) {
                echo json_encode(['success' => false, 'message' => 'Observações são obrigatórias para reprovação']);
                return;
            }
            
            // Buscar a solicitação
            $stmt = $this->db->prepare("
                SELECT s.*, u.name as solicitante_nome, t.titulo 
                FROM pops_its_solicitacoes_exclusao s
                LEFT JOIN users u ON s.solicitante_id = u.id
                LEFT JOIN pops_its_registros r ON s.registro_id = r.id
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                WHERE s.id = ? AND s.status = 'PENDENTE'
            ");
            $stmt->execute([$solicitacao_id]);
            $solicitacao = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$solicitacao) {
                echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada ou já processada']);
                return;
            }
            
            // Atualizar status da solicitação
            $stmt = $this->db->prepare("
                UPDATE pops_its_solicitacoes_exclusao 
                SET status = 'REPROVADA', avaliado_por = ?, avaliado_em = NOW(), observacoes_avaliacao = ?
                WHERE id = ?
            ");
            $stmt->execute([$user_id, $observacoes, $solicitacao_id]);
            
            // Log da ação
            error_log("EXCLUSÃO REPROVADA: Usuário $user_id reprovou exclusão do registro {$solicitacao['registro_id']} (Protocolo: #$solicitacao_id)");
            
            // Notificar o solicitante sobre reprovação da exclusão
            $this->criarNotificacao(
                $solicitacao['solicitante_id'],
                "❌ Solicitação de Exclusão Reprovada",
                "Sua solicitação #$solicitacao_id para exclusão do registro '{$solicitacao['titulo']}' foi reprovada. Motivo: $observacoes",
                "pops_its_exclusao_reprovada",
                "pops_its_solicitacao",
                $solicitacao_id
            );
            
            // Enviar email para o solicitante
            try {
                $stmt_user = $this->db->prepare("SELECT email FROM users WHERE id = ?");
                $stmt_user->execute([$solicitacao['solicitante_id']]);
                $user_email = $stmt_user->fetchColumn();
                
                if ($user_email) {
                    error_log("📧 Enviando email de exclusão reprovada para: $user_email");
                    $emailService = new \App\Services\EmailService();
                    $emailEnviado = $emailService->sendExclusaoReprovadaNotification(
                        $user_email,
                        $solicitacao['titulo'],
                        $solicitacao_id,
                        $observacoes
                    );
                    
                    if ($emailEnviado) {
                        error_log("✅ Email de exclusão reprovada enviado com sucesso");
                    }
                }
            } catch (\Exception $e) {
                error_log("⚠️ Erro ao enviar email: " . $e->getMessage());
            }
            
            echo json_encode([
                'success' => true, 
                'message' => "Solicitação reprovada. O solicitante ({$solicitacao['solicitante_nome']}) será notificado."
            ]);
            
        } catch (\Exception $e) {
            error_log("PopItsController::reprovarSolicitacao - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    // Registrar log de visualização via AJAX
    public function registrarLog()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                return;
            }
            
            $registro_id = $_POST['registro_id'] ?? null;
            $user_id = $_SESSION['user_id'];
            
            if (!$registro_id) {
                echo json_encode(['success' => false, 'message' => 'ID do registro não fornecido']);
                return;
            }
            
            // Registrar log
            $this->registrarLogVisualizacao($registro_id, $user_id);
            
            echo json_encode(['success' => true, 'message' => 'Log registrado com sucesso']);
            
        } catch (\Exception $e) {
            error_log("PopItsController::registrarLog - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar log']);
        }
    }

}
