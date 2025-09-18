<?php

require_once __DIR__ . '/src/Config/Database.php';
require_once __DIR__ . '/src/Core/Migration.php';

use App\Core\Migration;

try {
    echo "Executando migrações...\n";
    
    $migration = new Migration();
    $migration->run();
    
    echo "Migrações executadas com sucesso!\n";
} catch (Exception $e) {
    echo "Erro ao executar migrações: " . $e->getMessage() . "\n";
    exit(1);
}
