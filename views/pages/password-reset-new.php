<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nova Senha - SGQ OTI DJ</title>
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
        <!-- Logo e Título -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-full mb-4">
                <span class="text-3xl">🔑</span>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Nova Senha</h1>
            <p class="text-blue-100">Defina uma nova senha para sua conta</p>
        </div>

        <!-- Formulário -->
            <form id="formResetPassword" class="space-y-6">
                <input type="hidden" id="email" name="email">
                <input type="hidden" id="token" name="token">

                <!-- Email exibido (readonly) -->
                <div>
                    <label class="block text-white text-sm font-medium mb-2">
                        📧 Email
                    </label>
                    <div class="px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white">
                        <span id="displayEmail"></span>
                    </div>
                </div>

                <!-- Nova Senha -->
                <div>
                    <label for="new_password" class="block text-white text-sm font-medium mb-2">
                        🔒 Nova Senha
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password"
                            required
                            minlength="6"
                            class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                            placeholder="Mínimo 6 caracteres"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('new_password')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white opacity-70 hover:opacity-100"
                        >
                            👁️
                        </button>
                    </div>
                </div>

                <!-- Confirmar Senha -->
                <div>
                    <label for="confirm_password" class="block text-white text-sm font-medium mb-2">
                        🔒 Confirmar Nova Senha
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password"
                            required
                            minlength="6"
                            class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                            placeholder="Digite a senha novamente"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('confirm_password')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white opacity-70 hover:opacity-100"
                        >
                            👁️
                        </button>
                    </div>
                </div>

                <!-- Mensagem -->
                <div id="message" class="hidden"></div>

                <!-- Botões -->
                <div class="space-y-3">
                    <button 
                        type="submit" 
                        id="btnSubmit"
                        class="w-full bg-gradient-to-r from-green-500 to-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 hover:from-green-600 hover:to-green-800"
                    >
                        ✅ Redefinir Senha
                    </button>

                    <a 
                        href="/login" 
                        class="w-full block text-center py-3 text-blue-100 hover:text-white transition-colors"
                    >
                        ← Voltar para o Login
                    </a>
                </div>
            </form>

        <!-- Dicas de Senha -->
        <div class="mt-6 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg p-4">
            <p class="text-sm font-medium text-white mb-2">💡 Dicas para uma senha forte:</p>
            <ul class="text-xs text-blue-100 space-y-1">
                <li>• Mínimo de 6 caracteres (recomendado 8+)</li>
                <li>• Use letras maiúsculas e minúsculas</li>
                <li>• Inclua números e símbolos</li>
                <li>• Evite informações pessoais óbvias</li>
            </ul>
        </div>
</div>
</div>
</div>

<script>
// Pegar email e token da URL
const urlParams = new URLSearchParams(window.location.search);
const email = urlParams.get('email');
const token = urlParams.get('token');

if (!email || !token) {
    window.location.href = '/password-reset/request';
} else {
    document.getElementById('email').value = email;
    document.getElementById('token').value = token;
    document.getElementById('displayEmail').textContent = email;
}

// Função para mostrar/ocultar senha
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Auto-focus no campo de senha
document.getElementById('new_password').focus();

document.getElementById('formResetPassword').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btnSubmit = document.getElementById('btnSubmit');
    const messageDiv = document.getElementById('message');
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Validação local
    if (newPassword.length < 6) {
        messageDiv.className = 'bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg';
        messageDiv.innerHTML = '<p>⚠️ A senha deve ter no mínimo 6 caracteres</p>';
        return;
    }
    
    if (newPassword !== confirmPassword) {
        messageDiv.className = 'bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg';
        messageDiv.innerHTML = '<p>⚠️ As senhas não coincidem</p>';
        return;
    }
    
    // Desabilitar botão
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="animate-spin">⏳</span> Redefinindo...';
    messageDiv.className = 'hidden';
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('/password-reset/reset', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            messageDiv.className = 'bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg';
            messageDiv.innerHTML = `
                <p class="font-medium">✅ ${result.message}</p>
                <p class="text-sm mt-1">Redirecionando para login...</p>
            `;
            
            // Redirecionar após 3 segundos
            setTimeout(() => {
                window.location.href = '/login?reset=success';
            }, 3000);
            
        } else {
            messageDiv.className = 'bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg';
            messageDiv.innerHTML = `<p>❌ ${result.message}</p>`;
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<span>✅ Redefinir Senha</span>';
        }
        
    } catch (error) {
        console.error('Erro:', error);
        messageDiv.className = 'bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg';
        messageDiv.innerHTML = '<p>❌ Erro ao redefinir senha. Tente novamente.</p>';
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<span>✅ Redefinir Senha</span>';
    }
});

// Validação em tempo real
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && newPassword !== confirmPassword) {
        this.classList.add('border-red-500');
    } else {
        this.classList.remove('border-red-500');
    }
});
</script>
</body>
</html>
