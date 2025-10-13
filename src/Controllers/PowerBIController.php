<?php

namespace App\Controllers;

use App\Config\Database;
use PDO;

class PowerBIController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Página principal - Lista de APIs disponíveis
     */
    public function index()
    {
        // Verificar se está autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $title = 'APIs para Power BI - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/powerbi/index.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    /**
     * API: Garantias - Dados completos para Power BI
     * Endpoint: /api/powerbi/garantias
     */
    public function apiGarantias()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Verificar autenticação via token ou sessão
        if (!$this->verificarAutenticacao()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Token de autenticação inválido ou ausente'
            ]);
            exit;
        }

        try {
            // Parâmetros de filtro opcionais
            $dataInicio = $_GET['data_inicio'] ?? null;
            $dataFim = $_GET['data_fim'] ?? null;
            $status = $_GET['status'] ?? null;
            $fornecedorId = $_GET['fornecedor_id'] ?? null;
            $origem = $_GET['origem'] ?? null;

            // Query base
            $sql = "
                SELECT 
                    g.id,
                    g.numero_ticket_interno,
                    g.fornecedor_id,
                    f.nome_fantasia AS fornecedor_nome,
                    f.razao_social AS fornecedor_razao_social,
                    f.cnpj AS fornecedor_cnpj,
                    g.origem_garantia,
                    g.numero_nf_compras,
                    g.numero_nf_remessa_simples,
                    g.numero_nf_remessa_devolucao,
                    g.numero_serie,
                    g.numero_lote,
                    g.ticket_os_fornecedor,
                    g.status,
                    g.observacao,
                    g.total_itens,
                    g.valor_total,
                    g.usuario_id,
                    u.name AS usuario_nome,
                    u.email AS usuario_email,
                    g.created_at,
                    g.updated_at,
                    -- Dados dos itens agregados
                    GROUP_CONCAT(
                        DISTINCT CONCAT(
                            gi.tipo_produto, ':', 
                            gi.item, '|', 
                            gi.quantidade, '|', 
                            gi.valor_unitario, '|',
                            gi.valor_total, '|',
                            gi.defeito_relatado
                        ) SEPARATOR '||'
                    ) AS itens_detalhados,
                    -- Contagem de anexos
                    (SELECT COUNT(*) FROM garantias_anexos WHERE garantia_id = g.id) AS total_anexos,
                    -- Tipos de produtos
                    (SELECT COUNT(DISTINCT tipo_produto) FROM garantias_itens WHERE garantia_id = g.id) AS tipos_produtos,
                    -- Resumo por tipo
                    (SELECT SUM(quantidade) FROM garantias_itens WHERE garantia_id = g.id AND tipo_produto = 'toner') AS qtd_toners,
                    (SELECT SUM(quantidade) FROM garantias_itens WHERE garantia_id = g.id AND tipo_produto = 'maquina') AS qtd_maquinas,
                    (SELECT SUM(quantidade) FROM garantias_itens WHERE garantia_id = g.id AND tipo_produto = 'peca') AS qtd_pecas,
                    -- Valores por tipo
                    (SELECT SUM(valor_total) FROM garantias_itens WHERE garantia_id = g.id AND tipo_produto = 'toner') AS valor_toners,
                    (SELECT SUM(valor_total) FROM garantias_itens WHERE garantia_id = g.id AND tipo_produto = 'maquina') AS valor_maquinas,
                    (SELECT SUM(valor_total) FROM garantias_itens WHERE garantia_id = g.id AND tipo_produto = 'peca') AS valor_pecas
                FROM garantias g
                LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
                LEFT JOIN users u ON g.usuario_id = u.id
                LEFT JOIN garantias_itens gi ON g.id = gi.garantia_id
                WHERE 1=1
            ";

            $params = [];

            // Aplicar filtros
            if ($dataInicio) {
                $sql .= " AND DATE(g.created_at) >= ?";
                $params[] = $dataInicio;
            }

            if ($dataFim) {
                $sql .= " AND DATE(g.created_at) <= ?";
                $params[] = $dataFim;
            }

            if ($status) {
                $sql .= " AND g.status = ?";
                $params[] = $status;
            }

            if ($fornecedorId) {
                $sql .= " AND g.fornecedor_id = ?";
                $params[] = $fornecedorId;
            }

            if ($origem) {
                $sql .= " AND g.origem_garantia = ?";
                $params[] = $origem;
            }

            $sql .= " GROUP BY g.id ORDER BY g.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $garantias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Processar os dados para melhor estrutura
            foreach ($garantias as &$garantia) {
                // Converter valores nulos em 0
                $garantia['qtd_toners'] = (int)($garantia['qtd_toners'] ?? 0);
                $garantia['qtd_maquinas'] = (int)($garantia['qtd_maquinas'] ?? 0);
                $garantia['qtd_pecas'] = (int)($garantia['qtd_pecas'] ?? 0);
                $garantia['valor_toners'] = (float)($garantia['valor_toners'] ?? 0);
                $garantia['valor_maquinas'] = (float)($garantia['valor_maquinas'] ?? 0);
                $garantia['valor_pecas'] = (float)($garantia['valor_pecas'] ?? 0);

                // Processar itens detalhados
                if ($garantia['itens_detalhados']) {
                    $itensArray = [];
                    $itens = explode('||', $garantia['itens_detalhados']);
                    foreach ($itens as $item) {
                        $partes = explode('|', $item);
                        if (count($partes) >= 5) {
                            $tipo_produto = explode(':', $partes[0])[0];
                            $item_nome = explode(':', $partes[0])[1] ?? '';
                            $itensArray[] = [
                                'tipo_produto' => $tipo_produto,
                                'item' => $item_nome,
                                'quantidade' => (int)$partes[1],
                                'valor_unitario' => (float)$partes[2],
                                'valor_total' => (float)$partes[3],
                                'defeito' => $partes[4]
                            ];
                        }
                    }
                    $garantia['itens'] = $itensArray;
                }
                unset($garantia['itens_detalhados']);

                // Converter valores numéricos
                $garantia['total_itens'] = (int)$garantia['total_itens'];
                $garantia['valor_total'] = (float)$garantia['valor_total'];
                $garantia['total_anexos'] = (int)$garantia['total_anexos'];
            }

            // Estatísticas gerais
            $stats = [
                'total_registros' => count($garantias),
                'valor_total_geral' => array_sum(array_column($garantias, 'valor_total')),
                'total_itens_geral' => array_sum(array_column($garantias, 'total_itens')),
                'por_status' => $this->getEstatisticasPorStatus(),
                'por_fornecedor' => $this->getEstatisticasPorFornecedor($dataInicio, $dataFim),
                'por_origem' => $this->getEstatisticasPorOrigem($dataInicio, $dataFim),
                'por_tipo_produto' => [
                    'toners' => array_sum(array_column($garantias, 'qtd_toners')),
                    'maquinas' => array_sum(array_column($garantias, 'qtd_maquinas')),
                    'pecas' => array_sum(array_column($garantias, 'qtd_pecas'))
                ]
            ];

            echo json_encode([
                'success' => true,
                'data' => $garantias,
                'statistics' => $stats,
                'filters_applied' => [
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim,
                    'status' => $status,
                    'fornecedor_id' => $fornecedorId,
                    'origem' => $origem
                ],
                'generated_at' => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            error_log("❌ Erro na API de Garantias: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Internal Server Error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Estatísticas por status
     */
    private function getEstatisticasPorStatus()
    {
        $stmt = $this->db->query("
            SELECT 
                status,
                COUNT(*) as total,
                SUM(valor_total) as valor_total,
                SUM(total_itens) as total_itens
            FROM garantias
            GROUP BY status
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Estatísticas por fornecedor
     */
    private function getEstatisticasPorFornecedor($dataInicio = null, $dataFim = null)
    {
        $sql = "
            SELECT 
                f.id,
                f.nome_fantasia,
                f.razao_social,
                COUNT(g.id) as total_garantias,
                SUM(g.valor_total) as valor_total,
                SUM(g.total_itens) as total_itens
            FROM fornecedores f
            LEFT JOIN garantias g ON f.id = g.fornecedor_id
            WHERE 1=1
        ";

        $params = [];
        if ($dataInicio) {
            $sql .= " AND DATE(g.created_at) >= ?";
            $params[] = $dataInicio;
        }
        if ($dataFim) {
            $sql .= " AND DATE(g.created_at) <= ?";
            $params[] = $dataFim;
        }

        $sql .= " GROUP BY f.id ORDER BY total_garantias DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Estatísticas por origem
     */
    private function getEstatisticasPorOrigem($dataInicio = null, $dataFim = null)
    {
        $sql = "
            SELECT 
                origem_garantia,
                COUNT(*) as total,
                SUM(valor_total) as valor_total,
                SUM(total_itens) as total_itens
            FROM garantias
            WHERE 1=1
        ";

        $params = [];
        if ($dataInicio) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $dataInicio;
        }
        if ($dataFim) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $dataFim;
        }

        $sql .= " GROUP BY origem_garantia";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar autenticação para API
     * Aceita token via Authorization header ou sessão ativa
     */
    private function verificarAutenticacao(): bool
    {
        // Verificar token no header Authorization
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            return $this->validarToken($token);
        }

        // Fallback: verificar sessão ativa
        if (isset($_SESSION['user_id'])) {
            return true;
        }

        return false;
    }

    /**
     * Validar token de API
     * TODO: Implementar sistema de tokens mais robusto se necessário
     */
    private function validarToken(string $token): bool
    {
        // Por enquanto, aceitar um token fixo configurável
        // Em produção, implementar sistema de tokens com expiração
        $validToken = $_ENV['POWERBI_API_TOKEN'] ?? 'sgqoti2024@powerbi';
        
        return $token === $validToken;
    }

    /**
     * Gerar documentação da API
     */
    public function documentacao()
    {
        $title = 'Documentação API - Power BI';
        $viewFile = __DIR__ . '/../../views/pages/powerbi/documentacao.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }
}
