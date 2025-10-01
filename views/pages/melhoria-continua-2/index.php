<?php
$pageTitle = 'Melhoria Contínua 2.0';
$currentPage = 'melhoria-continua-2';
include __DIR__ . '/../../layouts/header.php';

$isAdmin = $_SESSION['user_role'] === 'admin';
$userId = $_SESSION['user_id'];
?>

<section class="space-y-6">
    
    <!-- Header Compacto -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-xl p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
            <span class="text-2xl">🚀</span>
          </div>
          <div>
            <h1 class="text-3xl font-bold text-white">Melhoria Contínua 2.0</h1>
            <div class="flex items-center gap-2 mt-1">
              <span class="beta-badge-large">BETA</span>
              <span class="text-white/80 text-sm">Sistema avançado de gestão de melhorias</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Formulário Inline -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
      <!-- Header do Formulário -->
      <div class="bg-gradient-to-r from-gray-50 to-blue-50 px-6 py-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-blue-100 rounded-xl">
            <span class="text-xl">📝</span>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Nova Melhoria</h2>
            <p class="text-gray-600 text-sm mt-1">Registre sua ideia seguindo a metodologia 5W2H</p>
          </div>
        </div>
      </div>
      
      <form id="melhoriaForm" class="p-6 space-y-6" enctype="multipart/form-data">
        
        <!-- Seção: Informações Básicas -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-blue-500 rounded-lg">
              <span class="text-white text-lg">ℹ️</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Informações Básicas</h3>
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">Título do Plano *</label>
              <input type="text" name="titulo" required class="form-input-premium" placeholder="Ex: Otimização do processo de impressão">
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">Departamento *</label>
              <select name="departamento_id" required class="form-input-premium">
                <option value="">Selecione um departamento</option>
                <?php foreach ($departamentos as $dept): ?>
                  <option value="<?= $dept['id'] ?>"><?= e($dept['nome']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- Seção: Metodologia 5W2H -->
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-6 border border-purple-100">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-purple-500 rounded-lg">
              <span class="text-white text-lg">📋</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Metodologia 5W2H</h3>
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center text-xs font-bold text-purple-600">O</span>
                O que será feito? *
              </label>
              <textarea name="o_que" required rows="3" class="form-input-premium resize-none" placeholder="Descreva detalhadamente o que será implementado..."></textarea>
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center text-xs font-bold text-purple-600">C</span>
                Como será feito? *
              </label>
              <textarea name="como" required rows="3" class="form-input-premium resize-none" placeholder="Explique a metodologia e os passos..."></textarea>
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center text-xs font-bold text-purple-600">O</span>
                Onde será feito? *
              </label>
              <textarea name="onde" required rows="3" class="form-input-premium resize-none" placeholder="Especifique o local ou área de aplicação..."></textarea>
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center text-xs font-bold text-purple-600">P</span>
                Por que será feito? *
              </label>
              <textarea name="porque" required rows="3" class="form-input-premium resize-none" placeholder="Justifique a necessidade e benefícios..."></textarea>
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center text-xs font-bold text-purple-600">Q</span>
                Quando será feito? *
              </label>
              <input type="date" name="quando" required class="form-input-premium">
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                <span class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center text-xs font-bold text-purple-600">Q</span>
                Quanto custa?
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">R$</span>
                <input type="number" step="0.01" name="quanto_custa" class="form-input-premium pl-10" placeholder="0,00">
              </div>
            </div>
          </div>
        </div>

        <!-- Seção: Responsáveis -->
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 border border-green-100">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-green-500 rounded-lg">
              <span class="text-white text-lg">👥</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Responsáveis</h3>
          </div>
          
          <div class="bg-white rounded-xl p-4 max-h-48 overflow-y-auto border border-green-200">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
              <?php foreach ($usuarios as $usuario): ?>
                <label class="flex items-center space-x-3 p-3 rounded-lg hover:bg-green-50 transition-colors cursor-pointer border border-gray-200 hover:border-green-300">
                  <input type="checkbox" name="responsaveis[]" value="<?= $usuario['id'] ?>" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                  <span class="text-sm text-gray-700 truncate font-medium"><?= e($usuario['name']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Seção: Detalhes Complementares -->
        <div class="bg-gradient-to-r from-orange-50 to-yellow-50 rounded-2xl p-6 border border-orange-100">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-orange-500 rounded-lg">
              <span class="text-white text-lg">📝</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Detalhes Complementares</h3>
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">Resultado Esperado *</label>
              <textarea name="resultado_esperado" required rows="4" class="form-input-premium resize-none" placeholder="Descreva os resultados e benefícios esperados com esta melhoria..."></textarea>
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">Idealizador da Ideia *</label>
              <input type="text" name="idealizador" required class="form-input-premium" placeholder="Nome completo do idealizador">
              
              <div class="mt-4 space-y-2">
                <label class="block text-sm font-semibold text-gray-700">Observações</label>
                <textarea name="observacao" rows="3" class="form-input-premium resize-none" placeholder="Informações adicionais, considerações especiais..."></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Seção: Anexos -->
        <div class="bg-gradient-to-r from-gray-50 to-slate-50 rounded-2xl p-6 border border-gray-200">
          <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-gray-600 rounded-lg">
              <span class="text-white text-lg">📎</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Anexos</h3>
          </div>
          
          <div class="space-y-4">
            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-gray-400 transition-colors">
              <input type="file" name="anexos[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.ppt,.pptx" class="hidden" id="fileInput">
              <label for="fileInput" class="cursor-pointer">
                <div class="text-4xl mb-2">📁</div>
                <p class="text-lg font-medium text-gray-700 mb-2">Clique para selecionar arquivos</p>
                <p class="text-sm text-gray-500">Máximo 5 arquivos de 10MB cada</p>
                <p class="text-xs text-gray-400 mt-1">Formatos: JPG, PNG, GIF, PDF, PPT, PPTX</p>
              </label>
            </div>
          </div>
        </div>

        <!-- Botão Submit -->
        <div class="flex justify-center pt-4">
          <button type="submit" class="btn-premium">
            <span class="text-xl">🚀</span>
            <span>Registrar Melhoria</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Grid de Melhorias -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
      <div class="bg-gradient-to-r from-gray-50 to-blue-50 px-6 py-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-blue-100 rounded-xl">
            <span class="text-xl">📊</span>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Minhas Melhorias</h2>
            <p class="text-gray-600 text-sm mt-1">Acompanhe o status das suas sugestões</p>
          </div>
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsáveis</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
              <?php if ($isAdmin): ?>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pontuação</th>
              <?php endif; ?>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($melhorias as $melhoria): ?>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900"><?= e($melhoria['titulo']) ?></div>
                <div class="text-sm text-gray-500">Por: <?= e($melhoria['criador_nome']) ?></div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <?= e($melhoria['departamento_nome'] ?? 'N/A') ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $melhoria['status'])) ?>">
                  <?= e($melhoria['status']) ?>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <?= e($melhoria['responsaveis_nomes'] ?? 'Nenhum') ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <?= date('d/m/Y', strtotime($melhoria['created_at'])) ?>
              </td>
              <?php if ($isAdmin): ?>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <?= $melhoria['pontuacao'] ? $melhoria['pontuacao'] . '/10' : '-' ?>
              </td>
              <?php endif; ?>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button onclick="viewMelhoria(<?= $melhoria['id'] ?>)" class="text-blue-600 hover:text-blue-900">Ver</button>
                
                <?php if ($melhoria['criado_por'] == $userId && $melhoria['status'] === 'Pendente Adaptação'): ?>
                <button onclick="editMelhoria(<?= $melhoria['id'] ?>)" class="text-green-600 hover:text-green-900">Editar</button>
                <?php endif; ?>
                
                <?php if ($isAdmin): ?>
                <button onclick="updateStatus(<?= $melhoria['id'] ?>)" class="text-purple-600 hover:text-purple-900">Status</button>
                <?php endif; ?>
                
                <?php if ($melhoria['criado_por'] == $userId && $melhoria['status'] === 'Recusada'): ?>
                <button onclick="deleteMelhoria(<?= $melhoria['id'] ?>)" class="text-red-600 hover:text-red-900">Excluir</button>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
