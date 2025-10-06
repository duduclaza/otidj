<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verificar Código - SGQ OTI DJ</title>
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
    .letter-spacing-wider {
      letter-spacing: 0.5em;
    }
  </style>
</head>
<body class="auth-bg min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md">
<div class="glass-effect rounded-2xl shadow-2xl p-8">
        <!-- Logo e Título -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-full mb-4">
                <span class="text-3xl">🔢</span>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Verificar Código</h1>
            <p class="text-blue-100">Digite o código de 6 dígitos enviado para seu email</p>
        </div>

        <!-- Formulário -->
            <form id="formVerifyCode" class="space-y-6">
                <!-- Email (readonly) -->
                <div>
                    <label for="email" class="block text-white text-sm font-medium mb-2">
                        📧 Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        readonly
                        class="w-full px-4 py-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-lg text-white"
                    >
                </div>

                <!-- Timer de Expiração -->
                <div id="timerDisplay" class="bg-yellow-500 bg-opacity-20 border border-yellow-400 rounded-lg px-4 py-3 text-center">
                    <p class="text-white text-sm mb-1">⏰ Tempo restante:</p>
                    <p id="countdown" class="text-yellow-300 text-3xl font-bold">2:00</p>
                </div>

                <!-- Código -->
                <div>
                    <label for="code" class="block text-white text-sm font-medium mb-2">
                        🔢 Código de Verificação
                    </label>
                    <input 
                        type="text" 
                        id="code" 
                        name="code"
                        required
                        maxlength="6"
                        pattern="[0-9]{6}"
                        class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 text-center text-2xl font-bold letter-spacing-wider"
                        placeholder="000000"
                        autocomplete="off"
                    >
                    <p class="text-xs text-blue-100 mt-1">Digite os 6 dígitos recebidos por email</p>
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
                        ✅ Verificar Código
                    </button>

                    <button 
                        type="button" 
                        onclick="window.location.href='/password-reset/request'"
                        class="w-full block text-center py-3 text-blue-100 hover:text-white transition-colors"
                    >
                        📨 Reenviar Código
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
            <p>⏰ Não recebeu o código? Verifique sua caixa de spam</p>
            <p class="mt-1">🔐 O código expira automaticamente</p>
        </div>
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

// Timer de contagem regressiva de 2 minutos
let timeLeft = 120; // 2 minutos em segundos
const countdownElement = document.getElementById('countdown');
const timerDisplay = document.getElementById('timerDisplay');
const btnSubmit = document.getElementById('btnSubmit');

function updateTimer() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    // Mudar cor baseado no tempo restante
    if (timeLeft <= 30) {
        timerDisplay.className = 'bg-red-500 bg-opacity-20 border border-red-400 rounded-lg px-4 py-3 text-center';
        countdownElement.className = 'text-red-300 text-3xl font-bold animate-pulse';
    } else if (timeLeft <= 60) {
        timerDisplay.className = 'bg-orange-500 bg-opacity-20 border border-orange-400 rounded-lg px-4 py-3 text-center';
        countdownElement.className = 'text-orange-300 text-3xl font-bold';
    }
    
    if (timeLeft === 0) {
        clearInterval(timerInterval);
        countdownElement.textContent = 'EXPIRADO';
        countdownElement.className = 'text-red-500 text-3xl font-bold';
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '⏰ Código Expirado';
        document.getElementById('code').disabled = true;
        
        // Mostrar mensagem de expiração
        const messageDiv = document.getElementById('message');
        messageDiv.className = 'bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg';
        messageDiv.innerHTML = '<p>⏰ O código expirou! Solicite um novo código.</p>';
    }
    
    timeLeft--;
}

// Iniciar o timer
updateTimer(); // Atualizar imediatamente
const timerInterval = setInterval(updateTimer, 1000);

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
            // Parar o timer
            clearInterval(timerInterval);
            countdownElement.textContent = '✓ VÁLIDO';
            countdownElement.className = 'text-green-300 text-3xl font-bold';
            timerDisplay.className = 'bg-green-500 bg-opacity-20 border border-green-400 rounded-lg px-4 py-3 text-center';
            
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
</body>
</html>
