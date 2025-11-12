<?php
session_start();

// Simular usuário logado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Test User';
    $_SESSION['user_role'] = 'admin';
}

// Carregar autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Carregar .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Testar listagem de formulários
$controller = new App\Controllers\NpsController();

echo "<h1>Teste Debug NPS</h1>";
echo "<h2>Session:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Chamando listar():</h2>";
ob_start();
$controller->listar();
$output = ob_get_clean();

echo "<pre>";
echo htmlspecialchars($output);
echo "</pre>";

echo "<h2>JSON Decodificado:</h2>";
$data = json_decode($output, true);
echo "<pre>";
print_r($data);
echo "</pre>";

if (isset($data['formularios']) && !empty($data['formularios'])) {
    echo "<h2>Primeiro Formulário:</h2>";
    echo "<pre>";
    print_r($data['formularios'][0]);
    echo "</pre>";
    
    echo "<h3>Verificação de Edição:</h3>";
    $f = $data['formularios'][0];
    echo "- total_respostas: " . $f['total_respostas'] . "<br>";
    echo "- total_respostas === 0: " . ($f['total_respostas'] === 0 ? 'SIM' : 'NÃO') . "<br>";
    echo "- total_respostas == 0: " . ($f['total_respostas'] == 0 ? 'SIM' : 'NÃO') . "<br>";
    echo "- Tipo: " . gettype($f['total_respostas']) . "<br>";
    echo "- Pode editar? " . ($f['total_respostas'] === 0 ? 'SIM' : 'NÃO') . "<br>";
}
