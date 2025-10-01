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
            $userId = $_SESSION['user_id'];
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

            // Enviar notificações aos responsáveis
            $this->enviarNotificacoes($amostragemId, $numeroNf, $responsaveis);

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

    private function enviarNotificacoes($amostragemId, $numeroNf, $responsaveis): void
    {
        try {
            $criadorNome = $_SESSION['user_name'] ?? 'Usuário';

            if (!empty($responsaveis)) {
                foreach ($responsaveis as $responsavelId) {
                    $stmt = $this->db->prepare('
                        INSERT INTO notifications (user_id, title, message, type, related_type, related_id, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ');
                    $stmt->execute([
                        $responsavelId,
                        '🔬 Nova Amostragem',
                        "$criadorNome designou você como responsável pela amostragem da NF: $numeroNf",
                        'info',
                        'amostragens_2',
                        $amostragemId
                    ]);
                }
            }
        } catch (\Exception $e) {
            error_log("Erro ao enviar notificações: " . $e->getMessage());
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
            
            $stmt = $this->db->prepare('
                SELECT id, nome, tipo, tamanho, ordem
                FROM amostragens_2_evidencias 
                WHERE amostragem_id = :id
                ORDER BY ordem
            ');
            $stmt->execute([':id' => $id]);
            $evidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'evidencias' => $evidencias
            ]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar evidências']);
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

    public function exportExcel(): void
    {
        // TODO: Implementar exportação para Excel
        echo "Exportação em desenvolvimento";
    }

    public function graficos(): void
    {
        // TODO: Implementar página de gráficos
        echo "Gráficos em desenvolvimento";
    }
}
