<div class="glass-effect rounded-2xl shadow-2xl p-8">
  <!-- Logo/Header -->
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-full mb-4">
      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
      </svg>
    </div>
    <h1 class="text-3xl font-bold text-white mb-2">Solicitar Acesso</h1>
    <p class="text-white text-opacity-80">Sistema de Gestão da Qualidade</p>
  </div>

  <!-- Registration Form -->
  <form id="registerForm" class="space-y-6">
    <div>
      <label class="block text-white text-sm font-medium mb-2">Nome Completo *</label>
      <input type="text" name="name" required 
             class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent"
             placeholder="Seu nome completo">
    </div>

    <div>
      <label class="block text-white text-sm font-medium mb-2">Email *</label>
      <input type="email" name="email" required 
             class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent"
             placeholder="seu@email.com">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-white text-sm font-medium mb-2">Setor</label>
        <input type="text" name="setor" 
               class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent"
               placeholder="Ex: TI, Qualidade">
      </div>

      <div>
        <label class="block text-white text-sm font-medium mb-2">Filial</label>
        <input type="text" name="filial" 
               class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent"
               placeholder="Ex: Matriz, Filial SP">
      </div>
    </div>

    <div>
      <label class="block text-white text-sm font-medium mb-2">Mensagem (Opcional)</label>
      <textarea name="message" rows="3"
                class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent resize-none"
                placeholder="Conte-nos um pouco sobre sua função e por que precisa de acesso ao sistema..."></textarea>
    </div>

    <button type="submit" 
            class="w-full bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 backdrop-blur-sm border border-white border-opacity-30">
      Enviar Solicitação
    </button>
  </form>

  <!-- Login Link -->
  <div class="mt-6 text-center">
    <p class="text-white text-opacity-80 text-sm">
      Já tem uma conta? 
      <a href="/login" class="text-white font-semibold hover:text-opacity-80 transition-colors">
        Fazer Login
      </a>
    </p>
  </div>

  <!-- Loading Overlay -->
  <div id="registerLoading" class="hidden absolute inset-0 bg-black bg-opacity-50 rounded-2xl flex items-center justify-center">
    <div class="text-white text-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mx-auto mb-2"></div>
      <div class="text-sm">Enviando solicitação...</div>
    </div>
  </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const loading = document.getElementById('registerLoading');
  const formData = new FormData(this);
  
  loading.classList.remove('hidden');
  
  fetch('/auth/register', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    loading.classList.add('hidden');
    
    if (result.success) {
      alert(result.message);
      window.location.href = '/login';
    } else {
      alert(result.message || 'Erro ao enviar solicitação');
    }
  })
  .catch(error => {
    loading.classList.add('hidden');
    alert('Erro de conexão. Tente novamente.');
  });
});
</script>
