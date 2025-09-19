<div class="glass-effect rounded-2xl shadow-2xl p-8">
  <!-- Logo/Header -->
  <div class="text-center mb-6">
    <div class="inline-flex items-center justify-center m-0">
      <img src="./public/img/logo.png" alt="DJ Logo" class="w-30 h-30 object-contain">
    </div>
    <h1 class="text-3xl font-bold text-white mb-3">🎉 Bem-vindo!</h1>
    <p class="text-blue-100 text-lg mb-4">Este é seu primeiro acesso ao SGQ OTI</p>
    <div class="bg-blue-500 bg-opacity-20 rounded-lg p-4 mb-6">
      <p class="text-blue-100 text-sm">
        Por segurança, recomendamos que você altere sua senha temporária para uma mais segura.
      </p>
    </div>
  </div>

  <!-- Change Password Form -->
  <form id="firstAccessForm" class="space-y-6">
    <div>
      <label class="block text-white text-sm font-medium mb-2">Nova Senha</label>
      <input type="password" name="new_password" required minlength="6"
             class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent"
             placeholder="Digite sua nova senha (mín. 6 caracteres)">
    </div>

    <div>
      <label class="block text-white text-sm font-medium mb-2">Confirmar Nova Senha</label>
      <input type="password" name="confirm_password" required minlength="6"
             class="w-full px-4 py-3 bg-white bg-opacity-20 border border-white border-opacity-30 rounded-lg text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 focus:border-transparent"
             placeholder="Confirme sua nova senha">
    </div>

    <div class="bg-yellow-500 bg-opacity-20 rounded-lg p-4">
      <div class="flex items-start space-x-3">
        <svg class="w-5 h-5 text-yellow-300 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <div>
          <h3 class="text-yellow-300 font-medium text-sm">Dicas para uma senha segura:</h3>
          <ul class="text-yellow-100 text-xs mt-2 space-y-1">
            <li>• Use pelo menos 6 caracteres</li>
            <li>• Combine letras, números e símbolos</li>
            <li>• Evite informações pessoais óbvias</li>
          </ul>
        </div>
      </div>
    </div>

    <button type="submit" 
            class="w-full btn-primary text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200">
      Alterar Senha e Continuar
    </button>
  </form>

  <!-- Skip Option -->
  <div class="mt-6 text-center">
    <button onclick="skipPasswordChange()" class="text-blue-300 text-sm hover:text-blue-200 transition-colors">
      Pular por agora (não recomendado)
    </button>
  </div>

  <!-- Loading Overlay -->
  <div id="changePasswordLoading" class="hidden absolute inset-0 bg-black bg-opacity-50 rounded-2xl flex items-center justify-center">
    <div class="text-white text-center">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mx-auto mb-2"></div>
      <div class="text-sm">Alterando senha...</div>
    </div>
  </div>
</div>

<script>
document.getElementById('firstAccessForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const loading = document.getElementById('changePasswordLoading');
  const formData = new FormData(this);
  
  // Validar senhas
  const newPassword = formData.get('new_password');
  const confirmPassword = formData.get('confirm_password');
  
  if (newPassword !== confirmPassword) {
    alert('As senhas não coincidem. Tente novamente.');
    return;
  }
  
  if (newPassword.length < 6) {
    alert('A senha deve ter pelo menos 6 caracteres.');
    return;
  }
  
  loading.classList.remove('hidden');
  
  fetch('/auth/change-first-password', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    loading.classList.add('hidden');
    
    if (result.success) {
      alert('Senha alterada com sucesso! Bem-vindo ao SGQ OTI DJ!');
      window.location.href = result.redirect || '/';
    } else {
      alert(result.message || 'Erro ao alterar senha');
    }
  })
  .catch(error => {
    loading.classList.add('hidden');
    alert('Erro de conexão. Tente novamente.');
  });
});

function skipPasswordChange() {
  if (confirm('Tem certeza que deseja pular a alteração da senha? Recomendamos alterar para maior segurança.')) {
    fetch('/auth/skip-first-password', {
      method: 'POST'
    })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        window.location.href = result.redirect || '/';
      }
    });
  }
}
</script>
