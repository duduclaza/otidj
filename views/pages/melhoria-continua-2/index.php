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
    <div class="mb-8">
      <div class="flex items-center gap-3 mb-2">
        <h1 class="text-3xl font-bold text-gray-900">🚀 Melhoria Contínua 2.0</h1>
        <span class="beta-badge-large">BETA</span>
      </div>
      <p class="text-gray-600">Sistema avançado de gestão de melhorias com controle de visibilidade e notificações</p>
    </div>

    <!-- Formulário Inline -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">📝 Nova Melhoria</h2>
        <p class="text-sm text-gray-600 mt-1">Preencha os campos abaixo para registrar uma nova melhoria</p>
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
        <div class="bg-blue-50 rounded-lg p-6">
          <h3 class="text-lg font-semibold text-blue-900 mb-4">📋 Metodologia 5W2H</h3>
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
          <div class="border border-gray-300 rounded-lg p-3 max-h-40 overflow-y-auto">
            <?php foreach ($usuarios as $usuario): ?>
              <label class="flex items-center space-x-2 py-1">
                <input type="checkbox" name="responsaveis[]" value="<?= $usuario['id'] ?>" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-gray-700"><?= e($usuario['name']) ?></span>
              </label>
            <?php endforeach; ?>
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
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
            Registrar Melhoria
          </button>
        </div>
      </form>
    </div>

    <!-- Grid de Melhorias -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">📊 Minhas Melhorias</h2>
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
.beta-badge-large {
  background: linear-gradient(45deg, #ff6b6b, #feca57);
  color: white;
  font-size: 0.75rem;
  font-weight: bold;
  padding: 4px 8px;
  border-radius: 6px;
  text-shadow: 0 1px 2px rgba(0,0,0,0.3);
  animation: pulse 2s infinite;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.status-pendente-análise { background: #fef3c7; color: #92400e; }
.status-em-andamento { background: #dbeafe; color: #1e40af; }
.status-concluída { background: #d1fae5; color: #065f46; }
.status-recusada { background: #fee2e2; color: #991b1b; }
.status-pendente-adaptação { background: #f3e8ff; color: #7c3aed; }
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
