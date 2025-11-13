<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;

class NpsController
{
    private $storageDir;
    private $respostasDir;
    
    public function __construct()
    {
        $this->storageDir = __DIR__ . '/../../storage/formularios';
        $this->respostasDir = __DIR__ . '/../../storage/formularios/respostas';
        
        // Criar diretórios se não existirem
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
        if (!is_dir($this->respostasDir)) {
            mkdir($this->respostasDir, 0755, true);
        }
    }
    
    /**
     * Página principal - Lista de formulários
     */
    public function index()
    {
        // Verificar autenticação
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $title = 'Formulários NPS - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/nps/index.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Listar formulários do usuário (AJAX)
     */
    public function listar()
    {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }
            
            $formularios = [];
            $files = glob($this->storageDir . '/formulario_*.json');
            
            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                
                // Filtrar apenas formulários do usuário ou se for admin
                if ($data['criado_por'] == $userId || ($_SESSION['user_role'] ?? '') === 'admin') {
                    // Contar respostas
                    $totalRespostas = $this->contarRespostas($data['id']);
                    
                    $formularios[] = [
                        'id' => $data['id'],
                        'titulo' => $data['titulo'],
                        'descricao' => $data['descricao'],
                        'ativo' => $data['ativo'],
                        'total_respostas' => $totalRespostas,
                        'criado_em' => $data['criado_em'],
                        'criado_por_nome' => $data['criado_por_nome'],
                        'link_publico' => $_ENV['APP_URL'] . '/nps/responder/' . $data['id']
                    ];
                }
            }
            
            // Ordenar por data de criação (mais recente primeiro)
            usort($formularios, function($a, $b) {
                return strtotime($b['criado_em']) - strtotime($a['criado_em']);
            });
            
