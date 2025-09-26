<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class GarantiasController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal
    public function index()
    {
        try {
            $fornecedores = $this->getFornecedores();
            
            $title = 'Garantias - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/garantias/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // Criar nova garantia
    public function create()
    {
        header('Content-Type: application/json');
        
        try {
            $fornecedor_id = (int)($_POST['fornecedor_id'] ?? 0);
            $origem_garantia = $_POST['origem_garantia'] ?? '';
            $numero_nf_compras = trim($_POST['numero_nf_compras'] ?? '');
            $numero_nf_remessa_simples = trim($_POST['numero_nf_remessa_simples'] ?? '');
            $numero_nf_remessa_devolucao = trim($_POST['numero_nf_remessa_devolucao'] ?? '');
            $numero_serie = trim($_POST['numero_serie'] ?? '');
            $numero_lote = trim($_POST['numero_lote'] ?? '');
            $numero_ticket_os = trim($_POST['numero_ticket_os'] ?? '');
            $status = $_POST['status'] ?? 'Em andamento';
            $observacao = trim($_POST['observacao'] ?? '');
            $user_id = $_SESSION['user_id'];

            // Validações
            if ($fornecedor_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Fornecedor é obrigatório']);
                return;
            }

            if (empty($origem_garantia)) {
                echo json_encode(['success' => false, 'message' => 'Origem da garantia é obrigatória']);
                return;
            }

            // Validar observação obrigatória para status específicos
            $statusComObservacao = ['Finalizado', 'Garantia Expirada', 'Garantia não coberta'];
            if (in_array($status, $statusComObservacao) && empty($observacao)) {
                echo json_encode(['success' => false, 'message' => 'Observação é obrigatória para este status']);
                return;
            }

            // Validar itens
            $itens = json_decode($_POST['itens'] ?? '[]', true);
            if (empty($itens)) {
                echo json_encode(['success' => false, 'message' => 'Pelo menos um item é obrigatório']);
                return;
            }

            $this->db->beginTransaction();

            // Inserir garantia
            $stmt = $this->db->prepare("
                INSERT INTO garantias (
                    fornecedor_id, origem_garantia, numero_nf_compras, numero_nf_remessa_simples, 
                    numero_nf_remessa_devolucao, numero_serie, numero_lote, 
                    numero_ticket_os, status, observacao
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $fornecedor_id, $origem_garantia, $numero_nf_compras, $numero_nf_remessa_simples,
                $numero_nf_remessa_devolucao, $numero_serie, $numero_lote,
                $numero_ticket_os, $status, $observacao
            ]);

            $garantia_id = $this->db->lastInsertId();

            // Inserir itens
            foreach ($itens as $index => $item) {
                if (empty($item['descricao']) || $item['quantidade'] <= 0 || $item['valor_unitario'] < 0) {
                    continue;
                }

                $stmt = $this->db->prepare("
                    INSERT INTO garantias_itens (
                        garantia_id, descricao, quantidade, valor_unitario
                    ) VALUES (?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $garantia_id,
                    trim($item['descricao']),
                    (int)$item['quantidade'],
                    (float)$item['valor_unitario']
                ]);
            }

            // Processar anexos
            $this->processarAnexos($garantia_id);

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Garantia criada com sucesso!', 'id' => $garantia_id]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao criar garantia: ' . $e->getMessage()]);
        }
    }

    // Listar garantias
    public function list()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT g.*, f.nome as fornecedor_nome,
                       COUNT(ga.id) as total_anexos
                FROM garantias g
                LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
                LEFT JOIN garantias_anexos ga ON g.id = ga.garantia_id
                GROUP BY g.id
                ORDER BY g.created_at DESC
            ");
            $stmt->execute();
            $garantias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $garantias]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao listar garantias: ' . $e->getMessage()]);
        }
    }

    // Obter detalhes de uma garantia
    public function show($id)
    {
        header('Content-Type: application/json');
        
        try {
            // Buscar garantia
            $stmt = $this->db->prepare("
                SELECT g.*, f.nome as fornecedor_nome, u.name as criador_nome
                FROM garantias g
                LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
                LEFT JOIN users u ON g.created_by = u.id
                WHERE g.id = ?
            ");
            $stmt->execute([$id]);
            $garantia = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$garantia) {
                echo json_encode(['success' => false, 'message' => 'Garantia não encontrada']);
                return;
            }

            // Buscar itens
            $stmt = $this->db->prepare("
                SELECT * FROM garantias_itens 
                WHERE garantia_id = ? 
                ORDER BY ordem
            ");
            $stmt->execute([$id]);
            $garantia['itens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar anexos
            $stmt = $this->db->prepare("
                SELECT id, tipo_anexo, nome_arquivo, tamanho_arquivo, descricao, created_at
                FROM garantias_anexos 
                WHERE garantia_id = ?
                ORDER BY tipo_anexo, created_at
            ");
            $stmt->execute([$id]);
            $garantia['anexos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $garantia]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar garantia: ' . $e->getMessage()]);
        }
    }

    // Atualizar garantia
    public function update($id)
    {
        header('Content-Type: application/json');
        
        try {
            $fornecedor_id = (int)($_POST['fornecedor_id'] ?? 0);
            $origem_garantia = $_POST['origem_garantia'] ?? '';
            $numero_nf_compras = trim($_POST['numero_nf_compras'] ?? '');
            $numero_nf_remessa_simples = trim($_POST['numero_nf_remessa_simples'] ?? '');
            $numero_nf_remessa_devolucao = trim($_POST['numero_nf_remessa_devolucao'] ?? '');
            $numero_serie = trim($_POST['numero_serie'] ?? '');
            $numero_lote = trim($_POST['numero_lote'] ?? '');
            $numero_ticket_os = trim($_POST['numero_ticket_os'] ?? '');
            $status = $_POST['status'] ?? 'Em andamento';
            $observacao = trim($_POST['observacao'] ?? '');

            // Validações
            if ($fornecedor_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Fornecedor é obrigatório']);
                return;
            }

            if (empty($origem_garantia)) {
                echo json_encode(['success' => false, 'message' => 'Origem da garantia é obrigatória']);
                return;
            }

            // Validar observação obrigatória para status específicos
            $statusComObservacao = ['Finalizado', 'Garantia Expirada', 'Garantia não coberta'];
            if (in_array($status, $statusComObservacao) && empty($observacao)) {
                echo json_encode(['success' => false, 'message' => 'Observação é obrigatória para este status']);
                return;
            }

            $this->db->beginTransaction();

            // Atualizar garantia
            $stmt = $this->db->prepare("
                UPDATE garantias SET
                    fornecedor_id = ?, numero_nf_compras = ?, numero_nf_remessa_simples = ?,
                    numero_nf_remessa_devolucao = ?, numero_serie = ?, numero_lote = ?,
                    numero_ticket_os = ?, origem_garantia = ?, status = ?, observacao = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $stmt->execute([
                $fornecedor_id, $numero_nf_compras, $numero_nf_remessa_simples,
                $numero_nf_remessa_devolucao, $numero_serie, $numero_lote,
                $numero_ticket_os, $origem_garantia, $status, $observacao, $id
            ]);

            // Atualizar itens se fornecidos
            if (isset($_POST['itens'])) {
                $itens = json_decode($_POST['itens'], true);
                
                // Remover itens existentes
                $stmt = $this->db->prepare("DELETE FROM garantias_itens WHERE garantia_id = ?");
                $stmt->execute([$id]);

                // Inserir novos itens
                foreach ($itens as $index => $item) {
                    if (empty($item['item']) || $item['quantidade'] <= 0 || $item['valor_unitario'] < 0) {
                        continue;
                    }

                    $stmt = $this->db->prepare("
                        INSERT INTO garantias_itens (
                            garantia_id, item, quantidade, valor_unitario, defeito, ordem
                        ) VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $id,
                        trim($item['item']),
                        (int)$item['quantidade'],
                        (float)$item['valor_unitario'],
                        trim($item['defeito'] ?? ''),
                        $index + 1
                    ]);
                }
            }

            // Processar novos anexos se houver
            if (!empty($_FILES)) {
                $this->processarAnexos($id);
            }

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Garantia atualizada com sucesso!']);

        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar garantia: ' . $e->getMessage()]);
        }
    }

    // Excluir garantia
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("DELETE FROM garantias WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Garantia não encontrada']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Garantia excluída com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir garantia: ' . $e->getMessage()]);
        }
    }

    // Download de anexo
    public function downloadAnexo($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT nome_arquivo, tipo_arquivo, tamanho_arquivo, conteudo_arquivo
                FROM garantias_anexos 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anexo) {
                http_response_code(404);
                echo 'Anexo não encontrado';
                return;
            }

            header('Content-Type: ' . $anexo['tipo_arquivo']);
            header('Content-Length: ' . $anexo['tamanho_arquivo']);
            header('Content-Disposition: attachment; filename="' . $anexo['nome_arquivo'] . '"');
            
            echo $anexo['conteudo_arquivo'];
            exit();

        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao baixar anexo: ' . $e->getMessage();
        }
    }

    // Excluir anexo
    public function deleteAnexo($id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("DELETE FROM garantias_anexos WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Anexo não encontrado']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Anexo excluído com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir anexo: ' . $e->getMessage()]);
        }
    }

    // Listar fornecedores (endpoint AJAX)
    public function listFornecedores()
    {
        header('Content-Type: application/json');
        
        try {
            $fornecedores = $this->getFornecedores();
            
            // Debug adicional
            $debug = [
                'count' => count($fornecedores),
                'query_executed' => true,
                'sample' => array_slice($fornecedores, 0, 3) // Primeiros 3 registros
            ];
            
            echo json_encode([
                'success' => true, 
                'data' => $fornecedores,
                'debug' => $debug
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao carregar fornecedores: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
    }

    // Métodos auxiliares
    private function processarAnexos($garantia_id)
    {
        $tiposAnexos = [
            'anexo_nf_compras' => 'nf_compras',
            'anexo_nf_remessa_simples' => 'nf_remessa_simples',
            'anexo_nf_remessa_devolucao' => 'nf_remessa_devolucao',
            'anexo_laudo_tecnico' => 'laudo_tecnico'
        ];

        // Processar anexos específicos
        foreach ($tiposAnexos as $campo => $tipo) {
            if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
                $this->salvarAnexo($garantia_id, $_FILES[$campo], $tipo);
            }
        }

        // Processar evidências (múltiplas imagens)
        if (isset($_FILES['anexo_evidencias']) && is_array($_FILES['anexo_evidencias']['tmp_name'])) {
            foreach ($_FILES['anexo_evidencias']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['anexo_evidencias']['error'][$index] === UPLOAD_ERR_OK) {
                    $arquivo = [
                        'name' => $_FILES['anexo_evidencias']['name'][$index],
                        'type' => $_FILES['anexo_evidencias']['type'][$index],
                        'tmp_name' => $tmpName,
                        'size' => $_FILES['anexo_evidencias']['size'][$index]
                    ];
                    $this->salvarAnexo($garantia_id, $arquivo, 'evidencia');
                }
            }
        }
    }

    private function salvarAnexo($garantia_id, $arquivo, $tipo)
    {
        // Validar tipo de arquivo
        $tiposPermitidos = [
            'application/pdf', 
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg', 
            'image/jpg',
            'image/png', 
            'image/gif',
            'image/webp'
        ];
        
        if (!in_array($arquivo['type'], $tiposPermitidos)) {
            throw new \Exception('Tipo de arquivo não permitido: ' . $arquivo['name']);
        }

        // Validar tamanho (10MB para PDFs/DOCs, 5MB para imagens)
        $maxSize = in_array($arquivo['type'], ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']) 
                   ? 5 * 1024 * 1024  // 5MB para imagens
                   : 10 * 1024 * 1024; // 10MB para documentos
        
        if ($arquivo['size'] > $maxSize) {
            $maxSizeMB = $maxSize / (1024 * 1024);
            throw new \Exception('Arquivo muito grande: ' . $arquivo['name'] . '. Máximo ' . $maxSizeMB . 'MB');
        }

        $conteudo = file_get_contents($arquivo['tmp_name']);

        $stmt = $this->db->prepare("
            INSERT INTO garantias_anexos (
                garantia_id, tipo_anexo, nome_arquivo, tipo_mime, 
                tamanho_bytes, conteudo_arquivo
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $garantia_id,
            $tipo,
            $arquivo['name'],
            $arquivo['type'],
            $arquivo['size'],
            $conteudo
        ]);
    }

    private function getFornecedores(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nome FROM fornecedores ORDER BY nome");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Log do erro para debug
            error_log("Erro ao buscar fornecedores: " . $e->getMessage());
            return [];
        }
    }
}
