<?php
$pageTitle = 'Melhoria Contínua 2.0';
$currentPage = 'melhoria-continua-2';
include __DIR__ . '/../../layouts/header.php';

$isAdmin = $_SESSION['user_role'] === 'admin';
$userId = $_SESSION['user_id'];
?>

<div class="min-h-screen bg-gray-50 py-8">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Header com badge BETA -->
    <div class="header-gradient mb-8">
      <div class="flex items-center gap-4 mb-3">
        <h1 class="text-4xl font-bold">🚀 Melhoria Contínua 2.0</h1>
        <span class="beta-badge-large">BETA</span>
      </div>
      <p class="text-white/90 text-lg">Sistema avançado de gestão de melhorias com controle de visibilidade e notificações</p>
    </div>

    <!-- Formulário Inline -->
    <div class="card-modern mb-8">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900">📝 Nova Melhoria</h2>
        <p class="text-gray-600 mt-2">Preencha os campos abaixo para registrar uma nova melhoria</p>
      </div>
      
      <form id="melhoriaForm" class="p-6 space-y-6" enctype="multipart/form-data">
        <!-- Linha 1: Título e Departamento -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Título do Plano *</label>
            <input type="text" name="titulo" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Digite o título da melhoria...">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Departamento *</label>
            <select name="departamento_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Selecione um departamento</option>
              <?php foreach ($departamentos as $dept): ?>
                <option value="<?= $dept['id'] ?>"><?= e($dept['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- 5W2H -->
        <div class="form-section">
          <h3 class="text-xl font-bold mb-6">📋 Metodologia 5W2H</h3>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">O que será feito? *</label>
              <textarea name="o_que" required rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Descreva o que será feito..."></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Como será feito? *</label>
              <textarea name="como" required rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Descreva como será feito..."></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Onde? *</label>
              <textarea name="onde" required rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Descreva onde será feito..."></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Por que será feito? *</label>
              <textarea name="porque" required rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Justifique por que será feito..."></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Quando será feito? *</label>
              <input type="date" name="quando" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Quanto custa?</label>
              <input type="number" step="0.01" name="quanto_custa" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="R$ 0,00">
            </div>
          </div>
        </div>

        <!-- Responsáveis -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Quem será o responsável?</label>
          <div class="responsaveis-grid max-h-48 overflow-y-auto">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
              <?php foreach ($usuarios as $usuario): ?>
                <label class="responsavel-item flex items-center space-x-2 cursor-pointer">
                  <input type="checkbox" name="responsaveis[]" value="<?= $usuario['id'] ?>" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <span class="text-sm text-gray-700 truncate font-medium"><?= e($usuario['name']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Resultado Esperado e Idealizador -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Resultado Esperado *</label>
            <textarea name="resultado_esperado" required rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Descreva o resultado esperado..."></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Idealizador da Ideia *</label>
            <input type="text" name="idealizador" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Nome do idealizador...">
          </div>
        </div>

        <!-- Observação -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Observação</label>
          <textarea name="observacao" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Observações adicionais..."></textarea>
        </div>

        <!-- Anexos -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Anexos</label>
          <input type="file" name="anexos[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.ppt,.pptx" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <p class="text-xs text-gray-500 mt-1">Máximo 5 arquivos de 10MB cada. Formatos: JPG, PNG, GIF, PDF, PPT</p>
        </div>

        <!-- Botão Submit -->
        <div class="flex justify-end">
          <button type="submit" class="btn-primary">
            ✨ Registrar Melhoria
          </button>
        </div>
      </form>
    </div>

    <!-- Grid de Melhorias -->
    <div class="card-modern">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-900">📊 Minhas Melhorias</h2>
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
  </div>
</div>

<style>
/* Badge BETA com efeito brilhante */
.beta-badge-large {
  background: linear-gradient(45deg, #ff6b6b, #feca57, #ff6b6b);
  background-size: 200% 200%;
  color: white;
  font-size: 0.75rem;
  font-weight: bold;
  padding: 6px 12px;
  border-radius: 20px;
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
  animation: gradientShift 3s ease infinite, pulse 2s infinite;
  box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
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
