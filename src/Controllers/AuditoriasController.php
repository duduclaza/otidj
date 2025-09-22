<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class AuditoriasController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal - Lista de auditorias
    public function index()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'auditorias', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $filiais = $this->getFiliais();
            
            // Usar o layout padrão com TailwindCSS
            $title = 'Auditorias - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/auditorias/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    // Listar auditorias com filtros
    public function listAuditorias()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'auditorias', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar auditorias']);
                return;
            }

            // Filtros
            $filial_id = $_GET['filial_id'] ?? '';
            $data_inicio = $_GET['data_inicio'] ?? '';
            $data_fim = $_GET['data_fim'] ?? '';

            // Construir query base
            $sql = "
                SELECT a.*, 
                       f.nome as filial_nome,
                       uc.name as criado_por_nome,
                       ua.name as atualizado_por_nome
                FROM auditorias a
                LEFT JOIN filiais f ON a.filial_id = f.id
                LEFT JOIN users uc ON a.created_by = uc.id
                LEFT JOIN users ua ON a.updated_by = ua.id
                WHERE 1=1
            ";
            
            $params = [];

            // Filtros
            if ($filial_id) {
                $sql .= " AND a.filial_id = ?";
                $params[] = $filial_id;
            }
            
            if ($data_inicio) {
                $sql .= " AND a.data_auditoria_inicio >= ?";
                $params[] = $data_inicio;
            }
            
            if ($data_fim) {
                $sql .= " AND a.data_auditoria_fim <= ?";
                $params[] = $data_fim;
            }

            $sql .= " ORDER BY a.data_auditoria_inicio DESC, a.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $auditorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Adicionar informação se tem anexo
            foreach ($auditorias as &$auditoria) {
                $auditoria['tem_anexo'] = !empty($auditoria['anexo_auditoria_blob']);
                // Remover o blob da resposta para economizar bandwidth
                unset($auditoria['anexo_auditoria_blob']);
            }

            echo json_encode(['success' => true, 'data' => $auditorias]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar auditorias: ' . $e->getMessage()]);
        }
    }

    // Criar nova auditoria
    public function create()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'auditorias', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para criar auditorias']);
                return;
            }

            // Validações
            $required = ['filial_id', 'data_auditoria_inicio', 'data_auditoria_fim'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => "Campo '{$field}' é obrigatório"]);
                    return;
                }
            }

            // Validar datas
            $data_inicio = $_POST['data_auditoria_inicio'];
            $data_fim = $_POST['data_auditoria_fim'];
            
            if ($data_inicio > $data_fim) {
                echo json_encode(['success' => false, 'message' => 'Data de início não pode ser maior que data de fim']);
                return;
            }

            // Processar upload do anexo
            $anexo_blob = null;
            $anexo_nome = null;
            $anexo_tipo = null;
            $anexo_tamanho = null;

            if (isset($_FILES['anexo_auditoria']) && $_FILES['anexo_auditoria']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['anexo_auditoria'];
                
                // Validar tamanho (máximo 15MB)
                if ($file['size'] > 15 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 15MB permitido.']);
                    return;
                }

                // Validar tipo de arquivo
                $allowed_types = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ];
                if (!in_array($file['type'], $allowed_types)) {
                    echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use PDF ou DOC/DOCX.']);
                    return;
                }

                $anexo_blob = file_get_contents($file['tmp_name']);
                $anexo_nome = $file['name'];
                $anexo_tipo = $file['type'];
                $anexo_tamanho = $file['size'];
            }

            // Inserir auditoria
            $stmt = $this->db->prepare("
                INSERT INTO auditorias (
                    filial_id, data_auditoria_inicio, data_auditoria_fim, 
                    anexo_auditoria_blob, anexo_auditoria_nome, anexo_auditoria_tipo, 
                    anexo_auditoria_tamanho, observacoes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['filial_id'],
                $data_inicio,
                $data_fim,
                $anexo_blob,
                $anexo_nome,
                $anexo_tipo,
                $anexo_tamanho,
                $_POST['observacoes'] ?? null,
                $_SESSION['user_id']
            ]);

            $auditoria_id = $this->db->lastInsertId();

            echo json_encode(['success' => true, 'message' => 'Auditoria registrada com sucesso!', 'auditoria_id' => $auditoria_id]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar auditoria: ' . $e->getMessage()]);
        }
    }

    // Atualizar auditoria
    public function update()
    {
        header('Content-Type: application/json');
        
        try {
            $auditoria_id = $_POST['id'] ?? 0;

            if (!$auditoria_id) {
                echo json_encode(['success' => false, 'message' => 'ID da auditoria é obrigatório']);
                return;
            }

            // Verificar se a auditoria existe
            $auditoria = $this->getAuditoriaById($auditoria_id);
            if (!$auditoria) {
                echo json_encode(['success' => false, 'message' => 'Auditoria não encontrada']);
                return;
            }

            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'auditorias', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para editar auditorias']);
                return;
            }

            // Validações
            $required = ['filial_id', 'data_auditoria_inicio', 'data_auditoria_fim'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => "Campo '{$field}' é obrigatório"]);
                    return;
                }
            }

            // Validar datas
            $data_inicio = $_POST['data_auditoria_inicio'];
            $data_fim = $_POST['data_auditoria_fim'];
            
            if ($data_inicio > $data_fim) {
                echo json_encode(['success' => false, 'message' => 'Data de início não pode ser maior que data de fim']);
                return;
            }

            // Processar upload do anexo (se houver)
            $anexo_blob = $auditoria['anexo_auditoria_blob'];
            $anexo_nome = $auditoria['anexo_auditoria_nome'];
            $anexo_tipo = $auditoria['anexo_auditoria_tipo'];
            $anexo_tamanho = $auditoria['anexo_auditoria_tamanho'];

            if (isset($_FILES['anexo_auditoria']) && $_FILES['anexo_auditoria']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['anexo_auditoria'];
                
                // Validar tamanho (máximo 15MB)
                if ($file['size'] > 15 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 15MB permitido.']);
                    return;
                }

                // Validar tipo de arquivo
                $allowed_types = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ];
                if (!in_array($file['type'], $allowed_types)) {
                    echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use PDF ou DOC/DOCX.']);
                    return;
                }

                $anexo_blob = file_get_contents($file['tmp_name']);
                $anexo_nome = $file['name'];
                $anexo_tipo = $file['type'];
                $anexo_tamanho = $file['size'];
            }

            // Atualizar auditoria
            $stmt = $this->db->prepare("
                UPDATE auditorias SET 
                    filial_id = ?, data_auditoria_inicio = ?, data_auditoria_fim = ?, 
                    anexo_auditoria_blob = ?, anexo_auditoria_nome = ?, anexo_auditoria_tipo = ?, 
                    anexo_auditoria_tamanho = ?, observacoes = ?, updated_by = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['filial_id'],
                $data_inicio,
                $data_fim,
                $anexo_blob,
                $anexo_nome,
                $anexo_tipo,
                $anexo_tamanho,
                $_POST['observacoes'] ?? null,
                $_SESSION['user_id'],
                $auditoria_id
            ]);

            echo json_encode(['success' => true, 'message' => 'Auditoria atualizada com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar auditoria: ' . $e->getMessage()]);
        }
    }

    // Excluir auditoria
    public function delete()
    {
        header('Content-Type: application/json');
        
        try {
            $auditoria_id = $_POST['id'] ?? 0;

            if (!$auditoria_id) {
                echo json_encode(['success' => false, 'message' => 'ID da auditoria é obrigatório']);
                return;
            }

            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'auditorias', 'delete')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para excluir auditorias']);
                return;
            }

            // Verificar se a auditoria existe
            $auditoria = $this->getAuditoriaById($auditoria_id);
            if (!$auditoria) {
                echo json_encode(['success' => false, 'message' => 'Auditoria não encontrada']);
                return;
            }

            // Excluir auditoria
            $stmt = $this->db->prepare("DELETE FROM auditorias WHERE id = ?");
            $stmt->execute([$auditoria_id]);

            echo json_encode(['success' => true, 'message' => 'Auditoria excluída com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir auditoria: ' . $e->getMessage()]);
        }
    }

    // Obter detalhes de uma auditoria
    public function getAuditoria($id)
    {
        header('Content-Type: application/json');
        
        try {
            $auditoria = $this->getAuditoriaById($id);
            if (!$auditoria) {
                echo json_encode(['success' => false, 'message' => 'Auditoria não encontrada']);
                return;
            }

            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'auditorias', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar esta auditoria']);
                return;
            }

            // Adicionar informação se tem anexo (sem retornar o blob)
            $auditoria['tem_anexo'] = !empty($auditoria['anexo_auditoria_blob']);
            unset($auditoria['anexo_auditoria_blob']); // Remover blob para economizar bandwidth

            echo json_encode(['success' => true, 'auditoria' => $auditoria]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar auditoria: ' . $e->getMessage()]);
        }
    }

    // Download do anexo
    public function downloadAnexo($id)
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'auditorias', 'view')) {
                http_response_code(403);
                echo 'Sem permissão para visualizar anexos';
                return;
            }

            $stmt = $this->db->prepare("
                SELECT anexo_auditoria_blob, anexo_auditoria_nome, anexo_auditoria_tipo 
                FROM auditorias 
                WHERE id = ? AND anexo_auditoria_blob IS NOT NULL
            ");
            $stmt->execute([$id]);
            $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anexo) {
                http_response_code(404);
                echo 'Anexo não encontrado';
                return;
            }

            // Definir headers para download
            header('Content-Type: ' . $anexo['anexo_auditoria_tipo']);
            header('Content-Disposition: attachment; filename="' . $anexo['anexo_auditoria_nome'] . '"');
            header('Content-Length: ' . strlen($anexo['anexo_auditoria_blob']));

            echo $anexo['anexo_auditoria_blob'];
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao baixar anexo: ' . $e->getMessage();
        }
    }

    // Métodos auxiliares
    private function getAuditoriaById($id)
    {
        $stmt = $this->db->prepare("
            SELECT a.*, 
                   f.nome as filial_nome,
                   uc.name as criado_por_nome,
                   ua.name as atualizado_por_nome
            FROM auditorias a
            LEFT JOIN filiais f ON a.filial_id = f.id
            LEFT JOIN users uc ON a.created_by = uc.id
            LEFT JOIN users ua ON a.updated_by = ua.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getFiliais()
    {
        $stmt = $this->db->query("SELECT id, nome FROM filiais ORDER BY nome");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Relatórios
    public function relatorios()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'auditorias', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $filiais = $this->getFiliais();
            
            $title = 'Auditorias - Relatórios - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/auditorias/relatorios.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }
}
