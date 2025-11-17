<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\EmailService;
use PDO;

class NaoConformidadesController
{
    private $db;
    private $uploadDir;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->uploadDir = __DIR__ . '/../../uploads/nao-conformidades/';
        
        // Criar diretório se não existir
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Página principal com abas
     */
    public function index()
    {
        // Verificar autenticação
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin']);

        // Inicializar arrays vazios
        $todasNcs = [];
        $pendentes = [];
        $emAndamento = [];
        $solucionadas = [];
        $usuarios = [];

        try {
            // Buscar todas as NCs
            $stmt = $this->db->prepare("
                SELECT 
                    nc.*,
                    uc.name as criador_nome,
                    ur.name as responsavel_nome,
                    ur.email as responsavel_email,
                    ua.name as acao_nome,
                    us.name as solucao_nome,
                    COUNT(DISTINCT a.id) as total_anexos
                FROM nao_conformidades nc
                LEFT JOIN users uc ON nc.usuario_criador_id = uc.id
                LEFT JOIN users ur ON nc.usuario_responsavel_id = ur.id
                LEFT JOIN users ua ON nc.usuario_acao_id = ua.id
                LEFT JOIN users us ON nc.usuario_solucao_id = us.id
                LEFT JOIN nao_conformidades_anexos a ON nc.id = a.nc_id
                GROUP BY nc.id
                ORDER BY nc.created_at DESC
            ");
            $stmt->execute();
            $todasNcs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Separar por status
            if (!empty($todasNcs)) {
                $pendentes = array_filter($todasNcs, function($nc) { 
                    return $nc['status'] === 'pendente'; 
                });
                $emAndamento = array_filter($todasNcs, function($nc) { 
                    return $nc['status'] === 'em_andamento'; 
                });
                $solucionadas = array_filter($todasNcs, function($nc) { 
                    return $nc['status'] === 'solucionada'; 
                });
            }

            // Buscar todos os usuários para o combo
            // Tenta com active, se falhar busca todos
            try {
                $stmt = $this->db->query("SELECT id, name, email FROM users WHERE active = 1 ORDER BY name");
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                // Se coluna active não existir, buscar todos
                $stmt = $this->db->query("SELECT id, name, email FROM users ORDER BY name");
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

        } catch (\Exception $e) {
            error_log("Erro ao carregar NCs: " . $e->getMessage());
            // Continua com arrays vazios
        }

        // Usar o layout padrão
        $title = 'Não Conformidades - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/nao-conformidades/index.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    /**
     * Criar nova NC
     */
    public function criar()
    {
        header('Content-Type: application/json');

        try {
            // Verificar se usuário está logado
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }

            // Validar campos
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $responsavelId = (int)($_POST['responsavel_id'] ?? 0);

            if (empty($titulo) || empty($descricao) || !$responsavelId) {
                echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
                exit;
            }

            $this->db->beginTransaction();

            // Inserir NC
            $stmt = $this->db->prepare("
                INSERT INTO nao_conformidades 
                (titulo, descricao, usuario_criador_id, usuario_responsavel_id, status, created_at)
                VALUES (?, ?, ?, ?, 'pendente', NOW())
            ");
            $stmt->execute([$titulo, $descricao, $_SESSION['user_id'], $responsavelId]);
            $ncId = $this->db->lastInsertId();

            // Processar uploads
            if (isset($_FILES['anexos']) && !empty($_FILES['anexos']['name'][0])) {
                $this->processarUploads($ncId, $_FILES['anexos'], 'evidencia_inicial', $_SESSION['user_id']);
            }

            $this->db->commit();

            // Enviar e-mail para responsável
            $this->enviarEmailNovaNc($ncId, $responsavelId);

            echo json_encode([
                'success' => true,
                'message' => 'NC criada com sucesso!',
                'nc_id' => $ncId
            ]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erro ao criar NC: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao criar NC: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Detalhes de uma NC
     */
    public function detalhes($id)
    {
        header('Content-Type: application/json');

        try {
            $stmt = $this->db->prepare("
                SELECT 
                    nc.*,
                    uc.name as criador_nome,
                    uc.email as criador_email,
                    ur.name as responsavel_nome,
                    ur.email as responsavel_email,
                    ua.name as acao_nome,
                    us.name as solucao_nome
                FROM nao_conformidades nc
                LEFT JOIN users uc ON nc.usuario_criador_id = uc.id
                LEFT JOIN users ur ON nc.usuario_responsavel_id = ur.id
                LEFT JOIN users ua ON nc.usuario_acao_id = ua.id
                LEFT JOIN users us ON nc.usuario_solucao_id = us.id
                WHERE nc.id = ?
            ");
            $stmt->execute([$id]);
            $nc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$nc) {
                echo json_encode(['success' => false, 'message' => 'NC não encontrada']);
                exit;
            }

            // Buscar anexos
            $stmt = $this->db->prepare("
                SELECT a.*, u.name as usuario_nome
                FROM nao_conformidades_anexos a
                LEFT JOIN users u ON a.usuario_id = u.id
                WHERE a.nc_id = ?
                ORDER BY a.created_at ASC
            ");
            $stmt->execute([$id]);
            $anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'nc' => $nc,
                'anexos' => $anexos
            ]);

        } catch (\Exception $e) {
            error_log("Erro ao buscar detalhes da NC: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar detalhes']);
        }
        exit;
    }

    /**
     * Registrar ação corretiva
     */
    public function registrarAcao($id)
    {
        header('Content-Type: application/json');

        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }

            $acao = trim($_POST['acao_corretiva'] ?? '');
            if (empty($acao)) {
                echo json_encode(['success' => false, 'message' => 'Digite a ação corretiva']);
                exit;
            }

            // Verificar se o usuário é o responsável
            $stmt = $this->db->prepare("SELECT usuario_responsavel_id, status FROM nao_conformidades WHERE id = ?");
            $stmt->execute([$id]);
            $nc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$nc) {
                echo json_encode(['success' => false, 'message' => 'NC não encontrada']);
                exit;
            }

            if ($nc['usuario_responsavel_id'] != $_SESSION['user_id']) {
                // Permitir admin também
                $isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin']);
                if (!$isAdmin) {
                    echo json_encode(['success' => false, 'message' => 'Apenas o responsável pode registrar ação']);
                    exit;
                }
            }

            $this->db->beginTransaction();

            // Atualizar NC
            $stmt = $this->db->prepare("
                UPDATE nao_conformidades 
                SET acao_corretiva = ?, 
                    usuario_acao_id = ?, 
                    data_acao = NOW(),
                    status = 'em_andamento',
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$acao, $_SESSION['user_id'], $id]);

            // Processar uploads de evidências
            if (isset($_FILES['anexos']) && !empty($_FILES['anexos']['name'][0])) {
                $this->processarUploads($id, $_FILES['anexos'], 'evidencia_acao', $_SESSION['user_id']);
            }

            $this->db->commit();

            // Enviar e-mail para o criador
            $this->enviarEmailAcaoRegistrada($id);

            echo json_encode(['success' => true, 'message' => 'Ação registrada com sucesso!']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erro ao registrar ação: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar ação: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Marcar NC como solucionada
     */
    public function marcarSolucionada($id)
    {
        header('Content-Type: application/json');

        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }

            // Verificar permissão (criador ou responsável)
            $stmt = $this->db->prepare("
                SELECT usuario_criador_id, usuario_responsavel_id 
                FROM nao_conformidades WHERE id = ?
            ");
            $stmt->execute([$id]);
            $nc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$nc) {
                echo json_encode(['success' => false, 'message' => 'NC não encontrada']);
                exit;
            }

            $isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin']);
            $isAutorizado = $nc['usuario_criador_id'] == $_SESSION['user_id'] || 
                           $nc['usuario_responsavel_id'] == $_SESSION['user_id'] ||
                           $isAdmin;

            if (!$isAutorizado) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                exit;
            }

            // Atualizar NC
            $stmt = $this->db->prepare("
                UPDATE nao_conformidades 
                SET status = 'solucionada',
                    usuario_solucao_id = ?,
                    data_solucao = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $id]);

            // Enviar e-mail de conclusão
            $this->enviarEmailNcSolucionada($id);

            echo json_encode(['success' => true, 'message' => 'NC marcada como solucionada!']);

        } catch (\Exception $e) {
            error_log("Erro ao marcar como solucionada: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao marcar como solucionada']);
        }
        exit;
    }

    /**
     * Download de anexo
     */
    public function downloadAnexo($id)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }

            $stmt = $this->db->prepare("SELECT * FROM nao_conformidades_anexos WHERE id = ?");
            $stmt->execute([$id]);
            $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anexo) {
                echo "Anexo não encontrado";
                exit;
            }

