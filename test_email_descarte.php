<?php
// Script de teste para sistema de email de descartes
// Execute este arquivo para testar o envio de emails

session_start();
require_once 'config/database.php';
require_once 'src/Services/EmailService.php';

echo "<h2>🧪 TESTE DE EMAIL - CONTROLE DE DESCARTES</h2>";

// Simular dados de um descarte
$descarteExemplo = [
    'id' => 999,
    'numero_serie' => 'TEST123456',
    'codigo_produto' => 'TONER-HP-85A',
    'descricao_produto' => 'Toner HP LaserJet 85A Preto',
    'responsavel_tecnico' => 'João Silva',
    'data_descarte' => date('Y-m-d'),
    'status' => 'Aguardando Descarte',
    'numero_os' => 'OS-2025-001',
    'observacoes' => 'Toner com defeito de fabricação, não recarregável.',
    'created_at' => date('Y-m-d H:i:s')
];

// Destinatários de teste
$destinatariosExemplo = [
    [
        'id' => 1,
        'name' => 'Administrador Teste',
        'email' => 'admin@exemplo.com' // ALTERE PARA SEU EMAIL REAL
    ],
    [
        'id' => 2,
        'name' => 'Super Admin Teste',
        'email' => 'superadmin@exemplo.com' // ALTERE PARA SEU EMAIL REAL
    ]
];

$criadorNome = 'Sistema de Teste';

echo "<h3>📋 Dados do Teste:</h3>";
echo "<ul>";
echo "<li><strong>Produto:</strong> {$descarteExemplo['codigo_produto']} - {$descarteExemplo['descricao_produto']}</li>";
echo "<li><strong>Série:</strong> {$descarteExemplo['numero_serie']}</li>";
echo "<li><strong>Responsável:</strong> {$descarteExemplo['responsavel_tecnico']}</li>";
echo "<li><strong>Status:</strong> {$descarteExemplo['status']}</li>";
echo "<li><strong>Destinatários:</strong> " . count($destinatariosExemplo) . " pessoa(s)</li>";
echo "</ul>";

echo "<h3>📧 Configuração SMTP:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> smtp.hostinger.com</li>";
echo "<li><strong>Porta:</strong> 465 (SSL)</li>";
echo "<li><strong>Usuário:</strong> suporte@djbr.sgqoti.com.br</li>";
echo "<li><strong>Remetente:</strong> SGQ OTI DJ</li>";
echo "</ul>";

echo "<h3>🚀 Executando Teste...</h3>";

try {
    // Criar instância do serviço de email
    $emailService = new \App\Services\EmailService();
    
    echo "<p>✅ Serviço de email inicializado</p>";
    
    // Testar conexão SMTP
    echo "<p>🔗 Testando conexão SMTP...</p>";
    $conexaoTeste = $emailService->testarConexao();
    
    if ($conexaoTeste['success']) {
        echo "<p style='color: green;'>✅ Conexão SMTP: " . $conexaoTeste['message'] . "</p>";
        
        // Enviar email de teste
        echo "<p>📤 Enviando email de teste...</p>";
        $resultado = $emailService->enviarNotificacaoDescarte(
            $descarteExemplo,
            $destinatariosExemplo,
            $criadorNome
        );
        
        if ($resultado['success']) {
            echo "<p style='color: green;'>✅ <strong>EMAIL ENVIADO COM SUCESSO!</strong></p>";
            echo "<p>📧 Mensagem: " . $resultado['message'] . "</p>";
            echo "<p>👥 Destinatários: " . implode(', ', array_column($destinatariosExemplo, 'email')) . "</p>";
            
            echo "<h3>📨 Verifique sua caixa de entrada!</h3>";
            echo "<p>O email deve chegar em alguns minutos. Verifique também a pasta de spam.</p>";
            
        } else {
            echo "<p style='color: red;'>❌ <strong>ERRO AO ENVIAR EMAIL</strong></p>";
            echo "<p>📧 Erro: " . $resultado['message'] . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Erro na conexão SMTP: " . $conexaoTeste['message'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>ERRO GERAL:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🔧 Próximos Passos:</h3>";
echo "<ol>";
echo "<li><strong>Se o teste passou:</strong> O sistema está funcionando! Emails serão enviados automaticamente quando novos descartes forem registrados.</li>";
echo "<li><strong>Se houve erro:</strong> Verifique as configurações SMTP no arquivo .env</li>";
echo "<li><strong>Para testar em produção:</strong> Registre um novo descarte no sistema e verifique se o email chega</li>";
echo "</ol>";

echo "<h3>📝 Configurações do .env:</h3>";
echo "<pre>";
echo "MAIL_MAILER=smtp\n";
echo "MAIL_HOST=smtp.hostinger.com\n";
echo "MAIL_PORT=465\n";
echo "MAIL_USERNAME=suporte@djbr.sgqoti.com.br\n";
echo "MAIL_PASSWORD=Pandora@1989\n";
echo "MAIL_ENCRYPTION=ssl\n";
echo "MAIL_FROM_ADDRESS=suporte@djbr.sgqoti.com.br\n";
echo "MAIL_FROM_NAME=\"SGQ OTI DJ\"\n";
echo "</pre>";

echo "<p><a href='https://djbr.sgqoti.com.br/controle-de-descartes' style='background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;'>🔗 Ir para Controle de Descartes</a></p>";
?>
