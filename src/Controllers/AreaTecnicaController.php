<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class AreaTecnicaController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ========================================
    // SISTEMA DE TRIAL (7 dias grátis)
    // ========================================
    
    private function getTrialInfo()
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM modulos_trial WHERE modulo = 'area_tecnica' LIMIT 1");
            $stmt->execute();
            $trial = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$trial) {
                return ['ativo' => false, 'dias_restantes' => 0, 'status' => 'nao_iniciado'];
            }
            
            $dataAtivacao = new \DateTime($trial['data_ativacao']);
            $dataExpiracao = clone $dataAtivacao;
            $dataExpiracao->modify('+7 days');
            $hoje = new \DateTime();
            
            // Se já foi pago, está ativo indefinidamente
            if ($trial['pago']) {
                return ['ativo' => true, 'dias_restantes' => -1, 'status' => 'pago', 'data_ativacao' => $trial['data_ativacao']];
            }
            
            // Calcular dias restantes do trial
            if ($hoje > $dataExpiracao) {
                return ['ativo' => false, 'dias_restantes' => 0, 'status' => 'expirado', 'data_ativacao' => $trial['data_ativacao']];
            }
            
            $diff = $hoje->diff($dataExpiracao);
            $diasRestantes = $diff->days + 1; // +1 para incluir o dia atual
            
            return ['ativo' => true, 'dias_restantes' => $diasRestantes, 'status' => 'trial', 'data_ativacao' => $trial['data_ativacao']];
            
        } catch (\Exception $e) {
            error_log("Erro ao verificar trial: " . $e->getMessage());
            return ['ativo' => false, 'dias_restantes' => 0, 'status' => 'erro'];
        }
    }
    
    public function ativarTrial()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                return;
            }
            
            // Verificar se é admin ou super_admin
            if (!isAdmin() && !isSuperAdmin()) {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores podem ativar o trial']);
                return;
            }
            
            // Verificar se já existe trial
            $stmt = $this->db->prepare("SELECT id FROM modulos_trial WHERE modulo = 'area_tecnica'");
            $stmt->execute();
            
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Trial já foi ativado anteriormente']);
                return;
            }
            
            // Ativar trial
            $stmt = $this->db->prepare("
                INSERT INTO modulos_trial (modulo, data_ativacao, ativado_por, pago)
                VALUES ('area_tecnica', NOW(), :user_id, 0)
            ");
            $stmt->execute([':user_id' => $_SESSION['user_id']]);
            
            echo json_encode(['success' => true, 'message' => 'Trial de 7 dias ativado com sucesso!']);
            
        } catch (\Exception $e) {
            error_log("Erro ao ativar trial: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao ativar trial']);
        }
    }
    
    public function getTrialStatus()
    {
        header('Content-Type: application/json');
        echo json_encode($this->getTrialInfo());
    }

    // ========================================
    // PÁGINA PRINCIPAL - ÁREA TÉCNICA
    // ========================================
    
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $trialInfo = $this->getTrialInfo();
        
        $title = 'Área Técnica - SGQ OTI';
        $viewFile = __DIR__ . '/../../views/pages/area-tecnica/index.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    // ========================================
    // CHECKLIST VIRTUAL - FORMULÁRIO PÚBLICO
    // ========================================
    
    public function checklistPublico()
    {
        // Página pública - não precisa de login
        $title = 'Checklist Virtual - SGQ OTI';
        include __DIR__ . '/../../views/pages/area-tecnica/checklist-publico.php';
    }
    
    public function salvarChecklist()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar trial ativo
            $trial = $this->getTrialInfo();
            if (!$trial['ativo']) {
                echo json_encode(['success' => false, 'message' => 'Módulo expirado. Entre em contato com a equipe de vendas.']);
                return;
            }
            
            $numeroSerie = trim($_POST['numero_serie'] ?? '');
            $manutencaoRealizada = trim($_POST['manutencao_realizada'] ?? '');
            $colaborador = trim($_POST['colaborador'] ?? '');
            
            // Validações
            if (empty($numeroSerie)) {
                echo json_encode(['success' => false, 'message' => 'Número de série é obrigatório']);
                return;
            }
            
            if (empty($manutencaoRealizada)) {
                echo json_encode(['success' => false, 'message' => 'Manutenção realizada é obrigatória']);
                return;
            }
            
            if (empty($colaborador)) {
                echo json_encode(['success' => false, 'message' => 'Nome do colaborador é obrigatório']);
                return;
            }
            
            // Processar fotos
            $fotoContador = null;
            $fotoEquipamento = null;
            
            $uploadDir = __DIR__ . '/../../public/uploads/checklists/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Foto do contador
            if (!empty($_FILES['foto_contador']['name'])) {
                $ext = pathinfo($_FILES['foto_contador']['name'], PATHINFO_EXTENSION);
                $fotoContador = 'contador_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['foto_contador']['tmp_name'], $uploadDir . $fotoContador);
            }
            
            // Foto do equipamento
            if (!empty($_FILES['foto_equipamento']['name'])) {
                $ext = pathinfo($_FILES['foto_equipamento']['name'], PATHINFO_EXTENSION);
                $fotoEquipamento = 'equip_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['foto_equipamento']['tmp_name'], $uploadDir . $fotoEquipamento);
            }
            
            // Data/hora de Brasília
            date_default_timezone_set('America/Sao_Paulo');
            $dataHora = date('Y-m-d H:i:s');
            
            // Inserir no banco
            $stmt = $this->db->prepare("
                INSERT INTO area_tecnica_checklists 
                (numero_serie, manutencao_realizada, colaborador, foto_contador, foto_equipamento, data_hora, ip_origem)
                VALUES (:numero_serie, :manutencao_realizada, :colaborador, :foto_contador, :foto_equipamento, :data_hora, :ip)
            ");
            
            $stmt->execute([
                ':numero_serie' => strtoupper($numeroSerie),
                ':manutencao_realizada' => $manutencaoRealizada,
                ':colaborador' => $colaborador,
                ':foto_contador' => $fotoContador,
                ':foto_equipamento' => $fotoEquipamento,
                ':data_hora' => $dataHora,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Checklist registrado com sucesso!',
                'data_hora' => date('d/m/Y H:i:s', strtotime($dataHora))
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao salvar checklist: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar checklist: ' . $e->getMessage()]);
        }
    }

    // ========================================
    // CONSULTA DE CHECKLISTS
    // ========================================
    
    public function consultaChecklists()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $trialInfo = $this->getTrialInfo();
        
        $title = 'Consulta de Checklists - SGQ OTI';
        $viewFile = __DIR__ . '/../../views/pages/area-tecnica/consulta-checklists.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
    
    public function buscarChecklists()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar trial
            $trial = $this->getTrialInfo();
            if (!$trial['ativo']) {
                echo json_encode(['success' => false, 'message' => 'Módulo expirado']);
                return;
            }
            
            $numeroSerie = trim($_GET['numero_serie'] ?? '');
            
            if (empty($numeroSerie)) {
                echo json_encode(['success' => false, 'message' => 'Digite um número de série']);
                return;
            }
            
            $stmt = $this->db->prepare("
                SELECT * FROM area_tecnica_checklists 
                WHERE numero_serie LIKE :numero_serie 
                ORDER BY data_hora DESC
            ");
            $stmt->execute([':numero_serie' => '%' . strtoupper($numeroSerie) . '%']);
            $checklists = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $checklists, 'total' => count($checklists)]);
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar checklists: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar']);
        }
    }
    
    public function listarTodosChecklists()
    {
        header('Content-Type: application/json');
        
        try {
            $trial = $this->getTrialInfo();
            if (!$trial['ativo']) {
                echo json_encode(['success' => false, 'message' => 'Módulo expirado']);
                return;
            }
            
            $page = (int)($_GET['page'] ?? 1);
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            // Total
            $stmt = $this->db->query("SELECT COUNT(*) FROM area_tecnica_checklists");
            $total = $stmt->fetchColumn();
            
            // Dados
            $stmt = $this->db->prepare("
                SELECT * FROM area_tecnica_checklists 
                ORDER BY data_hora DESC 
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $checklists = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'data' => $checklists, 
                'total' => $total,
                'page' => $page,
                'pages' => ceil($total / $limit)
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao listar']);
        }
    }
    
    public function verChecklist($id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM area_tecnica_checklists WHERE id = ?");
            $stmt->execute([$id]);
            $checklist = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$checklist) {
                echo json_encode(['success' => false, 'message' => 'Checklist não encontrado']);
                return;
            }
            
            echo json_encode(['success' => true, 'data' => $checklist]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro']);
        }
    }
}
