<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4">
    <div class="max-w-md w-full">
        <!-- Logo e Título -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-full mb-4">
                <span class="text-3xl">🔐</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Recuperar Senha</h1>
            <p class="text-gray-600">Digite seu email para receber o código de recuperação</p>
        </div>

        <!-- Formulário -->
        <div class="bg-white rounded-xl shadow-xl p-8">
            <form id="formRequestReset" class="space-y-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        📧 Email cadastrado
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
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
                        class="w-full bg-indigo-600 text-white py-3 rounded-lg font-medium hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2"
                    >
                        <span>📨 Enviar Código</span>
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
            <p>💡 Você receberá um código de 6 dígitos no seu email</p>
            <p class="mt-1">⏰ O código expira em 30 minutos</p>
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
            messageDiv.className = 'bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg';
            messageDiv.innerHTML = `
                <p class="font-medium">✅ ${result.message}</p>
                <p class="text-sm mt-1">Redirecionando para validação do código...</p>
            `;
            
            // Redirecionar após 2 segundos
            setTimeout(() => {
                window.location.href = '/password-reset/verify?email=' + encodeURIComponent(email);
            }, 2000);
            
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