            echo json_encode(['success' => true, 'formularios' => $formularios]);
            
        } catch (\Exception $e) {
            error_log('Erro ao listar formulários: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar formulários']);
        }
        exit;
    }
    
    /**
     * Criar novo formulário
     */
    public function criar()
    {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $userName = $_SESSION['user_name'] ?? 'Usuário';
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }
            
            // Receber dados do formulário
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $perguntas = json_decode($_POST['perguntas'] ?? '[]', true);
            
            // Validações
            if (empty($titulo)) {
                echo json_encode(['success' => false, 'message' => 'Título é obrigatório']);
                exit;
            }
            
            if (empty($perguntas) || !is_array($perguntas)) {
                echo json_encode(['success' => false, 'message' => 'Adicione pelo menos uma pergunta']);
                exit;
            }
            
            // Gerar ID único
            $formularioId = 'form_' . time() . '_' . uniqid();
            
            // Processar upload de logo
            $logoPath = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $logosDir = $this->storageDir . '/logos';
                if (!is_dir($logosDir)) {
                    mkdir($logosDir, 0755, true);
                }
                
                $fileExtension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $logoFilename = $formularioId . '.' . $fileExtension;
                $logoFullPath = $logosDir . '/' . $logoFilename;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoFullPath)) {
                    $logoPath = 'storage/formularios/logos/' . $logoFilename;
                }
            }
            
            // Dados do formulário
            $formulario = [
                'id' => $formularioId,
                'titulo' => $titulo,
                'descricao' => $descricao,
                'perguntas' => $perguntas,
                'logo' => $logoPath,
                'ativo' => true,
                'criado_por' => $userId,
                'criado_por_nome' => $userName,
                'criado_em' => date('Y-m-d H:i:s'),
                'atualizado_em' => date('Y-m-d H:i:s')
            ];
            
            // Salvar em arquivo JSON
            $filename = $this->storageDir . '/formulario_' . $formularioId . '.json';
            file_put_contents($filename, json_encode($formulario, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            echo json_encode([
                'success' => true, 
                'message' => 'Formulário criado com sucesso!',
                'formulario_id' => $formularioId,
                'link_publico' => $_ENV['APP_URL'] . '/nps/responder/' . $formularioId
            ]);
            
        } catch (\Exception $e) {
            error_log('Erro ao criar formulário: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao criar formulário']);
        }
        exit;
    }
    
    /**
     * Editar formulário existente
     */
    public function editar()
    {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $formularioId = $_POST['formulario_id'] ?? '';
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }
            
            // Carregar formulário
            $filename = $this->storageDir . '/formulario_' . $formularioId . '.json';
            if (!file_exists($filename)) {
                echo json_encode(['success' => false, 'message' => 'Formulário não encontrado']);
                exit;
            }
            
            $formulario = json_decode(file_get_contents($filename), true);
            
            // Verificar permissão
            if ($formulario['criado_por'] != $userId && ($_SESSION['user_role'] ?? '') !== 'admin') {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para editar este formulário']);
                exit;
            }
            
            // Verificar se tem respostas (não pode editar se tiver)
            $respostaFiles = glob($this->respostasDir . '/resposta_*.json');
            $totalRespostas = 0;
            foreach ($respostaFiles as $file) {
                $resposta = json_decode(file_get_contents($file), true);
                if ($resposta['formulario_id'] == $formularioId) {
                    $totalRespostas++;
                }
            }
            
            if ($totalRespostas > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Não é possível editar formulário com respostas! Total de respostas: ' . $totalRespostas
                ]);
                exit;
            }
            
            // Atualizar dados
            $formulario['titulo'] = trim($_POST['titulo'] ?? $formulario['titulo']);
            $formulario['descricao'] = trim($_POST['descricao'] ?? $formulario['descricao']);
            $formulario['perguntas'] = json_decode($_POST['perguntas'] ?? '[]', true) ?: $formulario['perguntas'];
            $formulario['atualizado_em'] = date('Y-m-d H:i:s');
            
            // Salvar
            file_put_contents($filename, json_encode($formulario, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            echo json_encode(['success' => true, 'message' => 'Formulário atualizado com sucesso!']);
            
        } catch (\Exception $e) {
            error_log('Erro ao editar formulário: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar formulário']);
        }
        exit;
    }
    
    /**
     * Ativar/Desativar formulário
     */
    public function toggleStatus()
    {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $formularioId = $_POST['formulario_id'] ?? '';
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }
            
            $filename = $this->storageDir . '/formulario_' . $formularioId . '.json';
            if (!file_exists($filename)) {
                echo json_encode(['success' => false, 'message' => 'Formulário não encontrado']);
                exit;
            }
            
            $formulario = json_decode(file_get_contents($filename), true);
            
            // Verificar permissão
            if ($formulario['criado_por'] != $userId && ($_SESSION['user_role'] ?? '') !== 'admin') {
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                exit;
            }
            
            // Toggle status
            $formulario['ativo'] = !$formulario['ativo'];
            $formulario['atualizado_em'] = date('Y-m-d H:i:s');
            
            file_put_contents($filename, json_encode($formulario, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            echo json_encode([
                'success' => true, 
                'message' => 'Status atualizado!',
                'ativo' => $formulario['ativo']
            ]);
            
        } catch (\Exception $e) {
            error_log('Erro ao alterar status: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao alterar status']);
        }
        exit;
    }
    
    /**
     * Excluir formulário
     */
    public function excluir()
    {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $formularioId = $_POST['formulario_id'] ?? '';
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }
            
            $filename = $this->storageDir . '/formulario_' . $formularioId . '.json';
            if (!file_exists($filename)) {
                echo json_encode(['success' => false, 'message' => 'Formulário não encontrado']);
                exit;
            }
            
            $formulario = json_decode(file_get_contents($filename), true);
            
            // Verificar permissão
            if ($formulario['criado_por'] != $userId && ($_SESSION['user_role'] ?? '') !== 'admin') {
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                exit;
            }
            
            // Verificar se tem respostas
            $totalRespostas = $this->contarRespostas($formularioId);
            if ($totalRespostas > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => "Não é possível excluir! Este formulário já possui $totalRespostas resposta(s)."
                ]);
                exit;
            }
            
            // Excluir arquivo
            unlink($filename);
            
            echo json_encode(['success' => true, 'message' => 'Formulário excluído com sucesso!']);
            
        } catch (\Exception $e) {
            error_log('Erro ao excluir formulário: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir formulário']);
        }
        exit;
    }
    
    /**
     * Página pública de resposta (SEM LOGIN)
     */
    public function responder($formularioId)
    {
        $filename = $this->storageDir . '/formulario_' . $formularioId . '.json';
        
        if (!file_exists($filename)) {
            echo '<!DOCTYPE html><html><body><h1>Formulário não encontrado</h1></body></html>';
            exit;
        }
        
        $formulario = json_decode(file_get_contents($filename), true);
        
        // Verificar se está ativo
        if (!$formulario['ativo']) {
            echo '<!DOCTYPE html><html><body><h1>Este formulário não está mais disponível</h1></body></html>';
            exit;
        }
        
        // Carregar view pública (sem layout do sistema)
        include __DIR__ . '/../../views/pages/nps/responder.php';
    }
    
    /**
     * Salvar resposta pública (SEM LOGIN)
     */
    public function salvarResposta()
    {
        header('Content-Type: application/json');
        
        try {
            $formularioId = $_POST['formulario_id'] ?? '';
            $respostas = json_decode($_POST['respostas'] ?? '[]', true);
            $nome = trim($_POST['nome'] ?? 'Anônimo');
            $email = trim($_POST['email'] ?? '');
            
            if (empty($formularioId) || empty($respostas)) {
                echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
                exit;
            }
            
            // Verificar se formulário existe e está ativo
            $formFilename = $this->storageDir . '/formulario_' . $formularioId . '.json';
            if (!file_exists($formFilename)) {
                echo json_encode(['success' => false, 'message' => 'Formulário não encontrado']);
                exit;
            }
            
            $formulario = json_decode(file_get_contents($formFilename), true);
            if (!$formulario['ativo']) {
                echo json_encode(['success' => false, 'message' => 'Formulário não está mais disponível']);
                exit;
            }
            
            // Gerar ID único para a resposta
            $respostaId = 'resp_' . time() . '_' . uniqid();
            
            // Dados da resposta
            $resposta = [
                'id' => $respostaId,
                'formulario_id' => $formularioId,
                'formulario_titulo' => $formulario['titulo'],
                'nome' => $nome,
                'email' => $email,
                'respostas' => $respostas,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'respondido_em' => date('Y-m-d H:i:s')
            ];
            
            // Salvar resposta
            $respostaFilename = $this->respostasDir . '/resposta_' . $respostaId . '.json';
            file_put_contents($respostaFilename, json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            echo json_encode([
                'success' => true, 
                'message' => 'Obrigado por responder! Sua opinião é muito importante para nós.'
            ]);
            
        } catch (\Exception $e) {
            error_log('Erro ao salvar resposta: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao enviar resposta']);
        }
        exit;
    }
    
    /**
     * Ver respostas de um formulário
     */
    public function verRespostas($formularioId)
    {
        // Verificar autenticação
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Verificar se formulário existe e se usuário tem permissão
        $formFilename = $this->storageDir . '/formulario_' . $formularioId . '.json';
        if (!file_exists($formFilename)) {
            echo 'Formulário não encontrado';
            exit;
        }
        
        $formulario = json_decode(file_get_contents($formFilename), true);
        
        if ($formulario['criado_por'] != $userId && ($_SESSION['user_role'] ?? '') !== 'admin') {
            echo 'Sem permissão para ver as respostas';
            exit;
        }
        
        // Carregar todas as respostas deste formulário
        $respostas = [];
        $files = glob($this->respostasDir . '/resposta_*.json');
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data['formulario_id'] === $formularioId) {
                $respostas[] = $data;
            }
        }
        
        // Ordenar por data (mais recente primeiro)
        usort($respostas, function($a, $b) {
            return strtotime($b['respondido_em']) - strtotime($a['respondido_em']);
        });
        
        // Verificar se é admin ou super_admin para permitir exclusão
        $userRole = $_SESSION['user_role'] ?? '';
        $podeExcluir = in_array($userRole, ['admin', 'super_admin']);
        
        $title = 'Respostas: ' . $formulario['titulo'] . ' - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/nps/respostas.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Contar respostas de um formulário
     */
    private function contarRespostas($formularioId)
    {
        $count = 0;
        $files = glob($this->respostasDir . '/resposta_*.json');
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data['formulario_id'] === $formularioId) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Obter detalhes de um formulário (AJAX)
     */
    public function detalhes($formularioId)
    {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }
            
            $filename = $this->storageDir . '/formulario_' . $formularioId . '.json';
            if (!file_exists($filename)) {
                echo json_encode(['success' => false, 'message' => 'Formulário não encontrado']);
                exit;
            }
            
            $formulario = json_decode(file_get_contents($filename), true);
            
            // Verificar permissão
            if ($formulario['criado_por'] != $userId && ($_SESSION['user_role'] ?? '') !== 'admin') {
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                exit;
            }
            
            echo json_encode(['success' => true, 'formulario' => $formulario]);
            
        } catch (\Exception $e) {
            error_log('Erro ao carregar detalhes: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar dados']);
        }
        exit;
    }
    
    /**
     * Excluir resposta (apenas admin/super_admin)
     */
    public function excluirResposta()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar se é admin ou super_admin
            $userRole = $_SESSION['user_role'] ?? '';
            if (!in_array($userRole, ['admin', 'super_admin'])) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para excluir respostas']);
                exit;
            }
            
            $respostaId = $_POST['resposta_id'] ?? '';
            
            if (empty($respostaId)) {
                echo json_encode(['success' => false, 'message' => 'ID da resposta não informado']);
                exit;
            }
            
            // Verificar se resposta existe
            $filename = $this->respostasDir . '/resposta_' . $respostaId . '.json';
            if (!file_exists($filename)) {
                echo json_encode(['success' => false, 'message' => 'Resposta não encontrada']);
                exit;
            }
            
            // Excluir arquivo
            unlink($filename);
            
            echo json_encode(['success' => true, 'message' => 'Resposta excluída com sucesso!']);
            
        } catch (\Exception $e) {
            error_log('Erro ao excluir resposta: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir resposta']);
        }
        exit;
    }
    
    /**
     * Dashboard com estatísticas e gráficos NPS
     */
    public function dashboard()
    {
        // Verificar autenticação
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? '';
        
        // Buscar lista de formulários para o filtro
        $formularios = [];
        $formFiles = glob($this->storageDir . '/formulario_*.json');
        foreach ($formFiles as $file) {
            $form = json_decode(file_get_contents($file), true);
            // Filtrar por usuário ou admin
            if ($form['criado_por'] == $userId || $userRole === 'admin' || $userRole === 'super_admin') {
                $formularios[] = [
                    'id' => $form['id'],
                    'titulo' => $form['titulo']
                ];
            }
        }
        
        // Ordenar formulários por título
        usort($formularios, function($a, $b) {
            return strcmp($a['titulo'], $b['titulo']);
        });
        
        // Coletar estatísticas gerais (sem filtro)
        $stats = $this->coletarEstatisticas($userId, $userRole);
        
        $title = 'Dashboard NPS - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/nps/dashboard.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
    
    /**
     * Página de debug para diagnosticar problemas de permissão/rotas
     */
    public function debug()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $userEmail = $_SESSION['user_email'] ?? '';
        $userRole = $_SESSION['user_role'] ?? '';

        // Permitir apenas administrador/super_admin ou master user (sem consultas adicionais)
        $masterEmail = '\App\Services\MasterUserService'::getMasterEmail();
        $isAllowed = in_array($userRole, ['admin', 'super_admin'], true) || (strtolower($userEmail) === strtolower($masterEmail));

        if (!$isAllowed) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Acesso negado ao debug NPS',
            ]);
            exit;
        }

        $response = [
            'success' => true,
            'session' => [
                'user_id' => $userId,
                'user_email' => $userEmail,
                'user_role' => $userRole,
            ],
            'storage' => [
                'storage_dir' => $this->storageDir,
                'respostas_dir' => $this->respostasDir,
                'storage_exists' => is_dir($this->storageDir),
                'respostas_exists' => is_dir($this->respostasDir),
                'formularios_count' => count(glob($this->storageDir . '/formulario_*.json')),
                'respostas_count' => count(glob($this->respostasDir . '/resposta_*.json')),
            ],
            'permissions' => [
                'module_exists' => null,
                'user_has_view' => null,
                'user_has_edit' => null,
                'user_has_delete' => null,
                'module_records' => [],
            ],
            'logs' => [
                'today_file' => null,
                'today_tail' => null,
            ],
        ];

        // Verificar permissões pelo serviço
        try {
            $response['permissions']['user_has_view'] = PermissionService::hasPermission($userId, 'nps', 'view');
            $response['permissions']['user_has_edit'] = PermissionService::hasPermission($userId, 'nps', 'edit');
            $response['permissions']['user_has_delete'] = PermissionService::hasPermission($userId, 'nps', 'delete');
        } catch (\Throwable $permissionError) {
            $response['permissions']['error'] = $permissionError->getMessage();
        }

        // Consultar banco de dados sobre o módulo nps
        try {
            $db = Database::getInstance();

            // Verificar existência do módulo na tabela profile_permissions
            $stmt = $db->prepare("SELECT COUNT(*) FROM profile_permissions WHERE module = 'nps'");
            $stmt->execute();
            $response['permissions']['module_exists'] = $stmt->fetchColumn() > 0;

            // Trazer até 10 registros do módulo
            $stmt = $db->prepare("SELECT p.name AS profile_name, pp.can_view, pp.can_edit, pp.can_delete, pp.can_export FROM profile_permissions pp JOIN profiles p ON pp.profile_id = p.id WHERE pp.module = 'nps' LIMIT 10");
            $stmt->execute();
            $response['permissions']['module_records'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $dbError) {
            $response['permissions']['db_error'] = $dbError->getMessage();
        }

        // Ler últimos logs do dia
        $logFile = __DIR__ . '/../../storage/logs/app_' . date('Y-m-d') . '.log';
        if (file_exists($logFile)) {
            $response['logs']['today_file'] = $logFile;
            $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines !== false) {
                $response['logs']['today_tail'] = array_slice($lines, -20);
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Coletar estatísticas para o dashboard
     * @param int $userId ID do usuário
     * @param string $userRole Role do usuário
     * @param string|null $formularioId ID do formulário para filtrar (opcional)
     */
    private function coletarEstatisticas($userId, $userRole, $formularioId = null)
    {
        $stats = [
            'total_formularios' => 0,
            'total_respostas' => 0,
            'nps_medio' => 0,
            'promotores' => 0,
            'neutros' => 0,
            'detratores' => 0,
            'formularios_ativos' => 0,
            'formularios_por_mes' => [],
            'respostas_por_dia' => [],
            'distribuicao_notas' => array_fill(0, 6, 0), // 0-5
        ];
        
        // Contar formulários
        $formFiles = glob($this->storageDir . '/formulario_*.json');
        foreach ($formFiles as $file) {
            $form = json_decode(file_get_contents($file), true);
            // Filtrar por usuário ou admin
            if ($form['criado_por'] == $userId || $userRole === 'admin' || $userRole === 'super_admin') {
                $stats['total_formularios']++;
                if ($form['ativo']) {
                    $stats['formularios_ativos']++;
                }
            }
        }
        
        // Coletar respostas
        $respostaFiles = glob($this->respostasDir . '/resposta_*.json');
        $ultimosDias = [];
        
        foreach ($respostaFiles as $file) {
            $resposta = json_decode(file_get_contents($file), true);
            
            // Aplicar filtro por formulário se especificado
            if ($formularioId !== null && $resposta['formulario_id'] !== $formularioId) {
                continue;
            }
            
            // Verificar se a resposta pertence a um formulário do usuário
            $formFile = $this->storageDir . '/formulario_' . $resposta['formulario_id'] . '.json';
            if (file_exists($formFile)) {
                $form = json_decode(file_get_contents($formFile), true);
                
                if ($form['criado_por'] == $userId || $userRole === 'admin' || $userRole === 'super_admin') {
                    $stats['total_respostas']++;
                    
                    // Analisar respostas para calcular NPS
                    // IMPORTANTE: Contar apenas a PRIMEIRA pergunta numérica 0-5 por resposta
                    $notaContabilizada = false;
                    foreach ($resposta['respostas'] as $r) {
                        if (!$notaContabilizada && is_numeric($r['resposta']) && $r['resposta'] >= 0 && $r['resposta'] <= 5) {
                            $nota = (int)$r['resposta'];
                            $stats['distribuicao_notas'][$nota]++;
                            
                            // Escala 0-5: Promotores (4-5), Neutros (3), Detratores (0-2)
                            if ($nota >= 4) {
                                $stats['promotores']++;
                            } elseif ($nota == 3) {
                                $stats['neutros']++;
                            } else {
                                $stats['detratores']++;
                            }
                            
                            // Marca que já contabilizou uma nota para essa resposta
                            $notaContabilizada = true;
                        }
                    }
                    
                    // Respostas por dia (últimos 30 dias)
                    $data = date('Y-m-d', strtotime($resposta['respondido_em']));
                    if (!isset($ultimosDias[$data])) {
                        $ultimosDias[$data] = 0;
                    }
                    $ultimosDias[$data]++;
                }
            }
        }
        
        // Calcular NPS
        $totalAvaliacoes = $stats['promotores'] + $stats['neutros'] + $stats['detratores'];
        if ($totalAvaliacoes > 0) {
            $stats['nps_medio'] = round((($stats['promotores'] - $stats['detratores']) / $totalAvaliacoes) * 100);
        }
        
        // Preparar dados dos últimos 30 dias
        for ($i = 29; $i >= 0; $i--) {
            $data = date('Y-m-d', strtotime("-$i days"));
            $stats['respostas_por_dia'][] = [
                'data' => date('d/m', strtotime($data)),
                'total' => $ultimosDias[$data] ?? 0
            ];
        }
        
        return $stats;
    }
    
    /**
     * API AJAX: Obter dados do dashboard com filtro opcional
     */
    public function getDashboardData()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }
            
            $userId = $_SESSION['user_id'];
            $userRole = $_SESSION['user_role'] ?? '';
            $formularioId = $_GET['formulario_id'] ?? null;
            
            // Se formulario_id for 'todos', passar null para ver todos
            if ($formularioId === 'todos') {
                $formularioId = null;
            }
            
            // Coletar estatísticas (com ou sem filtro)
            $stats = $this->coletarEstatisticas($userId, $userRole, $formularioId);
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            error_log('Erro ao carregar dados do dashboard: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar dados']);
        }
        exit;
    }
    
    /**
     * Exportar relatório NPS em CSV
     */
    public function exportarCSV()
    {
        // Verificar autenticação
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? '';
        
        // Coletar todas as respostas
        $dadosExportacao = [];
        $respostaFiles = glob($this->respostasDir . '/resposta_*.json');
        
        foreach ($respostaFiles as $file) {
            $resposta = json_decode(file_get_contents($file), true);
            
            // Verificar se pertence a formulário do usuário
            $formFile = $this->storageDir . '/formulario_' . $resposta['formulario_id'] . '.json';
            if (file_exists($formFile)) {
                $form = json_decode(file_get_contents($formFile), true);
                
                if ($form['criado_por'] == $userId || $userRole === 'admin' || $userRole === 'super_admin') {
                    // Preparar dados para exportação
                    $linha = [
                        'formulario' => $resposta['formulario_titulo'] ?? $form['titulo'],
                        'respondente_nome' => $resposta['nome'] ?? 'Anônimo',
                        'respondente_email' => $resposta['email'] ?? '',
                        'data_resposta' => date('d/m/Y H:i', strtotime($resposta['respondido_em'])),
                    ];
                    
                    // Adicionar cada resposta como coluna
                    $notaNPS = null;
                    foreach ($resposta['respostas'] as $index => $r) {
                        $pergunta = $r['pergunta'] ?? "Pergunta " . ($index + 1);
                        $respostaTexto = $r['resposta'];
                        
                        // Capturar primeira nota NPS (0-10)
                        if ($notaNPS === null && is_numeric($respostaTexto) && $respostaTexto >= 0 && $respostaTexto <= 10) {
                            $notaNPS = (int)$respostaTexto;
                        }
                        
                        $linha[$pergunta] = $respostaTexto;
                    }
                    
                    // Adicionar classificação NPS
                    if ($notaNPS !== null) {
                        if ($notaNPS >= 9) {
                            $linha['classificacao_nps'] = 'Promotor';
                        } elseif ($notaNPS >= 7) {
                            $linha['classificacao_nps'] = 'Neutro';
                        } else {
                            $linha['classificacao_nps'] = 'Detrator';
                        }
                        $linha['nota_nps'] = $notaNPS;
                    } else {
                        $linha['classificacao_nps'] = 'N/A';
                        $linha['nota_nps'] = '';
                    }
                    
                    $dadosExportacao[] = $linha;
                }
            }
        }
        
        // Ordenar por data (mais recente primeiro)
        usort($dadosExportacao, function($a, $b) {
            return strtotime(str_replace('/', '-', $b['data_resposta'])) - strtotime(str_replace('/', '-', $a['data_resposta']));
        });
        
        // Gerar arquivo CSV
        $filename = 'relatorio_nps_' . date('Y-m-d_His') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        // Abrir saída
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8 (compatibilidade com Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos (primeira linha)
        if (!empty($dadosExportacao)) {
            fputcsv($output, array_keys($dadosExportacao[0]), ';');
            
            // Dados
            foreach ($dadosExportacao as $linha) {
                fputcsv($output, $linha, ';');
            }
        } else {
            // Arquivo vazio
            fputcsv($output, ['Mensagem'], ';');
            fputcsv($output, ['Nenhuma resposta encontrada'], ';');
        }
        
        fclose($output);
        exit;
    }
}
