<div class="glass-effect rounded-2xl shadow-2xl p-8">
  <!-- Logo/Header -->
  <div class="text-center mb-6">
    <div class="inline-flex items-center justify-center m-0">
      <img src="/public/assets/logo.png" alt="DJLogo" class="w-22 h-10 object-contain">
    </div>
    <h1 class="text-4xl font-bold text-white mb-3">OTI</h1>
    <p class="text-blue-100 text-lg">Organização Tecnológica Integrada</p>
  </div>

  <!-- Login Form -->
  <form id="loginForm" class="space-y-6">
    <div>
      <label class="block text-white text-sm font-medium mb-2">Email</label>
      <input type="email" name="email" required 
             class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent"
             placeholder="seu@email.com">
    </div>

    <div>
      <label class="block text-white text-sm font-medium mb-2">Senha</label>
      <div class="relative">
        <input type="password" name="password" id="loginPassword" required 
               class="w-full px-4 py-3 pr-12 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent"
               placeholder="••••••••">
        <button type="button" onclick="toggleLoginPassword()" 
                class="absolute right-3 top-1/2 -translate-y-1/2 text-white hover:text-blue-200 transition-colors">
          <svg id="loginEyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
        </button>
      </div>
    </div>

    <button type="submit" 
            class="w-full btn-primary text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200">
      Entrar
    </button>
  </form>

  <!-- Forgot Password Link -->
  <div class="mt-4 text-center">
    <a href="/password-reset/request" class="text-blue-200 text-sm hover:text-white transition-colors">
      🔐 Esqueci minha senha
    </a>
  </div>

  <!-- Register Link -->
  <div class="mt-4 text-center">
    <p class="text-blue-100 text-sm">
      Não tem uma conta? 
      <a href="/request-access" class="text-blue-300 font-semibold hover:text-blue-200 transition-colors">
        Solicitar Acesso
      </a>
    </p>
  </div>

  <!-- Loading Overlay -->
  <div id="loginLoading" class="hidden absolute inset-0 bg-black bg-opacity-50 rounded-2xl flex items-center justify-center">
    <div class="text-white text-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mx-auto mb-2"></div>
      <div class="text-sm">Entrando...</div>
    </div>
  </div>
</div>

<script>
function toggleLoginPassword() {
  const passwordInput = document.getElementById('loginPassword');
  const eyeIcon = document.getElementById('loginEyeIcon');
  
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    eyeIcon.innerHTML = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
    `;
  } else {
    passwordInput.type = 'password';
    eyeIcon.innerHTML = `
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
    `;
  }
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const loading = document.getElementById('loginLoading');
  const formData = new FormData(this);
  
  loading.classList.remove('hidden');
  
  fetch('/auth/login', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    loading.classList.add('hidden');
    
    if (result.success) {
      // Usar a URL de redirecionamento retornada pelo servidor
      window.location.href = result.redirect || '/';
    } else {
      alert(result.message || 'Erro ao fazer login');
    }
  })
  .catch(error => {
    loading.classList.add('hidden');
    alert('Erro de conexão. Tente novamente.');
  });
});
</script>
