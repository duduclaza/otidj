<?php
// Diagnóstico simples para identificar erros de configuração

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>Diagnóstico SGQ</h1>";

echo '<p>Versão PHP: ' . phpversion() . '</p>';

echo '<h2>Autoload</h2>';
try {
    require __DIR__ . '/../vendor/autoload.php';
    echo '<p style="color:green">Autoload carregado com sucesso.</p>';

    if (class_exists(Dotenv\Dotenv::class)) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad();
        echo '<p style="color:green">Arquivo .env carregado.</p>';
    } else {
        echo '<p style="color:orange">Dotenv não encontrado — usando variáveis de ambiente atuais.</p>';
    }
} catch (Throwable $e) {
    echo '<pre style="color:red">Erro ao carregar autoload: ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    echo '</pre>';
    exit;
}


echo '<h2>Conexão com Banco de Dados</h2>';
try {
    $db = App\Config\Database::getInstance();
    $stmt = $db->query('SELECT NOW() as agora');
    $agora = $stmt->fetchColumn();
    echo '<p style="color:green">Conexão OK. NOW(): ' . htmlspecialchars((string) $agora) . '</p>';
} catch (Throwable $e) {
    echo '<pre style="color:red">Erro ao conectar no banco: ' . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n\n";
    echo $e->getTraceAsString();
    echo '</pre>';
    exit;
}


echo '<h2>Permissões do Módulo NPS</h2>';
try {
    $stmt = $db->query("SELECT p.name, pp.module, pp.can_view, pp.can_edit, pp.can_delete, pp.can_export
                         FROM profile_permissions pp
                         JOIN profiles p ON pp.profile_id = p.id
                         WHERE pp.module = 'nps'");
    $permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$permissoes) {
        echo '<p style="color:red">Nenhuma permissão encontrada para módulo NPS.</p>';
    } else {
        echo '<pre>' . htmlspecialchars(json_encode($permissoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
    }
} catch (Throwable $e) {
    echo '<pre style="color:red">Erro ao consultar permissões: ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    echo '</pre>';
}

echo '<h2>Respostas JSON de Debug</h2>';
try {
    $payload = [
        'session' => [
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_email' => $_SESSION['user_email'] ?? null,
            'user_role' => $_SESSION['user_role'] ?? null,
        ],
        'storage' => [
            'storage_dir' => __DIR__ . '/../storage/formularios',
            'respostas_dir' => __DIR__ . '/../storage/formularios/respostas',
            'storage_exists' => is_dir(__DIR__ . '/../storage/formularios'),
            'respostas_exists' => is_dir(__DIR__ . '/../storage/formularios/respostas'),
        ],
    ];

    echo '<pre>' . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
} catch (Throwable $e) {
    echo '<pre style="color:red">Erro ao montar payload: ' . $e->getMessage() . '</pre>';
}

echo '<p style="color:gray">Remova este arquivo após o diagnóstico.</p>';
