<?php
/**
 * CORREÇÃO RÁPIDA DO ERRO DE SINTAXE
 * 
 * Este script corrige o erro de sintaxe no PermissionMiddleware.php
 * que está causando o erro HTTP 500
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>Correção de Sintaxe - SGQ OTI DJ</title>";
echo "<meta charset='UTF-8'><style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;background:#f5f5f5;} .card{background:white;padding:20px;margin:10px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);} .success{color:#008000;} .error{color:#ff0000;} .warning{color:#ff8800;} .code{background:#f8f8f8;padding:10px;border-radius:4px;font-family:monospace;} .btn{display:inline-block;padding:15px 30px;background:#00cc00;color:white;text-decoration:none;border-radius:5px;margin:10px;font-size:18px;}</style></head><body>";

echo "<h1>🔧 CORREÇÃO DE ERRO DE SINTAXE</h1>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Problema:</strong> Parse error no PermissionMiddleware.php linha 58</p>";
echo "<hr>";

// Caminho do arquivo problemático
$middlewareFile = __DIR__ . '/src/Middleware/PermissionMiddleware.php';

echo "<h2>1. Verificando arquivo problemático...</h2>";

if (!file_exists($middlewareFile)) {
    echo "<p class='error'>❌ Arquivo não encontrado: $middlewareFile</p>";
    echo "<p>O arquivo pode estar em um local diferente.</p>";
    
    // Tentar encontrar o arquivo
    $possiblePaths = [
        __DIR__ . '/src/Middleware/PermissionMiddleware.php',
        __DIR__ . '/Middleware/PermissionMiddleware.php',
        __DIR__ . '/app/Middleware/PermissionMiddleware.php'
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            echo "<p class='success'>✅ Arquivo encontrado em: $path</p>";
            $middlewareFile = $path;
            break;
        }
    }
    
    if (!file_exists($middlewareFile)) {
        echo "<p class='error'>❌ Arquivo não encontrado em nenhum local esperado</p>";
        echo "</body></html>";
        exit;
    }
}

echo "<p class='success'>✅ Arquivo encontrado: $middlewareFile</p>";

// Fazer backup
$backupFile = $middlewareFile . '.backup.' . date('Y-m-d_H-i-s');
if (copy($middlewareFile, $backupFile)) {
    echo "<p class='success'>✅ Backup criado: " . basename($backupFile) . "</p>";
} else {
    echo "<p class='warning'>⚠️ Não foi possível criar backup</p>";
}

echo "<h2>2. Analisando o erro...</h2>";

// Ler o arquivo
$content = file_get_contents($middlewareFile);

// Procurar pelo padrão problemático
if (strpos($content, '{{ ... }}') !== false) {
    echo "<p class='error'>❌ Erro encontrado: '{{ ... }}' na linha ~58</p>";
    echo "<p>Este é um placeholder inválido que precisa ser removido.</p>";
    
    // Corrigir o erro
    echo "<h2>3. Aplicando correção...</h2>";
    
    // Substituir o padrão problemático
    $correctedContent = str_replace(
        "'/melhoria-continua/usuarios' => 'melhoria_continua',\n{{ ... }}\n        '/melhoria-continua/store' => 'melhoria_continua',",
        "'/melhoria-continua/usuarios' => 'melhoria_continua',\n        '/melhoria-continua/store' => 'melhoria_continua',",
        $content
    );
    
    // Também tentar outras variações possíveis
    $correctedContent = str_replace('{{ ... }}', '', $correctedContent);
    $correctedContent = preg_replace('/\{\{.*?\}\}/', '', $correctedContent);
    
    // Salvar o arquivo corrigido
    if (file_put_contents($middlewareFile, $correctedContent)) {
        echo "<p class='success'>✅ Arquivo corrigido com sucesso!</p>";
        
        // Verificar se a sintaxe está correta agora
        echo "<h2>4. Verificando sintaxe...</h2>";
        
        $output = [];
        $returnVar = 0;
        exec("php -l " . escapeshellarg($middlewareFile) . " 2>&1", $output, $returnVar);
        
        if ($returnVar === 0) {
            echo "<p class='success'>✅ Sintaxe PHP válida!</p>";
            echo "<div class='code'>" . implode('<br>', $output) . "</div>";
        } else {
            echo "<p class='error'>❌ Ainda há erros de sintaxe:</p>";
            echo "<div class='code'>" . implode('<br>', $output) . "</div>";
        }
        
    } else {
        echo "<p class='error'>❌ Falha ao salvar arquivo corrigido</p>";
    }
    
} else {
    echo "<p class='warning'>⚠️ Padrão '{{ ... }}' não encontrado</p>";
    echo "<p>O erro pode ter sido corrigido ou estar em outro formato.</p>";
    
    // Verificar sintaxe atual
    echo "<h2>3. Verificando sintaxe atual...</h2>";
    
    $output = [];
    $returnVar = 0;
    exec("php -l " . escapeshellarg($middlewareFile) . " 2>&1", $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "<p class='success'>✅ Sintaxe PHP válida!</p>";
    } else {
        echo "<p class='error'>❌ Erros de sintaxe encontrados:</p>";
        echo "<div class='code'>" . implode('<br>', $output) . "</div>";
        
        // Tentar correções adicionais
        echo "<h2>4. Tentando correções adicionais...</h2>";
        
        // Remover caracteres problemáticos comuns
        $patterns = [
            '/\{\{.*?\}\}/' => '', // Remove {{ ... }}
            '/\{\s*\.\.\.\s*\}/' => '', // Remove { ... }
            '/\[\s*\.\.\.\s*\]/' => '', // Remove [ ... ]
        ];
        
        $correctedContent = $content;
        foreach ($patterns as $pattern => $replacement) {
            $correctedContent = preg_replace($pattern, $replacement, $correctedContent);
        }
        
        if ($correctedContent !== $content) {
            if (file_put_contents($middlewareFile, $correctedContent)) {
                echo "<p class='success'>✅ Correções adicionais aplicadas</p>";
                
                // Verificar novamente
                exec("php -l " . escapeshellarg($middlewareFile) . " 2>&1", $output2, $returnVar2);
                if ($returnVar2 === 0) {
                    echo "<p class='success'>✅ Sintaxe corrigida!</p>";
                } else {
                    echo "<p class='error'>❌ Ainda há problemas:</p>";
                    echo "<div class='code'>" . implode('<br>', $output2) . "</div>";
                }
            }
        }
    }
}

// Resultado final
echo "<hr>";
echo "<h2>5. Resultado Final</h2>";

// Verificar se o sistema está funcionando
$finalCheck = [];
$finalReturn = 0;
exec("php -l " . escapeshellarg($middlewareFile) . " 2>&1", $finalCheck, $finalReturn);

if ($finalReturn === 0) {
    echo "<div style='background:#e6ffe6;padding:20px;border-radius:10px;text-align:center;'>";
    echo "<h2 class='success'>🎉 CORREÇÃO CONCLUÍDA!</h2>";
    echo "<p>O erro de sintaxe foi corrigido. O sistema deve estar funcionando agora.</p>";
    echo "<a href='/' class='btn'>🚀 TESTAR SISTEMA</a>";
    echo "<a href='/final_health_check.php' class='btn' style='background:#0066cc;'>🏥 VERIFICAÇÃO COMPLETA</a>";
    echo "</div>";
} else {
    echo "<div style='background:#ffe6e6;padding:20px;border-radius:10px;text-align:center;'>";
    echo "<h2 class='error'>⚠️ PROBLEMAS PERSISTEM</h2>";
    echo "<p>Ainda há erros de sintaxe que precisam ser corrigidos manualmente.</p>";
    echo "<div class='code'>" . implode('<br>', $finalCheck) . "</div>";
    echo "</div>";
}

// Instruções de limpeza
echo "<div style='background:#f0f8ff;padding:15px;border-radius:5px;margin:20px 0;'>";
echo "<h3>📋 Próximos Passos:</h3>";
echo "<ol>";
echo "<li>Teste o sistema clicando no botão acima</li>";
echo "<li>Se funcionar, execute uma verificação completa</li>";
echo "<li>Considere deletar os arquivos de correção após confirmação</li>";
echo "<li>Mantenha o backup em caso de problemas futuros</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align:center;color:#666;font-size:12px;'>";
echo "SGQ OTI DJ - Correção de Sintaxe<br>";
echo "Executado em " . date('d/m/Y H:i:s');
echo "</p>";

echo "</body></html>";
?>
