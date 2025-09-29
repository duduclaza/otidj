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
            // Verificar permissões para cada aba
            $user_id = $_SESSION['user_id'];
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            
            // Verificar permissões específicas para cada aba
            $canViewCadastroTitulos = \App\Services\PermissionService::hasPermission($user_id, 'pops_its_cadastro_titulos', 'view');
            $canViewMeusRegistros = \App\Services\PermissionService::hasPermission($user_id, 'pops_its_meus_registros', 'view');
            $canViewPendenteAprovacao = $isAdmin; // Apenas admin pode ver pendente aprovação
            $canViewVisualizacao = \App\Services\PermissionService::hasPermission($user_id, 'pops_its_visualizacao', 'view');
            
            // Carregar departamentos para o formulário
            $departamentos = $this->getDepartamentos();
            
            // Usar o layout padrão com TailwindCSS
            $title = 'POPs e ITs - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/pops-its/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
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
    // Método createTitulo() implementado no final do arquivo

    // ===== MÉTODOS IMPLEMENTADOS NO FINAL DO ARQUIVO =====
    // createTitulo(), listTitulos(), searchTitulos(), deleteTitulo()
    // createRegistro(), listMeusRegistros(), downloadArquivo()
    // Outros métodos auxiliares

    private function getNextVersion($titulo_id): string
    {
        $stmt = $this->db->prepare("
            SELECT COALESCE(MAX(CAST(SUBSTRING(versao, 2) AS UNSIGNED)), 0) + 1 as next_version
            FROM pops_its_registros 
            WHERE titulo_id = ?
        ");
        $stmt->execute([$titulo_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return 'v' . $result['next_version'];
    }

    // ===== MÉTODOS IMPLEMENTADOS CORRETAMENTE =====

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

    // ===== MÉTODOS IMPLEMENTADOS CORRETAMENTE NO FINAL =====

    // Criar título
    public function createTitulo()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            if (!\App\Services\PermissionService::hasPermission($user_id, 'pops_its_cadastro_titulos', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para criar títulos']);
                return;
            }
            
            // Verificar se a tabela existe
            try {
                $stmt = $this->db->query("SHOW TABLES LIKE 'pops_its_titulos'");
                if (!$stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Tabela pops_its_titulos não existe. Execute o script SQL primeiro.']);
                    return;
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erro ao verificar tabela: ' . $e->getMessage()]);
                return;
            }
            
            // Validar dados
            $tipo = $_POST['tipo'] ?? '';
            $titulo = trim($_POST['titulo'] ?? '');
            $departamento_id = $_POST['departamento_id'] ?? '';
            
            if (empty($tipo) || empty($titulo) || empty($departamento_id)) {
                echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
                return;
            }
            
            if (!in_array($tipo, ['POP', 'IT'])) {
                echo json_encode(['success' => false, 'message' => 'Tipo inválido']);
                return;
            }
            
            // Normalizar título para verificação de duplicidade
            $titulo_normalizado = $this->normalizarTitulo($titulo);
            
            // Verificar se já existe
            $stmt = $this->db->prepare("SELECT id FROM pops_its_titulos WHERE tipo = ? AND titulo_normalizado = ?");
            $stmt->execute([$tipo, $titulo_normalizado]);
            
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Já existe um ' . $tipo . ' com este título']);
                return;
            }
            
            // Inserir no banco
            $stmt = $this->db->prepare("
                INSERT INTO pops_its_titulos (tipo, titulo, titulo_normalizado, departamento_id, criado_por) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$tipo, $titulo, $titulo_normalizado, $departamento_id, $user_id]);
            
            echo json_encode(['success' => true, 'message' => 'Título cadastrado com sucesso!']);
            
        } catch (\Exception $e) {
            // Log detalhado do erro
            error_log("PopItsController::createTitulo - Erro: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }
    
    private function normalizarTitulo($titulo)
    {
        $titulo = mb_strtolower($titulo, 'UTF-8');
        $titulo = preg_replace('/\s+/', ' ', $titulo);
        return trim($titulo);
    }

}
                            }
                        }
                    } catch (\Exception $e) {
                        $registro['titulo'] = 'Erro ao carregar título';
                        $registro['tipo'] = '';
                    }
                }
                
                echo json_encode(['success' => true, 'data' => $registros]);
                
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erro na consulta: ' . $e->getMessage()]);
                return;
            }
            
        } catch (\Exception $e) {
            error_log("PopItsController::listMeusRegistros - Erro: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }
    
    // Download de arquivo
    public function downloadArquivo($id)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo "Acesso negado";
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            $registro_id = (int)$id;
            
            // Buscar o registro
            $stmt = $this->db->prepare("
                SELECT r.*, t.titulo 
                FROM pops_its_registros r
                LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
                WHERE r.id = ?
            ");
            $stmt->execute([$registro_id]);
            $registro = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$registro) {
                http_response_code(404);
                echo "Arquivo não encontrado";
                return;
            }
            
            // Verificar permissões
            $isAdmin = \App\Services\PermissionService::isAdmin($user_id);
            $isOwner = ($registro['criado_por'] == $user_id);
            
            // Se não é admin nem dono, verificar se tem acesso
            if (!$isAdmin && !$isOwner) {
                // Se é público, pode acessar
                if (!$registro['publico']) {
                    // Se não é público, verificar se o departamento do usuário tem acesso
                    // TODO: Implementar verificação de departamento do usuário
                    http_response_code(403);
                    echo "Acesso negado a este arquivo";
                    return;
                }
            }
            
            // Definir headers para download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $registro['nome_arquivo'] . '"');
            header('Content-Length: ' . $registro['tamanho_arquivo']);
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            // Enviar o arquivo
            echo $registro['arquivo'];
            
        } catch (\Exception $e) {
            error_log("PopItsController::downloadArquivo - Erro: " . $e->getMessage());
            http_response_code(500);
            echo "Erro interno do servidor";
        }
    }
    
    // Excluir título (apenas admin)
    public function deleteTitulo()
    {
        header('Content-Type: application/json');
        
        try {
            // Verificar se é admin
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                return;
            }
            
            $user_id = $_SESSION['user_id'];
            if (!\App\Services\PermissionService::isAdmin($user_id)) {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores podem excluir títulos']);
                return;
            }
            
            $titulo_id = (int)($_POST['titulo_id'] ?? 0);
            
            if ($titulo_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID do título é obrigatório']);
                return;
            }
            
            // Verificar se o título existe
            $stmt = $this->db->prepare("SELECT titulo, tipo FROM pops_its_titulos WHERE id = ?");
            $stmt->execute([$titulo_id]);
            $titulo = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$titulo) {
                echo json_encode(['success' => false, 'message' => 'Título não encontrado']);
                return;
            }
            
            // Verificar se existem registros aprovados com este título
            // Assumindo que existe uma tabela pops_its_registros com status 'aprovado'
            try {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as total 
                    FROM pops_its_registros 
                    WHERE titulo_id = ? AND status = 'aprovado'
                ");
                $stmt->execute([$titulo_id]);
                $registrosAprovados = $stmt->fetchColumn();
                
                if ($registrosAprovados > 0) {
                    echo json_encode([
                        'success' => false, 
                        'message' => "Não é possível excluir este título. Existem {$registrosAprovados} registro(s) aprovado(s) vinculado(s) a ele."
                    ]);
                    return;
                }
            } catch (\Exception $e) {
                // Se a tabela pops_its_registros não existir, continua com a exclusão
                // mas registra o aviso
                error_log("Aviso: Tabela pops_its_registros não encontrada - " . $e->getMessage());
            }
            
            // Excluir o título
            $stmt = $this->db->prepare("DELETE FROM pops_its_titulos WHERE id = ?");
            $stmt->execute([$titulo_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true, 
                    'message' => "Título \"{$titulo['titulo']}\" ({$titulo['tipo']}) excluído com sucesso!"
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao excluir título']);
            }
            
        } catch (\Exception $e) {
            error_log("PopItsController::deleteTitulo - Erro: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }
    
    private function normalizarTitulo($titulo)
    {
        // Remove acentos, converte para minúsculas e remove caracteres especiais
        $titulo = strtolower($titulo);
        $titulo = iconv('UTF-8', 'ASCII//TRANSLIT', $titulo);
        $titulo = preg_replace('/[^a-z0-9\s]/', '', $titulo);
        $titulo = preg_replace('/\s+/', ' ', $titulo);
        return trim($titulo);
    }
    
    // Teste simples para verificar se a rota funciona
    public function testeTitulos()
    {
        header('Content-Type: application/json');
        
        try {
            // Teste básico de conexão
            $stmt = $this->db->query("SELECT 1 as teste");
            $teste = $stmt->fetch();
            
            // Verificar se a tabela existe
            $stmt = $this->db->query("SHOW TABLES LIKE 'pops_its_titulos'");
            $tabelaExiste = $stmt->fetch() ? true : false;
            
            $resultado = [
                'success' => true,
                'conexao_db' => $teste ? 'OK' : 'ERRO',
                'tabela_existe' => $tabelaExiste,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            if ($tabelaExiste) {
                // Contar registros
                $stmt = $this->db->query("SELECT COUNT(*) as total FROM pops_its_titulos");
                $total = $stmt->fetchColumn();
                $resultado['total_registros'] = $total;
                
                // Se há registros, buscar alguns
                if ($total > 0) {
                    $stmt = $this->db->query("SELECT id, titulo, tipo FROM pops_its_titulos LIMIT 3");
                    $resultado['exemplos'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                }
            }
            
            echo json_encode($resultado);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine()
            ]);
        }
    }
    
    // Método de diagnóstico para verificar estrutura do banco
    public function diagnostico()
    {
        // Verificar se é admin
        if (!\App\Services\PermissionService::isAdmin($_SESSION['user_id'])) {
            http_response_code(403);
            echo "<h1>Acesso Negado</h1><p>Apenas administradores podem acessar o diagnóstico.</p>";
            return;
        }

        try {
            echo "<!DOCTYPE html>
            <html lang='pt-br'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Diagnóstico POPs e ITs</title>
                <script src='https://cdn.tailwindcss.com'></script>
            </head>
            <body class='bg-gray-100 p-8'>
                <div class='max-w-4xl mx-auto space-y-6'>";

            echo "<div class='bg-white rounded-lg shadow p-6'>
                    <h1 class='text-2xl font-bold mb-4'>🔍 Diagnóstico POPs e ITs</h1>
                    <p class='text-gray-600'>Verificando estrutura do banco de dados...</p>
                  </div>";

            // 1. Verificar se a tabela existe
            echo "<div class='bg-white rounded-lg shadow p-6'>
                    <h2 class='text-xl font-semibold mb-4'>📋 Verificação da Tabela</h2>";
            
            $stmt = $this->db->query("SHOW TABLES LIKE 'pops_its_titulos'");
            $tabelaExiste = $stmt->fetch();
            
            if ($tabelaExiste) {
                echo "<p class='text-green-600 font-semibold'>✅ Tabela 'pops_its_titulos' existe</p>";
                
                // Verificar estrutura da tabela
                $stmt = $this->db->query("DESCRIBE pops_its_titulos");
                $colunas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                echo "<h3 class='font-semibold mt-4 mb-2'>Estrutura da tabela:</h3>";
                echo "<div class='overflow-x-auto'>";
                echo "<table class='min-w-full border border-gray-300'>";
                echo "<thead class='bg-gray-50'>";
                echo "<tr><th class='border border-gray-300 px-4 py-2'>Campo</th><th class='border border-gray-300 px-4 py-2'>Tipo</th><th class='border border-gray-300 px-4 py-2'>Null</th><th class='border border-gray-300 px-4 py-2'>Key</th><th class='border border-gray-300 px-4 py-2'>Default</th></tr>";
                echo "</thead><tbody>";
                
                foreach ($colunas as $coluna) {
                    echo "<tr>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $coluna['Field'] . "</td>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $coluna['Type'] . "</td>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $coluna['Null'] . "</td>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . $coluna['Key'] . "</td>";
                    echo "<td class='border border-gray-300 px-4 py-2'>" . ($coluna['Default'] ?? 'NULL') . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table></div>";
                
                // Contar registros
                $stmt = $this->db->query("SELECT COUNT(*) as total FROM pops_its_titulos");
                $total = $stmt->fetch(\PDO::FETCH_ASSOC);
                echo "<p class='mt-4'>📊 Total de registros: <strong>" . $total['total'] . "</strong></p>";
                
            } else {
                echo "<p class='text-red-600 font-semibold'>❌ Tabela 'pops_its_titulos' NÃO existe</p>";
                echo "<div class='mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded'>";
                echo "<h4 class='font-semibold text-yellow-800'>🔧 Solução:</h4>";
                echo "<p class='text-yellow-700'>Execute o script SQL localizado em:</p>";
                echo "<code class='bg-yellow-100 px-2 py-1 rounded'>database/create_pops_its_titulos.sql</code>";
                echo "</div>";
            }
            echo "</div>";

            // 2. Verificar tabela departamentos
            echo "<div class='bg-white rounded-lg shadow p-6'>
                    <h2 class='text-xl font-semibold mb-4'>🏢 Verificação de Departamentos</h2>";
            
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM departamentos");
            $totalDept = $stmt->fetch(\PDO::FETCH_ASSOC);
            echo "<p>📊 Total de departamentos: <strong>" . $totalDept['total'] . "</strong></p>";
            
            if ($totalDept['total'] > 0) {
                $stmt = $this->db->query("SELECT id, nome FROM departamentos ORDER BY nome LIMIT 5");
                $depts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                echo "<h4 class='font-semibold mt-2'>Primeiros 5 departamentos:</h4>";
                echo "<ul class='list-disc list-inside'>";
                foreach ($depts as $dept) {
                    echo "<li>ID: " . $dept['id'] . " - " . $dept['nome'] . "</li>";
                }
                echo "</ul>";
            }
            echo "</div>";

            // 3. Verificar permissões
            echo "<div class='bg-white rounded-lg shadow p-6'>
                    <h2 class='text-xl font-semibold mb-4'>🔐 Verificação de Permissões</h2>";
            
            $user_id = $_SESSION['user_id'];
            $permissoes = [
                'pops_its_cadastro_titulos' => 'Cadastro de Títulos',
                'pops_its_meus_registros' => 'Meus Registros',
                'pops_its_pendente_aprovacao' => 'Pendente Aprovação',
                'pops_its_visualizacao' => 'Visualização'
            ];
            
            echo "<table class='min-w-full border border-gray-300'>";
            echo "<thead class='bg-gray-50'>";
            echo "<tr><th class='border border-gray-300 px-4 py-2'>Módulo</th><th class='border border-gray-300 px-4 py-2'>View</th><th class='border border-gray-300 px-4 py-2'>Edit</th></tr>";
            echo "</thead><tbody>";
            
            foreach ($permissoes as $modulo => $nome) {
                $hasView = \App\Services\PermissionService::hasPermission($user_id, $modulo, 'view') ? '✅' : '❌';
                $hasEdit = \App\Services\PermissionService::hasPermission($user_id, $modulo, 'edit') ? '✅' : '❌';
                echo "<tr>";
                echo "<td class='border border-gray-300 px-4 py-2'>" . $nome . "</td>";
                echo "<td class='border border-gray-300 px-4 py-2'>" . $hasView . "</td>";
                echo "<td class='border border-gray-300 px-4 py-2'>" . $hasEdit . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";

            echo "</div></body></html>";

        } catch (\Exception $e) {
            echo "<div class='bg-red-50 border border-red-200 rounded p-4'>
                    <h3 class='text-red-800 font-semibold'>❌ Erro no Diagnóstico:</h3>
                    <p class='text-red-700'>" . htmlspecialchars($e->getMessage()) . "</p>
                    <p class='text-red-600 text-sm'>Arquivo: " . htmlspecialchars($e->getFile()) . "</p>
                    <p class='text-red-600 text-sm'>Linha: " . $e->getLine() . "</p>
                  </div>";
        }
    }

}