</section>

<style>
/* Badge BETA com efeito brilhante */
.beta-badge-large {
  background: linear-gradient(45deg, #ff6b6b, #feca57, #ff6b6b);
  background-size: 200% 200%;
  color: white;
  font-size: 0.75rem;
  font-weight: bold;
  padding: 8px 16px;
  border-radius: 25px;
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
  animation: gradientShift 3s ease infinite, pulse 2s infinite;
  box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
  border: 2px solid rgba(255, 255, 255, 0.3);
}

/* Inputs Premium */
.form-input-premium {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 14px 18px;
  transition: all 0.3s ease;
  background: #ffffff;
  width: 100%;
  font-size: 0.95rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-input-premium:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1), 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
  outline: none;
}

.form-input-premium:hover {
  border-color: #d1d5db;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Botão Premium */
.btn-premium {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  color: white;
  padding: 16px 32px;
  border-radius: 16px;
  font-weight: 700;
  font-size: 1.1rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  transition: all 0.3s ease;
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
  display: flex;
  items-center;
  gap: 12px;
  cursor: pointer;
}

.btn-premium:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
  background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
}

@keyframes gradientShift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

/* Melhorar formulário */
.form-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-section h3 {
  color: white;
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.form-section .form-input {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
}

/* Status badges melhorados */
.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.status-pendente-análise { 
  background: linear-gradient(135deg, #fef3c7, #fde68a); 
  color: #92400e; 
  border: 1px solid #f59e0b;
}

.status-em-andamento { 
  background: linear-gradient(135deg, #dbeafe, #bfdbfe); 
  color: #1e40af; 
  border: 1px solid #3b82f6;
}

.status-concluída { 
  background: linear-gradient(135deg, #d1fae5, #a7f3d0); 
  color: #065f46; 
  border: 1px solid #10b981;
}

.status-recusada { 
  background: linear-gradient(135deg, #fee2e2, #fecaca); 
  color: #991b1b; 
  border: 1px solid #ef4444;
}

.status-pendente-adaptação { 
  background: linear-gradient(135deg, #f3e8ff, #e9d5ff); 
  color: #7c3aed; 
  border: 1px solid #8b5cf6;
}

/* Melhorar cards */
.card-modern {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
}

.card-modern:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Botões melhorados */
.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  color: white;
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

/* Inputs melhorados */
.form-input-modern {
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  padding: 12px 16px;
  transition: all 0.3s ease;
  background: #ffffff;
}

.form-input-modern:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  transform: translateY(-1px);
}

/* Header melhorado */
.header-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

/* Responsáveis grid melhorado */
.responsaveis-grid {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  border-radius: 12px;
  padding: 1.5rem;
  border: 2px solid #e2e8f0;
}

.responsavel-item {
  background: white;
  border-radius: 8px;
  padding: 12px;
  transition: all 0.3s ease;
  border: 1px solid #e2e8f0;
}

.responsavel-item:hover {
  background: #f8fafc;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>

<script>
document.getElementById('melhoriaForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  
  try {
    const response = await fetch('/melhoria-continua-2/store', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert(result.message);
      if (result.redirect) {
        window.location.href = result.redirect;
      }
    } else {
      alert('Erro: ' + result.message);
    }
  } catch (error) {
    alert('Erro ao enviar formulário');
  }
});

function viewMelhoria(id) {
  // Implementar modal de visualização
  alert('Visualizar melhoria ID: ' + id);
}

function editMelhoria(id) {
  // Implementar edição
  alert('Editar melhoria ID: ' + id);
}

function updateStatus(id) {
  // Implementar modal de atualização de status (apenas admin)
  alert('Atualizar status da melhoria ID: ' + id);
}

function deleteMelhoria(id) {
  if (confirm('Tem certeza que deseja excluir esta melhoria?')) {
    // Implementar exclusão
    alert('Excluir melhoria ID: ' + id);
  }
}
</script>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
