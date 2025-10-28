<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use App\Services\EmailService;
use PDO;

class HomologacoesKanbanController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Página principal do Kanban de Homologações
     */
    public function index()
    {
        try {
            // Verificar permissão
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }

            // Inicializar arrays vazios
            $homologacoes = [
                'aguardando_recebimento' => [],
                'recebido' => [],
                'em_analise' => [],
                'em_homologacao' => [],
                'aprovado' => [],
                'reprovado' => []
            ];

            // Buscar homologações do banco
            try {
                $stmt = $this->db->query("
                    SELECT h.*, 
                           u.name as criador_nome,
                           d.nome as departamento_nome,
                           GROUP_CONCAT(DISTINCT ur.name SEPARATOR ', ') as responsaveis_nomes,
                           COUNT(DISTINCT a.id) as total_anexos
                    FROM homologacoes h
                    LEFT JOIN users u ON h.created_by = u.id
                    LEFT JOIN departments d ON h.departamento_id = d.id
                    LEFT JOIN homologacoes_responsaveis hr ON h.id = hr.homologacao_id
                    LEFT JOIN users ur ON hr.user_id = ur.id
                    LEFT JOIN homologacoes_anexos a ON h.id = a.homologacao_id
                    GROUP BY h.id
                    ORDER BY h.created_at DESC
                ");
                $todasHomologacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Agrupar por status
                foreach ($todasHomologacoes as $h) {
                    $status = $h['status'] ?? 'aguardando_recebimento';
                    if (isset($homologacoes[$status])) {
                        $homologacoes[$status][] = $h;
                    }
                }
            } catch (\Exception $e) {
                error_log("Erro ao buscar homologações: " . $e->getMessage());
            }

            // Buscar usuários ativos
            $usuarios = [];
            try {
                $stmt = $this->db->query("
                    SELECT id, name, email 
                    FROM users 
                    WHERE status = 'active' 
                    ORDER BY name ASC
                ");
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                error_log("Erro ao buscar usuários: " . $e->getMessage());
            }

            // Buscar departamentos
            $departamentos = [];
            try {
                $stmt = $this->db->query("
                    SELECT id, nome 
                    FROM departments 
                    ORDER BY nome ASC
                ");
                $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                error_log("Erro ao buscar departamentos: " . $e->getMessage());
            }

            // Verificar se pode criar (sempre true para Master User)
            $canCreate = true;

            // Renderizar via layout principal
            $title = 'Homologações - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/homologacoes/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
            
        } catch (\Exception $e) {
            error_log("Erro no módulo Homologações: " . $e->getMessage());
            die("❌ ERRO no módulo Homologações: " . $e->getMessage() . "<br><br>Linha: " . $e->getLine() . "<br>Arquivo: " . $e->getFile());
        }
    }

    /**
     * Buscar e-mails dos responsáveis de uma homologação
     */
    private function getResponsaveisEmails(int $homologacaoId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT u.email FROM homologacoes_responsaveis hr LEFT JOIN users u ON u.id = hr.user_id WHERE hr.homologacao_id = ? AND u.status = 'active' AND u.email IS NOT NULL AND u.email <> ''");
            $stmt->execute([$homologacaoId]);
            return array_values(array_filter(array_map(function($r){return $r['email'] ?? null;}, $stmt->fetchAll(PDO::FETCH_ASSOC))));
        } catch (\Exception $e) {
            error_log('Erro ao buscar e-mails de responsáveis: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Enviar e-mail de atualização de status para responsáveis
     */
    private function enviarEmailStatusHomologacao(array $destinatarios, array $homologacao, string $novoStatus, string $obs = ''): void
    {
        try {
            if (empty($destinatarios)) return;
            $email = new EmailService();

            $assunto = "SGQ - Homologação #{$homologacao['id']} atualizada para: " . strtoupper($novoStatus);
            $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
            $body = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body style='font-family: Arial,sans-serif;line-height:1.6;color:#333;max-width:680px;margin:0 auto;padding:20px;'>"
                . "<div style='background:#6b7280;color:#fff;padding:18px 24px;border-radius:10px 10px 0 0;'><h2 style='margin:0;font-size:20px;'>SGQ OTI DJ • Atualização de Status</h2></div>"
                . "<div style='background:#fff;border:1px solid #e5e7eb;border-top:none;padding:20px'>"
                . "<p style='margin:0 0 8px'>Homologação: <strong>#" . htmlspecialchars((string)$homologacao['id']) . "</strong></p>"
                . "<p style='margin:0 0 8px'>Código: <strong>" . htmlspecialchars($homologacao['cod_referencia'] ?? '') . "</strong></p>"
                . "<p style='margin:0 0 8px'>Novo status: <strong>" . htmlspecialchars($novoStatus) . "</strong></p>"
                . (!empty($obs) ? ("<p style='margin:10px 0 0'><em>Observação:</em> " . nl2br(htmlspecialchars($obs)) . "</p>") : "")
                . "<div style='text-align:center;margin:22px 0'><a href='" . $appUrl . "/homologacoes' style='background:#2563eb;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold'>Abrir Homologações</a></div>"
                . "<p style='font-size:12px;color:#6b7280;margin-top:24px'>Este email foi enviado automaticamente pelo SGQ OTI DJ.</p>"
                . "</div></body></html>";

            $ok = $email->send($destinatarios, $assunto, $body, strip_tags($body));
            if (!$ok) {
                error_log('Falha ao enviar email de atualização de status: ' . ($email->getLastError() ?? 'sem detalhes'));
            }
        } catch (\Exception $e) {
            error_log('Erro ao enviar email de atualização de status: ' . $e->getMessage());
        }
    }

    /**
     * Verificar se usuário pode criar homologações (departamento Compras)
     */
    private function canCreateHomologacao(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("
                SELECT department, profile_id
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return false;
            }

            // Admin sempre pode
            if (PermissionService::hasAdminPrivileges($userId)) {
                return true;
            }

            // Verificar se é do departamento Compras
            $department = strtolower($user['department'] ?? '');
            return in_array($department, ['compras', 'administrativo']);

        } catch (\Exception $e) {
            error_log("Erro ao verificar permissão de criação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar homologações agrupadas por status para o Kanban
     */
    private function getHomologacoesKanban(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    h.*,
                    u.name as criador_nome,
                    COUNT(DISTINCT ha.id) as total_anexos,
                    GROUP_CONCAT(DISTINCT ur.name ORDER BY ur.name SEPARATOR ', ') as responsaveis_nomes
                FROM homologacoes h
                LEFT JOIN users u ON h.created_by = u.id
                LEFT JOIN homologacoes_responsaveis hr ON h.id = hr.homologacao_id
                LEFT JOIN users ur ON hr.user_id = ur.id
                LEFT JOIN homologacoes_anexos ha ON h.id = ha.homologacao_id
                GROUP BY h.id
                ORDER BY h.created_at DESC
            ");

            $homologacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar por status
            $kanban = [
                'aguardando_recebimento' => [],
                'recebido' => [],
                'em_analise' => [],
                'em_homologacao' => [],
                'aprovado' => [],
                'reprovado' => []
            ];

            foreach ($homologacoes as $homologacao) {
                $status = $homologacao['status'] ?? 'aguardando_recebimento';
                if (isset($kanban[$status])) {
                    $kanban[$status][] = $homologacao;
                }
            }

            return $kanban;

        } catch (\Exception $e) {
            error_log("Erro ao buscar homologações: " . $e->getMessage());
            return [
                'aguardando_recebimento' => [],
                'recebido' => [],
                'em_analise' => [],
                'em_homologacao' => [],
                'aprovado' => [],
                'reprovado' => []
            ];
        }
    }

    /**
     * Buscar usuários ativos para dropdown de responsáveis
     */
    private function getUsuariosAtivos(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, name, email, department 
                FROM users 
                WHERE status = 'active' 
                ORDER BY name ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erro ao buscar usuários: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Criar nova homologação
     */
    public function store()
    {
        header('Content-Type: application/json');

        try {
            // Verificar permissão
            if (!$this->canCreateHomologacao($_SESSION['user_id'])) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Você não tem permissão para criar homologações. Apenas o departamento de Compras pode criar.'
                ]);
                exit;
            }

            // Validar dados
            $codReferencia = trim($_POST['cod_referencia'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $departamentoId = !empty($_POST['departamento_id']) ? (int)$_POST['departamento_id'] : null;
            $avisarLogistica = isset($_POST['avisar_logistica']) && $_POST['avisar_logistica'] === '1';
            $responsaveis = $_POST['responsaveis'] ?? []; // Array de IDs
            $observacao = trim($_POST['observacao'] ?? '');

            if (empty($codReferencia) || empty($descricao)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Preencha o Código de Referência e Descrição'
                ]);
                exit;
            }

            if (empty($departamentoId)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Selecione o Departamento (Localização)'
                ]);
                exit;
            }

            if (empty($responsaveis) || !is_array($responsaveis)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Selecione pelo menos um responsável'
                ]);
                exit;
            }

            $this->db->beginTransaction();

            // Inserir homologação com status inicial
            $stmt = $this->db->prepare("
                INSERT INTO homologacoes (
                    cod_referencia, 
                    descricao, 
                    departamento_id,
                    avisar_logistica, 
                    observacao,
                    status, 
                    created_by, 
                    created_at
                ) VALUES (?, ?, ?, ?, ?, 'aguardando_recebimento', ?, NOW())
            ");
            $stmt->execute([
                $codReferencia,
                $descricao,
                $departamentoId,
                $avisarLogistica ? 1 : 0,
                $observacao,
                $_SESSION['user_id']
            ]);

            $homologacaoId = $this->db->lastInsertId();

            // Inserir responsáveis
            $stmtResp = $this->db->prepare("
                INSERT INTO homologacoes_responsaveis (homologacao_id, user_id, created_at) 
                VALUES (?, ?, NOW())
            ");

            foreach ($responsaveis as $userId) {
                $stmtResp->execute([$homologacaoId, (int)$userId]);
            }

            // Registrar histórico
            $stmtHist = $this->db->prepare("
                INSERT INTO homologacoes_historico (
                    homologacao_id, 
                    status_novo, 
                    usuario_id, 
                    observacao, 
                    created_at
                )
                VALUES (?, 'aguardando_recebimento', ?, 'Homologação criada', NOW())
            ");
            $stmtHist->execute([$homologacaoId, $_SESSION['user_id']]);

            $this->db->commit();

            // Enviar notificações (async)
            $this->enviarNotificacoes($homologacaoId, $responsaveis, $avisarLogistica);

            echo json_encode([
                'success' => true,
                'message' => 'Homologação criada com sucesso',
                'homologacao_id' => $homologacaoId
            ]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao criar homologação: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao criar homologação: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Atualizar status da homologação
     */
    public function updateStatus()
    {
        header('Content-Type: application/json');

        try {
            $homologacaoId = (int)($_POST['homologacao_id'] ?? 0);
            $novoStatus = $_POST['status'] ?? '';
            $localHomologacao = trim($_POST['local_homologacao'] ?? '');
            $dataInicioHomologacao = trim($_POST['data_inicio_homologacao'] ?? '');
            $alertaFinalizacao = trim($_POST['alerta_finalizacao'] ?? '');
            $observacao = trim($_POST['observacao'] ?? '');

            if (!$homologacaoId || !$novoStatus) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
                exit;
            }

            // Buscar status anterior
            $stmt = $this->db->prepare("SELECT status FROM homologacoes WHERE id = ?");
            $stmt->execute([$homologacaoId]);
            $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$homologacao) {
                echo json_encode(['success' => false, 'message' => 'Homologação não encontrada']);
                exit;
            }

            $this->db->beginTransaction();

            // Preparar update dinâmico
            $updates = ["status = ?", "updated_at = NOW()"];
            $params = [$novoStatus];

            if (!empty($localHomologacao)) {
                $updates[] = "local_homologacao = ?";
                $params[] = $localHomologacao;
            }

            if (!empty($dataInicioHomologacao)) {
                $updates[] = "data_inicio_homologacao = ?";
                $params[] = $dataInicioHomologacao;
            }

            if (!empty($alertaFinalizacao)) {
                $updates[] = "alerta_finalizacao = ?";
                $params[] = $alertaFinalizacao;
            }

            $params[] = $homologacaoId;

            // Atualizar status e campos adicionais
            $sql = "UPDATE homologacoes SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // Registrar no histórico
            $stmt = $this->db->prepare("
                INSERT INTO homologacoes_historico (
                    homologacao_id, 
                    status_anterior, 
                    status_novo, 
                    usuario_id, 
                    observacao, 
                    created_at
                )
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $homologacaoId,
                $homologacao['status'],
                $novoStatus,
                $_SESSION['user_id'],
                $observacao ?: "Status alterado de {$homologacao['status']} para {$novoStatus}"
            ]);

            $this->db->commit();

            // Buscar dados completos da homologação já com o novo status
            $stmt = $this->db->prepare("SELECT * FROM homologacoes WHERE id = ?");
            $stmt->execute([$homologacaoId]);
            $homologacaoAtual = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['id' => $homologacaoId, 'status' => $novoStatus];

            // Enviar e-mail para TODOS os responsáveis marcados desta homologação
            $emailsResp = $this->getResponsaveisEmails($homologacaoId);
            if (!empty($emailsResp)) {
                $this->enviarEmailStatusHomologacao($emailsResp, $homologacaoAtual, $novoStatus, $observacao);
            }

            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao atualizar status: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
        }
        exit;
    }

    /**
     * Buscar detalhes de uma homologação para exibição no card
     */
    public function details($id)
    {
        header('Content-Type: application/json');

        try {
            $stmt = $this->db->prepare("
                SELECT 
                    h.*,
                    u.name as criador_nome,
                    u.email as criador_email
                FROM homologacoes h
                LEFT JOIN users u ON h.created_by = u.id
                WHERE h.id = ?
            ");
            $stmt->execute([(int)$id]);
            $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$homologacao) {
                echo json_encode(['success' => false, 'message' => 'Homologação não encontrada']);
                exit;
            }

            // Buscar responsáveis
            $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.email
                FROM homologacoes_responsaveis hr
                LEFT JOIN users u ON hr.user_id = u.id
                WHERE hr.homologacao_id = ?
                ORDER BY u.name ASC
            ");
            $stmt->execute([(int)$id]);
            $responsaveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar histórico
            $stmt = $this->db->prepare("
                SELECT hh.*, u.name as usuario_nome
                FROM homologacoes_historico hh
                LEFT JOIN users u ON hh.usuario_id = u.id
                WHERE hh.homologacao_id = ?
                ORDER BY hh.created_at DESC
            ");
            $stmt->execute([(int)$id]);
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar anexos
            $stmt = $this->db->prepare("
                SELECT id, nome_arquivo, tipo_arquivo, tamanho_arquivo, created_at
                FROM homologacoes_anexos
                WHERE homologacao_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([(int)$id]);
            $anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'homologacao' => $homologacao,
                'responsaveis' => $responsaveis,
                'historico' => $historico,
                'anexos' => $anexos
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao buscar detalhes: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar detalhes']);
        }
        exit;
    }

    /**
     * Upload de evidências/anexos
     */
    public function uploadAnexo()
    {
        header('Content-Type: application/json');

        try {
            $homologacaoId = (int)($_POST['homologacao_id'] ?? 0);

            if (!$homologacaoId) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }

            if (!isset($_FILES['anexo']) || $_FILES['anexo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado']);
                exit;
            }

            $arquivo = $_FILES['anexo'];
            $nomeArquivo = $arquivo['name'];
            $tipoArquivo = $arquivo['type'];
            $tamanhoArquivo = $arquivo['size'];

            // Validar tamanho (5MB max)
            if ($tamanhoArquivo > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo: 5MB']);
                exit;
            }

            // Ler conteúdo do arquivo
            $conteudoArquivo = file_get_contents($arquivo['tmp_name']);

            // Inserir no banco
            $stmt = $this->db->prepare("
                INSERT INTO homologacoes_anexos (
                    homologacao_id, 
                    nome_arquivo, 
                    arquivo_blob, 
                    tipo_arquivo, 
                    tamanho_arquivo, 
                    uploaded_by, 
                    created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $homologacaoId,
                $nomeArquivo,
                $conteudoArquivo,
                $tipoArquivo,
                $tamanhoArquivo,
                $_SESSION['user_id']
            ]);

            echo json_encode([
                'success' => true, 
                'message' => 'Anexo enviado com sucesso',
                'anexo_id' => $this->db->lastInsertId()
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao fazer upload: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao enviar anexo']);
        }
        exit;
    }

    /**
     * Download de anexo
     */
    public function downloadAnexo($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT nome_arquivo, arquivo_blob, tipo_arquivo
                FROM homologacoes_anexos
                WHERE id = ?
            ");
            $stmt->execute([(int)$id]);
            $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anexo) {
                http_response_code(404);
                echo "Anexo não encontrado";
                exit;
            }

            header('Content-Type: ' . $anexo['tipo_arquivo']);
            header('Content-Disposition: attachment; filename="' . $anexo['nome_arquivo'] . '"');
            header('Content-Length: ' . strlen($anexo['arquivo_blob']));

            echo $anexo['arquivo_blob'];

        } catch (\Exception $e) {
            error_log('Erro ao fazer download: ' . $e->getMessage());
            http_response_code(500);
            echo "Erro ao fazer download";
        }
        exit;
    }

    /**
     * Deletar homologação
     */
    public function delete()
    {
        header('Content-Type: application/json');

        try {
            PermissionService::requirePermission($_SESSION['user_id'], 'homologacoes', 'delete');

            $homologacaoId = (int)($_POST['id'] ?? 0);

            if (!$homologacaoId) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }

            $stmt = $this->db->prepare("DELETE FROM homologacoes WHERE id = ?");
            $stmt->execute([$homologacaoId]);

            echo json_encode(['success' => true, 'message' => 'Homologação excluída com sucesso']);

        } catch (\Exception $e) {
            error_log('Erro ao excluir: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir homologação']);
        }
        exit;
    }

    /**
     * Enviar notificações por email e sininho
     */
    private function enviarNotificacoes(int $homologacaoId, array $responsaveis, bool $avisarLogistica)
    {
        try {
            // Buscar dados da homologação
            $stmt = $this->db->prepare("
                SELECT h.*, u.name as criador_nome 
                FROM homologacoes h
                LEFT JOIN users u ON h.created_by = u.id
                WHERE h.id = ?
            ");
            $stmt->execute([$homologacaoId]);
            $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$homologacao) {
                return;
            }

            // Notificar responsáveis (sininho + email)
            foreach ($responsaveis as $userId) {
                $this->criarNotificacao(
                    (int)$userId, 
                    $homologacaoId, 
                    "Você foi designado como responsável pela homologação #{$homologacaoId} - {$homologacao['cod_referencia']}"
                );
            }

            // Buscar emails dos responsáveis
            $emailsResponsaveis = [];
            if (!empty($responsaveis)) {
                $in  = str_repeat('?,', count($responsaveis) - 1) . '?';
                $stmtEmails = $this->db->prepare("SELECT name, email FROM users WHERE id IN ($in) AND status = 'active'");
                $stmtEmails->execute(array_map('intval', $responsaveis));
                $emailsResponsaveis = $stmtEmails->fetchAll(PDO::FETCH_ASSOC);
            }

            // Notificar logística se solicitado
            if ($avisarLogistica) {
                $stmtLog = $this->db->query("
                    SELECT id, email, name 
                    FROM users 
                    WHERE LOWER(department) = 'logistica' 
                    AND status = 'active'
                ");
                $logisticaUsers = $stmtLog->fetchAll(PDO::FETCH_ASSOC);

                foreach ($logisticaUsers as $user) {
                    $this->criarNotificacao(
                        (int)$user['id'], 
                        $homologacaoId, 
                        "Nova homologação aguardando recebimento: #{$homologacaoId} - {$homologacao['cod_referencia']}"
                    );
                }

                // Enviar email para logística
                $emailsLogistica = array_values(array_filter(array_map(function($u){ return $u['email'] ?? null; }, $logisticaUsers)));

                if (!empty($emailsLogistica)) {
                    $this->enviarEmailHomologacao($emailsLogistica, $homologacao, 'logistica');
                }
            }

            // Enviar email para responsáveis
            if (!empty($emailsResponsaveis)) {
                $emails = array_values(array_filter(array_map(function($r){ return $r['email'] ?? null; }, $emailsResponsaveis)));
                if (!empty($emails)) {
                    $this->enviarEmailHomologacao($emails, $homologacao, 'responsavel');
                }
            }

        } catch (\Exception $e) {
            error_log("Erro ao enviar notificações: " . $e->getMessage());
        }
    }

    /**
     * Criar notificação no sistema (sininho)
     */
    private function criarNotificacao(int $userId, int $homologacaoId, string $mensagem)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (
                    user_id, 
                    type, 
                    title, 
                    message, 
                    reference_type, 
                    reference_id, 
                    created_at
                )
                VALUES (?, 'homologacao', 'Nova Homologação', ?, 'homologacao', ?, NOW())
            ");
            $stmt->execute([$userId, $mensagem, $homologacaoId]);
        } catch (\Exception $e) {
            error_log("Erro ao criar notificação: " . $e->getMessage());
        }
    }

    /**
     * Envia email para responsáveis e/ou logística sobre a homologação
     */
    private function enviarEmailHomologacao(array $destinatarios, array $homologacao, string $tipo): void
    {
        try {
            if (empty($destinatarios)) return;
            $email = new EmailService();

            $assunto = ($tipo === 'logistica')
                ? "SGQ - Aguardando Recebimento: {$homologacao['cod_referencia']} (#{$homologacao['id']})"
                : "SGQ - Nova Homologação atribuída: {$homologacao['cod_referencia']}";

            $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
            $body = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body style='font-family: Arial,sans-serif;line-height:1.6;color:#333;max-width:680px;margin:0 auto;padding:20px;'>" .
                "<div style='background:#1e40af;color:#fff;padding:18px 24px;border-radius:10px 10px 0 0;'><h2 style='margin:0;font-size:20px;'>SGQ OTI DJ • Homologações</h2></div>" .
                "<div style='background:#fff;border:1px solid #e5e7eb;border-top:none;padding:20px'>" .
                "<p style='margin:0 0 12px'>Código: <strong>" . htmlspecialchars($homologacao['cod_referencia']) . "</strong></p>" .
                "<p style='margin:0 0 12px'>Descrição: " . nl2br(htmlspecialchars($homologacao['descricao'])) . "</p>" .
                "<p style='margin:0 0 12px'>Status: <strong>" . htmlspecialchars($homologacao['status']) . "</strong></p>" .
                "<div style='text-align:center;margin:22px 0'>" .
                "<a href='" . $appUrl . "/homologacoes' style='background:#2563eb;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold'>Abrir Homologações</a>" .
                "</div>" .
                "<p style='font-size:12px;color:#6b7280;margin-top:24px'>Este email foi enviado automaticamente pelo SGQ OTI DJ.</p>" .
                "</div></body></html>";

            $ok = $email->send($destinatarios, $assunto, $body, strip_tags($body));
            if (!$ok) {
                error_log('Falha ao enviar email de homologação: ' . ($email->getLastError() ?? 'sem detalhes'));
            }
        } catch (\Exception $e) {
            error_log('Erro ao enviar email de homologação: ' . $e->getMessage());
        }
    }
}
