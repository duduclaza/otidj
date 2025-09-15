<?php
// API para processamento de formulários do SGQ PRO
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $db = getDB();
    $response = ['success' => false, 'message' => '', 'data' => null];
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_filial':
            $nome = trim($_POST['nome_filial'] ?? '');
            if (empty($nome)) {
                throw new Exception('Nome da filial é obrigatório');
            }
            
            // Verifica se já existe
            $existing = $db->fetchOne("SELECT id FROM filiais WHERE nome = ?", [$nome]);
            if ($existing) {
                throw new Exception('Filial já cadastrada');
            }
            
            $id = $db->insert('filiais', [
                'nome' => $nome,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $response = [
                'success' => true,
                'message' => 'Filial cadastrada com sucesso',
                'data' => ['id' => $id, 'nome' => $nome]
            ];
            break;
            
        case 'add_departamento':
            $nome = trim($_POST['nome_departamento'] ?? '');
            if (empty($nome)) {
                throw new Exception('Nome do departamento é obrigatório');
            }
            
            $existing = $db->fetchOne("SELECT id FROM departamentos WHERE nome = ?", [$nome]);
            if ($existing) {
                throw new Exception('Departamento já cadastrado');
            }
            
            $id = $db->insert('departamentos', [
                'nome' => $nome,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $response = [
                'success' => true,
                'message' => 'Departamento cadastrado com sucesso',
                'data' => ['id' => $id, 'nome' => $nome]
            ];
            break;
            
        case 'add_fornecedor':
            $nome = trim($_POST['nome_fornecedor'] ?? '');
            $contato = trim($_POST['contato_fornecedor'] ?? '');
            $email = trim($_POST['email_fornecedor'] ?? '');
            
            if (empty($nome)) {
                throw new Exception('Nome do fornecedor é obrigatório');
            }
            
            $existing = $db->fetchOne("SELECT id FROM fornecedores WHERE nome = ?", [$nome]);
            if ($existing) {
                throw new Exception('Fornecedor já cadastrado');
            }
            
            $id = $db->insert('fornecedores', [
                'nome' => $nome,
                'contato' => $contato,
                'email' => $email,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $response = [
                'success' => true,
                'message' => 'Fornecedor cadastrado com sucesso',
                'data' => ['id' => $id, 'nome' => $nome]
            ];
            break;
            
        case 'get_filiais':
            $filiais = $db->fetchAll("SELECT * FROM filiais ORDER BY nome");
            $response = [
                'success' => true,
                'data' => $filiais
            ];
            break;
            
        case 'get_departamentos':
            $departamentos = $db->fetchAll("SELECT * FROM departamentos ORDER BY nome");
            $response = [
                'success' => true,
                'data' => $departamentos
            ];
            break;
            
        case 'get_fornecedores':
            $fornecedores = $db->fetchAll("SELECT * FROM fornecedores ORDER BY nome");
            $response = [
                'success' => true,
                'data' => $fornecedores
            ];
            break;
            
        default:
            throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    if (!isProduction()) {
        $response['debug'] = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
