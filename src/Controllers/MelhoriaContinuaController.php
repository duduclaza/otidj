<?php
namespace App\Controllers;

use App\Config\Database;

class MelhoriaContinuaController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/melhoria-continua/' . $view . '.php';
        $layout = __DIR__ . '/../../views/layouts/main.php';
        include $layout;
    }

    // Página com ABAS
    public function index(): void
    {
        $setores = $this->getSetores();
        $usuarios = $this->getUsuarios();
        $this->render('index', compact('setores','usuarios'));
    }

    // Páginas
    public function solicitacoes(): void
    {
        // Carregar dados básicos para selects
        $setores = $this->getSetores();
        $usuarios = $this->getUsuarios();
        $this->render('solicitacoes', compact('setores','usuarios'));
    }

    public function pendentes(): void
    {
        $this->render('pendentes');
    }

    public function historico(): void
    {
        $this->render('historico');
    }

    // APIs (stubs para não quebrar enquanto banco não está pronto)
    public function apiListSolicitacoes(): void
    {
        header('Content-Type: application/json');
        try {
            $user = $this->getCurrentUser();

            $stmt = $this->db->prepare("SELECT id, data_solicitacao, processo, setor, status FROM solicitacoes_melhorias WHERE usuario_id = ? ORDER BY id DESC");
            $stmt->execute([$user['id']]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $respStmt = $this->db->prepare("SELECT usuario_nome FROM solicitacoes_melhorias_responsaveis WHERE solicitacao_id = ? ORDER BY usuario_nome");
            foreach ($rows as &$r) {
                $respStmt->execute([$r['id']]);
                $r['responsaveis'] = array_column($respStmt->fetchAll(\PDO::FETCH_ASSOC), 'usuario_nome');
                $r['data'] = date('d/m/Y H:i', strtotime($r['data_solicitacao'] ?? 'now'));
            }

            echo json_encode(['success'=>true, 'data'=>$rows]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false, 'message'=>'Erro ao listar: '.$e->getMessage()]);
        }
    }

    public function apiCreateSolicitacao(): void
    {
        header('Content-Type: application/json');
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $user = $this->getCurrentUser();

            $setor = trim($_POST['setor'] ?? '');
            $processo = trim($_POST['processo'] ?? '');
            $descricao = trim($_POST['descricao_melhoria'] ?? '');
            $resultado = trim($_POST['resultado_esperado'] ?? '');
            $observacoes = trim($_POST['observacoes'] ?? '');
            $responsaveis = $_POST['responsaveis'] ?? [];

            if ($setor === '' || $processo === '' || $descricao === '' || $resultado === '' || empty($responsaveis)) {
                echo json_encode(['success'=>false,'message'=>'Preencha todos os campos obrigatórios e selecione ao menos um responsável.']);
                return;
            }

            $stmt = $this->db->prepare("INSERT INTO solicitacoes_melhorias (usuario_id, usuario_nome, setor, processo, descricao_melhoria, resultado_esperado, observacoes) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$user['id'], $user['name'], $setor, $processo, $descricao, $resultado, $observacoes]);
            $solicitacaoId = (int)$this->db->lastInsertId();

            // Inserir responsáveis
            $in = implode(',', array_fill(0, count($responsaveis), '?'));
            $usrStmt = $this->db->prepare("SELECT id, name, email FROM users WHERE id IN ($in)");
            $usrStmt->execute(array_map('intval', $responsaveis));
            $list = $usrStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $ins = $this->db->prepare("INSERT INTO solicitacoes_melhorias_responsaveis (solicitacao_id, usuario_id, usuario_nome, usuario_email) VALUES (?,?,?,?)");
            foreach ($list as $u) {
                $ins->execute([$solicitacaoId, (int)$u['id'], $u['name'], $u['email']]);
            }

            // Uploads
            $this->handleUploads($solicitacaoId, $_FILES['anexos'] ?? null);

            // Log
            $this->logAcao($solicitacaoId, $user, 'criar', 'Solicitação criada');

            echo json_encode(['success'=>true,'message'=>'Solicitação registrada com sucesso.']);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'message'=>'Erro ao criar: '.$e->getMessage()]);
        }
    }

    public function apiListPendentes(): void
    {
        header('Content-Type: application/json');
        try {
            $user = $this->getCurrentUser();
            $sql = "SELECT s.id, s.data_solicitacao, s.processo, s.setor, s.status, s.observacoes
                    FROM solicitacoes_melhorias s
                    INNER JOIN solicitacoes_melhorias_responsaveis r ON r.solicitacao_id = s.id
                    WHERE r.usuario_id = ? AND s.status IN ('pendente','em_analise')
                    ORDER BY s.id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user['id']]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as &$r) { $r['data'] = date('d/m/Y H:i', strtotime($r['data_solicitacao'] ?? 'now')); }
            echo json_encode(['success'=>true,'data'=>$rows]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'message'=>'Erro ao listar pendentes: '.$e->getMessage()]);
        }
    }

    public function apiUpdateStatus(): void
    {
        header('Content-Type: application/json');
        try {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $user = $this->getCurrentUser();
            $id = (int)($_POST['id'] ?? 0);
            $status = trim($_POST['status'] ?? '');
            $observacoes = trim($_POST['observacoes'] ?? '');
            if ($id <= 0 || $status === '' || $observacoes === '') {
                echo json_encode(['success'=>false,'message'=>'Informe ID, status e observação.']);
                return;
            }
            $stmt = $this->db->prepare("UPDATE solicitacoes_melhorias SET status = ?, observacoes = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $observacoes, $id]);
            $this->logAcao($id, $user, 'atualizar_status', 'Status: '.$status.' | Obs: '.$observacoes);
            echo json_encode(['success'=>true,'message'=>'Status atualizado com sucesso.']);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'message'=>'Erro ao atualizar: '.$e->getMessage()]);
        }
    }

    public function apiDelete(): void
    {
        header('Content-Type: application/json');
        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'ID inválido']); return; }
            // Somente o criador pode excluir (ou ajuste aqui se tiver checagem de admin)
            $user = $this->getCurrentUser();
            $chk = $this->db->prepare("SELECT usuario_id FROM solicitacoes_melhorias WHERE id = ?");
            $chk->execute([$id]);
            $ownerId = (int)($chk->fetchColumn() ?: 0);
            if ($ownerId !== (int)$user['id']) {
                echo json_encode(['success'=>false,'message'=>'Sem permissão para excluir esta solicitação.']);
                return;
            }
            $this->deleteUploadsDirectory($id);
            $stmt = $this->db->prepare("DELETE FROM solicitacoes_melhorias WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success'=>true,'message'=>'Solicitação excluída com sucesso.']);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'message'=>'Erro ao excluir: '.$e->getMessage()]);
        }
    }

    public function apiLogs(): void
    {
        header('Content-Type: application/json');
        try {
            $stmt = $this->db->query("SELECT created_at as data, solicitacao_id, usuario_nome as usuario, acao, detalhes FROM melhorias_logs ORDER BY id DESC LIMIT 500");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as &$r) { $r['data'] = date('d/m/Y H:i', strtotime($r['data'] ?? 'now')); }
            echo json_encode(['success'=>true,'data'=>$rows]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'data'=>[], 'message'=>'Erro ao carregar logs: '.$e->getMessage()]);
        }
    }

    public function details($params = []): void
    {
        header('Content-Type: application/json');
        $id = $params['id'] ?? null;
        echo json_encode(['success'=>true,'data'=>['id'=>$id]]);
    }

    public function print($params = []): void
    {
        $id = $params['id'] ?? null;
        echo '<html><head><title>Impressão Solicitação #'.htmlspecialchars((string)$id)."</title></head><body>";
        echo '<h1>Solicitação #'.htmlspecialchars((string)$id).'</h1>';
        echo '<p>Esta é uma página de impressão (stub).</p>';
        echo '</body></html>';
    }

    // Anexos
    public function apiListAnexos($params = []): void
    {
        header('Content-Type: application/json');
        try {
            $id = (int)($params['id'] ?? ($_GET['id'] ?? 0));
            if ($id <= 0) { echo json_encode(['success'=>false,'data'=>[]]); return; }
            $stmt = $this->db->prepare("SELECT id, nome_original, nome_arquivo, tipo_arquivo, tamanho_arquivo FROM solicitacoes_melhorias_anexos WHERE solicitacao_id = ? ORDER BY id");
            $stmt->execute([$id]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            echo json_encode(['success'=>true,'data'=>$rows]);
        } catch (\Throwable $e) {
            echo json_encode(['success'=>false,'data'=>[], 'message'=>$e->getMessage()]);
        }
    }

    public function downloadAnexo($params = []): void
    {
        $anexoId = (int)($params['id'] ?? 0);
        if ($anexoId <= 0) { http_response_code(404); echo 'Arquivo não encontrado'; return; }
        $stmt = $this->db->prepare("SELECT nome_original, caminho_arquivo, tipo_arquivo FROM solicitacoes_melhorias_anexos WHERE id = ?");
        $stmt->execute([$anexoId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row || !is_file($row['caminho_arquivo'])) { http_response_code(404); echo 'Arquivo não encontrado'; return; }
        header('Content-Description: File Transfer');
        header('Content-Type: '.($row['tipo_arquivo'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="'.basename($row['nome_original']).'"');
        header('Content-Length: '.filesize($row['caminho_arquivo']));
        readfile($row['caminho_arquivo']);
        exit;
    }

    public function viewAnexos($params = []): void
    {
        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) { 
            error_log("viewAnexos: ID inválido recebido: " . print_r($params, true));
            http_response_code(404); echo 'Solicitação inválida - ID não fornecido'; return; 
        }
        
        // Verificar se a solicitação existe
        $checkStmt = $this->db->prepare("SELECT id FROM solicitacoes_melhorias WHERE id = ?");
        $checkStmt->execute([$id]);
        if (!$checkStmt->fetchColumn()) {
            error_log("viewAnexos: Solicitação não encontrada para ID: " . $id);
            http_response_code(404); echo 'Solicitação não encontrada'; return;
        }
        
        $stmt = $this->db->prepare("SELECT id, nome_original, tamanho_arquivo, tipo_arquivo FROM solicitacoes_melhorias_anexos WHERE solicitacao_id = ? ORDER BY id");
        $stmt->execute([$id]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        error_log("viewAnexos: Encontrados " . count($rows) . " anexos para solicitação " . $id);

        // Se houver apenas um arquivo, já redireciona para download direto
        if (count($rows) === 1) {
            $only = $rows[0];
            header('Location: /melhoria-continua/anexos/'.(int)$only['id'].'/download');
            return;
        }

        echo '<!doctype html><html lang="pt-br"><head><meta charset="utf-8"><title>Anexos Solicitação #'.(int)$id.'</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"></head><body class="p-6 bg-gray-50">';
        echo '<div class="max-w-3xl mx-auto bg-white border rounded-lg shadow">';
        echo '<div class="px-6 py-4 border-b"><h1 class="text-lg font-semibold">Anexos da Solicitação #'.htmlspecialchars((string)$id).'</h1></div>';
        echo '<div class="p-6">';
        if (empty($rows)) {
            echo '<div class="text-gray-600">Nenhum anexo encontrado.</div>';
        } else {
            echo '<table class="min-w-full text-sm">';
            echo '<thead class="bg-gray-50"><tr><th class="text-left px-3 py-2">Arquivo</th><th class="text-left px-3 py-2">Tamanho</th><th class="text-left px-3 py-2">Ação</th></tr></thead>';
            echo '<tbody class="divide-y">';
            foreach ($rows as $r) {
                $url = '/melhoria-continua/anexos/'.(int)$r['id'].'/download';
                $size = isset($r['tamanho_arquivo']) ? (round(((int)$r['tamanho_arquivo'])/1024/1024, 2).' MB') : '-';
                echo '<tr>';
                echo '<td class="px-3 py-2">'.htmlspecialchars($r['nome_original']).'</td>';
                echo '<td class="px-3 py-2 text-gray-600">'.$size.'</td>';
                echo '<td class="px-3 py-2"><a class="inline-flex items-center px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700" href="'.$url.'">Baixar</a></td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        echo '</div></div>';
        echo '</body></html>';
    }

    // Helpers
    private function getSetores(): array
    {
        try {
            // Primeiro tenta buscar da tabela departments
            $stmt = $this->db->query("SELECT name FROM departments WHERE name IS NOT NULL AND name <> '' ORDER BY name");
            $setores = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
            
            // Se não encontrar, busca dos usuários como fallback
            if (empty($setores)) {
                $stmt = $this->db->query("SELECT DISTINCT setor FROM users WHERE setor IS NOT NULL AND setor <> '' ORDER BY setor");
                $setores = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
            }
            
            return $setores;
        } catch (\Throwable $e) { return []; }
    }

    private function getUsuarios(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { return []; }
    }

    private function getCurrentUser(): array
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Em produção, virá da autenticação. Aqui garantimos defaults para evitar quebras.
        $id = $_SESSION['user_id'] ?? 1;
        $name = $_SESSION['user_name'] ?? 'Usuário';
        $email = $_SESSION['user_email'] ?? 'user@example.com';
        return ['id'=>(int)$id,'name'=>$name,'email'=>$email];
    }

    private function handleUploads(int $solicitacaoId, $files): void
    {
        if (!$files || !isset($files['name']) || !is_array($files['name'])) return;
        $maxFiles = 5; $maxSize = 5 * 1024 * 1024; // 5MB
        $allowed = ['image/jpeg','image/png','image/gif','application/pdf'];

        $basePath = dirname(__DIR__, 2);
        $dir = $basePath . '/storage/uploads/melhorias/' . $solicitacaoId;
        if (!is_dir($dir)) @mkdir($dir, 0775, true);

        $count = count($files['name']);
        if ($count > $maxFiles) $count = $maxFiles;

        $ins = $this->db->prepare("INSERT INTO solicitacoes_melhorias_anexos (solicitacao_id, nome_arquivo, nome_original, tipo_arquivo, tamanho_arquivo, caminho_arquivo) VALUES (?,?,?,?,?,?)");
        for ($i=0; $i<$count; $i++) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
            $name = $files['name'][$i];
            $type = $files['type'][$i];
            $size = (int)$files['size'][$i];
            $tmp  = $files['tmp_name'][$i];
            if ($size > $maxSize) continue;
            if (!in_array($type, $allowed, true)) continue;

            $safe = preg_replace('/[^a-zA-Z0-9_\.-]/','_', $name);
            $dest = $dir . '/' . uniqid('anx_') . '_' . $safe;
            if (@move_uploaded_file($tmp, $dest)) {
                $ins->execute([$solicitacaoId, basename($dest), $name, $type, $size, $dest]);
            }
        }
    }

    private function deleteUploadsDirectory(int $solicitacaoId): void
    {
        $basePath = dirname(__DIR__, 2);
        $dir = $basePath . '/storage/uploads/melhorias/' . $solicitacaoId;
        if (!is_dir($dir)) return;
        foreach (glob($dir.'/*') as $f) { @unlink($f); }
        @rmdir($dir);
    }

    private function logAcao(int $solicitacaoId, array $user, string $acao, string $detalhes=''): void
    {
        $stmt = $this->db->prepare("INSERT INTO melhorias_logs (solicitacao_id, usuario_id, usuario_nome, acao, detalhes) VALUES (?,?,?,?,?)");
        $stmt->execute([$solicitacaoId, $user['id'] ?? null, $user['name'] ?? null, $acao, $detalhes]);
    }
}
