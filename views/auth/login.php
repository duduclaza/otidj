<div class="glass-effect rounded-2xl shadow-2xl p-8">
  <!-- Logo/Header -->
  <div class="text-center mb-6">
    <div class="inline-flex items-center justify-center m-0">
      <img src="/public/assets/logo.png" alt="DJLogo" class="w-22 h-10 object-contain">
    </div>
    <h1 class="text-4xl font-bold text-white mb-3">SGQ OTI</h1>
    <p class="text-blue-100 text-lg">Sistema de Gestão da Qualidade</p>
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
      <input type="password" name="password" required 
             class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent"
             placeholder="••••••••">
    </div>

    <button type="submit" 
            class="w-full btn-primary text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200">
      Entrar
    </button>
  </form>

  <!-- Register Link -->
  <div class="mt-6 text-center">
    <p class="text-blue-100 text-sm">
      Não tem uma conta? 
      <a href="/request-access" class="text-blue-300 font-semibold hover:text-blue-200 transition-colors">
        Solicitar Acesso
      </a>
    </p>
  </div>

  <!-- Master Access Key (Hidden) -->
  <div class="mt-2 text-center">
    <a href="/master/login" class="opacity-0 hover:opacity-100 transition-opacity duration-300 text-2xl inline-block">
      🔑
    </a>
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
