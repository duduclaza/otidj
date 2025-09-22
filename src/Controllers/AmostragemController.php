<?php

namespace App\Controllers;

use App\Config\Database;

class AmostragemController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $title = 'Amostragens - SGQ OTI DJ';
        $amostragens = [];
        $error = null;
        
        try {
            // Verificar se as tabelas existem
            $stmt = $this->db->prepare("SHOW TABLES LIKE 'amostragens'");
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                throw new \Exception("Tabela 'amostragens' não encontrada. Execute as migrations primeiro.");
            }
            
            // Buscar amostragens com informações de evidências
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       COALESCE(COUNT(e.id), 0) as total_evidencias
                FROM amostragens a 
                LEFT JOIN amostragens_evidencias e ON a.id = e.amostragem_id 
                GROUP BY a.id 
                ORDER BY a.data_registro DESC
            ");
            $stmt->execute();
            $amostragens = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Processar dados para exibição
            foreach ($amostragens as &$amostragem) {
                // Decodificar responsáveis
                if (!empty($amostragem['responsaveis'])) {
                    $amostragem['responsaveis_list'] = json_decode($amostragem['responsaveis'], true) ?: [];
                } else {
                    $amostragem['responsaveis_list'] = [];
                }
                
                // Verificar se tem PDF
                $amostragem['has_pdf'] = !empty($amostragem['arquivo_nf_blob']) || !empty($amostragem['arquivo_nf']);
            }

        } catch (\Exception $e) {
            $error = 'Erro ao carregar amostragens: ' . $e->getMessage();
        }
        
        $viewFile = __DIR__ . '/../../views/pages/toners/amostragens.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    public function store()
    {
        // Limpar qualquer output anterior
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        
        try {
            $numero_nf = trim($_POST['numero_nf'] ?? '');
            $status = $_POST['status'] ?? 'pendente';
            $observacao = trim($_POST['observacao'] ?? '');
            $responsaveisRaw = $_POST['responsaveis'] ?? [];

            // Validações básicas
            if (empty($numero_nf)) {
                echo json_encode(['success' => false, 'message' => 'Número da NF é obrigatório']);
                return;
            }
            
            if (empty($responsaveisRaw)) {
                echo json_encode(['success' => false, 'message' => 'Pelo menos um responsável deve ser selecionado']);
                return;
            }

            // Parse responsáveis
            $responsaveisParsed = $this->parseResponsaveis($responsaveisRaw);
            
            if (empty($responsaveisParsed)) {
                echo json_encode(['success' => false, 'message' => 'Responsáveis inválidos']);
                return;
            }

            // Processar PDF da NF
            $pdfData = $this->processPdfUpload($_FILES['arquivo_nf'] ?? null);

            // Processar evidências (para qualquer status)
            $evidenciasData = [];
            if (isset($_FILES['evidencias'])) {
                $evidenciasData = $this->processEvidenciasUpload($_FILES['evidencias']);
            }

            // Inserir amostragem no banco
            $amostragemId = $this->insertAmostragem([
                'numero_nf' => $numero_nf,
                'status' => $status,
                'observacao' => $observacao,
                'responsaveis' => $responsaveisParsed,
                'pdf' => $pdfData,
                'evidencias' => $evidenciasData
            ]);

            // Enviar emails para responsáveis
            $this->sendEmailToResponsaveis($responsaveisParsed, $numero_nf, $status, $amostragemId);
            
            echo json_encode(['success' => true, 'message' => 'Amostragem registrada com sucesso!', 'id' => $amostragemId]);
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar amostragem: ' . $e->getMessage()]);
            exit;
        }
    }

    // Parse responsáveis do formulário
    private function parseResponsaveis(array $responsaveisRaw): array
    {
        $responsaveisParsed = [];
        
        foreach ($responsaveisRaw as $r) {
            $decoded = json_decode($r, true);
            if (is_array($decoded) && isset($decoded['name'])) {
                $name = trim((string)$decoded['name']);
                $email = isset($decoded['email']) ? trim((string)$decoded['email']) : '';
                if (!empty($name)) {
                    $responsaveisParsed[] = ['name' => $name, 'email' => $email];
                }
            } else {
                $name = trim((string)$r);
                if (!empty($name)) {
                    $responsaveisParsed[] = ['name' => $name, 'email' => ''];
                }
            }
        }
        
        return $responsaveisParsed;
    }

    // Processar upload de PDF
    private function processPdfUpload(?array $file): array
    {
        $result = ['blob' => null, 'name' => null, 'type' => null, 'size' => null, 'path' => null];
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return $result;
        }
        
        // Validar tipo de arquivo
        $allowedTypes = ['application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new \Exception('Apenas arquivos PDF são permitidos');
        }
        
        // Validar tamanho (10MB max)
        if ($file['size'] > 10 * 1024 * 1024) {
            throw new \Exception('Arquivo PDF deve ter no máximo 10MB');
        }
        
        // Salvar em BLOB (preferencial)
        $result['blob'] = file_get_contents($file['tmp_name']);
        $result['name'] = $file['name'];
        $result['type'] = $file['type'];
        $result['size'] = $file['size'];
        
        return $result;
    }

    // Processar upload de evidências
    private function processEvidenciasUpload(array $files): array
    {
        $evidencias = [];
        
        if (empty($files['tmp_name'])) {
            return $evidencias;
        }
        
        foreach ($files['tmp_name'] as $key => $tmpName) {
            if ($files['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            // Validar tipo de imagem
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($files['type'][$key], $allowedTypes)) {
                continue; // Pular arquivos inválidos
            }
            
            // Validar tamanho (5MB max por imagem)
            if ($files['size'][$key] > 5 * 1024 * 1024) {
                continue; // Pular arquivos muito grandes
            }
            
            $evidencias[] = [
                'blob' => file_get_contents($tmpName),
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'size' => $files['size'][$key]
            ];
        }
        
        return $evidencias;
    }

    // Inserir amostragem no banco
    private function insertAmostragem(array $data): int
    {
        // Preparar dados para inserção
        $params = [
            ':numero_nf' => $data['numero_nf'],
            ':status' => $data['status'],
            ':observacao' => $data['observacao'],
            ':responsaveis' => json_encode($data['responsaveis'])
        ];
        
        // Campos base
        $columns = ['numero_nf', 'status', 'observacao', 'responsaveis'];
        $placeholders = [':numero_nf', ':status', ':observacao', ':responsaveis'];
        
        // PDF em BLOB
        if (!empty($data['pdf']['blob'])) {
            $columns[] = 'arquivo_nf_blob';
            $columns[] = 'arquivo_nf_name';
            $columns[] = 'arquivo_nf_type';
            $columns[] = 'arquivo_nf_size';
            $placeholders[] = ':arquivo_nf_blob';
            $placeholders[] = ':arquivo_nf_name';
            $placeholders[] = ':arquivo_nf_type';
            $placeholders[] = ':arquivo_nf_size';
            
            $params[':arquivo_nf_blob'] = $data['pdf']['blob'];
            $params[':arquivo_nf_name'] = $data['pdf']['name'];
            $params[':arquivo_nf_type'] = $data['pdf']['type'];
            $params[':arquivo_nf_size'] = $data['pdf']['size'];
        }
        
        // Timestamp
        $columns[] = 'data_registro';
        $placeholders[] = 'NOW()';
        
        $sql = 'INSERT INTO amostragens (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $amostragemId = (int)$this->db->lastInsertId();
        
        // Salvar evidências em tabela separada
        if (!empty($data['evidencias']) && $amostragemId) {
            $this->saveEvidenciasToDatabase($amostragemId, $data['evidencias']);
        }
        
        return $amostragemId;
    }

    // Salvar evidências na tabela separada
    private function saveEvidenciasToDatabase(int $amostragemId, array $evidencias): void
    {
        if (empty($evidencias)) {
            return;
        }
        
        $stmt = $this->db->prepare('INSERT INTO amostragens_evidencias (amostragem_id, image, name, type, size) VALUES (?, ?, ?, ?, ?)');
        
        foreach ($evidencias as $evidencia) {
            $stmt->execute([
                $amostragemId,
                $evidencia['blob'],
                $evidencia['name'],
                $evidencia['type'],
                $evidencia['size']
            ]);
        }
    }

    // Enviar emails para responsáveis
    private function sendEmailToResponsaveis(array $responsaveis, string $numero_nf, string $status, int $amostragemId): void
    {
        try {
            // Extrair nomes e emails
            $names = [];
            $emails = [];
            
            foreach ($responsaveis as $resp) {
                if (!empty($resp['name'])) {
                    $names[] = $resp['name'];
                }
                if (!empty($resp['email'])) {
                    $emails[] = $resp['email'];
                }
            }
            
            // Se não temos emails, buscar no banco pelos nomes
            $users = [];
            if (empty($emails) && !empty($names)) {
                $placeholders = str_repeat('?,', count($names) - 1) . '?';
                $stmt = $this->db->prepare("SELECT name, email FROM users WHERE name IN ($placeholders) AND status = 'active'");
                $stmt->execute($names);
                $users = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            }

            $subject = "Nova Amostragem Registrada - NF: $numero_nf";
            
            $message = "
                <h2>Nova Amostragem Registrada</h2>
                <p><strong>Número da NF:</strong> $numero_nf</p>
                <p><strong>Status:</strong> " . ucfirst($status) . "</p>
                <p><strong>Responsáveis:</strong> " . implode(', ', $names) . "</p>
                <p><strong>Data de Registro:</strong> " . date('d/m/Y H:i') . "</p>
                <br>
                <p>Acesse o sistema para visualizar todos os detalhes e anexos.</p>
                <p><em>Este é um e-mail automático do Sistema SGQ-OTI DJ</em></p>
            ";

            // Usar EmailService se disponível
            if (class_exists('\\App\\Services\\EmailService')) {
                $emailService = new \App\Services\EmailService();

                // Preferir emails diretos
                if (!empty($emails)) {
                    foreach ($emails as $email) {
                        $emailService->send($email, '', $subject, $message);
                    }
                } elseif (!empty($users)) {
                    foreach ($users as $user) {
                        if (!empty($user['email'])) {
                            $emailService->send($user['email'], $user['name'] ?? '', $subject, $message);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request - silently continue
        }
    }

    // Deletar amostragem
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        try {
            $id = (int)$id;
            
            // Verificar se existe
            $stmt = $this->db->prepare("SELECT id FROM amostragens WHERE id = ?");
            $stmt->execute([$id]);
            
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Amostragem não encontrada']);
                return;
            }
            
            // Deletar (CASCADE vai remover evidências automaticamente)
            $stmt = $this->db->prepare("DELETE FROM amostragens WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Amostragem excluída com sucesso!']);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir amostragem: ' . $e->getMessage()]);
        }
    }

    // Servir PDF
    public function show($id)
    {
        try {
            $id = (int)$id;
            
            $stmt = $this->db->prepare("SELECT arquivo_nf_blob, arquivo_nf_name, arquivo_nf_type, arquivo_nf, numero_nf FROM amostragens WHERE id = ?");
            $stmt->execute([$id]);
            $amostragem = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$amostragem) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Amostragem não encontrada']);
                return;
            }

            // Servir PDF do BLOB
            if (!empty($amostragem['arquivo_nf_blob'])) {
                $filename = $amostragem['arquivo_nf_name'] ?? ('NF_' . $amostragem['numero_nf'] . '.pdf');
                $type = $amostragem['arquivo_nf_type'] ?? 'application/pdf';
                
                header('Content-Type: ' . $type);
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                echo $amostragem['arquivo_nf_blob'];
                exit;
            }
            
            // Fallback: filesystem (se ainda existir)
            if (!empty($amostragem['arquivo_nf']) && file_exists($amostragem['arquivo_nf'])) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="NF_' . $amostragem['numero_nf'] . '.pdf"');
                readfile($amostragem['arquivo_nf']);
                exit;
            }

            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Arquivo PDF não encontrado']);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar PDF: ' . $e->getMessage()]);
        }
    }

    // Servir evidência
    public function evidencia($amostragemId, $evidenciaId)
    {
        try {
            $amostragemId = (int)$amostragemId;
            $evidenciaId = (int)$evidenciaId;
            
            $stmt = $this->db->prepare("SELECT image, name, type FROM amostragens_evidencias WHERE id = ? AND amostragem_id = ?");
            $stmt->execute([$evidenciaId, $amostragemId]);
            $evidencia = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$evidencia || empty($evidencia['image'])) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Evidência não encontrada']);
                return;
            }

            $filename = $evidencia['name'] ?? 'evidencia.jpg';
            $type = $evidencia['type'] ?? 'image/jpeg';
            
            header('Content-Type: ' . $type);
            header('Content-Disposition: inline; filename="' . $filename . '"');
            echo $evidencia['image'];
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar evidência: ' . $e->getMessage()]);
        }
    }

    // Listar evidências de uma amostragem
    public function getEvidencias($amostragemId)
    {
        header('Content-Type: application/json');
        
        try {
            $amostragemId = (int)$amostragemId;
            
            $stmt = $this->db->prepare("SELECT id, name, type, size FROM amostragens_evidencias WHERE amostragem_id = ? ORDER BY id");
            $stmt->execute([$amostragemId]);
            $evidencias = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'evidencias' => $evidencias]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar evidências: ' . $e->getMessage()]);
        }
    }

    // Atualizar amostragem (status e observação)
    public function update($id)
    {
        // Limpar qualquer output anterior
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        
        try {
            $id = (int)$id;
            $status = $_POST['status'] ?? '';
            $observacao = trim($_POST['observacao'] ?? '');
            
            // Validações
            if (!in_array($status, ['pendente', 'aprovado', 'reprovado'])) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                exit;
            }
            
            // Validar observação para status reprovado
            if ($status === 'reprovado' && empty($observacao)) {
                echo json_encode(['success' => false, 'message' => 'Observação é obrigatória para status reprovado']);
                exit;
            }
            
            // Verificar se a amostragem existe
            $stmt = $this->db->prepare("SELECT id FROM amostragens WHERE id = ?");
            $stmt->execute([$id]);
            
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Amostragem não encontrada']);
                exit;
            }
            
            // Atualizar amostragem
            $stmt = $this->db->prepare("UPDATE amostragens SET status = ?, observacao = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $observacao, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Amostragem atualizada com sucesso!']);
            exit;
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar amostragem: ' . $e->getMessage()]);
            exit;
        }
    }

    // Debug endpoint para testar salvamento
    public function testStore()
    {
        header('Content-Type: application/json');
        
        try {
            echo json_encode(['success' => true, 'message' => 'Endpoint funcionando', 'timestamp' => date('Y-m-d H:i:s')]);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
