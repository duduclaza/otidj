<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4">
    <div class="max-w-md w-full">
        <!-- Logo e Título -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-full mb-4">
                <span class="text-3xl">🔢</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Verificar Código</h1>
            <p class="text-gray-600">Digite o código de 6 dígitos enviado para seu email</p>
        </div>

        <!-- Formulário -->
        <div class="bg-white rounded-xl shadow-xl p-8">
            <form id="formVerifyCode" class="space-y-6">
                <!-- Email (readonly) -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        📧 Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        readonly
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50"
                    >
                </div>

                <!-- Código -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                        🔢 Código de Verificação
                    </label>
                    <input 
                        type="text" 
                        id="code" 
                        name="code"
                        required
                        maxlength="6"
                        pattern="[0-9]{6}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-center text-2xl font-bold letter-spacing-wider"
                        placeholder="000000"
                        autocomplete="off"
                    >
                    <p class="text-xs text-gray-500 mt-1">Digite os 6 dígitos recebidos por email</p>
                </div>

                <!-- Mensagem -->
                <div id="message" class="hidden"></div>

                <!-- Botões -->
                <div class="space-y-3">
                    <button 
                        type="submit" 
                        id="btnSubmit"
                        class="w-full bg-indigo-600 text-white py-3 rounded-lg font-medium hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2"
                    >
                        <span>✅ Verificar Código</span>
                    </button>

                    <button 
                        type="button" 
                        onclick="window.location.href='/password-reset/request'"
                        class="w-full block text-center py-3 text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        📨 Reenviar Código
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

        <!-- Informação -->
        <div class="mt-6 text-center text-sm text-gray-600">
            <p>⏰ Não recebeu o código? Verifique sua caixa de spam</p>
            <p class="mt-1">🔐 O código expira em 30 minutos</p>
        </div>
    </div>
</div>

<script>
// Pegar email da URL
const urlParams = new URLSearchParams(window.location.search);
const email = urlParams.get('email');
if (email) {
    document.getElementById('email').value = email;
} else {
    window.location.href = '/password-reset/request';
}

// Auto-focus no campo de código
document.getElementById('code').focus();

// Permitir apenas números
document.getElementById('code').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

document.getElementById('formVerifyCode').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btnSubmit = document.getElementById('btnSubmit');
    const messageDiv = document.getElementById('message');
    const email = document.getElementById('email').value;
    const code = document.getElementById('code').value;
    
    if (code.length !== 6) {
        messageDiv.className = 'bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg';
        messageDiv.innerHTML = '<p>⚠️ O código deve ter 6 dígitos</p>';
        return;
    }
    
    // Desabilitar botão
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="animate-spin">⏳</span> Verificando...';
    messageDiv.className = 'hidden';
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('/password-reset/verify-code', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            messageDiv.className = 'bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg';
            messageDiv.innerHTML = `
                <p class="font-medium">✅ ${result.message}</p>
                <p class="text-sm mt-1">Redirecionando para redefinição de senha...</p>
            `;
            
            // Redirecionar após 2 segundos
            setTimeout(() => {
                window.location.href = '/password-reset/new?email=' + encodeURIComponent(email) + '&token=' + code;
            }, 2000);
            
        } else {
            messageDiv.className = 'bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg';
            messageDiv.innerHTML = `<p>❌ ${result.message}</p>`;
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<span>✅ Verificar Código</span>';
            document.getElementById('code').value = '';
            document.getElementById('code').focus();
        }
        
    } catch (error) {
        console.error('Erro:', error);
        messageDiv.className = 'bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg';
        messageDiv.innerHTML = '<p>❌ Erro ao verificar código. Tente novamente.</p>';
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<span>✅ Verificar Código</span>';
    }
});
</script>

<style>
.letter-spacing-wider {
    letter-spacing: 0.5em;
}
</style>
