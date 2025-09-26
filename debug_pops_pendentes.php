<?php
// Script para diagnosticar problema dos POPs pendentes
require_once __DIR__ . '/src/Config/Database.php';
require_once __DIR__ . '/src/Services/PermissionService.php';

use App\Config\Database;
use App\Services\PermissionService;

try {
    $db = Database::getInstance();
    
    echo "<h2>🔍 DIAGNÓSTICO: POPs Pendentes de Aprovação</h2>";
    
    // 1. Verificar registros pendentes na tabela
    echo "<h3>1. Registros Pendentes no Banco:</h3>";
    $stmt = $db->prepare("
        SELECT r.*, 
               COALESCE(t.titulo, 'Título não encontrado') as titulo, 
               COALESCE(d.nome, 'Departamento não encontrado') as departamento_nome, 
               COALESCE(u.name, 'Usuário não encontrado') as criador_nome
        FROM pops_its_registros r
        LEFT JOIN pops_its_titulos t ON r.titulo_id = t.id
        LEFT JOIN departamentos d ON t.departamento_id = d.id
        LEFT JOIN users u ON r.created_by = u.id
        WHERE r.status = 'pendente'
        ORDER BY r.created_at ASC
    ");
    $stmt->execute();
    $registrosPendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total de registros pendentes:</strong> " . count($registrosPendentes) . "</p>";
    
    if (count($registrosPendentes) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Título</th><th>Departamento</th><th>Criador</th><th>Status</th><th>Data Criação</th></tr>";
        foreach ($registrosPendentes as $reg) {
            echo "<tr>";
            echo "<td>" . $reg['id'] . "</td>";
            echo "<td>" . htmlspecialchars($reg['titulo']) . "</td>";
            echo "<td>" . htmlspecialchars($reg['departamento_nome']) . "</td>";
            echo "<td>" . htmlspecialchars($reg['criador_nome']) . "</td>";
            echo "<td><span style='background: orange; color: white; padding: 2px 8px; border-radius: 3px;'>" . $reg['status'] . "</span></td>";
            echo "<td>" . $reg['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ <strong>PROBLEMA:</strong> Não há registros com status 'pendente' no banco!</p>";
        
        // Verificar todos os status existentes
        echo "<h4>Status existentes na tabela:</h4>";
        $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM pops_its_registros GROUP BY status");
        $stmt->execute();
        $statusCount = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<ul>";
        foreach ($statusCount as $status) {
            echo "<li><strong>" . $status['status'] . ":</strong> " . $status['count'] . " registros</li>";
        }
        echo "</ul>";
    }
    
    // 2. Testar a rota diretamente
    echo "<h3>2. Teste da Rota /pops-its/pendentes/list:</h3>";
    echo "<p><a href='/pops-its/pendentes/list?debug=1' target='_blank' style='background: #007cba; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>🧪 Testar Rota com Debug</a></p>";
    echo "<p><a href='/pops-its/pendentes/list' target='_blank' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>🔗 Testar Rota Normal</a></p>";
    
    // 3. Verificar permissões do usuário atual
    if (isset($_SESSION['user_id'])) {
        echo "<h3>3. Permissões do Usuário Atual:</h3>";
        $userId = $_SESSION['user_id'];
        
        // Verificar permissões POPs
        $permissions = [
            'pops_its_visualizacao' => 'Visualização',
            'pops_its_pendente_aprovacao' => 'Pendente Aprovação',
            'pops_its_cadastro_titulos' => 'Cadastro Títulos',
            'pops_its_meus_registros' => 'Meus Registros'
        ];
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Módulo</th><th>Descrição</th><th>View</th><th>Edit</th><th>Delete</th></tr>";
        foreach ($permissions as $module => $desc) {
            $canView = PermissionService::hasPermission($userId, $module, 'view');
            $canEdit = PermissionService::hasPermission($userId, $module, 'edit');
            $canDelete = PermissionService::hasPermission($userId, $module, 'delete');
            
            echo "<tr>";
            echo "<td>" . $module . "</td>";
            echo "<td>" . $desc . "</td>";
            echo "<td>" . ($canView ? '✅' : '❌') . "</td>";
            echo "<td>" . ($canEdit ? '✅' : '❌') . "</td>";
            echo "<td>" . ($canDelete ? '✅' : '❌') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar se é admin
        $isAdmin = PermissionService::isAdmin($userId);
        echo "<p><strong>É Administrador:</strong> " . ($isAdmin ? '✅ SIM' : '❌ NÃO') . "</p>";
        
    } else {
        echo "<h3>3. ⚠️ Usuário não está logado</h3>";
        echo "<p>Faça login para testar as permissões.</p>";
    }
    
    // 4. Verificar estrutura das tabelas
    echo "<h3>4. Estrutura das Tabelas:</h3>";
    
    // Verificar se tabelas existem
    $tables = ['pops_its_registros', 'pops_its_titulos', 'departamentos', 'users'];
    foreach ($tables as $table) {
        $stmt = $db->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = $stmt->fetch();
        
        echo "<p><strong>$table:</strong> " . ($exists ? '✅ Existe' : '❌ Não existe') . "</p>";
        
        if ($exists && $table === 'pops_its_registros') {
            // Mostrar estrutura da tabela principal
            echo "<h4>Colunas da tabela pops_its_registros:</h4>";
            $stmt = $db->prepare("DESCRIBE pops_its_registros");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>" . $col['Field'] . "</td>";
                echo "<td>" . $col['Type'] . "</td>";
                echo "<td>" . $col['Null'] . "</td>";
                echo "<td>" . $col['Key'] . "</td>";
                echo "<td>" . $col['Default'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // 5. Verificar logs de erro
    echo "<h3>5. Logs de Erro:</h3>";
    $logFile = __DIR__ . '/logs/pops_its_debug.log';
    if (file_exists($logFile)) {
        echo "<h4>Últimas 10 linhas do log:</h4>";
        $lines = file($logFile);
        $lastLines = array_slice($lines, -10);
        echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
        echo htmlspecialchars(implode('', $lastLines));
        echo "</pre>";
    } else {
        echo "<p>📝 Arquivo de log não existe ainda: $logFile</p>";
    }
    
    // 6. Soluções sugeridas
    echo "<h3>6. 🔧 Possíveis Soluções:</h3>";
    echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; border-left: 4px solid #007cba;'>";
    echo "<h4>Se não há registros pendentes:</h4>";
    echo "<ul>";
    echo "<li>Verifique se existem registros na tabela pops_its_registros</li>";
    echo "<li>Confirme se algum registro tem status = 'pendente'</li>";
    echo "<li>Verifique se os registros foram criados corretamente</li>";
    echo "</ul>";
    
    echo "<h4>Se há registros mas não aparecem:</h4>";
    echo "<ul>";
    echo "<li>Teste a rota /pops-its/pendentes/list diretamente</li>";
    echo "<li>Verifique permissões do usuário para 'pops_its_pendente_aprovacao'</li>";
    echo "<li>Confirme se o JavaScript está carregando os dados</li>";
    echo "<li>Verifique erros no console do navegador (F12)</li>";
    echo "</ul>";
    
    echo "<h4>Se visualização fica 'Carregando...':</h4>";
    echo "<ul>";
    echo "<li>Problema na rota /pops-its/visualizacao/list</li>";
    echo "<li>Verifique permissões para 'pops_its_visualizacao'</li>";
    echo "<li>Teste a rota diretamente no navegador</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
    echo "<br>Arquivo: " . $e->getFile();
    echo "<br>Linha: " . $e->getLine();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
table { margin: 10px 0; border-collapse: collapse; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f0f0f0; font-weight: bold; }
h2 { color: #333; border-bottom: 2px solid #333; padding-bottom: 5px; }
h3 { color: #666; margin-top: 25px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
ul { margin: 10px 0; }
li { margin: 3px 0; }
a { color: #007cba; text-decoration: none; }
a:hover { text-decoration: underline; }
pre { max-height: 300px; overflow-y: auto; }
</style>
