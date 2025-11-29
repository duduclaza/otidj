<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= $title ?? 'Checklist Virtual' ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
    }
    
    .glass-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .preview-img {
      max-width: 100%;
      max-height: 150px;
      object-fit: cover;
      border-radius: 8px;
    }
    
    input[type="file"] {
      display: none;
    }
    
    .upload-label {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
      border: 2px dashed #d1d5db;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s;
      min-height: 120px;
    }
    
    .upload-label:hover {
      border-color: #6366f1;
      background: #f5f3ff;
    }
    
    .upload-label.has-image {
      border-style: solid;
      border-color: #22c55e;
      background: #f0fdf4;
    }
    
    .datetime-display {
      background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    }
  </style>
</head>
<body class="p-4">
  <div class="max-w-lg mx-auto">
    
    <!-- Header -->
    <div class="text-center mb-6">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-lg mb-4">
        <span class="text-3xl">📋</span>
      </div>
      <h1 class="text-2xl font-bold text-white mb-1">Checklist Virtual</h1>
      <p class="text-white/80 text-sm">Registre a manutenção do equipamento</p>
    </div>
    
    <!-- Data e Hora -->
    <div class="datetime-display text-white rounded-xl p-4 mb-4 shadow-lg">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs text-gray-400 uppercase tracking-wide">Data e Hora (Brasília)</div>
          <div class="text-2xl font-bold" id="dataHora">--/--/---- --:--:--</div>
        </div>
        <div class="text-4xl">🕐</div>
      </div>
    </div>
    
    <!-- Formulário -->
    <form id="checklistForm" class="glass-card rounded-2xl shadow-xl p-6 space-y-5">
      
      <!-- Número de Série -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          🔢 Número de Série *
        </label>
        <input 
          type="text" 
          name="numero_serie" 
          id="numeroSerie"
          required
          placeholder="Digite o número de série"
          class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition uppercase"
          autocomplete="off"
        >
      </div>
      
      <!-- Manutenção Realizada -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          🔧 Manutenção Realizada *
        </label>
        <textarea 
          name="manutencao_realizada" 
          id="manutencaoRealizada"
          required
          rows="3"
          placeholder="Descreva o que foi feito no equipamento..."
          class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition resize-none"
        ></textarea>
      </div>
      
      <!-- Colaborador -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          👤 Colaborador *
        </label>
        <input 
          type="text" 
          name="colaborador" 
          id="colaborador"
          required
          placeholder="Seu nome completo"
          class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
          autocomplete="name"
        >
      </div>
      
      <!-- Fotos -->
      <div class="grid grid-cols-2 gap-4">
        <!-- Foto do Contador -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            📸 Foto do Contador
          </label>
          <label for="fotoContador" class="upload-label" id="labelContador">
            <span class="text-3xl mb-2">📷</span>
            <span class="text-sm text-gray-500 text-center">Toque para tirar foto</span>
          </label>
          <input 
            type="file" 
            name="foto_contador" 
            id="fotoContador"
            accept="image/*"
            capture="environment"
            onchange="previewImage(this, 'labelContador', 'previewContador')"
          >
          <img id="previewContador" class="preview-img mt-2 hidden w-full">
        </div>
        
        <!-- Foto do Equipamento -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            🖨️ Foto do Equipamento
          </label>
          <label for="fotoEquipamento" class="upload-label" id="labelEquipamento">
            <span class="text-3xl mb-2">📷</span>
            <span class="text-sm text-gray-500 text-center">Toque para tirar foto</span>
          </label>
          <input 
            type="file" 
            name="foto_equipamento" 
            id="fotoEquipamento"
            accept="image/*"
            capture="environment"
            onchange="previewImage(this, 'labelEquipamento', 'previewEquipamento')"
          >
          <img id="previewEquipamento" class="preview-img mt-2 hidden w-full">
        </div>
      </div>
      
      <!-- Botão Submit -->
      <button 
        type="submit" 
        id="btnSubmit"
        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform hover:scale-[1.02] active:scale-[0.98] text-lg flex items-center justify-center gap-2"
      >
        <span>✅ Registrar Checklist</span>
      </button>
      
    </form>
    
    <!-- Footer -->
    <div class="text-center mt-6 text-white/60 text-sm">
      <p>SGQ-OTI • Área Técnica</p>
    </div>
    
  </div>
  
  <!-- Modal de Sucesso -->
  <div id="modalSucesso" class="fixed inset-0 hidden z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-2xl p-8 max-w-sm w-full text-center shadow-2xl transform scale-95 animate-[scale_0.3s_ease-out_forwards]">
      <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <span class="text-5xl">✅</span>
      </div>
      <h3 class="text-2xl font-bold text-gray-900 mb-2">Registrado!</h3>
      <p class="text-gray-600 mb-2">Checklist salvo com sucesso</p>
      <p class="text-sm text-gray-500 mb-6" id="horaRegistro">--/--/---- --:--:--</p>
      <button onclick="novoChecklist()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-xl transition">
        📋 Novo Checklist
      </button>
    </div>
  </div>
  
  <script>
    // Atualizar data/hora a cada segundo
    function atualizarDataHora() {
      const agora = new Date();
      const options = { 
        timeZone: 'America/Sao_Paulo',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      };
      const dataHora = agora.toLocaleString('pt-BR', options);
      document.getElementById('dataHora').textContent = dataHora;
    }
    
    setInterval(atualizarDataHora, 1000);
    atualizarDataHora();
    
    // Preview de imagem
    function previewImage(input, labelId, previewId) {
      const label = document.getElementById(labelId);
      const preview = document.getElementById(previewId);
      
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.classList.remove('hidden');
          label.classList.add('has-image');
          label.innerHTML = '<span class="text-green-600 font-medium">✅ Foto selecionada</span><span class="text-xs text-gray-500 mt-1">Toque para trocar</span>';
        }
        
        reader.readAsDataURL(input.files[0]);
      }
    }
    
    // Submit do formulário
    document.getElementById('checklistForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const btn = document.getElementById('btnSubmit');
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Salvando...';
      
      try {
        const formData = new FormData(this);
        
        const response = await fetch('/area-tecnica/checklist/salvar', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          document.getElementById('horaRegistro').textContent = result.data_hora;
          document.getElementById('modalSucesso').classList.remove('hidden');
        } else {
          alert('❌ ' + result.message);
        }
        
      } catch (error) {
        console.error('Erro:', error);
        alert('❌ Erro ao enviar checklist. Verifique sua conexão.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    });
    
    // Novo checklist
    function novoChecklist() {
      document.getElementById('modalSucesso').classList.add('hidden');
      document.getElementById('checklistForm').reset();
      
      // Reset previews
      ['Contador', 'Equipamento'].forEach(tipo => {
        document.getElementById('preview' + tipo).classList.add('hidden');
        document.getElementById('label' + tipo).classList.remove('has-image');
        document.getElementById('label' + tipo).innerHTML = '<span class="text-3xl mb-2">📷</span><span class="text-sm text-gray-500 text-center">Toque para tirar foto</span>';
      });
      
      // Focar no primeiro campo
      document.getElementById('numeroSerie').focus();
    }
    
    // Salvar nome do colaborador no localStorage
    document.getElementById('colaborador').addEventListener('blur', function() {
      if (this.value) localStorage.setItem('colaborador_nome', this.value);
    });
    
    // Recuperar nome do colaborador
    document.addEventListener('DOMContentLoaded', function() {
      const nomeColaborador = localStorage.getItem('colaborador_nome');
      if (nomeColaborador) {
        document.getElementById('colaborador').value = nomeColaborador;
      }
    });
  </script>
  
  <style>
    @keyframes scale {
      to { transform: scale(1); }
    }
  </style>
</body>
</html>
