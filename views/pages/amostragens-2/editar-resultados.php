<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Resultados - Amostragem #<?= $amostragem['id'] ?? '' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-gray-900 min-h-screen">
    
    <!-- Container Principal -->
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        
        <!-- Cabeçalho -->
        <div class="bg-white rounded-lg shadow-2xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        ✅ Adicionar Resultados dos Testes
                    </h1>
                    <p class="text-gray-600 mt-1">Amostragem #<?= $amostragem['id'] ?> - NF: <?= e($amostragem['numero_nf']) ?></p>
                </div>
                <a href="/amostragens-2" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    ← Voltar
                </a>
            </div>
            
            <!-- Informações da Amostragem -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                <h3 class="font-semibold text-blue-900 mb-2">📦 Informações da Amostragem</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Produto:</span>
                        <p class="font-semibold text-gray-900"><?= e($amostragem['nome_produto']) ?></p>
                    </div>
                    <div>
                        <span class="text-gray-600">Código:</span>
                        <p class="font-semibold text-gray-900"><?= e($amostragem['codigo_produto']) ?></p>
                    </div>
                    <div>
                        <span class="text-gray-600">Fornecedor:</span>
                        <p class="font-semibold text-gray-900"><?= e($amostragem['fornecedor_nome']) ?></p>
                    </div>
                    <div>
                        <span class="text-gray-600">Qtd. Recebida:</span>
                        <p class="font-semibold text-gray-900"><?= e($amostragem['quantidade_recebida']) ?></p>
                    </div>
                    <div>
                        <span class="text-gray-600">Criado por:</span>
                        <p class="font-semibold text-gray-900"><?= e($amostragem['usuario_nome']) ?></p>
                    </div>
                    <div>
                        <span class="text-gray-600">Data:</span>
                        <p class="font-semibold text-gray-900"><?= date('d/m/Y H:i', strtotime($amostragem['created_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Resultados -->
        <form id="formResultados" class="bg-white rounded-lg shadow-2xl p-6" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $amostragem['id'] ?>">
            
            <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-3">
                🧪 Resultados dos Testes
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Quantidade Testada -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Quantidade Testada *
                    </label>
                    <input 
                        type="number" 
                        name="quantidade_testada" 
                        value="<?= $amostragem['quantidade_testada'] ?? '' ?>"
                        min="0" 
                        max="<?= $amostragem['quantidade_recebida'] ?>"
                        required 
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 text-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Ex: 10"
                    >
                    <p class="text-xs text-gray-500 mt-1">Máximo: <?= $amostragem['quantidade_recebida'] ?></p>
                </div>

                <!-- Quantidade Aprovada -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Quantidade Aprovada *
                    </label>
                    <input 
                        type="number" 
                        name="quantidade_aprovada" 
                        value="<?= $amostragem['quantidade_aprovada'] ?? '' ?>"
                        min="0" 
                        required 
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 text-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Ex: 8"
                    >
                </div>

                <!-- Quantidade Reprovada -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Quantidade Reprovada *
                    </label>
                    <input 
                        type="number" 
                        name="quantidade_reprovada" 
                        value="<?= $amostragem['quantidade_reprovada'] ?? '' ?>"
                        min="0" 
                        required 
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 text-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        placeholder="Ex: 2"
                    >
                </div>
            </div>

            <!-- Status Final -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Status Final *
                </label>
                <select 
                    name="status_final" 
                    required 
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 text-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="Pendente" <?= ($amostragem['status_final'] ?? '') == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="Aprovado" <?= ($amostragem['status_final'] ?? '') == 'Aprovado' ? 'selected' : '' ?>>Aprovado</option>
                    <option value="Aprovado Parcialmente" <?= ($amostragem['status_final'] ?? '') == 'Aprovado Parcialmente' ? 'selected' : '' ?>>Aprovado Parcialmente</option>
                    <option value="Reprovado" <?= ($amostragem['status_final'] ?? '') == 'Reprovado' ? 'selected' : '' ?>>Reprovado</option>
                </select>
            </div>

            <!-- Evidências -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    📷 Evidências Fotográficas (Opcional)
                </label>
                <input 
                    type="file" 
                    name="evidencias[]" 
                    multiple 
                    accept="image/*" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                <p class="text-xs text-gray-500 mt-1">
                    Selecione até 5 fotos como evidência dos testes. Máximo 10MB por foto.
                </p>
                
                <!-- Preview de evidências -->
                <div id="previewEvidencias" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3"></div>
            </div>

            <!-- Observações -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    📝 Observações (Opcional)
                </label>
                <textarea 
                    name="observacoes" 
                    rows="4" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Adicione observações sobre os testes realizados..."
                ><?= $amostragem['observacoes'] ?? '' ?></textarea>
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-4 pt-4 border-t">
                <a 
                    href="/amostragens-2" 
                    class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition-colors"
                >
                    Cancelar
                </a>
                <button 
                    type="submit" 
                    id="btnSalvar"
                    class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg shadow-lg transition-all transform hover:scale-105"
                >
                    💾 Salvar Resultados
                </button>
            </div>
        </form>

    </div>

    <script>
    // Preview de evidências
    document.querySelector('input[name="evidencias[]"]').addEventListener('change', function(e) {
        const preview = document.getElementById('previewEvidencias');
        preview.innerHTML = '';
        
        const files = Array.from(e.target.files);
        
        if (files.length > 5) {
            alert('Máximo de 5 fotos permitido!');
            e.target.value = '';
            return;
        }
        
        files.forEach((file, index) => {
            if (file.size > 10 * 1024 * 1024) {
                alert(`Arquivo ${file.name} é muito grande! Máximo 10MB.`);
                e.target.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg border-2 border-gray-200">
                    <div class="absolute top-1 right-1 bg-blue-500 text-white text-xs px-2 py-1 rounded">${index + 1}</div>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });

    // Submit do formulário
    document.getElementById('formResultados').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btnSalvar = document.getElementById('btnSalvar');
        const formData = new FormData(this);
        
        // Validar se testada = aprovada + reprovada
        const testada = parseInt(formData.get('quantidade_testada')) || 0;
        const aprovada = parseInt(formData.get('quantidade_aprovada')) || 0;
        const reprovada = parseInt(formData.get('quantidade_reprovada')) || 0;
        
        if (testada !== (aprovada + reprovada)) {
            alert('⚠️ Atenção: Quantidade testada deve ser igual à soma de aprovadas + reprovadas!');
            return;
        }
        
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '⏳ Salvando...';
        
        try {
            const response = await fetch('/amostragens-2/update', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('✅ Resultados salvos com sucesso!');
                window.location.href = '/amostragens-2';
            } else {
                alert('❌ Erro: ' + (result.message || 'Falha ao salvar'));
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = '💾 Salvar Resultados';
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('❌ Erro ao processar solicitação');
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '💾 Salvar Resultados';
        }
    });
    </script>

</body>
</html>
