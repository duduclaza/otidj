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
        try {
            $stmt = $this->db->prepare("SELECT * FROM amostragens ORDER BY data_registro DESC");
            $stmt->execute();
            $amostragens = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $title = 'Amostragens - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/toners/amostragens.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            echo view('pages/toners/amostragens', ['error' => 'Erro ao carregar amostragens: ' . $e->getMessage()]);
        }
    }

    public function store()
    {
        header('Content-Type: application/json');
        
        try {
            
            $numero_nf = $_POST['numero_nf'] ?? '';
            $status = $_POST['status'] ?? 'pendente';
            $observacao = $_POST['observacao'] ?? '';
            $responsaveisRaw = $_POST['responsaveis'] ?? [];

            // Parse responsaveis (can be names or JSON strings {name,email})
            $responsaveisParsed = [];
            $responsaveisNomes = [];
            foreach ($responsaveisRaw as $r) {
                $decoded = json_decode($r, true);
                if (is_array($decoded) && isset($decoded['name'])) {
                    $name = trim((string)$decoded['name']);
                    $email = isset($decoded['email']) ? trim((string)$decoded['email']) : '';
                    $responsaveisParsed[] = ['name' => $name, 'email' => $email];
                    if ($name !== '') { $responsaveisNomes[] = $name; }
                } else {
                    $name = trim((string)$r);
                    $responsaveisParsed[] = ['name' => $name, 'email' => ''];
                    if ($name !== '') { $responsaveisNomes[] = $name; }
                }
            }
            
            if (empty($numero_nf) || empty($status)) {
                echo json_encode(['success' => false, 'message' => 'Número da NF e status são obrigatórios']);
                return;
            }
            
            if (empty($responsaveisParsed)) {
                echo json_encode(['success' => false, 'message' => 'Pelo menos um responsável deve ser selecionado']);
                return;
            }

            // Handle file upload for NF PDF - salvar em MEDIUMBLOB
            $arquivo_nf = '';
            $pdfBlob = null; $pdfName = null; $pdfType = null; $pdfSize = null;
            if (isset($_FILES['arquivo_nf']) && $_FILES['arquivo_nf']['error'] === UPLOAD_ERR_OK) {
                // Salvar em BLOB se coluna existir
                if ($this->hasColumn('amostragens', 'arquivo_nf_blob')) {
                    $pdfBlob = file_get_contents($_FILES['arquivo_nf']['tmp_name']);
                    $pdfName = $_FILES['arquivo_nf']['name'];
                    $pdfType = $_FILES['arquivo_nf']['type'] ?? 'application/pdf';
                    $pdfSize = $_FILES['arquivo_nf']['size'];
                } else {
                    // Fallback filesystem
                    $uploadDir = 'uploads/nf/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $fileName = uniqid() . '_' . $_FILES['arquivo_nf']['name'];
                    $uploadPath = $uploadDir . $fileName;
                    if (move_uploaded_file($_FILES['arquivo_nf']['tmp_name'], $uploadPath)) {
                        $arquivo_nf = 'nf/' . $fileName;
                    }
                }
            }

            // Handle evidence files for rejected samples
            $evidencias = [];
            if ($status === 'reprovado' && isset($_FILES['evidencias'])) {
                $uploadDir = 'uploads/evidencias/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                foreach ($_FILES['evidencias']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['evidencias']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileName = uniqid() . '_' . $_FILES['evidencias']['name'][$key];
                        $uploadPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            $evidencias[] = 'evidencias/' . $fileName;
                        }
                    }
                }
            }
            
            // Handle fotos upload
            $fotos = [];
            if (isset($_FILES['fotos'])) {
                $uploadDir = 'uploads/fotos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                foreach ($_FILES['fotos']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['fotos']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileName = uniqid() . '_' . $_FILES['fotos']['name'][$key];
                        $uploadPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            $fotos[] = 'fotos/' . $fileName;
                        }
                    }
                }
            }

            // Build INSERT based on existing columns (produção pode não ter colunas novas)
            $hasResponsaveis = $this->hasColumn('amostragens', 'responsaveis');
            $hasFotos = $this->hasColumn('amostragens', 'fotos');
            $hasPdfBlob = $this->hasColumn('amostragens', 'arquivo_nf_blob');
            $hasEvidTable = $this->hasTable('amostragens_evidencias');

            $columns = ['numero_nf', 'status', 'observacao'];
            $placeholders = [':numero_nf', ':status', ':observacao'];
            
            // PDF: BLOB ou filesystem
            if ($hasPdfBlob && $pdfBlob) {
                $columns[] = 'arquivo_nf_blob'; $placeholders[] = ':arquivo_nf_blob';
                if ($this->hasColumn('amostragens', 'arquivo_nf_name')) { $columns[] = 'arquivo_nf_name'; $placeholders[] = ':arquivo_nf_name'; }
                if ($this->hasColumn('amostragens', 'arquivo_nf_type')) { $columns[] = 'arquivo_nf_type'; $placeholders[] = ':arquivo_nf_type'; }
                if ($this->hasColumn('amostragens', 'arquivo_nf_size')) { $columns[] = 'arquivo_nf_size'; $placeholders[] = ':arquivo_nf_size'; }
            } else {
                $columns[] = 'arquivo_nf'; $placeholders[] = ':arquivo_nf';
            }
            
            $columns[] = 'evidencias'; $placeholders[] = ':evidencias';
            if ($hasResponsaveis) { $columns[] = 'responsaveis'; $placeholders[] = ':responsaveis'; }
            if ($hasFotos) { $columns[] = 'fotos'; $placeholders[] = ':fotos'; }
            $columns[] = 'data_registro';
            $valuesSql = implode(', ', $placeholders) . ', NOW()';
            $columnsSql = implode(', ', $columns);

            $sql = "INSERT INTO amostragens ($columnsSql) VALUES ($valuesSql)";
            $stmt = $this->db->prepare($sql);

            $params = [
                ':numero_nf' => $numero_nf,
                ':status' => $status,
                ':observacao' => $observacao,
                ':evidencias' => json_encode($evidencias)
            ];
            
            // PDF params
            if ($hasPdfBlob && $pdfBlob) {
                $params[':arquivo_nf_blob'] = $pdfBlob;
                if ($this->hasColumn('amostragens', 'arquivo_nf_name')) { $params[':arquivo_nf_name'] = $pdfName; }
                if ($this->hasColumn('amostragens', 'arquivo_nf_type')) { $params[':arquivo_nf_type'] = $pdfType; }
                if ($this->hasColumn('amostragens', 'arquivo_nf_size')) { $params[':arquivo_nf_size'] = $pdfSize; }
            } else {
                $params[':arquivo_nf'] = $arquivo_nf;
            }
            
            if ($hasResponsaveis) { $params[':responsaveis'] = json_encode($responsaveisParsed); }
            if ($hasFotos) { $params[':fotos'] = json_encode($fotos); }

            $stmt->execute($params);
            $amostragemId = $this->db->lastInsertId();

            // Salvar evidências em tabela separada se existir
            if ($hasEvidTable && !empty($evidencias) && $amostragemId) {
                $this->saveEvidenciasToTable($amostragemId, $_FILES['evidencias'] ?? []);
            }

            // Build names and emails arrays
            $names = [];
            $emails = [];
            foreach ($responsaveisParsed as $rp) {
                if (!empty($rp['name'])) { $names[] = $rp['name']; }
                if (!empty($rp['email'])) { $emails[] = $rp['email']; }
            }

            // Send email to responsaveis
            $this->sendEmailToResponsaveis($names, $emails, $numero_nf, $status, $arquivo_nf, $fotos);
            
            echo json_encode(['success' => true, 'message' => 'Amostragem registrada com sucesso!'], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar amostragem: ' . $e->getMessage()], JSON_UNESCAPED_SLASHES);
        }
    }

    private function sendEmailToResponsaveis(array $names, array $emails, string $numero_nf, string $status, string $arquivo_nf, array $fotos)
    {
        try {
            $users = [];
            // If we don't have emails, try to resolve by names in DB
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
            ";

            if (!empty($arquivo_nf)) {
                $message .= "<p><strong>Anexo PDF:</strong> Disponível no sistema</p>";
            }

            if (!empty($fotos)) {
                $message .= "<p><strong>Fotos:</strong> " . count($fotos) . " foto(s) anexada(s)</p>";
            }

            $message .= "
                <br>
                <p>Acesse o sistema para visualizar todos os detalhes e anexos.</p>
                <p><em>Este é um e-mail automático do Sistema SGQ-OTI DJ</em></p>
            ";

            // Use EmailService if available
            if (class_exists('\\App\\Services\\EmailService')) {
                $emailService = new \App\Services\EmailService();

                // Prefer direct emails provided in request
                if (!empty($emails)) {
                    foreach ($emails as $email) {
                        $emailService->send(
                            $email,
                            '',
                            $subject,
                            $message
                        );
                    }
                } elseif (!empty($users)) {
                    foreach ($users as $user) {
                        if (!empty($user['email'])) {
                            $emailService->send(
                                $user['email'],
                                $user['name'] ?? '',
                                $subject,
                                $message
                            );
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Erro ao enviar email para responsáveis: " . $e->getMessage());
        }
    }

    public function update($id)
    {
        header('Content-Type: application/json');
        
        try {
            $numero_nf = $_POST['numero_nf'] ?? '';
            $status = $_POST['status'] ?? '';
            $observacao = $_POST['observacao'] ?? '';
            
            if (empty($numero_nf) || empty($status)) {
                echo json_encode(['success' => false, 'message' => 'Número da NF e status são obrigatórios']);
                return;
            }

            $stmt = $this->db->prepare("
                UPDATE amostragens 
                SET numero_nf = :numero_nf, status = :status, observacao = :observacao 
                WHERE id = :id
            ");

            $stmt->execute([
                ':numero_nf' => $numero_nf,
                ':status' => $status,
                ':observacao' => $observacao,
                ':id' => $id
            ]);

            echo json_encode(['success' => true, 'message' => 'Amostragem atualizada com sucesso!']);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar amostragem: ' . $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        header('Content-Type: application/json');
        
        try {
            // Get file paths before deletion
            $stmt = $this->db->prepare("SELECT arquivo_nf, evidencias FROM amostragens WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $amostragem = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($amostragem) {
                // Delete files
                if (!empty($amostragem['arquivo_nf']) && file_exists($amostragem['arquivo_nf'])) {
                    unlink($amostragem['arquivo_nf']);
                }

                if (!empty($amostragem['evidencias'])) {
                    $evidencias = json_decode($amostragem['evidencias'], true);
                    if (is_array($evidencias)) {
                        foreach ($evidencias as $evidencia) {
                            if (file_exists($evidencia)) {
                                unlink($evidencia);
                            }
                        }
                    }
                }
            }

            // Delete record
            $stmt = $this->db->prepare("DELETE FROM amostragens WHERE id = :id");
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Amostragem excluída com sucesso!']);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir amostragem: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM amostragens WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $amostragem = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$amostragem) {
                echo json_encode(['success' => false, 'message' => 'Amostragem não encontrada']);
                return;
            }

            // Serve PDF file: preferir BLOB
            if (!empty($amostragem['arquivo_nf_blob'])) {
                $filename = $amostragem['arquivo_nf_name'] ?? ('NF_' . $amostragem['numero_nf'] . '.pdf');
                $type = $amostragem['arquivo_nf_type'] ?? 'application/pdf';
                header('Content-Type: ' . $type);
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                echo $amostragem['arquivo_nf_blob'];
                exit;
            }
            
            // Fallback: filesystem
            if (!empty($amostragem['arquivo_nf']) && file_exists($amostragem['arquivo_nf'])) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="NF_' . $amostragem['numero_nf'] . '.pdf"');
                readfile($amostragem['arquivo_nf']);
                exit;
            }

            echo json_encode(['success' => false, 'message' => 'Arquivo PDF não encontrado']);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar amostragem: ' . $e->getMessage()]);
        }
    }

    // Helper: verifica se a coluna existe na tabela
    private function hasColumn(string $table, string $column): bool
    {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
            $stmt->execute([$column]);
            return (bool)$stmt->fetch();
        } catch (\PDOException $e) {
            return false;
        }
    }

    // Helper: verifica se a tabela existe
    private function hasTable(string $table): bool
    {
        try {
            $stmt = $this->db->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            return (bool)$stmt->fetch();
        } catch (\PDOException $e) {
            return false;
        }
    }

    // Salva evidências na tabela separada
    private function saveEvidenciasToTable(int $amostragemId, array $files): void
    {
        if (empty($files['tmp_name'])) return;
        
        $stmt = $this->db->prepare("INSERT INTO amostragens_evidencias (amostragem_id, image, name, type, size) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($files['tmp_name'] as $key => $tmpName) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $imageBlob = file_get_contents($tmpName);
                $name = $files['name'][$key] ?? null;
                $type = $files['type'][$key] ?? null;
                $size = $files['size'][$key] ?? 0;
                
                $stmt->execute([$amostragemId, $imageBlob, $name, $type, $size]);
            }
        }
    }

    // Endpoint para servir evidências do banco
    public function evidencia($amostragemId, $evidenciaId)
    {
        try {
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
}
