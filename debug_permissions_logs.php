<?php
// Script de diagnóstico para permissões de logs POPs e ITs
session_start();

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

use App\Config\Database;
use App\Services\PermissionService;

echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnóstico - Permissões Logs POPs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .success { color: #388e3c; }
        .error { color: #d32f2f; }
        .warning { color: #f57c00; }
        .info { color: #1976d2; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>";

echo "<div class='container'>";
echo "<h1>🔐 Diagnóstico - Permissões para Logs POPs e ITs</h1>";
echo "<p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>";

try {
    // Verificar se usuário está logado
    if (!isset($_SESSION['user_id'])) {
        echo "<p class='error'>❌ Usuário não está logado</p>";
        echo "<p class='info'>Para testar, faça login no sistema primeiro</p>";
        echo "<p><a href='/login'>🔗 Ir para Login</a></p>";
    } else {
        $user_id = $_SESSION['user_id'];
        echo "<p class='success'>✅ Usuário logado - ID: {$user_id}</p>";
        
        // Conectar ao banco
        $db = Database::getInstance();
        
        // Buscar informações do usuário
        $stmt = $db->prepare("
            SELECT u.*, p.name as profile_name 
            FROM users u 
            LEFT JOIN profiles p ON u.profile_id = p.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "<h2>👤 Informações do Usuário</h2>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valor</th></tr>";
            echo "<tr><td>ID</td><td>{$user['id']}</td></tr>";
            echo "<tr><td>Nome</td><td>{$user['name']}</td></tr>";
            echo "<tr><td>Email</td><td>{$user['email']}</td></tr>";
            echo "<tr><td>Perfil</td><td>{$user['profile_name']}</td></tr>";
            echo "<tr><td>Status</td><td>{$user['status']}</td></tr>";
            echo "</table>";
            
            // Verificar se é admin
            echo "<h2>🔍 Verificação de Permissões</h2>";
            
            $isAdmin = PermissionService::isAdmin($user_id);
            $isSuperAdmin = PermissionService::isSuperAdmin($user_id);
            
            echo "<p><strong>É Admin?</strong> " . ($isAdmin ? "<span class='success'>✅ SIM</span>" : "<span class='error'>❌ NÃO</span>") . "</p>";
            echo "<p><strong>É Super Admin?</strong> " . ($isSuperAdmin ? "<span class='success'>✅ SIM</span>" : "<span class='error'>❌ NÃO</span>") . "</p>";
            
            // Verificar permissões específicas do POPs
            $permissions = [
                'pops_its_visualizacao' => 'Visualização POPs',
                'pops_its_cadastro_titulos' => 'Cadastro de Títulos',
                'pops_its_meus_registros' => 'Meus Registros',
                'pops_its_pendente_aprovacao' => 'Pendente Aprovação'
            ];
            
            echo "<h3>📋 Permissões POPs e ITs:</h3>";
            echo "<table>";
            echo "<tr><th>Módulo</th><th>Descrição</th><th>View</th><th>Edit</th><th>Delete</th></tr>";
            
            foreach ($permissions as $module => $description) {
                $hasView = PermissionService::hasPermission($user_id, $module, 'view');
                $hasEdit = PermissionService::hasPermission($user_id, $module, 'edit');
                $hasDelete = PermissionService::hasPermission($user_id, $module, 'delete');
                
                echo "<tr>";
                echo "<td>{$module}</td>";
                echo "<td>{$description}</td>";
                echo "<td>" . ($hasView ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . "</td>";
                echo "<td>" . ($hasEdit ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . "</td>";
                echo "<td>" . ($hasDelete ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Verificar especificamente a lógica da aba de logs
            echo "<h2>🔍 Lógica da Aba de Logs</h2>";
            $canViewLogsVisualizacao = $isAdmin; // Esta é a lógica do controller
            
            echo "<p><strong>Pode ver logs de visualização?</strong> " . 
                 ($canViewLogsVisualizacao ? "<span class='success'>✅ SIM</span>" : "<span class='error'>❌ NÃO</span>") . "</p>";
            
            if (!$canViewLogsVisualizacao) {
                echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>⚠️ Por que não posso ver os logs?</h4>";
                echo "<p>A aba 'Log de Visualizações' só é visível para <strong>administradores</strong>.</p>";
                echo "<p>Seu perfil atual é: <strong>{$user['profile_name']}</strong></p>";
                echo "<p><strong>Soluções:</strong></p>";
                echo "<ul>";
                echo "<li>Solicite ao administrador para alterar seu perfil para 'Administrador'</li>";
                echo "<li>Ou solicite permissões específicas de administrador</li>";
                echo "</ul>";
                echo "</div>";
            }
            
            // Testar endpoint diretamente
            echo "<h2>🧪 Teste do Endpoint</h2>";
            if ($canViewLogsVisualizacao) {
                echo "<p class='info'>🔗 Testando endpoint: /pops-its/logs/visualizacao</p>";
                
                // Simular chamada para o controller
                try {
                    $controller = new \App\Controllers\PopItsController();
                    
                    // Capturar output
                    ob_start();
                    $controller->listLogsVisualizacao();
                    $output = ob_get_clean();
                    
                    $result = json_decode($output, true);
                    
                    if ($result) {
                        if ($result['success']) {
                            $count = count($result['data']);
                            echo "<p class='success'>✅ Endpoint funcionando - {$count} logs encontrados</p>";
                            
                            if ($count > 0) {
                                echo "<h4>Primeiros 3 logs:</h4>";
                                echo "<pre>" . json_encode(array_slice($result['data'], 0, 3), JSON_PRETTY_PRINT) . "</pre>";
                            }
                        } else {
                            echo "<p class='error'>❌ Endpoint retornou erro: {$result['message']}</p>";
                        }
                    } else {
                        echo "<p class='warning'>⚠️ Resposta não é JSON válido</p>";
                        echo "<pre>" . htmlspecialchars($output) . "</pre>";
                    }
                    
                } catch (Exception $e) {
                    echo "<p class='error'>❌ Erro ao testar endpoint: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p class='warning'>⚠️ Não é possível testar endpoint - usuário não é admin</p>";
            }
            
        } else {
            echo "<p class='error'>❌ Usuário não encontrado no banco de dados</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro geral: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</div>";

echo "<div class='container'>";
echo "<h2>🔧 Links Úteis</h2>";
echo "<p><a href='/'>🏠 Voltar ao Sistema</a></p>";
echo "<p><a href='/pops-e-its'>📚 Ir para POPs e ITs</a></p>";
echo "<p><a href='/pops-its/teste-logs'>🧪 Testar Logs (API)</a></p>";
echo "<p><a href='/debug_logs_pops.php'>🔍 Diagnóstico Completo de Logs</a></p>";
echo "</div>";

echo "</body></html>";
?>
