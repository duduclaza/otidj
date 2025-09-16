<?php
$title = 'Meu Perfil - SGQ OTI DJ';
$viewFile = __FILE__;
?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">Meu Perfil</h1>
  </div>

  <!-- Profile Card -->
  <div class="bg-white border rounded-lg p-6">
    <div class="flex items-center space-x-6 mb-6">
      <div class="relative">
        <div id="profilePhotoContainer" class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
          <img id="profilePhoto" src="" alt="Foto de Perfil" class="w-full h-full object-cover hidden">
          <svg id="defaultAvatar" class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
          </svg>
        </div>
        <button onclick="document.getElementById('photoInput').click()" class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full p-1 hover:bg-blue-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </button>
        <input type="file" id="photoInput" accept="image/*" class="hidden" onchange="uploadPhoto()">
      </div>
      <div>
        <h2 class="text-xl font-semibold text-gray-900" id="userName">Carregando...</h2>
        <p class="text-gray-600" id="userEmail">Carregando...</p>
        <p class="text-sm text-gray-500" id="userInfo">Carregando...</p>
      </div>
    </div>

    <!-- Change Password Form -->
    <div class="border-t pt-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Alterar Senha</h3>
      <form id="changePasswordForm" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Senha Atual *</label>
            <input type="password" name="current_password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha *</label>
            <input type="password" name="new_password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha *</label>
            <input type="password" name="confirm_password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
        </div>
        <div class="flex justify-end">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
            Alterar Senha
          </button>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
let currentUser = null;

// Load user profile on page load
document.addEventListener('DOMContentLoaded', function() {
  loadUserProfile();
});

function loadUserProfile() {
  fetch('/api/profile')
    .then(response => response.json())
    .then(user => {
      if (user.error) {
        alert('Erro ao carregar perfil: ' + user.error);
        return;
      }
      
      currentUser = user;
      document.getElementById('userName').textContent = user.name;
      document.getElementById('userEmail').textContent = user.email;
      document.getElementById('userInfo').textContent = `${user.setor || 'N/A'} - ${user.filial || 'N/A'}`;
      
      // Load profile photo if exists
      if (user.profile_photo) {
        const img = document.getElementById('profilePhoto');
        img.src = `data:${user.profile_photo_type};base64,${user.profile_photo}`;
        img.classList.remove('hidden');
        document.getElementById('defaultAvatar').classList.add('hidden');
      }
    })
    .catch(error => {
      console.error('Erro ao carregar perfil:', error);
      alert('Erro ao carregar perfil do usuário');
    });
}

function uploadPhoto() {
  const fileInput = document.getElementById('photoInput');
  const file = fileInput.files[0];
  
  if (!file) return;
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    alert('Por favor, selecione apenas arquivos de imagem.');
    return;
  }
  
  // Validate file size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    alert('A imagem deve ter no máximo 5MB.');
    return;
  }
  
  const formData = new FormData();
  formData.append('profile_photo', file);
  
  fetch('/api/profile/photo', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      // Update photo display
      const img = document.getElementById('profilePhoto');
      const reader = new FileReader();
      reader.onload = function(e) {
        img.src = e.target.result;
        img.classList.remove('hidden');
        document.getElementById('defaultAvatar').classList.add('hidden');
      };
      reader.readAsDataURL(file);
      
      alert('Foto de perfil atualizada com sucesso!');
    } else {
      alert('Erro ao atualizar foto: ' + result.message);
    }
  })
  .catch(error => {
    console.error('Erro ao fazer upload da foto:', error);
    alert('Erro ao fazer upload da foto');
  });
}

// Handle password change form
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const newPassword = formData.get('new_password');
  const confirmPassword = formData.get('confirm_password');
  
  if (newPassword !== confirmPassword) {
    alert('A nova senha e a confirmação não coincidem.');
    return;
  }
  
  fetch('/api/profile/password', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert('Senha alterada com sucesso!');
      this.reset();
    } else {
      alert('Erro ao alterar senha: ' + result.message);
    }
  })
  .catch(error => {
    console.error('Erro ao alterar senha:', error);
    alert('Erro ao alterar senha');
  });
});
</script>
