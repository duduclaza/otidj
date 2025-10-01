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
                SELECT m.*, u.name as criador_nome,
                       GROUP_CONCAT(ur.name SEPARATOR ", ") as responsaveis_nomes
                FROM melhoria_continua_2 m
                LEFT JOIN users u ON m.criado_por = u.id
                LEFT JOIN users ur ON FIND_IN_SET(ur.id, m.responsaveis)
                GROUP BY m.id
                ORDER BY m.created_at DESC
            ');
            $stmt->execute();
        } else {
            // Usuário comum vê apenas suas melhorias e aquelas onde é responsável
            $stmt = $this->db->prepare('
                SELECT m.*, u.name as criador_nome,
                       GROUP_CONCAT(ur.name SEPARATOR ", ") as responsaveis_nomes
                FROM melhoria_continua_2 m
                LEFT JOIN users u ON m.criado_por = u.id
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
                $anexos = $this->processarAnexos($_FILES['anexos']);
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

            // Só pode editar se for o criador e status for "Pendente Adaptação"
            if ($melhoria['criado_por'] != $userId || $melhoria['status'] !== 'Pendente Adaptação') {
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

            // Processar novos anexos se houver
            $anexos_existentes = json_decode($melhoria['anexos'] ?? '[]', true);
            if (!empty($_FILES['anexos']['name'][0])) {
                $novos_anexos = $this->processarAnexos($_FILES['anexos']);
                $anexos_existentes = array_merge($anexos_existentes, $novos_anexos);
            }

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
                ':anexos' => json_encode($anexos_existentes)
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

    public function updateStatus(): void
    {
        // Apenas admins podem alterar status
        if ($_SESSION['user_role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        try {
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $pontuacao = !empty($_POST['pontuacao']) ? (int)$_POST['pontuacao'] : null;

            $statusValidos = ['Pendente análise', 'Em andamento', 'Concluída', 'Recusada', 'Pendente Adaptação'];
            if (!in_array($status, $statusValidos)) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                return;
            }

            $stmt = $this->db->prepare('
                UPDATE melhoria_continua_2 SET 
                    status = :status, 
                    pontuacao = :pontuacao,
                    updated_at = NOW()
                WHERE id = :id
            ');

            $stmt->execute([
                ':id' => $id,
                ':status' => $status,
                ':pontuacao' => $pontuacao
            ]);

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
        $uploadDir = __DIR__ . '/../../uploads/melhoria-continua-2/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $maxFiles = 5;
        $maxSize = 10 * 1024 * 1024; // 10MB
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];

        for ($i = 0; $i < min(count($files['name']), $maxFiles); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileSize = $files['size'][$i];
                $fileType = $files['type'][$i];
                $fileName = $files['name'][$i];

                if ($fileSize > $maxSize) {
                    continue; // Pular arquivo muito grande
                }

                if (!in_array($fileType, $allowedTypes)) {
                    continue; // Pular tipo não permitido
                }

                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = uniqid() . '.' . $extension;
                $filePath = $uploadDir . $newFileName;

                if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                    $anexos[] = [
                        'nome_original' => $fileName,
                        'nome_arquivo' => $newFileName,
                        'tamanho' => $fileSize,
                        'tipo' => $fileType
                    ];
                }
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
}
