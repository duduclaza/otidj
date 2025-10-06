<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Recuperar Senha - SGQ OTI DJ</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .auth-bg {
      background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 50%, #1e293b 100%);
      position: relative;
    }
    .auth-bg::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="%23ffffff" stroke-width="0.5" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>') repeat;
    }
    .glass-effect {
      backdrop-filter: blur(15px);
      background: rgba(30, 64, 175, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
  </style>
</head>
<body class="auth-bg min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md">
<div class="glass-effect rounded-2xl shadow-2xl p-8">
    <div class="max-w-md w-full">
        <!-- Logo e Título -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-full mb-4">
                <span class="text-3xl">🔐</span>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Recuperar Senha</h1>
            <p class="text-blue-100">Digite seu email para receber o código de recuperação</p>
        </div>

        <!-- Formulário -->
        <form id="formRequestReset" class="space-y-6">
            <!-- Email -->
            <div>
                <label for="email" class="block text-white text-sm font-medium mb-2">
                    📧 Email cadastrado
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email"
                    required
                    class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                    placeholder="seu.email@exemplo.com"
                >
            </div>

            <!-- Mensagem -->
            <div id="message" class="hidden"></div>

            <!-- Botões -->
            <div class="space-y-3">
                <button 
                    type="submit" 
                    id="btnSubmit"
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 hover:from-blue-600 hover:to-blue-800"
                >
                    📨 Enviar Código
                </button>

                <a 
                    href="/login" 
                    class="w-full block text-center py-3 text-blue-100 hover:text-white transition-colors"
                >
                    ← Voltar para o Login
                </a>
            </div>
        </form>

        <!-- Informação -->
        <div class="mt-6 text-center text-sm text-blue-100">
            <p>💡 Você receberá um código de 6 dígitos no seu email</p>
            <p class="mt-1">⏰ O código expira em 2 minutos</p>
        </div>
</div>
</div>
</div>

<script>
document.getElementById('formRequestReset').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btnSubmit = document.getElementById('btnSubmit');
    const messageDiv = document.getElementById('message');
    const email = document.getElementById('email').value;
    
    // Desabilitar botão
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="animate-spin">⏳</span> Enviando...';
    messageDiv.className = 'hidden';
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('/password-reset/request', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // DEBUG - Mostrar token no console (REMOVER EM PRODUÇÃO)
            if (result.debug_token) {
                console.log('🔐 CÓDIGO DE TESTE:', result.debug_token);
            }
            
            messageDiv.className = 'bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg';
            messageDiv.innerHTML = `
                <p class="font-medium">✅ ${result.message}</p>
                ${result.debug_token ? '<p class="text-sm mt-2 font-bold">🔐 Código de teste: ' + result.debug_token + '</p>' : ''}
                <p class="text-sm mt-1">Redirecionando para validação do código...</p>
            `;
            
            // Redirecionar após 3 segundos (mais tempo para ver o código)
            setTimeout(() => {
                window.location.href = '/password-reset/verify?email=' + encodeURIComponent(email);
            }, 3000);
            
        } else {
            messageDiv.className = 'bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg';
            messageDiv.innerHTML = `<p>❌ ${result.message}</p>`;
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<span>📨 Enviar Código</span>';
        }
        
    } catch (error) {
        console.error('Erro:', error);
        messageDiv.className = 'bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg';
        messageDiv.innerHTML = '<p>❌ Erro ao processar solicitação. Tente novamente.</p>';
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<span>📨 Enviar Código</span>';
    }
});
</script>
</body>
</html>
