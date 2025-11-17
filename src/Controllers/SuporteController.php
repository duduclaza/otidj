<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class SuporteController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal do suporte
    public function index(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $userRole = $_SESSION['user_role'] ?? '';
            
            // Verificar se é admin ou super_admin
            // Admin: pode criar solicitações e ver as suas
            // Super Admin: pode ver todas e gerenciar status/observações
            if (!in_array($userRole, ['admin', 'super_admin'])) {
                http_response_code(403);
                echo "Acesso negado. Apenas Administradores podem acessar o suporte.";
                return;
            }

            // Super Admin vê todas as solicitações
            // Admin vê apenas suas próprias solicitações
            if ($userRole === 'super_admin') {
                $stmt = $this->db->prepare('
                    SELECT s.*, u.name as solicitante_nome, u.email as solicitante_email,
                           r.name as resolvido_por_nome
                    FROM suporte_solicitacoes s
                    LEFT JOIN users u ON s.solicitante_id = u.id
                    LEFT JOIN users r ON s.resolvido_por = r.id
                    ORDER BY 
                        FIELD(s.status, "Pendente", "Em Análise", "Concluído"),
                        s.created_at DESC
                ');
                $stmt->execute();
            } else {
                $stmt = $this->db->prepare('
                    SELECT s.*, u.name as solicitante_nome, u.email as solicitante_email,
                           r.name as resolvido_por_nome
                    FROM suporte_solicitacoes s
                    LEFT JOIN users u ON s.solicitante_id = u.id
                    LEFT JOIN users r ON s.resolvido_por = r.id
                    WHERE s.solicitante_id = :user_id
                    ORDER BY s.created_at DESC
                ');
                $stmt->execute([':user_id' => $userId]);
            }

            $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $title = 'Suporte - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/suporte/index.php';
            include __DIR__ . '/../../views/layouts/main.php';

        } catch (\Exception $e) {
            error_log("Erro no Suporte: " . $e->getMessage());
            http_response_code(500);
            echo "Erro ao carregar suporte: " . $e->getMessage();
        }
    }

    // Criar nova solicitação (APENAS ADMIN)
    public function store(): void
    {
        header('Content-Type: application/json');

        try {
            $userId = $_SESSION['user_id'];
            $userRole = $_SESSION['user_role'] ?? '';

            // APENAS ADMINS podem criar solicitações
            // Super Admins NÃO podem criar, apenas gerenciar
            if ($userRole !== 'admin') {
                echo json_encode(['success' => false, 'message' => 'Apenas Administradores podem criar solicitações de suporte. Super Administradores apenas gerenciam.']);
                return;
            }

            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            // Validações
            if (empty($titulo) || empty($descricao)) {
                echo json_encode(['success' => false, 'message' => 'Título e descrição são obrigatórios']);
                return;
            }

            // Processar anexos
            $anexos = [];
            if (!empty($_FILES['anexos']['name'][0])) {
                $anexos = $this->processarAnexos($_FILES['anexos']);
            }

            // Inserir solicitação
            $stmt = $this->db->prepare('
                INSERT INTO suporte_solicitacoes (
                    titulo, descricao, anexos, status, solicitante_id, created_at
                ) VALUES (
                    :titulo, :descricao, :anexos, "Pendente", :solicitante_id, NOW()
                )
            ');

            $stmt->execute([
                ':titulo' => $titulo,
                ':descricao' => $descricao,
                ':anexos' => json_encode($anexos),
                ':solicitante_id' => $userId
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Solicitação enviada com sucesso!',
                'redirect' => '/suporte'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao criar solicitação: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao criar solicitação: ' . $e->getMessage()]);
        }
    }

    // Atualizar status e observações (APENAS SUPER ADMIN)
    public function updateStatus(): void
    {
        header('Content-Type: application/json');

        try {
            $userRole = $_SESSION['user_role'] ?? '';

            // APENAS SUPER_ADMIN pode atualizar status e adicionar observações
            if ($userRole !== 'super_admin') {
                echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas Super Administradores podem gerenciar solicitações.']);
                return;
            }

            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $resolucao = trim($_POST['resolucao'] ?? '');

            // Validações
            if (!in_array($status, ['Pendente', 'Em Análise', 'Concluído'])) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                return;
            }

            if ($status === 'Concluído' && empty($resolucao)) {
                echo json_encode(['success' => false, 'message' => 'Resolução é obrigatória para concluir']);
                return;
            }

            // Atualizar
            $sql = 'UPDATE suporte_solicitacoes SET status = :status';
            $params = [':id' => $id, ':status' => $status];

            if ($status === 'Concluído') {
                $sql .= ', resolucao = :resolucao, resolvido_por = :resolvido_por, resolvido_em = NOW()';
                $params[':resolucao'] = $resolucao;
                $params[':resolvido_por'] = $_SESSION['user_id'];
            }

            $sql .= ', updated_at = NOW() WHERE id = :id';

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);

        } catch (\Exception $e) {
            error_log('Erro ao atualizar status: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }

    // Detalhes da solicitação
    public function details($id): void
    {
        header('Content-Type: application/json');

        try {
            $stmt = $this->db->prepare('
                SELECT s.*, u.name as solicitante_nome, u.email as solicitante_email,
                       r.name as resolvido_por_nome
                FROM suporte_solicitacoes s
                LEFT JOIN users u ON s.solicitante_id = u.id
                LEFT JOIN users r ON s.resolvido_por = r.id
                WHERE s.id = :id
            ');
            $stmt->execute([':id' => $id]);
            $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$solicitacao) {
                echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
                return;
            }

            // Decodificar anexos
            if (!empty($solicitacao['anexos'])) {
                $solicitacao['anexos_array'] = json_decode($solicitacao['anexos'], true);
            }

            echo json_encode(['success' => true, 'data' => $solicitacao]);

        } catch (\Exception $e) {
            error_log('Erro ao buscar detalhes: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }

    // Download de anexo
    public function downloadAnexo($anexoId): void
    {
        try {
            // Buscar todas as solicitações e encontrar o anexo
            $stmt = $this->db->prepare('SELECT id, anexos FROM suporte_solicitacoes');
            $stmt->execute();
            $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($solicitacoes as $sol) {
                if (empty($sol['anexos'])) continue;
                
                $anexos = json_decode($sol['anexos'], true);
                if (!is_array($anexos)) continue;

                foreach ($anexos as $idx => $anexo) {
                    $currentId = $sol['id'] . '_' . $idx;
                    if ($currentId == $anexoId) {
                        // Encontrou o anexo
                        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/storage/uploads/suporte/';
                        $filePath = $uploadDir . $anexo['arquivo'];

                        if (file_exists($filePath)) {
                            header('Content-Type: ' . $anexo['tipo']);
                            header('Content-Disposition: attachment; filename="' . $anexo['nome_original'] . '"');
                            header('Content-Length: ' . filesize($filePath));
                            readfile($filePath);
                            exit;
                        }
                    }
                }
            }

            http_response_code(404);
            echo "Anexo não encontrado";

        } catch (\Exception $e) {
            error_log('Erro ao baixar anexo: ' . $e->getMessage());
            http_response_code(500);
            echo "Erro ao baixar anexo";
        }
    }

    // Processar anexos
    private function processarAnexos($files): array
    {
        $anexos = [];
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/storage/uploads/suporte/';

        // Criar diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $nomeOriginal = $files['name'][$i];
                $tamanho = $files['size'][$i];
                $tipo = $files['type'][$i];
                $tmpName = $files['tmp_name'][$i];

                // Validar tamanho (max 10MB)
                if ($tamanho > 10 * 1024 * 1024) {
                    continue;
                }

                // Gerar nome único
                $extensao = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
                $nomeArquivo = uniqid('suporte_') . '.' . $extensao;
                $caminhoCompleto = $uploadDir . $nomeArquivo;

                if (move_uploaded_file($tmpName, $caminhoCompleto)) {
                    $anexos[] = [
                        'nome_original' => $nomeOriginal,
                        'arquivo' => $nomeArquivo,
                        'tamanho' => $tamanho,
                        'tipo' => $tipo
                    ];
                }
            }
        }

        return $anexos;
    }
}
