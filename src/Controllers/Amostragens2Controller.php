<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class Amostragens2Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        try {
            // Verificar permissão
            $isAdmin = $_SESSION['user_role'] === 'admin';
            if (!$isAdmin && !PermissionService::hasPermission($_SESSION['user_id'], 'amostragens_2', 'view')) {
                http_response_code(403);
                echo "Acesso negado";
                return;
            }

            $userId = $_SESSION['user_id'];
            $userFilialId = $_SESSION['user_filial_id'] ?? null;

            // Buscar amostragens com filtros
            $where = [];
            $params = [];

            // Filtros
            if (!empty($_GET['codigo_produto'])) {
                $where[] = "a.codigo_produto LIKE :codigo";
                $params[':codigo'] = '%' . $_GET['codigo_produto'] . '%';
            }

            if (!empty($_GET['user_id'])) {
                $where[] = "a.user_id = :user_id";
                $params[':user_id'] = $_GET['user_id'];
            }

            if (!empty($_GET['filial_id'])) {
                $where[] = "a.filial_id = :filial_id";
                $params[':filial_id'] = $_GET['filial_id'];
            }

            if (!empty($_GET['fornecedor_id'])) {
                $where[] = "a.fornecedor_id = :fornecedor_id";
                $params[':fornecedor_id'] = $_GET['fornecedor_id'];
            }

            if (!empty($_GET['status'])) {
                $where[] = "a.status_final = :status";
                $params[':status'] = $_GET['status'];
            }

            if (!empty($_GET['data_inicio'])) {
                $where[] = "DATE(a.created_at) >= :data_inicio";
                $params[':data_inicio'] = $_GET['data_inicio'];
            }

            if (!empty($_GET['data_fim'])) {
                $where[] = "DATE(a.created_at) <= :data_fim";
                $params[':data_fim'] = $_GET['data_fim'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $stmt = $this->db->prepare("
                SELECT a.*, 
                       u.name as usuario_nome,
                       f.nome as filial_nome,
                       forn.nome as fornecedor_nome,
                       (SELECT COUNT(*) FROM amostragens_2_evidencias WHERE amostragem_id = a.id) as total_evidencias
                FROM amostragens_2 a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN filiais f ON a.filial_id = f.id
                LEFT JOIN fornecedores forn ON a.fornecedor_id = forn.id
                $whereClause
                ORDER BY a.created_at DESC
            ");
            $stmt->execute($params);
            $amostragens = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar dados para dropdowns
            $stmt = $this->db->prepare('SELECT id, name FROM users WHERE status = "active" ORDER BY name');
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare('SELECT id, nome FROM filiais ORDER BY nome');
            $stmt->execute();
            $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare('SELECT id, nome FROM fornecedores ORDER BY nome');
            $stmt->execute();
            $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar produtos por tipo
            // Toners
            $stmt = $this->db->prepare('SELECT id, modelo as codigo, modelo as nome FROM toners ORDER BY modelo');
            $stmt->execute();
            $toners = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Máquinas
            $stmt = $this->db->prepare('SELECT id, cod_referencia as codigo, modelo as nome FROM cadastro_maquinas ORDER BY cod_referencia');
            $stmt->execute();
            $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Peças
            $stmt = $this->db->prepare('SELECT id, codigo_referencia as codigo, descricao as nome FROM cadastro_pecas ORDER BY codigo_referencia');
            $stmt->execute();
            $pecas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $title = 'Amostragens 2.0 - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/amostragens-2/index.php';
            include __DIR__ . '/../../views/layouts/main.php';

        } catch (\Exception $e) {
            error_log("Erro em Amostragens 2.0: " . $e->getMessage());
            http_response_code(500);
            echo "Erro ao carregar o módulo: " . $e->getMessage();
        }
    }

    public function store(): void
    {
        header('Content-Type: application/json');

        try {
            // Verificar se usuário está logado
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não está logado']);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            
            // Verificar se o usuário existe no banco
            $stmt = $this->db->prepare('SELECT id FROM users WHERE id = :user_id');
            $stmt->execute([':user_id' => $userId]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado no sistema']);
                return;
            }
            
            // Buscar filial do usuário ou usar primeira filial disponível
            $filialId = $_SESSION['user_filial_id'] ?? null;
            
            if (!$filialId) {
                $stmt = $this->db->prepare('SELECT id FROM filiais LIMIT 1');
                $stmt->execute();
                $filial = $stmt->fetch(PDO::FETCH_ASSOC);
                $filialId = $filial['id'] ?? 1;
            }

            // Validar dados
            $numeroNf = trim($_POST['numero_nf'] ?? '');
            $tipoProduto = $_POST['tipo_produto'] ?? '';
            $produtoId = (int)($_POST['produto_id'] ?? 0);
            $codigoProduto = trim($_POST['codigo_produto'] ?? '');
            $nomeProduto = trim($_POST['nome_produto'] ?? '');
            $quantidadeRecebida = (int)($_POST['quantidade_recebida'] ?? 0);
            $quantidadeTestada = (int)($_POST['quantidade_testada'] ?? 0);
            $quantidadeAprovada = (int)($_POST['quantidade_aprovada'] ?? 0);
            $quantidadeReprovada = (int)($_POST['quantidade_reprovada'] ?? 0);
            $fornecedorId = (int)($_POST['fornecedor_id'] ?? 0);
            $responsaveis = $_POST['responsaveis'] ?? [];
            $statusFinal = $_POST['status_final'] ?? 'Pendente';

            error_log("Dados recebidos - NF: $numeroNf, Tipo: $tipoProduto, Produto: $produtoId, Fornecedor: $fornecedorId");

            if (empty($numeroNf) || empty($tipoProduto) || $produtoId <= 0 || $quantidadeRecebida <= 0 || $quantidadeTestada <= 0 || $fornecedorId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
                return;
            }
            
            // Verificar se o fornecedor existe
            $stmt = $this->db->prepare('SELECT id FROM fornecedores WHERE id = :fornecedor_id');
            $stmt->execute([':fornecedor_id' => $fornecedorId]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Fornecedor selecionado não existe']);
                return;
            }

            // Processar anexo da NF
            $anexoNf = null;
            $anexoNfNome = null;
            $anexoNfTipo = null;
            $anexoNfTamanho = null;

            if (!empty($_FILES['anexo_nf']['tmp_name'])) {
                $anexoNf = file_get_contents($_FILES['anexo_nf']['tmp_name']);
                $anexoNfNome = $_FILES['anexo_nf']['name'];
                $anexoNfTipo = $_FILES['anexo_nf']['type'];
                $anexoNfTamanho = $_FILES['anexo_nf']['size'];

                if ($anexoNfTamanho > 10 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'Anexo da NF deve ter no máximo 10MB']);
                    return;
                }
            }

            // Inserir amostragem
            $responsaveisStr = !empty($responsaveis) ? implode(',', $responsaveis) : '';

            $stmt = $this->db->prepare('
                INSERT INTO amostragens_2 (
                    user_id, filial_id, numero_nf, anexo_nf, anexo_nf_nome, anexo_nf_tipo, anexo_nf_tamanho,
                    tipo_produto, produto_id, codigo_produto, nome_produto,
                    quantidade_recebida, quantidade_testada, quantidade_aprovada, quantidade_reprovada,
                    fornecedor_id, responsaveis, status_final, created_at
                ) VALUES (
                    :user_id, :filial_id, :numero_nf, :anexo_nf, :anexo_nf_nome, :anexo_nf_tipo, :anexo_nf_tamanho,
                    :tipo_produto, :produto_id, :codigo_produto, :nome_produto,
                    :quantidade_recebida, :quantidade_testada, :quantidade_aprovada, :quantidade_reprovada,
                    :fornecedor_id, :responsaveis, :status_final, NOW()
                )
            ');

            $stmt->execute([
                ':user_id' => $userId,
                ':filial_id' => $filialId,
                ':numero_nf' => $numeroNf,
                ':anexo_nf' => $anexoNf,
                ':anexo_nf_nome' => $anexoNfNome,
                ':anexo_nf_tipo' => $anexoNfTipo,
                ':anexo_nf_tamanho' => $anexoNfTamanho,
                ':tipo_produto' => $tipoProduto,
                ':produto_id' => $produtoId,
                ':codigo_produto' => $codigoProduto,
                ':nome_produto' => $nomeProduto,
                ':quantidade_recebida' => $quantidadeRecebida,
                ':quantidade_testada' => $quantidadeTestada,
                ':quantidade_aprovada' => $quantidadeAprovada,
                ':quantidade_reprovada' => $quantidadeReprovada,
                ':fornecedor_id' => $fornecedorId,
                ':responsaveis' => $responsaveisStr,
                ':status_final' => $statusFinal
            ]);

            $amostragemId = $this->db->lastInsertId();

            // Processar evidências (fotos)
            if (!empty($_FILES['evidencias']['tmp_name'][0])) {
                $this->processarEvidencias($amostragemId, $_FILES['evidencias']);
            }

            // Enviar email automático para responsáveis ao criar nova amostragem
            try {
                error_log("🔧 DEBUG: Tentando enviar email para amostragem #{$amostragemId}");
                $emailEnviado = $this->enviarEmailNovaAmostragem($amostragemId);
                if ($emailEnviado) {
                    error_log("✅ Email de nova amostragem enviado automaticamente para amostragem #{$amostragemId}");
                } else {
                    error_log("⚠️ Falha ao enviar email automático para amostragem #{$amostragemId} (não crítico)");
                }
            } catch (\Exception $e) {
                // Log do erro mas não falha a operação
                error_log("⚠️ Erro ao enviar email automático (não crítico): " . $e->getMessage());
                error_log("⚠️ Stack trace: " . $e->getTraceAsString());
            } catch (\Error $e) {
                // Log do erro fatal mas não falha a operação
                error_log("⚠️ Erro fatal ao enviar email automático (não crítico): " . $e->getMessage());
                error_log("⚠️ Stack trace: " . $e->getTraceAsString());
            }

            echo json_encode([
                'success' => true,
                'message' => 'Amostragem cadastrada com sucesso!',
                'redirect' => '/amostragens-2'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao salvar amostragem: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar: ' . $e->getMessage()]);
        }
    }

    private function processarEvidencias($amostragemId, $files): void
    {
        $maxFiles = 5;
        $maxSize = 10 * 1024 * 1024; // 10MB

        for ($i = 0; $i < min(count($files['name']), $maxFiles); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileSize = $files['size'][$i];
                $fileName = $files['name'][$i];
                $fileType = $files['type'][$i];

                if ($fileSize > $maxSize) {
                    continue;
                }

                if (!str_starts_with($fileType, 'image/')) {
                    continue;
                }

                $evidencia = file_get_contents($files['tmp_name'][$i]);

                $stmt = $this->db->prepare('
                    INSERT INTO amostragens_2_evidencias (amostragem_id, evidencia, nome, tipo, tamanho, ordem)
                    VALUES (:amostragem_id, :evidencia, :nome, :tipo, :tamanho, :ordem)
                ');

                $stmt->execute([
                    ':amostragem_id' => $amostragemId,
                    ':evidencia' => $evidencia,
                    ':nome' => $fileName,
                    ':tipo' => $fileType,
                    ':tamanho' => $fileSize,
                    ':ordem' => $i + 1
                ]);
            }
        }
    }

    private function enviarEmailNovaAmostragem(int $amostragemId): bool
    {
        try {
            error_log("=== ENVIANDO EMAIL DE NOVA AMOSTRAGEM ===");
            error_log("Amostragem ID: {$amostragemId}");
            
            // Buscar dados completos da amostragem
            $stmt = $this->db->prepare('
                SELECT 
                    a.*,
                    f.nome as fornecedor_nome,
                    u.name as criador_nome
                FROM amostragens_2 a
                LEFT JOIN fornecedores f ON a.fornecedor_id = f.id
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.id = :id
            ');
            $stmt->execute([':id' => $amostragemId]);
            $amostragem = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$amostragem) {
                error_log("❌ Amostragem #{$amostragemId}: Não encontrada");
                return false;
            }

            error_log("✅ Amostragem encontrada: NF {$amostragem['numero_nf']}");
            error_log("Responsáveis (IDs): " . ($amostragem['responsaveis'] ?? 'VAZIO'));

            // Buscar emails dos responsáveis se houver
            if (!empty($amostragem['responsaveis'])) {
                $responsaveisIds = array_map('trim', explode(',', $amostragem['responsaveis']));
                error_log("IDs dos responsáveis: " . implode(', ', $responsaveisIds));
                
                $placeholders = str_repeat('?,', count($responsaveisIds) - 1) . '?';
                $stmt = $this->db->prepare("SELECT name, email FROM users WHERE id IN ($placeholders) AND email IS NOT NULL AND email != ''");
                $stmt->execute($responsaveisIds);
                $responsaveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Responsáveis encontrados: " . count($responsaveis));
                foreach ($responsaveis as $resp) {
                    error_log("  - {$resp['name']} ({$resp['email']})");
                }
                
                $emails = array_column($responsaveis, 'email');

                if (empty($emails)) {
                    error_log("❌ Amostragem #{$amostragemId}: Nenhum email válido encontrado para os responsáveis");
                    return false;
                }

                error_log("📧 Tentando enviar email para: " . implode(', ', $emails));

                // Enviar email
                if (!class_exists('\App\Services\EmailService')) {
                    error_log("❌ Classe EmailService não encontrada");
                    return false;
                }
                
                $emailService = new \App\Services\EmailService();
                error_log("EmailService criado");
                
                $enviado = $emailService->sendAmostragemNotification($amostragem, $emails, 'nova');

                if ($enviado) {
                    error_log("✅ Email de nova amostragem enviado para amostragem #{$amostragemId} para: " . implode(', ', $emails));
                    return true;
                } else {
                    error_log("❌ Falha ao enviar email de nova amostragem para amostragem #{$amostragemId}");
                    return false;
                }
            } else {
                error_log("⚠️ Amostragem #{$amostragemId}: Sem responsáveis cadastrados");
                return false;
            }

        } catch (\Exception $e) {
            error_log("Erro ao enviar email de nova amostragem: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    private function enviarEmailMudancaStatusAmostragem(int $amostragemId, string $novoStatus): bool
    {
        try {
            error_log("=== ENVIANDO EMAIL DE MUDANÇA DE STATUS AMOSTRAGEM ===");
            error_log("Amostragem ID: {$amostragemId}, Novo Status: {$novoStatus}");
            
            // Buscar dados completos da amostragem
            $stmt = $this->db->prepare('
                SELECT 
                    a.*,
                    f.nome as fornecedor_nome,
                    u.name as criador_nome
                FROM amostragens_2 a
                LEFT JOIN fornecedores f ON a.fornecedor_id = f.id
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.id = :id
            ');
            $stmt->execute([':id' => $amostragemId]);
            $amostragem = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$amostragem) {
                error_log("❌ Amostragem #{$amostragemId}: Não encontrada");
                return false;
            }

            error_log("✅ Amostragem encontrada: NF {$amostragem['numero_nf']}");
            error_log("Responsáveis (IDs): " . ($amostragem['responsaveis'] ?? 'VAZIO'));

            // Buscar emails dos responsáveis se houver
            if (!empty($amostragem['responsaveis'])) {
                $responsaveisIds = array_map('trim', explode(',', $amostragem['responsaveis']));
                error_log("IDs dos responsáveis: " . implode(', ', $responsaveisIds));
                
                $placeholders = str_repeat('?,', count($responsaveisIds) - 1) . '?';
                $stmt = $this->db->prepare("SELECT name, email FROM users WHERE id IN ($placeholders) AND email IS NOT NULL AND email != ''");
                $stmt->execute($responsaveisIds);
                $responsaveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Responsáveis encontrados: " . count($responsaveis));
                foreach ($responsaveis as $resp) {
                    error_log("  - {$resp['name']} ({$resp['email']})");
                }
                
                $emails = array_column($responsaveis, 'email');

                if (empty($emails)) {
                    error_log("❌ Amostragem #{$amostragemId}: Nenhum email válido encontrado para os responsáveis");
                    return false;
                }

                error_log("📧 Tentando enviar email para: " . implode(', ', $emails));

                // Enviar email com template específico do status
                $emailService = new \App\Services\EmailService();
                error_log("EmailService criado");
                
                $enviado = $emailService->sendAmostragemNotification($amostragem, $emails, 'status', $novoStatus);

                if ($enviado) {
                    error_log("✅ Email de mudança de status enviado para amostragem #{$amostragemId} para: " . implode(', ', $emails));
                    return true;
                } else {
                    error_log("❌ Falha ao enviar email de mudança de status para amostragem #{$amostragemId}");
                    return false;
                }
            } else {
                error_log("⚠️ Amostragem #{$amostragemId}: Sem responsáveis cadastrados");
                return false;
            }

        } catch (\Exception $e) {
            error_log("Erro ao enviar email de mudança de status: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function enviarEmailDetalhes(): void
    {
        // Limpar qualquer output anterior
        while (ob_get_level()) { ob_end_clean(); }
        
        header('Content-Type: application/json');
        
        try {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }
            
            // Verificar se a amostragem existe
            $stmt = $this->db->prepare('SELECT id FROM amostragens_2 WHERE id = :id');
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Amostragem não encontrada']);
                return;
            }
            
            $ok = $this->enviarEmailNovaAmostragem($id);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => '📧 Email enviado com sucesso aos responsáveis!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao enviar email - verifique se há responsáveis com email cadastrado']);
            }
            
        } catch (\Throwable $e) {
            error_log('Erro ao enviar email manual: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Erro interno ao enviar email: ' . $e->getMessage()]);
        }
    }

    public function downloadNf($id = null): void
    {
        try {
            $id = (int)$id;
            
            $stmt = $this->db->prepare('
                SELECT anexo_nf, anexo_nf_nome, anexo_nf_tipo 
                FROM amostragens_2 
                WHERE id = :id
            ');
            $stmt->execute([':id' => $id]);
            $amostra = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$amostra || !$amostra['anexo_nf']) {
                http_response_code(404);
                echo "Anexo não encontrado";
                return;
            }
            
            header('Content-Type: ' . $amostra['anexo_nf_tipo']);
            header('Content-Disposition: attachment; filename="' . $amostra['anexo_nf_nome'] . '"');
            header('Content-Length: ' . strlen($amostra['anexo_nf']));
            echo $amostra['anexo_nf'];
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo "Erro ao baixar anexo";
        }
    }

    public function getEvidencias($id = null): void
    {
        header('Content-Type: application/json');
        
        try {
            $id = (int)$id;
            
            error_log("Buscando evidências para amostragem ID: $id");
            
            $stmt = $this->db->prepare('
                SELECT id, nome, tipo, tamanho, ordem
                FROM amostragens_2_evidencias 
                WHERE amostragem_id = :id
                ORDER BY ordem
            ');
            $stmt->execute([':id' => $id]);
            $evidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Evidências encontradas: " . count($evidencias));
            
            echo json_encode([
                'success' => true,
                'evidencias' => $evidencias,
                'count' => count($evidencias)
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar evidências: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar evidências: ' . $e->getMessage()]);
        }
    }

    public function downloadEvidencia($id = null, $evidenciaId = null): void
    {
        try {
            $evidenciaId = (int)$evidenciaId;
            
            $stmt = $this->db->prepare('
                SELECT evidencia, nome, tipo 
                FROM amostragens_2_evidencias 
                WHERE id = :id
            ');
            $stmt->execute([':id' => $evidenciaId]);
            $evidencia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$evidencia) {
                http_response_code(404);
                echo "Evidência não encontrada";
                return;
            }
            
            header('Content-Type: ' . $evidencia['tipo']);
            header('Content-Disposition: attachment; filename="' . $evidencia['nome'] . '"');
            header('Content-Length: ' . strlen($evidencia['evidencia']));
            echo $evidencia['evidencia'];
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo "Erro ao baixar evidência";
        }
    }

    public function details($id = null): void
    {
        header('Content-Type: application/json');
        
        try {
            $id = (int)$id;
            
            error_log("Buscando detalhes da amostragem ID: $id");
            
            $stmt = $this->db->prepare('SELECT * FROM amostragens_2 WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $amostragem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$amostragem) {
                error_log("Amostragem ID $id não encontrada");
                echo json_encode(['success' => false, 'message' => 'Amostragem não encontrada']);
                return;
            }
            
            error_log("Amostragem encontrada: " . json_encode($amostragem));
            
            echo json_encode([
                'success' => true,
                'amostragem' => $amostragem
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar detalhes: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar detalhes: ' . $e->getMessage()]);
        }
    }

    public function update(): void
    {
        header('Content-Type: application/json');

        try {
            $id = (int)($_POST['amostragem_id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }

            $numeroNf = trim($_POST['numero_nf'] ?? '');
            $tipoProduto = $_POST['tipo_produto'] ?? '';
            $produtoId = (int)($_POST['produto_id'] ?? 0);
            $codigoProduto = trim($_POST['codigo_produto'] ?? '');
            $nomeProduto = trim($_POST['nome_produto'] ?? '');
            $quantidadeRecebida = (int)($_POST['quantidade_recebida'] ?? 0);
            $quantidadeTestada = (int)($_POST['quantidade_testada'] ?? 0);
            $quantidadeAprovada = (int)($_POST['quantidade_aprovada'] ?? 0);
            $quantidadeReprovada = (int)($_POST['quantidade_reprovada'] ?? 0);
            $fornecedorId = (int)($_POST['fornecedor_id'] ?? 0);
            $responsaveis = $_POST['responsaveis'] ?? [];
            $statusFinal = $_POST['status_final'] ?? 'Pendente';
            
            $responsaveisStr = !empty($responsaveis) ? implode(',', $responsaveis) : '';

            $stmt = $this->db->prepare('
                UPDATE amostragens_2 SET
                    numero_nf = :numero_nf,
                    tipo_produto = :tipo_produto,
                    produto_id = :produto_id,
                    codigo_produto = :codigo_produto,
                    nome_produto = :nome_produto,
                    quantidade_recebida = :quantidade_recebida,
                    quantidade_testada = :quantidade_testada,
                    quantidade_aprovada = :quantidade_aprovada,
                    quantidade_reprovada = :quantidade_reprovada,
                    fornecedor_id = :fornecedor_id,
                    responsaveis = :responsaveis,
                    status_final = :status_final,
                    updated_at = NOW()
                WHERE id = :id
            ');

            $stmt->execute([
                ':id' => $id,
                ':numero_nf' => $numeroNf,
                ':tipo_produto' => $tipoProduto,
                ':produto_id' => $produtoId,
                ':codigo_produto' => $codigoProduto,
                ':nome_produto' => $nomeProduto,
                ':quantidade_recebida' => $quantidadeRecebida,
                ':quantidade_testada' => $quantidadeTestada,
                ':quantidade_aprovada' => $quantidadeAprovada,
                ':quantidade_reprovada' => $quantidadeReprovada,
                ':fornecedor_id' => $fornecedorId,
                ':responsaveis' => $responsaveisStr,
                ':status_final' => $statusFinal
            ]);

            // Enviar email automático para responsáveis sempre que atualizar
            try {
                $emailEnviado = $this->enviarEmailMudancaStatusAmostragem($id, $statusFinal);
                if ($emailEnviado) {
                    error_log("✅ Email de mudança de status enviado automaticamente para amostragem #{$id} - Status: {$statusFinal}");
                } else {
                    error_log("⚠️ Falha ao enviar email automático para amostragem #{$id} (não crítico)");
                }
            } catch (\Exception $e) {
                // Log do erro mas não falha a operação
                error_log("⚠️ Erro ao enviar email automático (não crítico): " . $e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => 'Amostragem atualizada com sucesso!',
                'redirect' => '/amostragens-2'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao atualizar amostragem: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
    }

    public function delete(): void
    {
        header('Content-Type: application/json');

        try {
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }

            // Excluir evidências primeiro (CASCADE deve fazer isso automaticamente)
            $stmt = $this->db->prepare('DELETE FROM amostragens_2_evidencias WHERE amostragem_id = :id');
            $stmt->execute([':id' => $id]);

            // Excluir amostragem
            $stmt = $this->db->prepare('DELETE FROM amostragens_2 WHERE id = :id');
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Amostragem excluída com sucesso!']);

        } catch (\Exception $e) {
            error_log('Erro ao excluir amostragem: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir: ' . $e->getMessage()]);
        }
    }

    public function exportExcel(): void
    {
        try {
            // Buscar filtros
            $filters = [];
            $params = [];
            
            if (!empty($_GET['codigo_produto'])) {
                $filters[] = "codigo_produto LIKE :codigo_produto";
                $params[':codigo_produto'] = '%' . $_GET['codigo_produto'] . '%';
            }
            
            if (!empty($_GET['user_id'])) {
                $filters[] = "user_id = :user_id";
                $params[':user_id'] = $_GET['user_id'];
            }
            
            if (!empty($_GET['filial_id'])) {
                $filters[] = "filial_id = :filial_id";
                $params[':filial_id'] = $_GET['filial_id'];
            }
            
            if (!empty($_GET['fornecedor_id'])) {
                $filters[] = "fornecedor_id = :fornecedor_id";
                $params[':fornecedor_id'] = $_GET['fornecedor_id'];
            }
            
            if (!empty($_GET['status_final'])) {
                $filters[] = "status_final = :status_final";
                $params[':status_final'] = $_GET['status_final'];
            }
            
            if (!empty($_GET['data_inicio'])) {
                $filters[] = "DATE(created_at) >= :data_inicio";
                $params[':data_inicio'] = $_GET['data_inicio'];
            }
            
            if (!empty($_GET['data_fim'])) {
                $filters[] = "DATE(created_at) <= :data_fim";
                $params[':data_fim'] = $_GET['data_fim'];
            }
            
            $whereClause = !empty($filters) ? 'WHERE ' . implode(' AND ', $filters) : '';
            
            // Buscar dados
            $stmt = $this->db->prepare("
                SELECT 
                    a.*,
                    u.name as usuario_nome,
                    f.nome as filial_nome,
                    forn.nome as fornecedor_nome
                FROM amostragens_2 a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN filiais f ON a.filial_id = f.id
                LEFT JOIN fornecedores forn ON a.fornecedor_id = forn.id
                $whereClause
                ORDER BY a.created_at DESC
            ");
            $stmt->execute($params);
            $amostragens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($amostragens)) {
                echo json_encode(['success' => false, 'message' => 'Nenhum registro encontrado']);
                return;
            }
            
            // Gerar arquivo Excel (CSV com formatação)
            $filename = 'amostragens_2_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalhos
            fputcsv($output, [
                'Data',
                'Número NF',
                'Usuário',
                'Filial',
                'Tipo Produto',
                'Código Produto',
                'Nome Produto',
                'Qtd Recebida',
                'Qtd Testada',
                'Qtd Aprovada',
                'Qtd Reprovada',
                'Fornecedor',
                'Responsáveis',
                'Status Final',
                'Observações'
            ], ';');
            
            // Dados
            foreach ($amostragens as $amostra) {
                fputcsv($output, [
                    date('d/m/Y H:i', strtotime($amostra['created_at'])),
                    $amostra['numero_nf'],
                    $amostra['usuario_nome'],
                    $amostra['filial_nome'],
                    $amostra['tipo_produto'],
                    $amostra['codigo_produto'],
                    $amostra['nome_produto'],
                    $amostra['quantidade_recebida'],
                    $amostra['quantidade_testada'],
                    $amostra['quantidade_aprovada'],
                    $amostra['quantidade_reprovada'],
                    $amostra['fornecedor_nome'],
                    $amostra['responsaveis'],
                    $amostra['status_final'],
                    $amostra['observacoes'] ?? ''
                ], ';');
            }
            
            fclose($output);
            
        } catch (\Exception $e) {
            error_log('Erro ao exportar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao exportar: ' . $e->getMessage()]);
        }
    }

    public function graficos(): void
    {
        // TODO: Implementar página de gráficos
        echo "Gráficos em desenvolvimento";
    }
}
