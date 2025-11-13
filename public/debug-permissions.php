<?php
session_start();
require __DIR__ . ''/../vendor/autoload.php'';

echo ''<pre style="background:#000;color:#0f0;padding:20px;font-family:monospace">'';
echo ''===== DEBUG DE PERMISSÕES =====" . PHP_EOL . PHP_EOL;

if (!isset($_SESSION[''user_id''])) {
    echo ''ERRO: Usuário não está logado!'' . PHP_EOL;
    exit;
}

$userId = $_SESSION[''user_id''];
echo ''User ID: '' . $userId . PHP_EOL;
echo ''User Name: '' . ($_SESSION[''user_name''] ?? ''N/A'') . PHP_EOL;
echo ''User Role: '' . ($_SESSION[''user_role''] ?? ''N/A'') . PHP_EOL;
echo ''User Profile: '' . json_encode($_SESSION[''user_profile''] ?? []) . PHP_EOL . PHP_EOL;

// Carregar permissões
$permissions = \App\Services\PermissionService::getUserPermissions($userId);
echo ''===== PERMISSÕES DO BANCO =====" . PHP_EOL;
echo json_encode($permissions, JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;

// Testar alguns módulos importantes
$testModules = [''dashboard'', ''cadastro_toners'', ''cadastro_maquinas'', ''nps'', ''garantias''];
echo ''===== TESTE DE PERMISSÕES =====" . PHP_EOL;
foreach ($testModules as $module) {
    $hasView = \App\Services\PermissionService::hasPermission($userId, $module, ''view'');
    echo $module . '' (view): '' . ($hasView ? '' SIM'' : '' NÃO'') . PHP_EOL;
}

echo ''</pre>'';