            $filePath = $this->uploadDir . $anexo['caminho_arquivo'];
            
            if (!file_exists($filePath)) {
                echo "Arquivo não encontrado no servidor";
                exit;
            }

            header('Content-Type: ' . $anexo['tipo_arquivo']);
            header('Content-Disposition: attachment; filename="' . $anexo['nome_arquivo'] . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;

        } catch (\Exception $e) {
            error_log("Erro ao baixar anexo: " . $e->getMessage());
            echo "Erro ao baixar anexo";
            exit;
        }
    }

    /**
     * Processar uploads de arquivos
     */
    private function processarUploads($ncId, $files, $tipoAnexo, $usuarioId)
    {
        $arquivosPermitidos = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf', 'video/mp4'];
        $tamanhoMaximo = 30 * 1024 * 1024; // 30MB

        $count = count($files['name']);
        
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $nomeArquivo = $files['name'][$i];
            $tipoArquivo = $files['type'][$i];
            $tamanhoArquivo = $files['size'][$i];
            $tmpName = $files['tmp_name'][$i];

            // Validar tipo
            if (!in_array($tipoArquivo, $arquivosPermitidos)) {
                throw new \Exception("Tipo de arquivo não permitido: $nomeArquivo");
            }

            // Validar tamanho
            if ($tamanhoArquivo > $tamanhoMaximo) {
                throw new \Exception("Arquivo muito grande: $nomeArquivo");
            }

            // Gerar nome único
            $extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
            $nomeUnico = uniqid() . '_' . time() . '.' . $extensao;
            $caminhoDestino = $this->uploadDir . $nomeUnico;

            // Mover arquivo
            if (!move_uploaded_file($tmpName, $caminhoDestino)) {
                throw new \Exception("Erro ao salvar arquivo: $nomeArquivo");
            }

            // Salvar no banco
            $stmt = $this->db->prepare("
                INSERT INTO nao_conformidades_anexos 
                (nc_id, nome_arquivo, tipo_arquivo, tamanho_bytes, caminho_arquivo, tipo_anexo, usuario_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $ncId,
                $nomeArquivo,
                $tipoArquivo,
                $tamanhoArquivo,
                $nomeUnico,
                $tipoAnexo,
                $usuarioId
            ]);
        }
    }

    /**
     * Enviar e-mail para nova NC
     */
    private function enviarEmailNovaNc($ncId, $responsavelId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT nc.*, u.name as responsavel_nome, u.email as responsavel_email,
                       c.name as criador_nome
                FROM nao_conformidades nc
                JOIN users u ON nc.usuario_responsavel_id = u.id
                JOIN users c ON nc.usuario_criador_id = c.id
                WHERE nc.id = ?
            ");
            $stmt->execute([$ncId]);
            $nc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$nc) return;

            $assunto = "🚨 Nova Não Conformidade: {$nc['titulo']}";
            $mensagem = "
                <h2>Nova Não Conformidade Registrada</h2>
                <p>Olá <strong>{$nc['responsavel_nome']}</strong>,</p>
                <p>Uma nova Não Conformidade foi registrada e você foi designado como responsável pela correção.</p>
                
                <h3>Detalhes:</h3>
                <ul>
                    <li><strong>ID:</strong> #{$ncId}</li>
                    <li><strong>Título:</strong> {$nc['titulo']}</li>
                    <li><strong>Apontado por:</strong> {$nc['criador_nome']}</li>
                    <li><strong>Data:</strong> " . date('d/m/Y H:i', strtotime($nc['created_at'])) . "</li>
                </ul>
                
                <h3>Descrição:</h3>
                <p>{$nc['descricao']}</p>
                
                <p><a href='" . $_SERVER['HTTP_HOST'] . "/nao-conformidades' style='background:#3b82f6;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;'>Acessar Sistema</a></p>
                
                <p>Por favor, acesse o sistema para registrar a ação corretiva.</p>
            ";

            EmailService::send($nc['responsavel_email'], $assunto, $mensagem);
        } catch (\Exception $e) {
            error_log("Erro ao enviar e-mail de nova NC: " . $e->getMessage());
        }
    }

    /**
     * Enviar e-mail quando ação for registrada
     */
    private function enviarEmailAcaoRegistrada($ncId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT nc.*, u.email as criador_email, u.name as criador_nome,
                       r.name as responsavel_nome
                FROM nao_conformidades nc
                JOIN users u ON nc.usuario_criador_id = u.id
                JOIN users r ON nc.usuario_responsavel_id = r.id
                WHERE nc.id = ?
            ");
            $stmt->execute([$ncId]);
            $nc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$nc) return;

            $assunto = "✅ Ação Registrada na NC #{$ncId}: {$nc['titulo']}";
            $mensagem = "
                <h2>Ação Corretiva Registrada</h2>
                <p>Olá <strong>{$nc['criador_nome']}</strong>,</p>
                <p>O responsável <strong>{$nc['responsavel_nome']}</strong> registrou uma ação corretiva para a NC #{$ncId}.</p>
                
                <h3>NC:</h3>
                <p><strong>{$nc['titulo']}</strong></p>
                
                <h3>Ação Corretiva:</h3>
                <p>{$nc['acao_corretiva']}</p>
                
                <p><a href='" . $_SERVER['HTTP_HOST'] . "/nao-conformidades' style='background:#3b82f6;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;'>Acessar Sistema</a></p>
            ";

            EmailService::send($nc['criador_email'], $assunto, $mensagem);
        } catch (\Exception $e) {
            error_log("Erro ao enviar e-mail de ação registrada: " . $e->getMessage());
        }
    }

    /**
     * Enviar e-mail quando NC for solucionada
     */
    private function enviarEmailNcSolucionada($ncId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT nc.*, 
                       c.email as criador_email, c.name as criador_nome,
                       r.email as responsavel_email, r.name as responsavel_nome
                FROM nao_conformidades nc
                JOIN users c ON nc.usuario_criador_id = c.id
                JOIN users r ON nc.usuario_responsavel_id = r.id
                WHERE nc.id = ?
            ");
            $stmt->execute([$ncId]);
            $nc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$nc) return;

            $assunto = "✅ NC Solucionada: {$nc['titulo']}";
            $mensagem = "
                <h2>Não Conformidade Solucionada</h2>
                <p>A NC #{$ncId} foi marcada como <strong>SOLUCIONADA</strong>.</p>
                
                <h3>Detalhes:</h3>
                <ul>
                    <li><strong>Título:</strong> {$nc['titulo']}</li>
                    <li><strong>Responsável:</strong> {$nc['responsavel_nome']}</li>
                    <li><strong>Data de Solução:</strong> " . date('d/m/Y H:i') . "</li>
                </ul>
                
                <p><a href='" . $_SERVER['HTTP_HOST'] . "/nao-conformidades' style='background:#10b981;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;'>Acessar Sistema</a></p>
            ";

            // Enviar para criador e responsável
            EmailService::send($nc['criador_email'], $assunto, $mensagem);
            if ($nc['criador_email'] !== $nc['responsavel_email']) {
                EmailService::send($nc['responsavel_email'], $assunto, $mensagem);
            }
        } catch (\Exception $e) {
            error_log("Erro ao enviar e-mail de NC solucionada: " . $e->getMessage());
        }
    }
}
