<?php
// Fallback index.php para redirecionamento
// Caso o .htaccess não funcione, este arquivo redireciona para public/

// Verificar se estamos na raiz e redirecionar para public/
if (basename(__DIR__) !== 'public') {
    // Se o arquivo public/index.php existe, redirecionar
    if (file_exists(__DIR__ . '/public/index.php')) {
        // Redirecionar mantendo a query string se houver
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        $redirect = '/public/' . ($_SERVER['REQUEST_URI'] ?? '');
        
        // Limpar double slashes
        $redirect = preg_replace('#/+#', '/', $redirect);
        
        header('Location: ' . $redirect);
        exit;
    }
}

// Se chegou até aqui, há um problema
echo '<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGQ OTI DJ - Configuração</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error { color: #d32f2f; }
        .success { color: #388e3c; }
    </style>
</head>
<body>
    <div class="container">
        <h1>SGQ OTI DJ - Sistema de Gestão da Qualidade</h1>
        <p class="error">⚠️ Problema de configuração detectado</p>
        <p>O sistema não conseguiu carregar corretamente. Possíveis causas:</p>
        <ul>
            <li>Arquivo public/index.php não encontrado</li>
            <li>Configuração do servidor web incorreta</li>
            <li>Problema com o .htaccess</li>
        </ul>
        <p><strong>Soluções:</strong></p>
        <ol>
            <li>Verificar se o arquivo public/index.php existe</li>
            <li>Configurar o DocumentRoot para apontar para a pasta public/</li>
            <li>Verificar se o mod_rewrite está habilitado</li>
        </ol>
        <hr>
        <p><small>Para suporte técnico, entre em contato com o administrador do sistema.</small></p>
    </div>
</body>
</html>';
?>
