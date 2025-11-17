<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class ControleDescartesController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal - Lista de descartes
    public function index()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $filiais = $this->getFiliais();
            $usuariosNotificacao = $this->getUsuariosParaNotificacao();
            
            // Usar o layout padrão com TailwindCSS
            $title = 'Controle de Descartes - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/controle-descartes/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    // Listar descartes com filtros
    public function listDescartes()
    {
        // Limpar qualquer output anterior
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar descartes']);
                return;
            }

            // Filtros
            $numero_serie = $_GET['numero_serie'] ?? '';
            $numero_os = $_GET['numero_os'] ?? '';
            $filial_id = $_GET['filial_id'] ?? '';
            $data_inicio = $_GET['data_inicio'] ?? '';
            $data_fim = $_GET['data_fim'] ?? '';

            // Construir query base
            $sql = "
                SELECT d.*, 
                       f.nome as filial_nome,
                       uc.name as criado_por_nome,
                       ua.name as atualizado_por_nome
                FROM controle_descartes d
                LEFT JOIN filiais f ON d.filial_id = f.id
                LEFT JOIN users uc ON d.created_by = uc.id
                LEFT JOIN users ua ON d.updated_by = ua.id
                WHERE 1=1
            ";
            
            $params = [];

            // Filtros
            if ($numero_serie) {
                $sql .= " AND d.numero_serie LIKE ?";
                $params[] = "%{$numero_serie}%";
            }
            
            if ($numero_os) {
                $sql .= " AND d.numero_os LIKE ?";
                $params[] = "%{$numero_os}%";
            }
            
            if ($filial_id) {
                $sql .= " AND d.filial_id = ?";
                $params[] = $filial_id;
            }
            
            if ($data_inicio) {
                $sql .= " AND d.data_descarte >= ?";
                $params[] = $data_inicio;
            }
            
            if ($data_fim) {
                $sql .= " AND d.data_descarte <= ?";
                $params[] = $data_fim;
            }

            $sql .= " ORDER BY d.data_descarte DESC, d.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $descartes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Adicionar informação se tem anexo
            foreach ($descartes as &$descarte) {
                $descarte['tem_anexo'] = !empty($descarte['anexo_os_blob']);
                // Remover o blob da resposta para economizar bandwidth
                unset($descarte['anexo_os_blob']);
            }

            echo json_encode(['success' => true, 'data' => $descartes]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar descartes: ' . $e->getMessage()]);
        }
    }

    // Criar novo descarte
    public function create()
    {
        // Limpar qualquer output anterior
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para criar descartes']);
                return;
            }

            // Validações
            $required = ['numero_serie', 'filial_id', 'codigo_produto', 'descricao_produto', 'responsavel_tecnico'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => "Campo '{$field}' é obrigatório"]);
                    return;
                }
            }
            
            // Validar se pelo menos um usuário foi selecionado para notificação
            if (empty($_POST['notificar_usuarios']) || !is_array($_POST['notificar_usuarios'])) {
                echo json_encode(['success' => false, 'message' => 'Selecione pelo menos um usuário para notificar']);
                return;
            }
            
            // Converter array de IDs em string separada por vírgula
            $notificarUsuarios = null;
            if (!empty($_POST['notificar_usuarios']) && is_array($_POST['notificar_usuarios'])) {
                $notificarUsuarios = implode(',', array_map('intval', $_POST['notificar_usuarios']));
            }

            // Data do descarte (se não informada, usar hoje)
            $data_descarte = !empty($_POST['data_descarte']) ? $_POST['data_descarte'] : date('Y-m-d');

            // Processar upload do anexo
            $anexo_blob = null;
            $anexo_nome = null;
            $anexo_tipo = null;
            $anexo_tamanho = null;

            if (isset($_FILES['anexo_os']) && $_FILES['anexo_os']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['anexo_os'];
                
                // Validar tamanho (máximo 10MB)
                if ($file['size'] > 10 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 10MB permitido.']);
                    return;
                }

                // Validar tipo de arquivo
                $allowed_types = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'];
                if (!in_array($file['type'], $allowed_types)) {
                    echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use PNG, JPEG ou PDF.']);
                    return;
                }

                $anexo_blob = file_get_contents($file['tmp_name']);
                $anexo_nome = $file['name'];
                $anexo_tipo = $file['type'];
                $anexo_tamanho = $file['size'];
            }

            // Verificar se coluna notificar_usuarios existe
            $colunaNotificarExiste = false;
            try {
                $checkCol = $this->db->query("SHOW COLUMNS FROM controle_descartes LIKE 'notificar_usuarios'");
                $colunaNotificarExiste = $checkCol->rowCount() > 0;
            } catch (\Exception $e) {
                $colunaNotificarExiste = false;
            }

            // Inserir descarte com status inicial "Aguardando Descarte"
            if ($colunaNotificarExiste) {
                $stmt = $this->db->prepare("
                    INSERT INTO controle_descartes (
                        numero_serie, filial_id, codigo_produto, descricao_produto, 
                        data_descarte, numero_os, anexo_os_blob, anexo_os_nome, 
                        anexo_os_tipo, anexo_os_tamanho, responsavel_tecnico, 
                        observacoes, notificar_usuarios, status, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aguardando Descarte', ?)
                ");
                
                $stmt->execute([
                    $_POST['numero_serie'],
                    $_POST['filial_id'],
                    $_POST['codigo_produto'],
                    $_POST['descricao_produto'],
                    $data_descarte,
                    $_POST['numero_os'] ?? null,
                    $anexo_blob,
                    $anexo_nome,
                    $anexo_tipo,
                    $anexo_tamanho,
                    $_POST['responsavel_tecnico'],
                    $_POST['observacoes'] ?? null,
                    $notificarUsuarios,
                    $_SESSION['user_id']
                ]);
            } else {
                // Sem coluna notificar_usuarios
                $stmt = $this->db->prepare("
                    INSERT INTO controle_descartes (
                        numero_serie, filial_id, codigo_produto, descricao_produto, 
                        data_descarte, numero_os, anexo_os_blob, anexo_os_nome, 
                        anexo_os_tipo, anexo_os_tamanho, responsavel_tecnico, 
                        observacoes, status, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aguardando Descarte', ?)
                ");
                
                $stmt->execute([
                    $_POST['numero_serie'],
                    $_POST['filial_id'],
                    $_POST['codigo_produto'],
                    $_POST['descricao_produto'],
                    $data_descarte,
                    $_POST['numero_os'] ?? null,
                    $anexo_blob,
                    $anexo_nome,
                    $anexo_tipo,
                    $anexo_tamanho,
                    $_POST['responsavel_tecnico'],
                    $_POST['observacoes'] ?? null,
                    $_SESSION['user_id']
                ]);
            }

            $descarte_id = $this->db->lastInsertId();
            
            // Enviar notificação por email para admins e qualidade
            try {
                $this->notificarNovoDescarte($descarte_id);
            } catch (\Exception $emailError) {
                error_log('Erro ao enviar notificação de novo descarte: ' . $emailError->getMessage());
                // Não falhar a criação se email falhar
            }

            echo json_encode(['success' => true, 'message' => 'Descarte registrado com sucesso!', 'descarte_id' => $descarte_id]);
        } catch (\PDOException $e) {
            error_log('Erro PDO no controle de descartes: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro de banco de dados: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            error_log('Erro geral no controle de descartes: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar descarte: ' . $e->getMessage()]);
        }
    }

    // Atualizar descarte
    public function update()
    {
        // Limpar qualquer output anterior
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $descarte_id = $_POST['id'] ?? 0;

            if (!$descarte_id) {
                echo json_encode(['success' => false, 'message' => 'ID do descarte é obrigatório']);
                return;
            }

            // Verificar se o descarte existe
            $descarte = $this->getDescarteById($descarte_id);
            if (!$descarte) {
                echo json_encode(['success' => false, 'message' => 'Descarte não encontrado']);
                return;
            }

            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para editar descartes']);
                return;
            }

            // Validações
            $required = ['numero_serie', 'filial_id', 'codigo_produto', 'descricao_produto', 'responsavel_tecnico'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => "Campo '{$field}' é obrigatório"]);
                    return;
                }
            }

            // Data do descarte
            $data_descarte = !empty($_POST['data_descarte']) ? $_POST['data_descarte'] : $descarte['data_descarte'];

            // Processar upload do anexo (se houver)
            $anexo_blob = $descarte['anexo_os_blob'];
            $anexo_nome = $descarte['anexo_os_nome'];
            $anexo_tipo = $descarte['anexo_os_tipo'];
            $anexo_tamanho = $descarte['anexo_os_tamanho'];

            if (isset($_FILES['anexo_os']) && $_FILES['anexo_os']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['anexo_os'];
                
                // Validar tamanho (máximo 10MB)
                if ($file['size'] > 10 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 10MB permitido.']);
                    return;
                }

                // Validar tipo de arquivo
                $allowed_types = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'];
                if (!in_array($file['type'], $allowed_types)) {
                    echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use PNG, JPEG ou PDF.']);
                    return;
                }

                $anexo_blob = file_get_contents($file['tmp_name']);
                $anexo_nome = $file['name'];
                $anexo_tipo = $file['type'];
                $anexo_tamanho = $file['size'];
            }

            // Atualizar descarte
            $stmt = $this->db->prepare("
                UPDATE controle_descartes SET 
                    numero_serie = ?, filial_id = ?, codigo_produto = ?, 
                    descricao_produto = ?, data_descarte = ?, numero_os = ?, 
                    anexo_os_blob = ?, anexo_os_nome = ?, anexo_os_tipo = ?, 
                    anexo_os_tamanho = ?, responsavel_tecnico = ?, 
                    observacoes = ?, updated_by = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['numero_serie'],
                $_POST['filial_id'],
                $_POST['codigo_produto'],
                $_POST['descricao_produto'],
                $data_descarte,
                $_POST['numero_os'] ?? null,
                $anexo_blob,
                $anexo_nome,
                $anexo_tipo,
                $anexo_tamanho,
                $_POST['responsavel_tecnico'],
                $_POST['observacoes'] ?? null,
                $_SESSION['user_id'],
                $descarte_id
            ]);

            echo json_encode(['success' => true, 'message' => 'Descarte atualizado com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar descarte: ' . $e->getMessage()]);
        }
    }

    // Excluir descarte
    public function delete()
    {
        // Limpar qualquer output anterior
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $descarte_id = $_POST['id'] ?? 0;

            if (!$descarte_id) {
                echo json_encode(['success' => false, 'message' => 'ID do descarte é obrigatório']);
                return;
            }

            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'delete')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para excluir descartes']);
                return;
            }

            // Verificar se o descarte existe
            $descarte = $this->getDescarteById($descarte_id);
            if (!$descarte) {
                echo json_encode(['success' => false, 'message' => 'Descarte não encontrado']);
                return;
            }

            // Excluir descarte
            $stmt = $this->db->prepare("DELETE FROM controle_descartes WHERE id = ?");
            $stmt->execute([$descarte_id]);

            echo json_encode(['success' => true, 'message' => 'Descarte excluído com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir descarte: ' . $e->getMessage()]);
        }
    }

    // Obter detalhes de um descarte
    public function getDescarte($id)
    {
        // Limpar qualquer output anterior
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $descarte = $this->getDescarteById($id);
            if (!$descarte) {
                echo json_encode(['success' => false, 'message' => 'Descarte não encontrado']);
                return;
            }

            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar este descarte']);
                return;
            }

            // Adicionar informação se tem anexo (sem retornar o blob)
            $descarte['tem_anexo'] = !empty($descarte['anexo_os_blob']);
            unset($descarte['anexo_os_blob']); // Remover blob para economizar bandwidth

            echo json_encode(['success' => true, 'descarte' => $descarte]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar descarte: ' . $e->getMessage()]);
        }
    }

    // Download do anexo
    public function downloadAnexo($id)
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'view')) {
                http_response_code(403);
                echo 'Sem permissão para visualizar anexos';
                return;
            }

            $stmt = $this->db->prepare("
                SELECT anexo_os_blob, anexo_os_nome, anexo_os_tipo 
                FROM controle_descartes 
                WHERE id = ? AND anexo_os_blob IS NOT NULL
            ");
            $stmt->execute([$id]);
            $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anexo) {
                http_response_code(404);
                echo 'Anexo não encontrado';
                return;
            }

            // Definir headers para download
            header('Content-Type: ' . $anexo['anexo_os_tipo']);
            header('Content-Disposition: attachment; filename="' . $anexo['anexo_os_nome'] . '"');
            header('Content-Length: ' . strlen($anexo['anexo_os_blob']));

            echo $anexo['anexo_os_blob'];
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao baixar anexo: ' . $e->getMessage();
        }
    }

    // Métodos auxiliares
    private function getDescarteById($id)
    {
        $stmt = $this->db->prepare("
            SELECT d.*, 
                   f.nome as filial_nome,
                   uc.name as criado_por_nome,
                   ua.name as atualizado_por_nome
            FROM controle_descartes d
            LEFT JOIN filiais f ON d.filial_id = f.id
            LEFT JOIN users uc ON d.created_by = uc.id
            LEFT JOIN users ua ON d.updated_by = ua.id
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getFiliais()
    {
        $stmt = $this->db->query("SELECT id, nome FROM filiais ORDER BY nome");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getUsuariosParaNotificacao()
    {
        // Buscar todos usuários com email (sem filtrar por status)
        // Alguns sistemas usam 'status', outros 'active', então buscar todos
        try {
            $stmt = $this->db->query("
                SELECT id, name, email, role 
                FROM users 
                WHERE email IS NOT NULL 
                AND email != ''
                ORDER BY name
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Erro ao buscar usuários para notificação: ' . $e->getMessage());
            return [];
        }
    }

    // Relatórios
    public function relatorios()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $filiais = $this->getFiliais();
            
            $title = 'Controle de Descartes - Relatórios - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/controle-descartes/relatorios.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    // Baixar template Excel
    public function downloadTemplate()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'import')) {
                http_response_code(403);
                echo 'Sem permissão para baixar template';
                return;
            }

            // Buscar filiais para o exemplo
            $filiais = $this->getFiliais();
            $filialExemplo = !empty($filiais) ? $filiais[0]['nome'] : 'Jundiaí';

            // Criar CSV com template
            $filename = 'template_descartes_' . date('Ymd') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Abrir output como arquivo
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8 (para Excel reconhecer acentos)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Cabeçalhos (exatamente como no grid)
            $headers = [
                'Número de Série',
                'Filial',
                'Código do Produto',
                'Descrição do Produto',
                'Data do Descarte',
                'Número da OS',
                'Responsável Técnico',
                'Observações'
            ];
            fputcsv($output, $headers, ';');

            // Linha de exemplo
            $exemplo = [
                'SERIE12345',
                $filialExemplo,
                'PROD-001',
                'Impressora HP LaserJet Pro M404dn',
                date('Y-m-d'),
                'OS-2024-001',
                'João Silva',
                'Equipamento com defeito irreparável na placa principal'
            ];
            fputcsv($output, $exemplo, ';');

            fclose($output);
            exit;
        } catch (\Exception $e) {
            error_log('Erro ao gerar template: ' . $e->getMessage());
            http_response_code(500);
            echo 'Erro ao gerar template: ' . $e->getMessage();
        }
    }

    // Importar descartes via Excel/CSV
    public function importar()
    {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'import')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para importar descartes']);
                return;
            }

            // Verificar se arquivo foi enviado
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Nenhum arquivo foi enviado ou erro no upload']);
                return;
            }

            $file = $_FILES['arquivo'];

            // Validar tamanho (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 5MB permitido.']);
                return;
            }

            // Validar tipo
            $allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($extension, ['csv', 'xls', 'xlsx'])) {
                echo json_encode(['success' => false, 'message' => 'Formato de arquivo não suportado. Use CSV, XLS ou XLSX.']);
                return;
            }

            // Ler arquivo CSV
            $filePath = $file['tmp_name'];
            $handle = fopen($filePath, 'r');
            
            if ($handle === false) {
                echo json_encode(['success' => false, 'message' => 'Não foi possível abrir o arquivo']);
                return;
            }

            // Pular BOM se existir
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }

            // Ler cabeçalhos
            $headers = fgetcsv($handle, 0, ';');
            if (!$headers) {
                $headers = fgetcsv($handle, 0, ',');
            }
            
            if (!$headers) {
                fclose($handle);
                echo json_encode(['success' => false, 'message' => 'Arquivo vazio ou formato inválido']);
                return;
            }

            // Buscar mapa de filiais
            $filiais = $this->getFiliais();
            $filiaisMap = [];
            foreach ($filiais as $filial) {
                $filiaisMap[strtolower($filial['nome'])] = $filial['id'];
            }

            $imported = 0;
            $errors = [];
            $linha = 1; // Começar da linha 1 (cabeçalho)

            // Processar cada linha
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                $linha++;
                
                // Tentar com vírgula se ponto e vírgula não funcionou
                if (count($data) == 1 && strpos($data[0], ',') !== false) {
                    $data = str_getcsv($data[0], ',');
                }

                // Pular linhas vazias
                if (empty(array_filter($data))) {
                    continue;
                }

                try {
                    // Mapear dados
                    $numeroSerie = trim($data[0] ?? '');
                    $filialNome = trim($data[1] ?? '');
                    $codigoProduto = trim($data[2] ?? '');
                    $descricaoProduto = trim($data[3] ?? '');
                    $dataDescarte = trim($data[4] ?? '');
                    $numeroOs = trim($data[5] ?? '');
                    $responsavelTecnico = trim($data[6] ?? '');
                    $observacoes = trim($data[7] ?? '');

                    // Validar campos obrigatórios
                    if (empty($numeroSerie) || empty($filialNome) || empty($codigoProduto) || 
                        empty($descricaoProduto) || empty($responsavelTecnico)) {
                        $errors[] = "Linha $linha: Campos obrigatórios faltando";
                        continue;
                    }

                    // Buscar ID da filial
                    $filialId = $filiaisMap[strtolower($filialNome)] ?? null;
                    if (!$filialId) {
                        $errors[] = "Linha $linha: Filial '$filialNome' não encontrada";
                        continue;
                    }

                    // Data do descarte (se vazia, usar hoje)
                    if (empty($dataDescarte)) {
                        $dataDescarte = date('Y-m-d');
                    } else {
                        // Tentar converter data
                        $dataDescarte = date('Y-m-d', strtotime($dataDescarte));
                    }

                    // Inserir descarte
                    $stmt = $this->db->prepare("
                        INSERT INTO controle_descartes (
                            numero_serie, filial_id, codigo_produto, descricao_produto, 
                            data_descarte, numero_os, responsavel_tecnico, 
                            observacoes, created_by
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $numeroSerie,
                        $filialId,
                        $codigoProduto,
                        $descricaoProduto,
                        $dataDescarte,
                        !empty($numeroOs) ? $numeroOs : null,
                        $responsavelTecnico,
                        !empty($observacoes) ? $observacoes : null,
                        $_SESSION['user_id']
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Linha $linha: " . $e->getMessage();
                }
            }

            fclose($handle);

            // Retornar resultado
            echo json_encode([
                'success' => true,
                'imported' => $imported,
                'errors' => $errors,
                'message' => "Importação concluída: $imported registros importados"
            ]);

        } catch (\Exception $e) {
            error_log('Erro na importação: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao processar importação: ' . $e->getMessage()]);
        }
    }
    
    // Alterar status do descarte (apenas admin ou qualidade)
    public function alterarStatus()
    {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $descarte_id = $_POST['id'] ?? 0;
            $novo_status = $_POST['status'] ?? '';
            $justificativa = trim($_POST['justificativa'] ?? '');
            
            if (!$descarte_id) {
                echo json_encode(['success' => false, 'message' => 'ID do descarte é obrigatório']);
                return;
            }
            
            // Validar status
            $status_validos = ['Aguardando Descarte', 'Itens Descartados', 'Descartes Reprovados'];
            if (!in_array($novo_status, $status_validos)) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                return;
            }
            
            // Verificar se usuário tem permissão (admin ou qualidade)
            $user_role = $_SESSION['user_role'] ?? '';
            $user_id = $_SESSION['user_id'] ?? 0;
            
            // Buscar perfis do usuário
            $stmt = $this->db->prepare("
                SELECT p.nome 
                FROM user_profiles up
                JOIN profiles p ON up.profile_id = p.id
                WHERE up.user_id = ?
            ");
            $stmt->execute([$user_id]);
            $perfis = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $tem_permissao = ($user_role === 'admin' || $user_role === 'super_admin' || 
                             in_array('Qualidade', $perfis) || in_array('qualidade', $perfis));
            
            if (!$tem_permissao) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão. Apenas Admin ou Qualidade podem alterar status.']);
                return;
            }
            
            // Verificar se descarte existe
            $descarte = $this->getDescarteById($descarte_id);
            if (!$descarte) {
                echo json_encode(['success' => false, 'message' => 'Descarte não encontrado']);
                return;
            }
            
            // Atualizar status
            $stmt = $this->db->prepare("
                UPDATE controle_descartes 
                SET status = ?,
                    status_alterado_por = ?,
                    status_alterado_em = NOW(),
                    justificativa_status = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $novo_status,
                $user_id,
                $justificativa,
                $descarte_id
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => "Status alterado para '{$novo_status}' com sucesso!"
            ]);
            
        } catch (\Exception $e) {
            error_log('Erro ao alterar status: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao alterar status: ' . $e->getMessage()]);
        }
    }
    
    // Notificar usuários selecionados sobre novo descarte
    private function notificarNovoDescarte($descarte_id)
    {
        try {
            // Buscar dados do descarte
            $descarte = $this->getDescarteById($descarte_id);
            if (!$descarte) {
                return;
            }
            
            // Buscar usuários que foram selecionados para notificação
            if (empty($descarte['notificar_usuarios'])) {
                error_log('Controle Descartes: Nenhum usuário selecionado para notificação no descarte ID ' . $descarte_id);
                return;
            }
            
            // Converter string de IDs em array
            $usuariosIds = explode(',', $descarte['notificar_usuarios']);
            $usuariosIds = array_filter(array_map('intval', $usuariosIds));
            
            if (empty($usuariosIds)) {
                error_log('Controle Descartes: IDs de usuários inválidos no descarte ID ' . $descarte_id);
                return;
            }
            
            // Buscar dados dos usuários selecionados
            $placeholders = implode(',', array_fill(0, count($usuariosIds), '?'));
            $stmt = $this->db->prepare("
                SELECT id, name, email
                FROM users
                WHERE id IN ($placeholders)
                AND email IS NOT NULL 
                AND email != ''
                ORDER BY name
            ");
            $stmt->execute($usuariosIds);
            $destinatarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($destinatarios)) {
                error_log('Controle Descartes: Nenhum destinatário válido encontrado para o descarte ID ' . $descarte_id);
                return;
            }
            
            // Preparar email
            $assunto = "🗑️ Novo Descarte Registrado - Aguardando Aprovação";
            
            $mensagem = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                        <h1 style='color: white; margin: 0; font-size: 24px;'>🗑️ Novo Descarte Registrado</h1>
                    </div>
                    
                    <div style='background: #f7fafc; padding: 30px; border-radius: 0 0 10px 10px;'>
                        <div style='background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #f59e0b;'>
                            <h2 style='color: #2d3748; margin-top: 0;'>Status:</h2>
                            <p style='font-size: 18px; margin: 10px 0;'>
                                <span style='background: #fef3c7; color: #92400e; padding: 8px 16px; border-radius: 20px; font-weight: bold;'>
                                    ⏳ Aguardando Descarte
                                </span>
                            </p>
                        </div>
                        
                        <div style='background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                            <h3 style='color: #2d3748; margin-top: 0;'>📦 Informações do Equipamento:</h3>
                            <table style='width: 100%; border-collapse: collapse;'>
                                <tr>
                                    <td style='padding: 8px 0; color: #4a5568;'><strong>Número de Série:</strong></td>
                                    <td style='padding: 8px 0; color: #2d3748;'>{$descarte['numero_serie']}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #4a5568;'><strong>Filial:</strong></td>
                                    <td style='padding: 8px 0; color: #2d3748;'>{$descarte['filial_nome']}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #4a5568;'><strong>Código Produto:</strong></td>
                                    <td style='padding: 8px 0; color: #2d3748;'>{$descarte['codigo_produto']}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #4a5568;'><strong>Descrição:</strong></td>
                                    <td style='padding: 8px 0; color: #2d3748;'>{$descarte['descricao_produto']}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #4a5568;'><strong>Data do Descarte:</strong></td>
                                    <td style='padding: 8px 0; color: #2d3748;'>" . date('d/m/Y', strtotime($descarte['data_descarte'])) . "</td>
                                </tr>
                                " . ($descarte['numero_os'] ? "<tr>
                                    <td style='padding: 8px 0; color: #4a5568;'><strong>Número OS:</strong></td>
                                    <td style='padding: 8px 0; color: #2d3748;'>{$descarte['numero_os']}</td>
                                </tr>" : "") . "
                                <tr>
                                    <td style='padding: 8px 0; color: #4a5568;'><strong>Responsável Técnico:</strong></td>
                                    <td style='padding: 8px 0; color: #2d3748;'>{$descarte['responsavel_tecnico']}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #4a5568;'><strong>Registrado por:</strong></td>
                                    <td style='padding: 8px 0; color: #2d3748;'>{$descarte['criado_por_nome']}</td>
                                </tr>
                            </table>
                        </div>
                        
                        " . ($descarte['observacoes'] ? "
                        <div style='background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                            <h3 style='color: #2d3748; margin-top: 0;'>📝 Observações:</h3>
                            <p style='color: #4a5568; line-height: 1.6; margin: 0;'>{$descarte['observacoes']}</p>
                        </div>
                        " : "") . "
                        
                        <div style='background: #fef3c7; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #f59e0b;'>
                            <p style='margin: 0; color: #92400e;'>
                                <strong>⚠️ Ação Necessária:</strong><br>
                                Este descarte está aguardando aprovação. Acesse o sistema para revisar e alterar o status para:<br>
                                • <strong>Itens Descartados</strong> (se aprovado)<br>
                                • <strong>Descartes Reprovados</strong> (se não aprovado)
                            </p>
                        </div>
                        
                        <div style='text-align: center; margin-top: 30px;'>
                            <a href='" . ($_ENV['APP_URL'] ?? 'http://localhost') . "/controle-descartes' 
                               style='display: inline-block; background: #f59e0b; color: white; padding: 12px 30px; 
                                      text-decoration: none; border-radius: 5px; font-weight: bold;'>
                                Ver Controle de Descartes
                            </a>
                        </div>
                        
                        <p style='text-align: center; color: #a0aec0; font-size: 12px; margin-top: 30px;'>
                            Esta é uma notificação automática do sistema de Controle de Descartes - SGQ OTI DJ
                        </p>
                    </div>
                </div>
            ";
            
            // Enviar email para cada destinatário
            $emails_enviados = 0;
            foreach ($destinatarios as $dest) {
                try {
                    if (class_exists('\App\Services\EmailService')) {
                        \App\Services\EmailService::send($dest['email'], $assunto, $mensagem);
                        $emails_enviados++;
                    }
                } catch (\Exception $e) {
                    error_log("Controle Descartes: Erro ao enviar email para {$dest['email']}: " . $e->getMessage());
                }
            }
            
            error_log("Controle Descartes: {$emails_enviados} email(s) enviado(s) sobre novo descarte ID {$descarte_id}");
            
        } catch (\Exception $e) {
            error_log('Controle Descartes: Erro ao notificar: ' . $e->getMessage());
        }
    }
}
