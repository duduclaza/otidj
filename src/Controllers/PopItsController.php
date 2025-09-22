<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class PopItsController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal com abas
    public function index()
    {
        try {
            $departamentos = $this->getDepartamentos();
            $titulos = $this->getTitulos();

            include __DIR__ . '/../../views/pages/pops-its/index.php';
        } catch (\Throwable $e) {
            // Logar erro para diagnóstico
            try {
                $logDir = __DIR__ . '/../../logs';
                if (!is_dir($logDir)) { @mkdir($logDir, 0777, true); }
                $msg = date('Y-m-d H:i:s') . ' POPs-ITs index ERRO: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine() . "\n";
                file_put_contents($logDir . '/pops_its_debug.log', $msg, FILE_APPEND);
            } catch (\Throwable $ignored) {}

            // Exibir detalhes somente se APP_DEBUG=true ou ?debug=1
            $appDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
            $reqDebug = isset($_GET['debug']) && $_GET['debug'] == '1';
            if ($appDebug || $reqDebug) {
                echo 'Erro: ' . htmlspecialchars($e->getMessage());
                echo '<br>Arquivo: ' . htmlspecialchars($e->getFile());
                echo '<br>Linha: ' . (int)$e->getLine();
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                exit;
            }
            // Caso contrário, lançar novamente para página 500 padrão
            throw $e;
        }
    }


    // ===== ABA 1: CADASTRO DE TÍTULOS =====
    
    public function createTitulo()
    {
        try {
            $titulo = trim($_POST['titulo'] ?? '');
            $departamento_id = (int)($_POST['departamento_id'] ?? 0);
            $user_id = $_SESSION['user_id'];

            if (empty($titulo) || $departamento_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Título e departamento são obrigatórios']);
                exit();
            }

            // Verificar se já existe título igual no mesmo departamento
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM pops_its_titulos 
                WHERE titulo = ? AND departamento_id = ?
            ");
            $stmt->execute([$titulo, $departamento_id]);
            
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Já existe um título com este nome neste departamento']);
                exit();
            }

            // Inserir novo título
            $stmt = $this->db->prepare("
                INSERT INTO pops_its_titulos (titulo, departamento_id, created_by) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$titulo, $departamento_id, $user_id]);

            echo json_encode(['success' => true, 'message' => 'Título cadastrado com sucesso!']);
            exit();

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar título: ' . $e->getMessage()]);
            exit();
        }
    }

    public function listTitulos()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, d.nome as departamento_nome, u.name as criador_nome
                FROM pops_its_titulos t
                JOIN departamentos d ON t.departamento_id = d.id
                JOIN users u ON t.created_by = u.id
                ORDER BY t.created_at DESC
            ");
            $stmt->execute();
            $titulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $titulos]);
            exit();

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao listar títulos: ' . $e->getMessage()]);
            exit();
        }
    }

    // ===== ABA 2: MEUS REGISTROS =====

    public function createRegistro()
    {
        try {
            $titulo_id = (int)($_POST['titulo_id'] ?? 0);
            $visibilidade = $_POST['visibilidade'] ?? 'departamentos';
            $departamentos_permitidos = $_POST['departamentos_permitidos'] ?? [];
            $user_id = $_SESSION['user_id'];

            if ($titulo_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Título é obrigatório']);
                exit();
            }

            // Validar arquivo
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Arquivo é obrigatório']);
                exit();
            }

            $file = $_FILES['arquivo'];
            
            // Validar tipo de arquivo
            $allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use PDF, PNG, JPEG, JPG ou PPT']);
                exit();
            }

            // Validar tamanho (10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 10MB']);
                exit();
            }

            // Calcular próxima versão
            $versao = $this->getNextVersion($titulo_id);

            // Ler arquivo para BLOB
            $arquivo_blob = file_get_contents($file['tmp_name']);

            // Inserir registro
            $stmt = $this->db->prepare("
                INSERT INTO pops_its_registros 
                (titulo_id, versao, arquivo_blob, arquivo_name, arquivo_type, arquivo_size, visibilidade, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $titulo_id, $versao, $arquivo_blob, $file['name'], 
                $file['type'], $file['size'], $visibilidade, $user_id
            ]);

            $registro_id = $this->db->lastInsertId();

            // Se visibilidade for departamentos específicos, inserir departamentos permitidos
            if ($visibilidade === 'departamentos' && !empty($departamentos_permitidos)) {
                foreach ($departamentos_permitidos as $dept_id) {
                    $stmt = $this->db->prepare("
                        INSERT INTO pops_its_departamentos_permitidos (registro_id, departamento_id) 
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$registro_id, (int)$dept_id]);
                }
            }

            echo json_encode(['success' => true, 'message' => "Registro criado com sucesso! Versão: {$versao}"]);
            exit();

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar registro: ' . $e->getMessage()]);
            exit();
        }
    }

    public function listMeusRegistros()
    {
        try {
            $user_id = $_SESSION['user_id'];
            
            $stmt = $this->db->prepare("
                SELECT r.*, t.titulo, d.nome as departamento_nome
                FROM pops_its_registros r
                JOIN pops_its_titulos t ON r.titulo_id = t.id
                JOIN departamentos d ON t.departamento_id = d.id
                WHERE r.created_by = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $registros]);
            exit();

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao listar registros: ' . $e->getMessage()]);
            exit();
        }
    }

    public function updateRegistro()
    {
        try {
            $registro_id = (int)($_POST['registro_id'] ?? 0);
            $user_id = $_SESSION['user_id'];

            // Verificar se o registro pertence ao usuário
            $stmt = $this->db->prepare("SELECT * FROM pops_its_registros WHERE id = ? AND created_by = ?");
            $stmt->execute([$registro_id, $user_id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado ou sem permissão']);
                exit();
            }

            // Só pode atualizar se estiver reprovado
            if ($registro['status'] !== 'reprovado') {
                echo json_encode(['success' => false, 'message' => 'Só é possível atualizar registros reprovados']);
                exit();
            }

            // Validar novo arquivo
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Novo arquivo é obrigatório']);
                exit();
            }

            $file = $_FILES['arquivo'];
            
            // Validações do arquivo (mesmo do create)
            $allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido']);
                exit();
            }

            if ($file['size'] > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 10MB']);
                exit();
            }

            // Atualizar registro
            $arquivo_blob = file_get_contents($file['tmp_name']);
            
            $stmt = $this->db->prepare("
                UPDATE pops_its_registros 
                SET arquivo_blob = ?, arquivo_name = ?, arquivo_type = ?, arquivo_size = ?, 
                    status = 'pendente', observacao_reprovacao = NULL, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $arquivo_blob, $file['name'], $file['type'], $file['size'], $registro_id
            ]);

            echo json_encode(['success' => true, 'message' => 'Registro atualizado com sucesso! Status alterado para pendente']);
            exit();

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar registro: ' . $e->getMessage()]);
            exit();
        }
    }

    public function deleteRegistro()
    {
        try {
            $registro_id = (int)($_POST['registro_id'] ?? 0);
            $user_id = $_SESSION['user_id'];

            // Verificar se o registro pertence ao usuário
            $stmt = $this->db->prepare("SELECT * FROM pops_its_registros WHERE id = ? AND created_by = ?");
            $stmt->execute([$registro_id, $user_id]);
            
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado ou sem permissão']);
                exit();
            }

            // Excluir registro (CASCADE remove departamentos permitidos)
            $stmt = $this->db->prepare("DELETE FROM pops_its_registros WHERE id = ?");
            $stmt->execute([$registro_id]);

            echo json_encode(['success' => true, 'message' => 'Registro excluído com sucesso!']);
            exit();

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir registro: ' . $e->getMessage()]);
            exit();
        }
    }

    // ===== ABA 3: PENDENTE APROVAÇÃO =====

    public function listPendentesAprovacao()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, t.titulo, d.nome as departamento_nome, u.name as criador_nome
                FROM pops_its_registros r
                JOIN pops_its_titulos t ON r.titulo_id = t.id
                JOIN departamentos d ON t.departamento_id = d.id
                JOIN users u ON r.created_by = u.id
                WHERE r.status = 'pendente'
                ORDER BY r.created_at ASC
            ");
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $registros]);
            exit();

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao listar pendentes: ' . $e->getMessage()]);
            exit();
        }
    }

    public function aprovarRegistro()
    {
        try {
            $registro_id = (int)($_POST['registro_id'] ?? 0);
            $user_id = $_SESSION['user_id'];

            $stmt = $this->db->prepare("
                UPDATE pops_its_registros 
                SET status = 'aprovado', approved_by = ?, approved_at = NOW()
                WHERE id = ? AND status = 'pendente'
            ");
            $stmt->execute([$user_id, $registro_id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado ou já processado']);
                exit();
            }

            echo json_encode(['success' => true, 'message' => 'Registro aprovado com sucesso!']);
            exit();

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao aprovar registro: ' . $e->getMessage()]);
            exit();
        }
    }

    public function reprovarRegistro()
    {
        try {
            $registro_id = (int)($_POST['registro_id'] ?? 0);
            $observacao = trim($_POST['observacao'] ?? '');
            $user_id = $_SESSION['user_id'];

            if (empty($observacao)) {
                echo json_encode(['success' => false, 'message' => 'Observação da reprovação é obrigatória']);
                exit();
            }

            $stmt = $this->db->prepare("
                UPDATE pops_its_registros 
                SET status = 'reprovado', observacao_reprovacao = ?, approved_by = ?, approved_at = NOW()
                WHERE id = ? AND status = 'pendente'
            ");
            $stmt->execute([$observacao, $user_id, $registro_id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado ou já processado']);
                exit();
            }

            echo json_encode(['success' => true, 'message' => 'Registro reprovado com sucesso!']);
            exit();

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao reprovar registro: ' . $e->getMessage()]);
            exit();
        }
    }

    // ===== ABA 4: VISUALIZAÇÃO =====

    public function listVisualizacao()
    {
        try {
            $user_id = $_SESSION['user_id'];
            $user_dept_id = $this->getUserDepartmentId($user_id);

            $stmt = $this->db->prepare("
                SELECT DISTINCT r.*, t.titulo, d.nome as departamento_nome, u.name as criador_nome
                FROM pops_its_registros r
                JOIN pops_its_titulos t ON r.titulo_id = t.id
                JOIN departamentos d ON t.departamento_id = d.id
                JOIN users u ON r.created_by = u.id
                LEFT JOIN pops_its_departamentos_permitidos dp ON r.id = dp.registro_id
                WHERE r.status = 'aprovado' 
                AND (
                    r.visibilidade = 'publico' 
                    OR (r.visibilidade = 'departamentos' AND dp.departamento_id = ?)
                )
                ORDER BY t.titulo, r.versao DESC
            ");
            $stmt->execute([$user_dept_id]);
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $registros]);
            exit();

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao listar registros: ' . $e->getMessage()]);
            exit();
        }
    }

    // ===== MÉTODOS AUXILIARES =====

    public function downloadArquivo($id)
    {
        try {
            $user_id = $_SESSION['user_id'];
            $user_dept_id = $this->getUserDepartmentId($user_id);

            // Verificar permissão de visualização
            $stmt = $this->db->prepare("
                SELECT r.*, t.titulo
                FROM pops_its_registros r
                JOIN pops_its_titulos t ON r.titulo_id = t.id
                LEFT JOIN pops_its_departamentos_permitidos dp ON r.id = dp.registro_id
                WHERE r.id = ? 
                AND (
                    r.status = 'aprovado' 
                    AND (
                        r.visibilidade = 'publico' 
                        OR (r.visibilidade = 'departamentos' AND dp.departamento_id = ?)
                    )
                    OR r.created_by = ?
                    OR r.status = 'pendente'
                )
            ");
            $stmt->execute([$id, $user_dept_id, $user_id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$registro) {
                http_response_code(404);
                echo 'Arquivo não encontrado ou sem permissão';
                exit();
            }

            // Servir arquivo
            header('Content-Type: ' . $registro['arquivo_type']);
            header('Content-Length: ' . $registro['arquivo_size']);
            header('Content-Disposition: inline; filename="' . $registro['arquivo_name'] . '"');
            
            echo $registro['arquivo_blob'];
            exit();

        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao baixar arquivo: ' . $e->getMessage();
            exit();
        }
    }

    private function getNextVersion($titulo_id): string
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) + 1 as next_version 
            FROM pops_its_registros 
            WHERE titulo_id = ?
        ");
        $stmt->execute([$titulo_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return 'v' . $result['next_version'];
    }

    private function getDepartamentos(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM departamentos ORDER BY nome");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Se tabela não existe, retorna array vazio
            return [];
        }
    }

    private function getTitulos(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, d.nome as departamento_nome 
                FROM pops_its_titulos t
                JOIN departamentos d ON t.departamento_id = d.id
                ORDER BY t.titulo
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Se tabela não existe, retorna array vazio
            return [];
        }
    }

    private function getUserDepartmentId($user_id): ?int
    {
        $stmt = $this->db->prepare("SELECT departamento_id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['departamento_id'] : null;
    }
}
