<?php

namespace App\Controllers;

use App\Config\Database;

class FMEAController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal do FMEA
    public function index()
    {
        $title = 'FMEA - Análise de Modo e Efeito de Falha';
        $viewFile = __DIR__ . '/../../views/pages/fmea/index.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    // Listar registros FMEA (AJAX)
    public function list()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT id, modo_falha, efeito_falha, severidade, ocorrencia, deteccao, 
                       rpn, risco, acao_sugerida, data_registro
                FROM fmea 
                ORDER BY data_registro DESC
            ");
            $stmt->execute();
            $fmeas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $fmeas]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar registros: ' . $e->getMessage()]);
        }
    }

    // Criar novo registro FMEA
    public function store()
    {
        header('Content-Type: application/json');
        
        try {
            $modo_falha = trim($_POST['modo_falha'] ?? '');
            $efeito_falha = trim($_POST['efeito_falha'] ?? '');
            $severidade = (int)($_POST['severidade'] ?? 0);
            $ocorrencia = (int)($_POST['ocorrencia'] ?? 0);
            $deteccao = (int)($_POST['deteccao'] ?? 0);
            $acao_sugerida = trim($_POST['acao_sugerida'] ?? '');
            
            // Validações
            if (empty($modo_falha)) {
                echo json_encode(['success' => false, 'message' => 'Modo de falha é obrigatório']);
                return;
            }
            
            if (empty($efeito_falha)) {
                echo json_encode(['success' => false, 'message' => 'Efeito da falha é obrigatório']);
                return;
            }
            
            if ($severidade < 0 || $severidade > 10) {
                echo json_encode(['success' => false, 'message' => 'Severidade deve estar entre 0 e 10']);
                return;
            }
            
            if ($ocorrencia < 0 || $ocorrencia > 10) {
                echo json_encode(['success' => false, 'message' => 'Ocorrência deve estar entre 0 e 10']);
                return;
            }
            
            if ($deteccao < 0 || $deteccao > 10) {
                echo json_encode(['success' => false, 'message' => 'Detecção deve estar entre 0 e 10']);
                return;
            }
            
            if (empty($acao_sugerida)) {
                echo json_encode(['success' => false, 'message' => 'Ação sugerida é obrigatória']);
                return;
            }
            
            // Inserir no banco
            $stmt = $this->db->prepare("
                INSERT INTO fmea (modo_falha, efeito_falha, severidade, ocorrencia, deteccao, acao_sugerida, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $modo_falha,
                $efeito_falha,
                $severidade,
                $ocorrencia,
                $deteccao,
                $acao_sugerida,
                $_SESSION['user_id'] ?? null
            ]);
            
            $id = $this->db->lastInsertId();
            
            echo json_encode(['success' => true, 'message' => 'Registro FMEA criado com sucesso!', 'id' => $id]);
            exit();
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao criar registro: ' . $e->getMessage()]);
            exit();
        }
    }

    // Atualizar registro FMEA
    public function update($id)
    {
        header('Content-Type: application/json');
        
        try {
            $id = (int)$id;
            $modo_falha = trim($_POST['modo_falha'] ?? '');
            $efeito_falha = trim($_POST['efeito_falha'] ?? '');
            $severidade = (int)($_POST['severidade'] ?? 0);
            $ocorrencia = (int)($_POST['ocorrencia'] ?? 0);
            $deteccao = (int)($_POST['deteccao'] ?? 0);
            $acao_sugerida = trim($_POST['acao_sugerida'] ?? '');
            
            // Validações (mesmas do store)
            if (empty($modo_falha) || empty($efeito_falha) || empty($acao_sugerida)) {
                echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
                return;
            }
            
            if ($severidade < 0 || $severidade > 10 || $ocorrencia < 0 || $ocorrencia > 10 || $deteccao < 0 || $deteccao > 10) {
                echo json_encode(['success' => false, 'message' => 'Valores devem estar entre 0 e 10']);
                return;
            }
            
            // Atualizar no banco
            $stmt = $this->db->prepare("
                UPDATE fmea 
                SET modo_falha = ?, efeito_falha = ?, severidade = ?, ocorrencia = ?, deteccao = ?, acao_sugerida = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $modo_falha,
                $efeito_falha,
                $severidade,
                $ocorrencia,
                $deteccao,
                $acao_sugerida,
                $id
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Registro FMEA atualizado com sucesso!']);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar registro: ' . $e->getMessage()]);
        }
    }

    // Excluir registro FMEA
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        try {
            $id = (int)$id;
            
            $stmt = $this->db->prepare("DELETE FROM fmea WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Registro FMEA excluído com sucesso!']);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir registro: ' . $e->getMessage()]);
        }
    }

    // Buscar registro específico para edição
    public function show($id)
    {
        header('Content-Type: application/json');
        
        try {
            $id = (int)$id;
            
            $stmt = $this->db->prepare("
                SELECT id, modo_falha, efeito_falha, severidade, ocorrencia, deteccao, 
                       rpn, risco, acao_sugerida, data_registro
                FROM fmea 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $fmea = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$fmea) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado']);
                return;
            }
            
            echo json_encode(['success' => true, 'data' => $fmea]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar registro: ' . $e->getMessage()]);
        }
    }

    // Dados para gráficos
    public function chartData()
    {
        header('Content-Type: application/json');
        
        try {
            // Distribuição por Risco
            $stmt = $this->db->prepare("
                SELECT risco, COUNT(*) as total 
                FROM fmea 
                GROUP BY risco 
                ORDER BY total DESC
            ");
            $stmt->execute();
            $riscoData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // RPN por mês
            $stmt = $this->db->prepare("
                SELECT DATE_FORMAT(data_registro, '%Y-%m') as mes, 
                       AVG(rpn) as rpn_medio,
                       COUNT(*) as total_registros
                FROM fmea 
                WHERE data_registro >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(data_registro, '%Y-%m')
                ORDER BY mes
            ");
            $stmt->execute();
            $rpnData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Top 10 maiores RPNs
            $stmt = $this->db->prepare("
                SELECT modo_falha, rpn, risco
                FROM fmea 
                ORDER BY rpn DESC 
                LIMIT 10
            ");
            $stmt->execute();
            $topRpn = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Estatísticas gerais
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_registros,
                    AVG(rpn) as rpn_medio,
                    MAX(rpn) as rpn_maximo,
                    MIN(rpn) as rpn_minimo,
                    SUM(CASE WHEN risco = 'Risco Crítico' THEN 1 ELSE 0 END) as criticos,
                    SUM(CASE WHEN risco = 'Risco Alto' THEN 1 ELSE 0 END) as altos
                FROM fmea
            ");
            $stmt->execute();
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'risco_distribution' => $riscoData,
                'rpn_timeline' => $rpnData,
                'top_rpn' => $topRpn,
                'statistics' => $stats
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar dados dos gráficos: ' . $e->getMessage()]);
        }
    }

    // Página de impressão
    public function print($id)
    {
        try {
            $id = (int)$id;
            
            $stmt = $this->db->prepare("
                SELECT id, modo_falha, efeito_falha, severidade, ocorrencia, deteccao, 
                       rpn, risco, acao_sugerida, data_registro
                FROM fmea 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $fmea = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$fmea) {
                echo "Registro não encontrado";
                return;
            }
            
            $title = 'FMEA - Registro #' . $id;
            $viewFile = __DIR__ . '/../../views/pages/fmea/print.php';
            include __DIR__ . '/../../views/layouts/print.php';
            
        } catch (\Exception $e) {
            echo "Erro ao carregar registro: " . $e->getMessage();
        }
    }
}
