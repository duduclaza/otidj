<!-- Card Branco -->
<div class="bg-white rounded-2xl shadow-2xl p-8 relative">
  <!-- Logo DJ no topo -->
  <div class="text-center mb-8">
    <img src="/assets/logodj.png" alt="DJ Logo" class="mx-auto h-12 object-contain mb-4">
  </div>

  <!-- Login Form -->
  <form id="loginForm" class="space-y-6">
    <div>
      <input type="email" name="email" required 
             class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
             placeholder="Seu Email">
    </div>

    <div>
      <div class="relative">
        <input type="password" name="password" id="loginPassword" required 
               class="w-full px-4 py-3 pr-12 border-2 border-blue-300 rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
               placeholder="Sua Senha">
        <button type="button" onclick="toggleLoginPassword()" 
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors">
          <svg id="loginEyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
        </button>
      </div>
    </div>

    <button type="submit" 
            class="w-full btn-primary text-white font-semibold py-3 px-6 rounded-lg">
      Entrar
    </button>
  </form>

  <!-- Links -->
  <div class="mt-4 text-center space-y-2">
    <div>
      <a href="/password-reset/request" class="text-sm text-gray-600 hover:text-blue-600 transition-colors inline-flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
        Esqueci minha senha?
      </a>
    </div>
    <div>
      <a href="/request-access" class="text-sm bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors inline-block">
        Solicitar Acesso
      </a>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div id="loginLoading" class="hidden absolute inset-0 bg-white bg-opacity-90 rounded-2xl flex items-center justify-center">
    <div class="text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-blue-600 mx-auto mb-4"></div>
      <div class="text-gray-700 font-semibold">Entrando...</div>
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
