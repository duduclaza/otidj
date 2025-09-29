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

    // ===== ABA 1: LISTAR TÍTULOS =====
    // Método listTitulos() implementado no final do arquivo

    // ===== ABA 2: MEUS REGISTROS =====

    public function createRegistro()
    {
        try {
            $titulo_id = (int)($_POST['titulo_id'] ?? 0);
            $visibilidade = $_POST['visibilidade'] ?? 'departamentos';
            $departamentos_permitidos = $_POST['departamentos_permitidos'] ?? [];
            $user_id = $_SESSION['user_id'];

            if ($titulo_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Título é obrigatório']);
                exit();
            }

            // Validar arquivo
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Arquivo é obrigatório']);
                exit();
            }

            $file = $_FILES['arquivo'];
            
            // Validar tipo de arquivo
            $allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use PDF, PNG, JPEG, JPG ou PPT']);
                exit();
            }

            // Validar tamanho (10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 10MB']);
                exit();
            }

            // Calcular próxima versão
            $versao = $this->getNextVersion($titulo_id);

            // Ler arquivo para BLOB
            $arquivo_blob = file_get_contents($file['tmp_name']);

            // Inserir registro
            $stmt = $this->db->prepare("
                INSERT INTO pops_its_registros 
                (titulo_id, versao, arquivo_conteudo, arquivo_nome, arquivo_tipo, arquivo_tamanho, visibilidade, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $titulo_id, $versao, $arquivo_blob, $file['name'], 
                $file['type'], $file['size'], $visibilidade, $user_id
            ]);

            $registro_id = $this->db->lastInsertId();

            // Se visibilidade for departamentos específicos, inserir departamentos permitidos
            if ($visibilidade === 'departamentos' && !empty($departamentos_permitidos)) {
                foreach ($departamentos_permitidos as $dept_id) {
                    $stmt = $this->db->prepare("
                        INSERT INTO pops_its_departamentos_permitidos (registro_id, departamento_id) 
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$registro_id, (int)$dept_id]);
                }
            }

            echo json_encode(['success' => true, 'message' => "Registro criado com sucesso! Versão: {$versao}"]);
            exit();

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar registro: ' . $e->getMessage()]);
            exit();
        }
    }

    public function listMeusRegistros()
    {
        // Iniciar sessão
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Limpar qualquer output anterior
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json');
        header('Cache-Control: no-cache');

        try {
            // Verificar se usuário está logado
            if (!isset($_SESSION['user_id'])) {
                $json = json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                header('Content-Length: ' . strlen($json));
                echo $json;
                return;
            }

            $user_id = $_SESSION['user_id'];

            // Teste simples primeiro
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM pops_its_registros WHERE created_by = ?");
            $stmt->execute([$user_id]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC);

            // Query simplificada para buscar registros
            $stmt = $this->db->prepare("
                SELECT id, titulo_id, versao, status, observacao_reprovacao,
                       arquivo_nome, arquivo_tipo, arquivo_tamanho,
                       created_at, updated_at, created_by
                FROM pops_its_registros
                WHERE created_by = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$user_id]);
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Adicionar informações do título para cada registro
            foreach ($registros as &$registro) {
                try {
                    $stmtTitulo = $this->db->prepare("
                        SELECT titulo, departamento_id
                        FROM pops_its_titulos
                        WHERE id = ?
                    ");
                    $stmtTitulo->execute([$registro['titulo_id']]);
                    $titulo = $stmtTitulo->fetch(PDO::FETCH_ASSOC);

                    if ($titulo) {
                        $registro['titulo'] = $titulo['titulo'];

                        // Buscar nome do departamento
                        $stmtDepto = $this->db->prepare("
                            SELECT nome FROM departamentos WHERE id = ?
                        ");
                        $stmtDepto->execute([$titulo['departamento_id']]);
                        $depto = $stmtDepto->fetch(PDO::FETCH_ASSOC);

                        $registro['departamento_nome'] = $depto ? $depto['nome'] : 'Departamento não encontrado';
                    } else {
                        $registro['titulo'] = 'Título não encontrado';
                        $registro['departamento_nome'] = 'Departamento não encontrado';
                    }
                } catch (\Exception $e) {
                    $registro['titulo'] = 'Erro ao carregar título';
                    $registro['departamento_nome'] = 'Erro ao carregar departamento';
                }
            }

            $json = json_encode([
                'success' => true,
                'data' => $registros,
                'count' => count($registros),
                'debug' => [
                    'user_id' => $user_id,
                    'total_registros' => $count['total']
                ]
            ]);

            header('Content-Length: ' . strlen($json));
            echo $json;
            return;

        } catch (\Exception $e) {
            $json = json_encode([
                'success' => false,
                'message' => 'Erro ao listar registros: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            header('Content-Length: ' . strlen($json));
            echo $json;
            return;
        }
    }

    public function testEndpoint()
    {
        // Iniciar sessão se necessário
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json');
        header('Cache-Control: no-cache');

        try {
            // Teste básico de conexão com banco
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM pops_its_registros");
            $stmt->execute();
            $totalRegistros = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Teste de permissões
            $userId = $_SESSION['user_id'] ?? null;
            $isLogged = isset($_SESSION['user_id']);

            echo json_encode([
                'success' => true,
                'message' => 'Endpoint funcionando perfeitamente!',
                'data' => [
                    'php_version' => phpversion(),
                    'session_status' => session_status(),
                    'is_logged_in' => $isLogged,
                    'user_id' => $userId,
                    'total_registros_no_banco' => $totalRegistros,
                    'current_time' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro no teste: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
        exit();
    }

    public function updateRegistro()
    {
        try {
            $registro_id = (int)($_POST['registro_id'] ?? 0);
            $user_id = $_SESSION['user_id'];

            // Verificar se o registro pertence ao usuário
            $stmt = $this->db->prepare("SELECT * FROM pops_its_registros WHERE id = ? AND created_by = ?");
            $stmt->execute([$registro_id, $user_id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado ou sem permissão']);
                exit();
            }

            // Só pode atualizar se estiver reprovado
            if ($registro['status'] !== 'reprovado') {
                echo json_encode(['success' => false, 'message' => 'Só é possível atualizar registros reprovados']);
                exit();
            }

            // Validar novo arquivo
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Novo arquivo é obrigatório']);
                exit();
            }

            $file = $_FILES['arquivo'];
            
            // Validações do arquivo (mesmo do create)
            $allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido']);
                exit();
            }

            if ($file['size'] > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 10MB']);
                exit();
            }

            // Atualizar registro
            $arquivo_blob = file_get_contents($file['tmp_name']);
            
            $stmt = $this->db->prepare("
                UPDATE pops_its_registros 
                SET arquivo_conteudo = ?, arquivo_nome = ?, arquivo_tipo = ?, arquivo_tamanho = ?, 
                    status = 'pendente', observacao_reprovacao = NULL, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $arquivo_blob, $file['name'], $file['type'], $file['size'], $registro_id
            ]);

            echo json_encode(['success' => true, 'message' => 'Registro atualizado com sucesso! Status alterado para pendente']);
            exit();

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar registro: ' . $e->getMessage()]);
            exit();
        }
    }

    

    // ===== ABA 3: PENDENTE APROVAÇÃO =====

    public function listPendentesAprovacao()
    {
    // Garantir sessão
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    header('Content-Type: application/json');
    header('Cache-Control: no-cache');

    $debug = isset($_GET['debug']) && $_GET['debug'] == '1';
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) { @mkdir($logDir, 0777, true); }

    try {
        $stmt = $this->db->prepare(
            "SELECT r.*, 
                    COALESCE(t.titulo, 'Título não encontrado') as titulo, 
                    COALESCE(d.nome, 'Departamento não encontrado') as departamento_nome, 
                    COALESCE(u.name, 'Usuário não encontrado') as criador_nome
             FROM pops_its_registros r
             LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
             LEFT JOIN departamentos d ON t.departamento_id = d.id
             LEFT JOIN users u ON r.created_by = u.id
             WHERE r.status = 'pendente'
             ORDER BY r.created_at ASC"
        );
        $stmt->execute();
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($debug) {
            @file_put_contents($logDir . '/pops_its_debug.log',
                date('Y-m-d H:i:s') . " listPendentesAprovacao reached. count=" . count($registros) . "\n",
                FILE_APPEND
            );
            echo json_encode([
                'success' => true,
                'reached'  => true,
                'count'    => count($registros),
                'data'     => $registros,
                'time'     => date('Y-m-d H:i:s')
            ]);
        } else {
            echo json_encode(['success' => true, 'data' => $registros]);
        }
    } catch (\Exception $e) {
        @file_put_contents($logDir . '/pops_its_debug.log',
            date('Y-m-d H:i:s') . ' listPendentesAprovacao ERROR: ' . $e->getMessage() . "\n",
            FILE_APPEND
        );
        echo json_encode(['success' => false, 'message' => 'Erro ao listar pendentes: ' . $e->getMessage()]);
    }
    exit();
}

public function aprovarRegistro()
{
    header('Content-Type: application/json');
    try {
        // Verificar se é admin
        if (!\App\Services\PermissionService::isAdmin($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores podem aprovar registros.']);
            return;
        }

        $registro_id = (int)($_POST['registro_id'] ?? 0);
        $user_id = $_SESSION['user_id'];

        $stmt = $this->db->prepare(
            "UPDATE pops_its_registros 
             SET status = 'aprovado', approved_by = ?, approved_at = NOW()
             WHERE id = ? AND status = 'pendente'"
        );
        $stmt->execute([$user_id, $registro_id]);

        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'message' => 'Registro não encontrado ou já processado']);
            return;
        }

        echo json_encode(['success' => true, 'message' => 'Registro aprovado com sucesso!']);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao aprovar registro: ' . $e->getMessage()]);
    }
}

    public function reprovarRegistro()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar se é admin
            if (!\App\Services\PermissionService::isAdmin($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores podem reprovar registros.']);
                return;
            }
            
            $registro_id = (int)($_POST['registro_id'] ?? 0);
            $observacao = trim($_POST['observacao'] ?? '');
            $user_id = $_SESSION['user_id'];

            if (empty($observacao)) {
                echo json_encode(['success' => false, 'message' => 'Observação da reprovação é obrigatória']);
                return;
            }

            $stmt = $this->db->prepare("
                UPDATE pops_its_registros 
                SET status = 'reprovado', observacao_reprovacao = ?, approved_by = ?, approved_at = NOW()
                WHERE id = ? AND status = 'pendente'
            ");
            $stmt->execute([$observacao, $user_id, $registro_id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado ou já processado']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Registro reprovado com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao reprovar registro: ' . $e->getMessage()]);
        }
    }

    // ===== ABA 4: VISUALIZAÇÃO =====

    public function listVisualizacao()
    {
        header('Content-Type: application/json');
        
        try {
            $user_id = $_SESSION['user_id'];
            $user_dept_id = $this->getUserDepartmentId($user_id);

            $stmt = $this->db->prepare("
                SELECT DISTINCT r.*, t.titulo, d.nome as departamento_nome, u.name as criador_nome
                FROM pops_its_registros r
                JOIN pops_its_titulos t ON r.titulo_id = t.id
                JOIN departamentos d ON t.departamento_id = d.id
                JOIN users u ON r.created_by = u.id
                LEFT JOIN pops_its_departamentos_permitidos dp ON r.id = dp.registro_id
                WHERE r.status = 'aprovado' 
                AND (
                    r.visibilidade = 'publico' 
                    OR (r.visibilidade = 'departamentos' AND dp.departamento_id = ?)
                )
                ORDER BY t.titulo, r.versao DESC
            ");
            $stmt->execute([$user_dept_id]);
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $registros]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao listar registros: ' . $e->getMessage()]);
        }
    }

    // ===== MÉTODOS AUXILIARES =====

    public function downloadArquivo($id)
    {
        try {
            $user_id = $_SESSION['user_id'];
            $user_dept_id = $this->getUserDepartmentId($user_id);

            // Verificar permissão de visualização
            $stmt = $this->db->prepare("
                SELECT r.*, t.titulo
                FROM pops_its_registros r
                JOIN pops_its_titulos t ON r.titulo_id = t.id
                LEFT JOIN pops_its_departamentos_permitidos dp ON r.id = dp.registro_id
                WHERE r.id = ? 
                AND (
                    r.status = 'aprovado' 
                    AND (
                        r.visibilidade = 'publico' 
                        OR (r.visibilidade = 'departamentos' AND dp.departamento_id = ?)
                    )
                    OR r.created_by = ?
                    OR r.status = 'pendente'
                )
            ");
            $stmt->execute([$id, $user_dept_id, $user_id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                http_response_code(404);
                echo 'Arquivo não encontrado ou sem permissão';
                exit();
            }

            // Servir arquivo
            header('Content-Type: ' . $registro['arquivo_tipo']);
            header('Content-Length: ' . $registro['arquivo_tamanho']);
            header('Content-Disposition: inline; filename="' . $registro['arquivo_nome'] . '"');
            
            echo $registro['arquivo_conteudo'];
            exit();

        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao baixar arquivo: ' . $e->getMessage();
            exit();
        }
    }

    private function getNextVersion($titulo_id): string
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) + 1 as next_version 
            FROM pops_its_registros 
            WHERE titulo_id = ?
        ");
        $stmt->execute([$titulo_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return 'v' . $result['next_version'];
    }

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

    private function getTitulos(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, d.nome as departamento_nome 
                FROM pops_its_titulos t
                JOIN departamentos d ON t.departamento_id = d.id
                ORDER BY t.titulo
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Se tabela não existe, retorna array vazio
            return [];
        }
    }

    // ===== SISTEMA DE SOLICITAÇÕES =====

    public function createSolicitacao()
    {
        header('Content-Type: application/json');

        try {
            // Verificar se usuário está logado
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }

            $registro_id = (int)($_POST['registro_id'] ?? 0);
            $tipo_solicitacao = $_POST['tipo_solicitacao'] ?? '';
            $justificativa = trim($_POST['justificativa'] ?? '');
            $user_id = $_SESSION['user_id'];

            if (empty($tipo_solicitacao)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de solicitação é obrigatório']);
                return;
            }

            if (empty($justificativa)) {
                echo json_encode(['success' => false, 'message' => 'Justificativa é obrigatória']);
                return;
            }

            // Verificar se o registro pertence ao usuário
            $stmt = $this->db->prepare("SELECT * FROM pops_its_registros WHERE id = ? AND created_by = ?");
            $stmt->execute([$registro_id, $user_id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado ou sem permissão']);
                return;
            }

            // Inserir solicitação
            $stmt = $this->db->prepare("
                INSERT INTO pops_its_solicitacoes (
                    registro_id, solicitante_id, tipo_solicitacao, justificativa,
                    status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, 'pendente', NOW(), NOW())
            ");
            $stmt->execute([$registro_id, $user_id, $tipo_solicitacao, $justificativa]);

            echo json_encode(['success' => true, 'message' => 'Solicitação criada com sucesso!']);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar solicitação: ' . $e->getMessage()]);
        }
    }

    public function listSolicitacoes()
    {
        header('Content-Type: application/json');

        try {
            // Verificar se é admin
            if (!\App\Services\PermissionService::isAdmin($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores podem ver solicitações.']);
                return;
            }

            $stmt = $this->db->prepare("
                SELECT s.*, r.titulo, r.versao, r.status as registro_status,
                       u_solicitante.name as solicitante_nome,
                       u_solicitante.email as solicitante_email
                FROM pops_its_solicitacoes s
                JOIN pops_its_registros r ON s.registro_id = r.id
                JOIN users u_solicitante ON s.solicitante_id = u_solicitante.id
                WHERE s.status = 'pendente'
                ORDER BY s.created_at DESC
            ");
            $stmt->execute();
            $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $solicitacoes]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao listar solicitações: ' . $e->getMessage()]);
        }
    }

    public function aprovarSolicitacao()
    {
        header('Content-Type: application/json');

        try {
            // Verificar se é admin
            if (!\App\Services\PermissionService::isAdmin($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores podem aprovar solicitações.']);
                return;
            }

            $solicitacao_id = (int)($_POST['solicitacao_id'] ?? 0);
            $admin_id = $_SESSION['user_id'];

            // Buscar solicitação
            $stmt = $this->db->prepare("SELECT * FROM pops_its_solicitacoes WHERE id = ? AND status = 'pendente'");
            $stmt->execute([$solicitacao_id]);
            $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$solicitacao) {
                echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada ou já processada']);
                return;
            }

            // Executar ação baseada no tipo de solicitação
            if ($solicitacao['tipo_solicitacao'] === 'exclusao') {
                // Excluir o registro
                $stmt = $this->db->prepare("DELETE FROM pops_its_registros WHERE id = ?");
                $stmt->execute([$solicitacao['registro_id']]);
            }

            // Marcar solicitação como aprovada
            $stmt = $this->db->prepare("
                UPDATE pops_its_solicitacoes
                SET status = 'aprovada', aprovada_por = ?, aprovada_em = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$admin_id, $solicitacao_id]);

            echo json_encode(['success' => true, 'message' => 'Solicitação aprovada e executada com sucesso!']);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao aprovar solicitação: ' . $e->getMessage()]);
        }
    }

    public function reprovarSolicitacao()
    {
        header('Content-Type: application/json');

        try {
            // Verificar se é admin
            if (!\App\Services\PermissionService::isAdmin($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores podem reprovar solicitações.']);
                return;
            }

            $solicitacao_id = (int)($_POST['solicitacao_id'] ?? 0);
            $observacao = trim($_POST['observacao'] ?? '');
            $admin_id = $_SESSION['user_id'];

            if (empty($observacao)) {
                echo json_encode(['success' => false, 'message' => 'Observação é obrigatória']);
                return;
            }

            // Marcar solicitação como reprovada
            $stmt = $this->db->prepare("
                UPDATE pops_its_solicitacoes
                SET status = 'reprovada', observacao_reprovacao = ?, aprovada_por = ?, aprovada_em = NOW(), updated_at = NOW()
                WHERE id = ? AND status = 'pendente'
            ");
            $stmt->execute([$observacao, $admin_id, $solicitacao_id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada ou já processada']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Solicitação reprovada com sucesso!']);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao reprovar solicitação: ' . $e->getMessage()]);
        }
    }

    // Método de diagnóstico para produção
    public function diagnosticoPendentes()
    {
        // Verificar se é admin
        if (!\App\Services\PermissionService::isAdmin($_SESSION['user_id'])) {
            http_response_code(403);
            echo "<h1>Acesso Negado</h1><p>Apenas administradores podem acessar o diagnóstico.</p>";
            return;
        }

        try {
            echo "<!DOCTYPE html>
            <html lang='pt-br'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Diagnóstico POPs Pendentes - SGQ OTI DJ</title>
                <script src='https://cdn.tailwindcss.com'></script>
            </head>
            <body class='bg-gray-100 p-8'>
                <div class='max-w-6xl mx-auto'>
                    <h1 class='text-3xl font-bold mb-6 text-gray-900'>🔍 Diagnóstico: POPs Pendentes de Aprovação</h1>";

            // 1. Verificar registros pendentes
            echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
                    <h2 class='text-xl font-semibold mb-4'>1. Registros Pendentes no Banco</h2>";

            $stmt = $this->db->prepare("
                SELECT r.*, 
                       COALESCE(t.titulo, 'Título não encontrado') as titulo, 
                       COALESCE(d.nome, 'Departamento não encontrado') as departamento_nome, 
                       COALESCE(u.name, 'Usuário não encontrado') as criador_nome
                FROM pops_its_registros r
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                LEFT JOIN departamentos d ON t.departamento_id = d.id
                LEFT JOIN users u ON r.created_by = u.id
                WHERE r.status = 'pendente'
                ORDER BY r.created_at ASC
            ");
            $stmt->execute();
            $registrosPendentes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo "<p class='text-lg mb-4'><strong>Total de registros pendentes:</strong> <span class='bg-orange-100 text-orange-800 px-3 py-1 rounded-full'>" . count($registrosPendentes) . "</span></p>";

            if (count($registrosPendentes) > 0) {
                echo "<div class='overflow-x-auto'>
                        <table class='min-w-full bg-white border border-gray-200'>
                            <thead class='bg-gray-50'>
                                <tr>
                                    <th class='px-4 py-2 text-left'>ID</th>
                                    <th class='px-4 py-2 text-left'>Título</th>
                                    <th class='px-4 py-2 text-left'>Departamento</th>
                                    <th class='px-4 py-2 text-left'>Criador</th>
                                    <th class='px-4 py-2 text-left'>Status</th>
                                    <th class='px-4 py-2 text-left'>Data Criação</th>
                                </tr>
                            </thead>
                            <tbody>";
                foreach ($registrosPendentes as $reg) {
                    echo "<tr class='border-t'>
                            <td class='px-4 py-2'>" . $reg['id'] . "</td>
                            <td class='px-4 py-2'>" . htmlspecialchars($reg['titulo']) . "</td>
                            <td class='px-4 py-2'>" . htmlspecialchars($reg['departamento_nome']) . "</td>
                            <td class='px-4 py-2'>" . htmlspecialchars($reg['criador_nome']) . "</td>
                            <td class='px-4 py-2'><span class='bg-orange-100 text-orange-800 px-2 py-1 rounded text-sm'>" . $reg['status'] . "</span></td>
                            <td class='px-4 py-2'>" . $reg['created_at'] . "</td>
                          </tr>";
                }
                echo "</tbody></table></div>";
            } else {
                echo "<div class='bg-red-50 border border-red-200 rounded p-4'>
                        <p class='text-red-800'><strong>❌ PROBLEMA:</strong> Não há registros com status 'pendente' no banco!</p>
                      </div>";

                // Verificar todos os status
                echo "<h3 class='text-lg font-semibold mt-4 mb-2'>Status existentes na tabela:</h3>";
                $stmt = $this->db->prepare("SELECT status, COUNT(*) as count FROM pops_its_registros GROUP BY status");
                $stmt->execute();
                $statusCount = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                echo "<ul class='list-disc list-inside'>";
                foreach ($statusCount as $status) {
                    echo "<li><strong>" . $status['status'] . ":</strong> " . $status['count'] . " registros</li>";
                }
                echo "</ul>";
            }
            echo "</div>";

            // 2. Teste das rotas
            echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
                    <h2 class='text-xl font-semibold mb-4'>2. Teste das Rotas</h2>
                    <div class='space-y-2'>
                        <a href='/pops-its/pendentes/list?debug=1' target='_blank' class='inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mr-2'>🧪 Testar Rota Pendentes (Debug)</a>
                        <a href='/pops-its/pendentes/list' target='_blank' class='inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mr-2'>🔗 Testar Rota Pendentes</a>
                        <a href='/pops-its/visualizacao/list' target='_blank' class='inline-block bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700'>👁️ Testar Rota Visualização</a>
                    </div>
                  </div>";

            // 3. Permissões do usuário
            echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
                    <h2 class='text-xl font-semibold mb-4'>3. Permissões do Usuário Atual</h2>";

            $userId = $_SESSION['user_id'];
            $permissions = [
                'pops_its_visualizacao' => 'Visualização',
                'pops_its_pendente_aprovacao' => 'Pendente Aprovação',
                'pops_its_cadastro_titulos' => 'Cadastro Títulos',
                'pops_its_meus_registros' => 'Meus Registros'
            ];

            echo "<div class='overflow-x-auto'>
                    <table class='min-w-full bg-white border border-gray-200'>
                        <thead class='bg-gray-50'>
                            <tr>
                                <th class='px-4 py-2 text-left'>Módulo</th>
                                <th class='px-4 py-2 text-left'>Descrição</th>
                                <th class='px-4 py-2 text-center'>View</th>
                                <th class='px-4 py-2 text-center'>Edit</th>
                                <th class='px-4 py-2 text-center'>Delete</th>
                            </tr>
                        </thead>
                        <tbody>";

            foreach ($permissions as $module => $desc) {
                $canView = \App\Services\PermissionService::hasPermission($userId, $module, 'view');
                $canEdit = \App\Services\PermissionService::hasPermission($userId, $module, 'edit');
                $canDelete = \App\Services\PermissionService::hasPermission($userId, $module, 'delete');

                echo "<tr class='border-t'>
                        <td class='px-4 py-2 font-mono text-sm'>" . $module . "</td>
                        <td class='px-4 py-2'>" . $desc . "</td>
                        <td class='px-4 py-2 text-center'>" . ($canView ? '✅' : '❌') . "</td>
                        <td class='px-4 py-2 text-center'>" . ($canEdit ? '✅' : '❌') . "</td>
                        <td class='px-4 py-2 text-center'>" . ($canDelete ? '✅' : '❌') . "</td>
                      </tr>";
            }
            echo "</tbody></table></div>";

            $isAdmin = \App\Services\PermissionService::isAdmin($userId);
            echo "<p class='mt-4'><strong>É Administrador:</strong> " . ($isAdmin ? '<span class="text-green-600">✅ SIM</span>' : '<span class="text-red-600">❌ NÃO</span>') . "</p>";
            echo "</div>";

            // 4. Informações do usuário
            echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
                    <h2 class='text-xl font-semibold mb-4'>4. Informações do Usuário</h2>";

            $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.email, u.profile_id, p.name as profile_name, p.is_admin
                FROM users u 
                LEFT JOIN profiles p ON u.profile_id = p.id 
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user) {
                echo "<ul class='space-y-2'>
                        <li><strong>ID:</strong> " . $user['id'] . "</li>
                        <li><strong>Nome:</strong> " . htmlspecialchars($user['name']) . "</li>
                        <li><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</li>
                        <li><strong>Perfil:</strong> " . htmlspecialchars($user['profile_name'] ?? 'Sem perfil') . "</li>
                        <li><strong>Perfil é Admin:</strong> " . ($user['is_admin'] ? '<span class="text-green-600">✅ SIM</span>' : '<span class="text-red-600">❌ NÃO</span>') . "</li>
                      </ul>";
            }
            echo "</div>";

            // 5. Soluções sugeridas
            echo "<div class='bg-blue-50 border border-blue-200 rounded p-6'>
                    <h2 class='text-xl font-semibold mb-4'>🔧 Possíveis Soluções</h2>
                    <div class='space-y-4'>
                        <div>
                            <h3 class='font-semibold text-blue-800'>Se não há registros pendentes:</h3>
                            <ul class='list-disc list-inside text-blue-700 ml-4'>
                                <li>Verifique se existem registros na tabela pops_its_registros</li>
                                <li>Confirme se algum registro tem status = 'pendente'</li>
                                <li>Verifique se os registros foram criados corretamente</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class='font-semibold text-blue-800'>Se há registros mas não aparecem:</h3>
                            <ul class='list-disc list-inside text-blue-700 ml-4'>
                                <li>Teste as rotas acima diretamente</li>
                                <li>Verifique permissões para 'pops_its_pendente_aprovacao'</li>
                                <li>Confirme se o JavaScript está carregando os dados</li>
                                <li>Verifique erros no console do navegador (F12)</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class='font-semibold text-blue-800'>Se visualização fica 'Carregando...':</h3>
                            <ul class='list-disc list-inside text-blue-700 ml-4'>
                                <li>Problema na rota /pops-its/visualizacao/list</li>
                                <li>Verifique permissões para 'pops_its_visualizacao'</li>
                                <li>Teste a rota diretamente no navegador</li>
                            </ul>
                        </div>
                    </div>
                  </div>";

            echo "</div></body></html>";

        } catch (\Exception $e) {
            echo "<div class='bg-red-50 border border-red-200 rounded p-4'>
                    <h3 class='text-red-800 font-semibold'>❌ Erro no Diagnóstico:</h3>
                    <p class='text-red-700'>" . htmlspecialchars($e->getMessage()) . "</p>
                    <p class='text-red-600 text-sm'>Arquivo: " . htmlspecialchars($e->getFile()) . "</p>
                    <p class='text-red-600 text-sm'>Linha: " . $e->getLine() . "</p>
                  </div>";
        }
    }

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
    
    // Listar títulos
    public function listTitulos()
    {
        header('Content-Type: application/json');
        
        try {
            // Primeiro verificar se a tabela existe
            $stmt = $this->db->query("SHOW TABLES LIKE 'pops_its_titulos'");
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Tabela pops_its_titulos não existe']);
                return;
            }
            
            // Verificar se há registros na tabela
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM pops_its_titulos");
            $total = $stmt->fetchColumn();
            
            if ($total == 0) {
                echo json_encode(['success' => true, 'data' => [], 'message' => 'Nenhum título cadastrado']);
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
            
            // Log para debug
            error_log("PopIts listTitulos: Encontrados " . count($titulos) . " títulos");
            
            echo json_encode(['success' => true, 'data' => $titulos, 'total' => count($titulos)]);
            
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
            
            $sql = "
                SELECT DISTINCT titulo, tipo
                FROM pops_its_titulos 
                WHERE titulo LIKE ?
            ";
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
    
    // Criar registro (Aba 2)
    public function createRegistro()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            if (!\App\Services\PermissionService::hasPermission($user_id, 'pops_its_meus_registros', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para criar registros']);
                return;
            }
            
            // Verificar se as tabelas existem
            $stmt = $this->db->query("SHOW TABLES LIKE 'pops_its_registros'");
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Tabela pops_its_registros não existe. Execute o script SQL primeiro.']);
                return;
            }
            
            // Validar dados
            $titulo_id = (int)($_POST['titulo_id'] ?? 0);
            $visibilidade = $_POST['visibilidade'] ?? '';
            $departamentos_permitidos = $_POST['departamentos_permitidos'] ?? [];
            
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
            $allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg', 
                           'application/vnd.ms-powerpoint', 
                           'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use PDF, PNG, JPEG ou PPT']);
                return;
            }
            
            // Validar tamanho (10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 10MB']);
                return;
            }
            
            // Verificar se o título existe
            $stmt = $this->db->prepare("SELECT titulo FROM pops_its_titulos WHERE id = ?");
            $stmt->execute([$titulo_id]);
            $titulo = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$titulo) {
                echo json_encode(['success' => false, 'message' => 'Título não encontrado']);
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
            
            // Iniciar transação
            $this->db->beginTransaction();
            
            try {
                // Inserir registro
                $stmt = $this->db->prepare("
                    INSERT INTO pops_its_registros 
                    (titulo_id, versao, arquivo, nome_arquivo, extensao, tamanho_arquivo, publico, criado_por) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $publico = ($visibilidade === 'publico') ? 1 : 0;
                $stmt->execute([
                    $titulo_id, $proxima_versao, $arquivo_conteudo, $nome_arquivo, 
                    $extensao, $tamanho_arquivo, $publico, $user_id
                ]);
                
                $registro_id = $this->db->lastInsertId();
                
                // Se não é público, inserir departamentos permitidos
                if ($visibilidade === 'departamentos' && !empty($departamentos_permitidos)) {
                    $stmt = $this->db->prepare("
                        INSERT INTO pops_its_registros_departamentos (registro_id, departamento_id) 
                        VALUES (?, ?)
                    ");
                    
                    foreach ($departamentos_permitidos as $dept_id) {
                        $stmt->execute([$registro_id, (int)$dept_id]);
                    }
                }
                
                $this->db->commit();
                
                echo json_encode([
                    'success' => true, 
                    'message' => "Registro criado com sucesso! {$titulo['titulo']} v{$proxima_versao} está pendente de aprovação."
                ]);
                
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            error_log("PopItsController::createRegistro - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
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
                echo json_encode(['success' => true, 'data' => [], 'message' => 'Tabela não existe ainda']);
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
            
            // Para cada registro não público, buscar departamentos permitidos
            foreach ($registros as &$registro) {
                if (!$registro['publico']) {
                    $stmt = $this->db->prepare("
                        SELECT d.nome 
                        FROM pops_its_registros_departamentos rd
                        LEFT JOIN departamentos d ON rd.departamento_id = d.id
                        WHERE rd.registro_id = ?
                    ");
                    $stmt->execute([$registro['id']]);
                    $departamentos = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                    $registro['departamentos_permitidos'] = $departamentos;
                }
            }
            
            echo json_encode(['success' => true, 'data' => $registros]);
            
        } catch (\Exception $e) {
            error_log("PopItsController::listMeusRegistros - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar registros: ' . $e->getMessage()]);
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
                    // Se não é público, verificar se o departamento do usuário tem acesso
                    // TODO: Implementar verificação de departamento do usuário
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
            
            // Enviar o arquivo
            echo $registro['arquivo'];
            
        } catch (\Exception $e) {
            error_log("PopItsController::downloadArquivo - Erro: " . $e->getMessage());
            http_response_code(500);
            echo "Erro interno do servidor";
        }
    }
    
    // Excluir título (apenas admin)
    public function deleteTitulo()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar se é admin
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
            
            // Verificar se o título existe
            $stmt = $this->db->prepare("SELECT titulo, tipo FROM pops_its_titulos WHERE id = ?");
            $stmt->execute([$titulo_id]);
            $titulo = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$titulo) {
                echo json_encode(['success' => false, 'message' => 'Título não encontrado']);
                return;
            }
            
            // Verificar se existem registros aprovados com este título
            // Assumindo que existe uma tabela pops_its_registros com status 'aprovado'
            try {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total 
                    FROM pops_its_registros 
                    WHERE titulo_id = ? AND status = 'aprovado'
                ");
                $stmt->execute([$titulo_id]);
                $registrosAprovados = $stmt->fetchColumn();
                
                if ($registrosAprovados > 0) {
                    echo json_encode([
                        'success' => false, 
                        'message' => "Não é possível excluir este título. Existem {$registrosAprovados} registro(s) aprovado(s) vinculado(s) a ele."
                    ]);
                    return;
                }
            } catch (\Exception $e) {
                // Se a tabela pops_its_registros não existir, continua com a exclusão
                // mas registra o aviso
                error_log("Aviso: Tabela pops_its_registros não encontrada - " . $e->getMessage());
            }
            
            // Excluir o título
            $stmt = $this->db->prepare("DELETE FROM pops_its_titulos WHERE id = ?");
            $stmt->execute([$titulo_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true, 
                    'message' => "Título \"{$titulo['titulo']}\" ({$titulo['tipo']}) excluído com sucesso!"
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao excluir título']);
            }
            
        } catch (\Exception $e) {
            error_log("PopItsController::deleteTitulo - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }
    
    private function normalizarTitulo($titulo)
    {
        // Remove acentos, converte para minúsculas e remove caracteres especiais
        $titulo = strtolower($titulo);
        $titulo = iconv('UTF-8', 'ASCII//TRANSLIT', $titulo);
        $titulo = preg_replace('/[^a-z0-9\s]/', '', $titulo);
        $titulo = preg_replace('/\s+/', ' ', $titulo);
        return trim($titulo);
    }
    
    // Teste simples para verificar se a rota funciona
    public function testeTitulos()
    {
        header('Content-Type: application/json');
        
        try {
            // Teste básico de conexão
            $stmt = $this->db->query("SELECT 1 as teste");
            $teste = $stmt->fetch();
            
            // Verificar se a tabela existe
            $stmt = $this->db->query("SHOW TABLES LIKE 'pops_its_titulos'");
            $tabelaExiste = $stmt->fetch() ? true : false;
            
            $resultado = [
                'success' => true,
                'conexao_db' => $teste ? 'OK' : 'ERRO',
                'tabela_existe' => $tabelaExiste,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            if ($tabelaExiste) {
                // Contar registros
                $stmt = $this->db->query("SELECT COUNT(*) as total FROM pops_its_titulos");
                $total = $stmt->fetchColumn();
                $resultado['total_registros'] = $total;
                
                // Se há registros, buscar alguns
                if ($total > 0) {
                    $stmt = $this->db->query("SELECT id, titulo, tipo FROM pops_its_titulos LIMIT 3");
                    $resultado['exemplos'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                }
            }
            
            echo json_encode($resultado);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine()
            ]);
        }
    }
    
    // Método de diagnóstico para verificar estrutura do banco
    public function diagnostico()
    {
        // Verificar se é admin
        if (!\App\Services\PermissionService::isAdmin($_SESSION['user_id'])) {
            http_response_code(403);
            echo "<h1>Acesso Negado</h1><p>Apenas administradores podem acessar o diagnóstico.</p>";
            return;
        }

        try {
            echo "<!DOCTYPE html>
            <html lang='pt-br'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Diagnóstico POPs e ITs</title>
                <script src='https://cdn.tailwindcss.com'></script>
            </head>
            <body class='bg-gray-100 p-8'>
                <div class='max-w-4xl mx-auto space-y-6'>";

            echo "<div class='bg-white rounded-lg shadow p-6'>
                    <h1 class='text-2xl font-bold mb-4'>🔍 Diagnóstico POPs e ITs</h1>
                    <p class='text-gray-600'>Verificando estrutura do banco de dados...</p>
                  </div>";

            // 1. Verificar se a tabela existe
            echo "<div class='bg-white rounded-lg shadow p-6'>
                    <h2 class='text-xl font-semibold mb-4'>📋 Verificação da Tabela</h2>";
            
            $stmt = $this->db->query("SHOW TABLES LIKE 'pops_its_titulos'");
            $tabelaExiste = $stmt->fetch();
            
            if ($tabelaExiste) {
                echo "<p class='text-green-600 font-semibold'>✅ Tabela 'pops_its_titulos' existe</p>";
                
                // Verificar estrutura da tabela
                $stmt = $this->db->query("DESCRIBE pops_its_titulos");
                $colunas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                echo "<h3 class='font-semibold mt-4 mb-2'>Estrutura da tabela:</h3>";
                echo "<div class='overflow-x-auto'>";
                echo "<table class='min-w-full border border-gray-300'>";
                echo "<thead class='bg-gray-50'>";
                echo "<tr><th class='border border-gray-300 px-4 py-2'>Campo</th><th class='border border-gray-300 px-4 py-2'>Tipo</th><th class='border border-gray-300 px-4 py-2'>Null</th><th class='border border-gray-300 px-4 py-2'>Key</th><th class='border border-gray-300 px-4 py-2'>Default</th></tr>";
                echo "</thead><tbody>";
                
                foreach ($colunas as $coluna) {
                    echo "<tr>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $coluna['Field'] . "</td>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $coluna['Type'] . "</td>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $coluna['Null'] . "</td>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $coluna['Key'] . "</td>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . ($coluna['Default'] ?? 'NULL') . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table></div>";
                
                // Contar registros
                $stmt = $this->db->query("SELECT COUNT(*) as total FROM pops_its_titulos");
                $total = $stmt->fetch(\PDO::FETCH_ASSOC);
                echo "<p class='mt-4'>📊 Total de registros: <strong>" . $total['total'] . "</strong></p>";
                
            } else {
                echo "<p class='text-red-600 font-semibold'>❌ Tabela 'pops_its_titulos' NÃO existe</p>";
                echo "<div class='mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded'>";
                echo "<h4 class='font-semibold text-yellow-800'>🔧 Solução:</h4>";
                echo "<p class='text-yellow-700'>Execute o script SQL localizado em:</p>";
                echo "<code class='bg-yellow-100 px-2 py-1 rounded'>database/create_pops_its_titulos.sql</code>";
                echo "</div>";
            }
            echo "</div>";

            // 2. Verificar tabela departamentos
            echo "<div class='bg-white rounded-lg shadow p-6'>
                    <h2 class='text-xl font-semibold mb-4'>🏢 Verificação de Departamentos</h2>";
            
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM departamentos");
            $totalDept = $stmt->fetch(\PDO::FETCH_ASSOC);
            echo "<p>📊 Total de departamentos: <strong>" . $totalDept['total'] . "</strong></p>";
            
            if ($totalDept['total'] > 0) {
                $stmt = $this->db->query("SELECT id, nome FROM departamentos ORDER BY nome LIMIT 5");
                $depts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                echo "<h4 class='font-semibold mt-2'>Primeiros 5 departamentos:</h4>";
                echo "<ul class='list-disc list-inside'>";
                foreach ($depts as $dept) {
                    echo "<li>ID: " . $dept['id'] . " - " . $dept['nome'] . "</li>";
                }
                echo "</ul>";
            }
            echo "</div>";

            // 3. Verificar permissões
            echo "<div class='bg-white rounded-lg shadow p-6'>
                    <h2 class='text-xl font-semibold mb-4'>🔐 Verificação de Permissões</h2>";
            
            $user_id = $_SESSION['user_id'];
            $permissoes = [
                'pops_its_cadastro_titulos' => 'Cadastro de Títulos',
                'pops_its_meus_registros' => 'Meus Registros',
                'pops_its_pendente_aprovacao' => 'Pendente Aprovação',
                'pops_its_visualizacao' => 'Visualização'
            ];
            
            echo "<table class='min-w-full border border-gray-300'>";
            echo "<thead class='bg-gray-50'>";
            echo "<tr><th class='border border-gray-300 px-4 py-2'>Módulo</th><th class='border border-gray-300 px-4 py-2'>View</th><th class='border border-gray-300 px-4 py-2'>Edit</th></tr>";
            echo "</thead><tbody>";
            
            foreach ($permissoes as $modulo => $nome) {
                $hasView = \App\Services\PermissionService::hasPermission($user_id, $modulo, 'view') ? '✅' : '❌';
                $hasEdit = \App\Services\PermissionService::hasPermission($user_id, $modulo, 'edit') ? '✅' : '❌';
                echo "<tr>";
                echo "<td class='border border-gray-300 px-4 py-2'>" . $nome . "</td>";
                echo "<td class='border border-gray-300 px-4 py-2'>" . $hasView . "</td>";
                echo "<td class='border border-gray-300 px-4 py-2'>" . $hasEdit . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";

            echo "</div></body></html>";

        } catch (\Exception $e) {
            echo "<div class='bg-red-50 border border-red-200 rounded p-4'>
                    <h3 class='text-red-800 font-semibold'>❌ Erro no Diagnóstico:</h3>
                    <p class='text-red-700'>" . htmlspecialchars($e->getMessage()) . "</p>
                    <p class='text-red-600 text-sm'>Arquivo: " . htmlspecialchars($e->getFile()) . "</p>
                    <p class='text-red-600 text-sm'>Linha: " . $e->getLine() . "</p>
                  </div>";
        }
    }

}
