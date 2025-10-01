<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class MelhoriaContinua2Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        // Verificar permissão (admin sempre tem acesso)
        if ($_SESSION['user_role'] !== 'admin' && !PermissionService::hasPermission($_SESSION['user_id'], 'melhoria_continua_2', 'view')) {
            http_response_code(403);
            echo "Acesso negado";
            return;
        }

        $userId = $_SESSION['user_id'];
        $isAdmin = $_SESSION['user_role'] === 'admin';

        // Buscar melhorias baseado nas regras de visibilidade
        if ($isAdmin) {
            // Admin vê todas as melhorias
            $stmt = $this->db->prepare('
                SELECT m.*, u.name as criador_nome, d.nome as departamento_nome,
                       GROUP_CONCAT(ur.name SEPARATOR ", ") as responsaveis_nomes
                FROM melhoria_continua_2 m
                LEFT JOIN users u ON m.criado_por = u.id
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                LEFT JOIN users ur ON FIND_IN_SET(ur.id, m.responsaveis)
                GROUP BY m.id
                ORDER BY m.created_at DESC
            ');
            $stmt->execute();
        } else {
            // Usuário comum vê apenas suas melhorias e aquelas onde é responsável
            $stmt = $this->db->prepare('
                SELECT m.*, u.name as criador_nome, d.nome as departamento_nome,
                       GROUP_CONCAT(ur.name SEPARATOR ", ") as responsaveis_nomes
                FROM melhoria_continua_2 m
                LEFT JOIN users u ON m.criado_por = u.id
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                LEFT JOIN users ur ON FIND_IN_SET(ur.id, m.responsaveis)
                WHERE m.criado_por = :user_id OR FIND_IN_SET(:user_id, m.responsaveis)
                GROUP BY m.id
                ORDER BY m.created_at DESC
            ');
            $stmt->execute([':user_id' => $userId]);
        }

        $melhorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Buscar usuários para dropdown de responsáveis
        $stmt = $this->db->prepare('SELECT id, name FROM users WHERE status = "active" ORDER BY name');
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Buscar departamentos
        $stmt = $this->db->prepare('SELECT id, nome FROM departamentos ORDER BY nome');
        $stmt->execute();
        $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $title = 'Melhoria Contínua 2.0 - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/melhoria-continua-2/index.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    public function store(): void
    {
        // Verificar permissão de view (usuário pode criar se tem acesso ao módulo)
        if ($_SESSION['user_role'] !== 'admin' && !PermissionService::hasPermission($_SESSION['user_id'], 'melhoria_continua_2', 'view')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        try {
            $titulo = trim($_POST['titulo'] ?? '');
            $departamento_id = (int)($_POST['departamento_id'] ?? 0);
            $o_que = trim($_POST['o_que'] ?? '');
            $como = trim($_POST['como'] ?? '');
            $onde = trim($_POST['onde'] ?? '');
            $porque = trim($_POST['porque'] ?? '');
            $quando = $_POST['quando'] ?? null;
            $quanto_custa = !empty($_POST['quanto_custa']) ? (float)$_POST['quanto_custa'] : null;
            $responsaveis = $_POST['responsaveis'] ?? [];
            $resultado_esperado = trim($_POST['resultado_esperado'] ?? '');
            $idealizador = trim($_POST['idealizador'] ?? '');
            $observacao = trim($_POST['observacao'] ?? '');

            // Validações
            if (empty($titulo) || empty($o_que) || empty($como) || empty($onde) || empty($porque) || empty($resultado_esperado) || empty($idealizador)) {
                echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos']);
                return;
            }

            // Converter array de responsáveis para string
            $responsaveis_str = !empty($responsaveis) ? implode(',', $responsaveis) : '';

            // Processar anexos
            $anexos = [];
            if (!empty($_FILES['anexos']['name'][0])) {
                error_log("Processando anexos: " . print_r($_FILES['anexos'], true));
                $anexos = $this->processarAnexos($_FILES['anexos']);
                error_log("Anexos processados: " . print_r($anexos, true));
            } else {
                error_log("Nenhum anexo enviado");
            }

            $stmt = $this->db->prepare('
                INSERT INTO melhoria_continua_2 (
                    titulo, departamento_id, o_que, como, onde, porque, quando, 
                    quanto_custa, responsaveis, resultado_esperado, idealizador, 
                    status, observacao, anexos, criado_por, created_at
                ) VALUES (
                    :titulo, :departamento_id, :o_que, :como, :onde, :porque, :quando,
                    :quanto_custa, :responsaveis, :resultado_esperado, :idealizador,
                    "Pendente análise", :observacao, :anexos, :criado_por, NOW()
                )
            ');

            $stmt->execute([
                ':titulo' => $titulo,
                ':departamento_id' => $departamento_id,
                ':o_que' => $o_que,
                ':como' => $como,
                ':onde' => $onde,
                ':porque' => $porque,
                ':quando' => $quando,
                ':quanto_custa' => $quanto_custa,
                ':responsaveis' => $responsaveis_str,
                ':resultado_esperado' => $resultado_esperado,
                ':idealizador' => $idealizador,
                ':observacao' => $observacao,
                ':anexos' => json_encode($anexos),
                ':criado_por' => $_SESSION['user_id']
            ]);

            $melhoriaId = $this->db->lastInsertId();

            // Enviar notificações
            $this->enviarNotificacoes($melhoriaId, $titulo, $responsaveis);

            echo json_encode([
                'success' => true, 
                'message' => 'Melhoria registrada com sucesso!',
                'redirect' => '/melhoria-continua-2'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao salvar melhoria: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    public function update(): void
    {
        if (!PermissionService::hasPermission($_SESSION['user_id'], 'melhoria_continua_2', 'edit')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        try {
            $id = (int)($_POST['id'] ?? 0);
            $userId = $_SESSION['user_id'];

            // Verificar se o usuário pode editar esta melhoria
            $stmt = $this->db->prepare('SELECT criado_por, status FROM melhoria_continua_2 WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $melhoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$melhoria) {
                echo json_encode(['success' => false, 'message' => 'Melhoria não encontrada']);
                return;
            }

            // Admin pode editar qualquer melhoria, usuário comum só se for criador e status "Pendente Adaptação"
            $isAdmin = $_SESSION['user_role'] === 'admin';
            if (!$isAdmin && ($melhoria['criado_por'] != $userId || $melhoria['status'] !== 'Pendente Adaptação')) {
                echo json_encode(['success' => false, 'message' => 'Você não pode editar esta melhoria']);
                return;
            }

            // Atualizar dados
            $titulo = trim($_POST['titulo'] ?? '');
            $departamento_id = (int)($_POST['departamento_id'] ?? 0);
            $o_que = trim($_POST['o_que'] ?? '');
            $como = trim($_POST['como'] ?? '');
            $onde = trim($_POST['onde'] ?? '');
            $porque = trim($_POST['porque'] ?? '');
            $quando = $_POST['quando'] ?? null;
            $quanto_custa = !empty($_POST['quanto_custa']) ? (float)$_POST['quanto_custa'] : null;
            $responsaveis = $_POST['responsaveis'] ?? [];
            $resultado_esperado = trim($_POST['resultado_esperado'] ?? '');
            $idealizador = trim($_POST['idealizador'] ?? '');
            $observacao = trim($_POST['observacao'] ?? '');

            $responsaveis_str = !empty($responsaveis) ? implode(',', $responsaveis) : '';

            // Processar anexos
            // 1. Pegar anexos atuais (já filtrados pelo frontend - removidos os deletados)
            $anexos_atuais = [];
            if (!empty($_POST['anexos_atuais'])) {
                $anexos_atuais = json_decode($_POST['anexos_atuais'], true) ?? [];
            }
            
            // 2. Processar novos anexos se houver
            if (!empty($_FILES['anexos']['name'][0])) {
                $novos_anexos = $this->processarAnexos($_FILES['anexos']);
                $anexos_atuais = array_merge($anexos_atuais, $novos_anexos);
            }
            
            error_log("Anexos finais para update: " . print_r($anexos_atuais, true));

            $stmt = $this->db->prepare('
                UPDATE melhoria_continua_2 SET
                    titulo = :titulo, departamento_id = :departamento_id, o_que = :o_que,
                    como = :como, onde = :onde, porque = :porque, quando = :quando,
                    quanto_custa = :quanto_custa, responsaveis = :responsaveis,
                    resultado_esperado = :resultado_esperado, idealizador = :idealizador,
                    observacao = :observacao, anexos = :anexos, status = "Pendente análise",
                    updated_at = NOW()
                WHERE id = :id
            ');

            $stmt->execute([
                ':id' => $id,
                ':titulo' => $titulo,
                ':departamento_id' => $departamento_id,
                ':o_que' => $o_que,
                ':como' => $como,
                ':onde' => $onde,
                ':porque' => $porque,
                ':quando' => $quando,
                ':quanto_custa' => $quanto_custa,
                ':responsaveis' => $responsaveis_str,
                ':resultado_esperado' => $resultado_esperado,
                ':idealizador' => $idealizador,
                ':observacao' => $observacao,
                ':anexos' => json_encode($anexos_atuais)
            ]);

            // Enviar notificações
            $this->enviarNotificacoes($id, $titulo, $responsaveis);

            echo json_encode([
                'success' => true, 
                'message' => 'Melhoria atualizada e reenviada para análise!',
                'redirect' => '/melhoria-continua-2'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao atualizar melhoria: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    public function updateStatus($id = null): void
    {
        header('Content-Type: application/json');
        
        // Apenas admins podem alterar status
        if ($_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        try {
            // Suportar tanto POST quanto JSON
            if ($id) {
                $data = json_decode(file_get_contents('php://input'), true);
                $status = $data['status'] ?? '';
                $pontuacao = null;
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $status = $_POST['status'] ?? '';
                $pontuacao = !empty($_POST['pontuacao']) ? (int)$_POST['pontuacao'] : null;
            }

            $statusValidos = ['Pendente análise', 'Em andamento', 'Concluída', 'Recusada', 'Pendente Adaptação'];
            if (!in_array($status, $statusValidos)) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                return;
            }

            $stmt = $this->db->prepare('
                UPDATE melhoria_continua_2 SET 
                    status = :status' . ($pontuacao !== null ? ', pontuacao = :pontuacao' : '') . ',
                    updated_at = NOW()
                WHERE id = :id
            ');

            $params = [
                ':id' => $id,
                ':status' => $status
            ];
            
            if ($pontuacao !== null) {
                $params[':pontuacao'] = $pontuacao;
            }
            
            $stmt->execute($params);

            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);

        } catch (\Exception $e) {
            error_log('Erro ao atualizar status: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    public function delete(): void
    {
        try {
            $id = (int)($_POST['id'] ?? 0);
            $userId = $_SESSION['user_id'];

            // Verificar se pode excluir
            $stmt = $this->db->prepare('SELECT criado_por, status FROM melhoria_continua_2 WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $melhoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$melhoria) {
                echo json_encode(['success' => false, 'message' => 'Melhoria não encontrada']);
                return;
            }

            // Só pode excluir se for o criador e status for "Recusada"
            if ($melhoria['criado_por'] != $userId || $melhoria['status'] !== 'Recusada') {
                echo json_encode(['success' => false, 'message' => 'Você não pode excluir esta melhoria']);
                return;
            }

            $stmt = $this->db->prepare('DELETE FROM melhoria_continua_2 WHERE id = :id');
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Melhoria excluída com sucesso!']);

        } catch (\Exception $e) {
            error_log('Erro ao excluir melhoria: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    private function processarAnexos($files): array
    {
        $anexos = [];
        // Caminho absoluto do servidor
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/storage/uploads/melhorias/';
        $webPath = '/storage/uploads/melhorias/';
        
        // Criar diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
            error_log("Diretório criado: $uploadDir");
        }
        
        error_log("Upload dir: $uploadDir");
        error_log("DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT']);

        $maxFiles = 5;
        $maxSize = 10 * 1024 * 1024; // 10MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];

        for ($i = 0; $i < min(count($files['name']), $maxFiles); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileSize = $files['size'][$i];
                $fileType = $files['type'][$i];
                $fileName = $files['name'][$i];

                if ($fileSize > $maxSize) {
                    error_log("Arquivo muito grande: $fileName ($fileSize bytes)");
                    continue; // Pular arquivo muito grande
                }

                if (!in_array($fileType, $allowedTypes)) {
                    error_log("Tipo não permitido: $fileName ($fileType)");
                    continue; // Pular tipo não permitido
                }

                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = 'melhoria_' . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
                $filePath = $uploadDir . $newFileName;

                if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                    $anexos[] = [
                        'nome' => $fileName,
                        'arquivo' => $newFileName,
                        'url' => $webPath . $newFileName,
                        'tamanho' => $fileSize,
                        'tipo' => $fileType
                    ];
                    error_log("Arquivo salvo com sucesso: $filePath");
                } else {
                    error_log("Erro ao mover arquivo: $fileName para $filePath");
                }
            } else {
                error_log("Erro no upload do arquivo: " . $files['error'][$i]);
            }
        }

        return $anexos;
    }

    private function enviarNotificacoes($melhoriaId, $titulo, $responsaveis): void
    {
        // Buscar admins
        $stmt = $this->db->prepare('SELECT id, name, email FROM users WHERE role = "admin" AND status = "active"');
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Buscar responsáveis
        $responsaveisData = [];
        if (!empty($responsaveis)) {
            $placeholders = str_repeat('?,', count($responsaveis) - 1) . '?';
            $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE id IN ($placeholders) AND status = 'active'");
            $stmt->execute($responsaveis);
            $responsaveisData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Aqui você pode implementar o envio de email usando o EmailService
        // Por enquanto, apenas log
        error_log("Notificação enviada para melhoria ID: $melhoriaId - Título: $titulo");
    }


    // Atualizar Pontuação Inline (Admin)
    public function updatePontuacao($id): void
    {
        header('Content-Type: application/json');
        
        if ($_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $pontuacao = (int)($data['pontuacao'] ?? 0);

            $stmt = $this->db->prepare('UPDATE melhoria_continua_2 SET pontuacao = :pontuacao WHERE id = :id');
            $stmt->execute([':pontuacao' => $pontuacao, ':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Pontuação atualizada com sucesso']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar pontuação']);
        }
    }

    // Ver Detalhes da Melhoria (JSON para AJAX)
    public function details($id): void
    {
        header('Content-Type: application/json');

        try {
            $stmt = $this->db->prepare('
                SELECT m.*, u.name as criador_nome, d.nome as departamento_nome,
                       GROUP_CONCAT(ur.name SEPARATOR ", ") as responsaveis_nomes
                FROM melhoria_continua_2 m
                LEFT JOIN users u ON m.criado_por = u.id
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                LEFT JOIN users ur ON FIND_IN_SET(ur.id, m.responsaveis)
                WHERE m.id = :id
                GROUP BY m.id
            ');
            $stmt->execute([':id' => $id]);
            $melhoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$melhoria) {
                echo json_encode(['success' => false, 'message' => 'Melhoria não encontrada']);
                return;
            }

            // Buscar anexos
            $anexos = [];
            if (!empty($melhoria['anexos'])) {
                $anexos = json_decode($melhoria['anexos'], true) ?? [];
            }
            $melhoria['anexos'] = $anexos;

            echo json_encode(['success' => true, 'melhoria' => $melhoria]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar detalhes']);
        }
    }

    // Ver Detalhes da Melhoria (Página HTML)
    public function view($id): void
    {
        try {
            $stmt = $this->db->prepare('
                SELECT m.*, u.name as criador_nome, d.nome as departamento_nome,
                       GROUP_CONCAT(ur.name SEPARATOR ", ") as responsaveis_nomes
                FROM melhoria_continua_2 m
                LEFT JOIN users u ON m.criado_por = u.id
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                LEFT JOIN users ur ON FIND_IN_SET(ur.id, m.responsaveis)
                WHERE m.id = :id
                GROUP BY m.id
            ');
            $stmt->execute([':id' => $id]);
            $melhoria = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$melhoria) {
                echo "Melhoria não encontrada";
                return;
            }

            // Incluir a view
            include __DIR__ . '/../../views/pages/melhoria-continua-2/view.php';
        } catch (\Exception $e) {
            echo "Erro ao carregar detalhes: " . $e->getMessage();
        }
    }
}
