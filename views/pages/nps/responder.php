<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($formulario['titulo']) ?> - Formulário Online</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen py-12 px-4">
  <div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-xl overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-8 text-white text-center">
        <?php if (!empty($formulario['logo'])): ?>
          <div class="mb-4 flex justify-center">
            <img src="/<?= e($formulario['logo']) ?>" alt="Logo" class="h-24 w-auto object-contain">
          </div>
        <?php endif; ?>
        <h1 class="text-3xl font-bold mb-2"><?= e($formulario['titulo']) ?></h1>
        <?php if ($formulario['descricao']): ?>
          <p class="text-blue-100"><?= e($formulario['descricao']) ?></p>
        <?php endif; ?>
      </div>

      <!-- Formulário -->
      <form id="formularioResposta" class="p-8 space-y-6">
        <input type="hidden" name="formulario_id" value="<?= e($formulario['id']) ?>">
        
        <!-- Dados do Respondente -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Seu Nome *</label>
            <input type="text" name="nome" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" placeholder="Digite seu nome">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Seu Email *</label>
            <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" placeholder="seu@email.com">
          </div>
        </div>

        <!-- Perguntas -->
        <div class="border-t border-gray-200 pt-6">
          <?php foreach ($formulario['perguntas'] as $index => $pergunta): ?>
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-900 mb-3">
                <?= ($index + 1) ?>. <?= e($pergunta['texto']) ?> *
              </label>
              
              <?php if ($pergunta['tipo'] === 'texto'): ?>
                <textarea name="resposta_<?= $index ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Digite sua resposta"></textarea>
              
              <?php elseif ($pergunta['tipo'] === 'numero'): ?>
                <div class="flex items-center space-x-2">
                  <span class="text-sm text-gray-500">0</span>
                  <input type="range" name="resposta_<?= $index ?>" min="0" max="10" value="5" class="flex-1" oninput="document.getElementById('valor_<?= $index ?>').textContent = this.value">
                  <span class="text-sm text-gray-500">10</span>
                  <span id="valor_<?= $index ?>" class="text-lg font-bold text-blue-600 w-8 text-center">5</span>
                </div>
              
              <?php elseif ($pergunta['tipo'] === 'sim_nao'): ?>
                <div class="flex space-x-4">
                  <label class="flex items-center">
                    <input type="radio" name="resposta_<?= $index ?>" value="Sim" required class="mr-2">
                    <span>Sim</span>
                  </label>
                  <label class="flex items-center">
                    <input type="radio" name="resposta_<?= $index ?>" value="Não" required class="mr-2">
                    <span>Não</span>
                  </label>
                </div>
              
              <?php elseif ($pergunta['tipo'] === 'multipla'): ?>
                <select name="resposta_<?= $index ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                  <option value="">Selecione uma opção</option>
                  <option value="Ótimo">Ótimo</option>
                  <option value="Bom">Bom</option>
                  <option value="Regular">Regular</option>
                  <option value="Ruim">Ruim</option>
                  <option value="Péssimo">Péssimo</option>
                </select>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Botão Enviar -->
        <div class="pt-6 border-t border-gray-200">
          <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-lg hover:from-blue-700 hover:to-indigo-700 font-medium transition-colors">
            Enviar Respostas
          </button>
        </div>
      </form>

      <!-- Mensagem de Sucesso -->
      <div id="mensagemSucesso" class="hidden p-8 text-center">
        <div class="text-6xl mb-4">✅</div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Obrigado por responder!</h2>
        <p class="text-gray-600">Sua opinião é muito importante para nós.</p>
      </div>
    </div>
  </div>

  <script>
  document.getElementById('formularioResposta').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const respostas = [];
    
    <?php foreach ($formulario['perguntas'] as $index => $pergunta): ?>
      respostas.push({
        pergunta: <?= json_encode($pergunta['texto']) ?>,
        resposta: formData.get('resposta_<?= $index ?>')
      });
    <?php endforeach; ?>
    
    const dados = new FormData();
    dados.append('formulario_id', formData.get('formulario_id'));
    dados.append('nome', formData.get('nome') || 'Anônimo');
    dados.append('email', formData.get('email') || '');
    dados.append('respostas', JSON.stringify(respostas));
    
    fetch('/nps/salvar-resposta', {
      method: 'POST',
      body: dados
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        document.getElementById('formularioResposta').classList.add('hidden');
        document.getElementById('mensagemSucesso').classList.remove('hidden');
      } else {
        alert('Erro: ' + data.message);
      }
    })
    .catch(err => alert('Erro de conexão'));
  });
  </script>
</body>
</html>
