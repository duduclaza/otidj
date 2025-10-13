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
     * Endpoint de teste simplificado
     */
    public function apiTest()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');

        try {
            // Teste 1: Autenticação
            $auth = $this->verificarAutenticacao();
            
            // Teste 2: Conexão com banco
            $dbTest = $this->db->query("SELECT 1 as test")->fetch();
            
            // Teste 3: Query simples em garantias
            $count = $this->db->query("SELECT COUNT(*) as total FROM garantias")->fetch();
            
            // Teste 4: Colunas da tabela garantias
            $columns = $this->db->query("SHOW COLUMNS FROM garantias")->fetchAll(PDO::FETCH_COLUMN);
            
            // Teste 5: Colunas da tabela garantias_itens
            $itemsColumns = $this->db->query("SHOW COLUMNS FROM garantias_itens")->fetchAll(PDO::FETCH_COLUMN);
            
            // Teste 6: Query simples de garantias com JOIN
            $simpleQuery = $this->db->query("
                SELECT g.id, g.numero_ticket_interno, g.status, f.nome as fornecedor
                FROM garantias g 
                LEFT JOIN fornecedores f ON g.fornecedor_id = f.id 
                LIMIT 1
            ")->fetch();
            
            echo json_encode([
                'success' => true,
                'tests' => [
                    'autenticacao' => $auth ? 'OK' : 'FALHOU',
                    'conexao_db' => $dbTest ? 'OK' : 'FALHOU',
                    'tabela_garantias' => $count ? 'OK (' . $count['total'] . ' registros)' : 'FALHOU',
                    'query_simples_join' => $simpleQuery ? 'OK' : 'FALHOU'
                ],
                'table_info' => [
                    'garantias_columns' => $columns,
                    'garantias_itens_columns' => $itemsColumns
                ],
                'sample_data' => $simpleQuery,
                'server_info' => [
                    'php_version' => PHP_VERSION,
                    'api_token_presente' => isset($_GET['api_token']) ? 'SIM' : 'NÃO'
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString())
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API Simplificada - Apenas dados básicos (sem estatísticas)
     */
    public function apiGarantiasSimples()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');

        try {
            if (!$this->verificarAutenticacao()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Query simplificada - apenas dados básicos
            $sql = "
                SELECT 
                    g.id,
                    g.numero_ticket_interno,
                    g.numero_ticket_os,
                    g.fornecedor_id,
                    f.nome AS fornecedor_nome,
                    f.contato AS fornecedor_contato,
                    f.rma AS fornecedor_rma,
                    g.origem_garantia,
                    g.numero_nf_compras,
                    g.numero_nf_remessa_simples,
                    g.numero_nf_remessa_devolucao,
                    g.numero_serie,
                    g.numero_lote,
                    g.status,
                    g.observacao,
                    g.descricao_defeito,
                    g.usuario_notificado_id,
                    un.name AS usuario_notificado_nome,
                    g.total_itens,
                    g.valor_total,
                    g.created_at,
                    g.updated_at
                FROM garantias g
                LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
                LEFT JOIN users un ON g.usuario_notificado_id = un.id
                ORDER BY g.created_at DESC
                LIMIT 100
            ";

            $garantias = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $garantias,
                'total' => count($garantias),
                'generated_at' => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * API: Garantias - Dados completos para Power BI
     * Endpoint: /api/powerbi/garantias
     */
    public function apiGarantias()
    {
        // Configurar headers antes de qualquer saída
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        try {
            // Log de acesso
            error_log("🔵 API Garantias - Acesso recebido");
            
            // Verificar autenticação via token ou sessão
            if (!$this->verificarAutenticacao()) {
                error_log("❌ API Garantias - Autenticação falhou");
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Unauthorized',
                    'message' => 'Token de autenticação inválido ou ausente. Use: ?api_token=sgqoti2024@powerbi'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            error_log("✅ API Garantias - Autenticação OK");
            // Parâmetros de filtro opcionais
            $dataInicio = $_GET['data_inicio'] ?? null;
            $dataFim = $_GET['data_fim'] ?? null;
            $status = $_GET['status'] ?? null;
            $fornecedorId = $_GET['fornecedor_id'] ?? null;
            $origem = $_GET['origem'] ?? null;

            // Query base - usando campos corretos confirmados
            $sql = "
                SELECT 
                    g.id,
                    g.numero_ticket_interno,
                    g.numero_ticket_os,
                    g.fornecedor_id,
                    f.nome AS fornecedor_nome,
                    f.contato AS fornecedor_contato,
                    f.rma AS fornecedor_rma,
                    g.origem_garantia,
                    g.numero_nf_compras,
                    g.numero_nf_remessa_simples,
                    g.numero_nf_remessa_devolucao,
                    g.numero_serie,
                    g.numero_lote,
                    g.status,
                    g.observacao,
                    g.descricao_defeito,
                    g.usuario_notificado_id,
                    un.name AS usuario_notificado_nome,
                    un.email AS usuario_notificado_email,
                    g.total_itens,
                    g.valor_total,
                    g.created_at,
                    g.updated_at,
                    -- Dados dos itens agregados
                    GROUP_CONCAT(
                        DISTINCT CONCAT(
                            gi.tipo_produto, ':', 
                            gi.codigo_produto, '|',
                            gi.nome_produto, '|', 
                            gi.quantidade, '|', 
                            gi.valor_unitario, '|',
                            gi.descricao
                        ) SEPARATOR '||'
                    ) AS itens_detalhados,
                    -- Contagem de anexos
                    (SELECT COUNT(*) FROM garantias_anexos WHERE garantia_id = g.id) AS total_anexos,
                    -- Tipos de produtos únicos
                    (SELECT COUNT(DISTINCT tipo_produto) FROM garantias_itens WHERE garantia_id = g.id) AS tipos_produtos,
                    -- Resumo por tipo (quantidade)
                    (SELECT COALESCE(SUM(quantidade), 0) FROM garantias_itens WHERE garantia_id = g.id AND tipo_produto = 'toner') AS qtd_toners,
                    (SELECT COALESCE(SUM(quantidade), 0) FROM garantias_itens WHERE garantia_id = g.id AND tipo_produto = 'maquina') AS qtd_maquinas,
                    (SELECT COALESCE(SUM(quantidade), 0) FROM garantias_itens WHERE garantia_id = g.id AND tipo_produto = 'peca') AS qtd_pecas,
                    -- Valores por tipo
                    (SELECT COALESCE(SUM(quantidade * valor_unitario), 0) FROM garantias_itens WHERE garantia_id = g.id AND tipo_produto = 'toner') AS valor_toners,
                    (SELECT COALESCE(SUM(quantidade * valor_unitario), 0) FROM garantias_itens WHERE garantia_id = g.id AND tipo_produto = 'maquina') AS valor_maquinas,
                    (SELECT COALESCE(SUM(quantidade * valor_unitario), 0) FROM garantias_itens WHERE garantia_id = g.id AND tipo_produto = 'peca') AS valor_pecas
                FROM garantias g
                LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
                LEFT JOIN users un ON g.usuario_notificado_id = un.id
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

            error_log("🔵 API Garantias - Executando query SQL");
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $garantias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("✅ API Garantias - Query executada com sucesso. " . count($garantias) . " registros encontrados");

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
                // Formato: tipo_produto:codigo_produto|nome_produto|quantidade|valor_unitario|descricao
                if ($garantia['itens_detalhados']) {
                    $itensArray = [];
                    $itens = explode('||', $garantia['itens_detalhados']);
                    foreach ($itens as $item) {
                        if (empty($item)) continue;
                        
                        $partes = explode('|', $item);
                        if (count($partes) >= 5) {
                            // Primeiro campo contém tipo_produto:codigo_produto
                            $tipoCodigo = explode(':', $partes[0]);
                            $tipo_produto = $tipoCodigo[0] ?? '';
                            $codigo_produto = $tipoCodigo[1] ?? '';
                            
                            $itensArray[] = [
                                'tipo_produto' => $tipo_produto,
                                'codigo_produto' => $codigo_produto,
                                'nome_produto' => $partes[1],
                                'quantidade' => (int)$partes[2],
                                'valor_unitario' => (float)$partes[3],
                                'valor_total' => (int)$partes[2] * (float)$partes[3],
                                'descricao' => $partes[4]
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
            error_log("🔵 API Garantias - Calculando estatísticas");
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
            error_log("✅ API Garantias - Estatísticas calculadas");

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

        } catch (\PDOException $e) {
            error_log("❌ Erro de Banco de Dados na API de Garantias: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Database Error',
                'message' => 'Erro ao consultar banco de dados: ' . $e->getMessage(),
                'code' => $e->getCode()
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            error_log("❌ Erro na API de Garantias: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Internal Server Error',
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ], JSON_UNESCAPED_UNICODE);
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
                f.nome AS fornecedor_nome,
                f.contato AS fornecedor_contato,
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
     * Aceita token via: 1) Parâmetro URL, 2) Authorization header, 3) Sessão ativa
     */
    private function verificarAutenticacao(): bool
    {
        // MÉTODO 1: Token via parâmetro de URL (melhor compatibilidade com Power BI)
        $urlToken = $_GET['api_token'] ?? null;
        if ($urlToken && $this->validarToken($urlToken)) {
            return true;
        }

        // MÉTODO 2: Token via header Authorization (para ferramentas que suportam)
        $authHeader = $this->getAuthorizationHeader();
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
            if ($this->validarToken($token)) {
                return true;
            }
        }

        // MÉTODO 3: Sessão ativa (fallback)
        if (isset($_SESSION['user_id'])) {
            return true;
        }

        return false;
    }

    /**
     * Obter header Authorization de forma compatível
     */
    private function getAuthorizationHeader(): ?string
    {
        // Tentar $_SERVER['HTTP_AUTHORIZATION'] primeiro
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }

        // Fallback para REDIRECT_HTTP_AUTHORIZATION (alguns servidores)
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        // Tentar getallheaders() se disponível
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            return $headers['Authorization'] ?? $headers['authorization'] ?? null;
        }

        return null;
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
