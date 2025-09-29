<?php
// Teste básico de funcionamento
echo "PHP funcionando! Data/Hora: " . date('Y-m-d H:i:s');

// Teste de conexão com banco
try {
    require_once __DIR__ . '/../src/Config/Database.php';
    $db = App\Config\Database::getInstance();
    echo "<br>Conexão com banco: OK";
} catch (Exception $e) {
    echo "<br>Erro de conexão: " . $e->getMessage();
}

// Teste de sessão
session_start();
echo "<br>Sessão: OK";

// Teste de autoload
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<br>Autoload: OK";
} catch (Exception $e) {
    echo "<br>Erro autoload: " . $e->getMessage();
}

phpinfo();
?>
