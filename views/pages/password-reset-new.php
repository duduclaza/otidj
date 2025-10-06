<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4">
    <div class="max-w-md w-full">
        <!-- Logo e Título -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-600 rounded-full mb-4">
                <span class="text-3xl">🔑</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Nova Senha</h1>
            <p class="text-gray-600">Defina uma nova senha para sua conta</p>
        </div>

        <!-- Formulário -->
        <div class="bg-white rounded-xl shadow-xl p-8">
            <form id="formResetPassword" class="space-y-6">
                <input type="hidden" id="email" name="email">
                <input type="hidden" id="token" name="token">

                <!-- Email exibido (readonly) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        📧 Email
                    </label>
                    <div class="px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                        <span id="displayEmail"></span>
                    </div>
                </div>

                <!-- Nova Senha -->
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                        🔒 Nova Senha
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password"
                            required
                            minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Mínimo 6 caracteres"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('new_password')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                        >
                            👁️
                        </button>
                    </div>
                </div>

                <!-- Confirmar Senha -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                        🔒 Confirmar Nova Senha
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password"
                            required
                            minlength="6"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Digite a senha novamente"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword('confirm_password')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
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
                        class="w-full bg-green-600 text-white py-3 rounded-lg font-medium hover:bg-green-700 transition-colors flex items-center justify-center gap-2"
                    >
                        <span>✅ Redefinir Senha</span>
                    </button>

                    <a 
                        href="/login" 
                        class="w-full block text-center py-3 text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        ← Voltar para o Login
                    </a>
                </div>
            </form>
        </div>

        <!-- Dicas de Senha -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm font-medium text-blue-900 mb-2">💡 Dicas para uma senha forte:</p>
            <ul class="text-xs text-blue-800 space-y-1">
                <li>• Mínimo de 6 caracteres (recomendado 8+)</li>
                <li>• Use letras maiúsculas e minúsculas</li>
                <li>• Inclua números e símbolos</li>
                <li>• Evite informações pessoais óbvias</li>
            </ul>
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
