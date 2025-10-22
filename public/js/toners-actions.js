// Funções para gerenciar ações de toners

// Função para exibir alertas
function showAlert(type, message) {
    // Remover alertas existentes
    const existingAlert = document.querySelector('.toner-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Criar elemento de alerta
    const alertDiv = document.createElement('div');
    alertDiv.className = `toner-alert fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium flex items-center ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    
    // Ícone baseado no tipo
    const icon = type === 'success' ? 
        '<svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' :
        '<svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
    
    alertDiv.innerHTML = `${icon}${message}`;
    
    // Adicionar ao corpo do documento
    document.body.appendChild(alertDiv);
    
    // Remover após 5 segundos
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        alertDiv.style.transition = 'opacity 0.5s';
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 500);
    }, 5000);
}

// Adicionar estilos para os botões de ação
function initializeTonerActions() {
    // Adicionar evento de clique para os botões de editar
    document.querySelectorAll('[onclick^="editToner"]').forEach(btn => {
        const id = btn.getAttribute('onclick').match(/editToner\((\d+)\)/)[1];
        btn.classList.add('edit-btn-' + id);
        
        // Remover o atributo onclick antigo para evitar duplicação
        const oldOnClick = btn.getAttribute('onclick');
        btn.removeAttribute('onclick');
        
        // Adicionar evento de clique diretamente
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            editToner(id);
        });
    });
    
    // Adicionar evento de clique para os botões de salvar
    document.querySelectorAll('[onclick^="saveToner"]').forEach(btn => {
        const id = btn.getAttribute('onclick').match(/saveToner\((\d+)\)/)[1];
        btn.classList.add('save-btn-' + id);
        
        // Remover o atributo onclick antigo
        const oldOnClick = btn.getAttribute('onclick');
        btn.removeAttribute('onclick');
        
        // Adicionar evento de clique diretamente
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            saveToner(id);
        });
    });
    
    // Adicionar evento de clique para os botões de cancelar
    document.querySelectorAll('[onclick^="cancelEditToner"]').forEach(btn => {
        const id = btn.getAttribute('onclick').match(/cancelEditToner\((\d+)\)/)[1];
        btn.classList.add('cancel-btn-' + id);
        
        // Remover o atributo onclick antigo
        const oldOnClick = btn.getAttribute('onclick');
        btn.removeAttribute('onclick');
        
        // Adicionar evento de clique diretamente
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            cancelEditToner(id);
        });
    });
    
    // Adicionar evento de clique para os botões de exclusão
    document.querySelectorAll('[onclick^="deleteToner"]').forEach(btn => {
        const id = btn.getAttribute('onclick').match(/deleteToner\((\d+)\)/)[1];
        
        // Remover o atributo onclick antigo
        const oldOnClick = btn.getAttribute('onclick');
        btn.removeAttribute('onclick');
        
        // Adicionar evento de clique diretamente
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            deleteToner(id, e);
        });
    });
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    initializeTonerActions();
    
    // Adicionar evento de tecla Enter nos campos de edição
    document.addEventListener('keypress', function(e) {
        if (e.target.matches('[class*="edit-input-"]') && e.key === 'Enter') {
            e.preventDefault();
            const id = e.target.className.match(/edit-input-\w+-(\d+)/)[1];
            saveToner(id);
        }
    });
});

// Função para editar um toner
function editToner(id) {
    console.log('Editando toner ID:', id);
    
    // Ativar modo de edição para todos os campos
    const fields = ['modelo', 'peso_cheio', 'peso_vazio', 'gramatura', 'capacidade_folhas', 'preco_toner', 'gramatura_por_folha', 'custo_por_folha', 'cor', 'tipo'];
    
    fields.forEach(field => {
        const display = document.querySelector(`.edit-display-${field}-${id}`);
        const input = document.querySelector(`.edit-input-${field}-${id}`);
        
        if (display && input) {
            display.classList.add('hidden');
            input.classList.remove('hidden');
            
            // Adicionar evento para salvar ao pressionar Enter
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveToner(id);
                }
            });
        }
    });
    
    // Mostrar/ocultar botões
    const editBtn = document.querySelector(`.edit-btn-${id}`);
    const saveBtn = document.querySelector(`.save-btn-${id}`);
    const cancelBtn = document.querySelector(`.cancel-btn-${id}`);
    
    if (editBtn) editBtn.classList.add('hidden');
    if (saveBtn) saveBtn.classList.remove('hidden');
    if (cancelBtn) cancelBtn.classList.remove('hidden');
    
    // Focar no primeiro campo de input
    const firstInput = document.querySelector(`.edit-input-modelo-${id}`);
    if (firstInput) firstInput.focus();
}

// Função para cancelar edição
function cancelEditToner(id) {
    console.log('Cancelando edição do toner ID:', id);
    
    // Desativar modo de edição para todos os campos
    const fields = ['modelo', 'peso_cheio', 'peso_vazio', 'gramatura', 'capacidade_folhas', 'preco_toner', 'gramatura_por_folha', 'custo_por_folha', 'cor', 'tipo'];
    
    fields.forEach(field => {
        const display = document.querySelector(`.edit-display-${field}-${id}`);
        const input = document.querySelector(`.edit-input-${field}-${id}`);
        
        if (display && input) {
            display.classList.remove('hidden');
            input.classList.add('hidden');
        }
    });
    
    // Mostrar/ocultar botões
    const editBtn = document.querySelector(`.edit-btn-${id}`);
    const saveBtn = document.querySelector(`.save-btn-${id}`);
    const cancelBtn = document.querySelector(`.cancel-btn-${id}`);
    
    if (editBtn) editBtn.classList.remove('hidden');
    if (saveBtn) saveBtn.classList.add('hidden');
    if (cancelBtn) cancelBtn.classList.add('hidden');
}

// Função para salvar edição
function saveToner(id) {
    console.log('Salvando alterações do toner ID:', id);
    
    // Mostrar loading nos botões
    const saveBtn = document.querySelector(`.save-btn-${id}`);
    const originalSaveText = saveBtn ? saveBtn.innerHTML : '';
    if (saveBtn) {
        saveBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Salvando...';
        saveBtn.disabled = true;
    }
    
    // Coletar dados do formulário
    const formData = new FormData();
    
    // Adicionar token CSRF
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (token) {
        formData.append('_token', token);
    }
    
    // Adicionar ID do toner
    formData.append('id', id);
    
    // Coletar valores dos campos
    const fields = ['modelo', 'peso_cheio', 'peso_vazio', 'gramatura', 'capacidade_folhas', 'preco_toner', 'gramatura_por_folha', 'custo_por_folha', 'cor', 'tipo'];
    
    fields.forEach(field => {
        const input = document.querySelector(`.edit-input-${field}-${id}`);
        if (input) {
            formData.append(field, input.value);
        }
    });
    
    // Enviar via AJAX
    fetch('/toners/update', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token || '',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async (response) => {
        const contentType = response.headers.get('content-type') || '';
        let data;
        if (contentType.includes('application/json')) {
            try { data = await response.json(); } catch (e) { data = null; }
        } else {
            const text = await response.text();
            // Se não for JSON, gerar um erro mais amigável com parte do HTML retornado
            throw new Error(text?.slice(0, 200) || 'Resposta não reconhecida do servidor');
        }
        if (!response.ok) {
            // Tentar extrair mensagem do JSON
            const msg = (data && (data.message || data.error)) || `Erro ${response.status}`;
            throw new Error(msg);
        }
        return data;
    })
    .then(data => {
        console.log('Resposta do servidor:', data);
        
        if (data.success) {
            // Atualizar os valores exibidos
            fields.forEach(field => {
                const input = document.querySelector(`.edit-input-${field}-${id}`);
                const display = document.querySelector(`.edit-display-${field}-${id}`);
                
                if (input && display) {
                    // Atualizar valor exibido
                    if (field === 'cor' || field === 'tipo') {
                        // Para selects, pegar o texto da opção selecionada
                        const selectedOption = input.options[input.selectedIndex];
                        display.textContent = selectedOption.text;
                    } else {
                        display.textContent = input.value;
                    }
                    
                    // Restaurar visibilidade
                    display.classList.remove('hidden');
                    input.classList.add('hidden');
                }
            });
            
            // Mostrar mensagem de sucesso
            showAlert('success', 'Toner atualizado com sucesso!');
        } else {
            // Mostrar mensagem de erro
            showAlert('error', data.message || 'Erro ao atualizar o toner. Por favor, tente novamente.');
        }
    })
    .catch(error => {
        console.error('Erro ao salvar:', error);
        showAlert('error', error?.message || 'Erro ao conectar ao servidor. Verifique sua conexão e tente novamente.');
    })
    .finally(() => {
        // Restaurar botão de salvar
        if (saveBtn) {
            saveBtn.innerHTML = originalSaveText;
            saveBtn.disabled = false;
        }
        
        // Mostrar botão de editar novamente
        const editBtn = document.querySelector(`.edit-btn-${id}`);
        const cancelBtn = document.querySelector(`.cancel-btn-${id}`);
        
        if (editBtn) editBtn.classList.remove('hidden');
        if (saveBtn) saveBtn.classList.add('hidden');
        if (cancelBtn) cancelBtn.classList.add('hidden');
    });
}

// Função para excluir um toner
function deleteToner(id, event) {
    // Evitar múltiplos cliques
    if (event && event.target.getAttribute('data-deleting') === 'true') {
        return;
    }
    
    // Mostrar confirmação personalizada
    const confirmed = window.confirm('Tem certeza que deseja excluir este toner? Esta ação não pode ser desfeita.');
    if (!confirmed) return;
    
    // Mostrar indicador de carregamento
    const deleteBtn = event ? event.target : document.querySelector(`[onclick*="deleteToner(${id})"]`);
    const originalText = deleteBtn ? deleteBtn.innerHTML : '';
    
    if (deleteBtn) {
        deleteBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Excluindo...';
        deleteBtn.setAttribute('data-deleting', 'true');
        deleteBtn.disabled = true;
    }
    
    // Obter token CSRF
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    
    // Enviar requisição DELETE via AJAX
    fetch(`/toners/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token || '',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        console.log('Resposta da exclusão:', data);
        
        if (data.success) {
            // Mostrar mensagem de sucesso
            showAlert('success', 'Toner excluído com sucesso!');
            
            // Remover a linha da tabela
            const row = document.querySelector(`tr[data-toner-id="${id}"]`) || 
                        document.querySelector(`[onclick*="editToner(${id})"]`).closest('tr');
            
            if (row) {
                // Adicionar animação de fade out
                row.style.transition = 'opacity 0.5s';
                row.style.opacity = '0';
                
                // Remover após a animação
                setTimeout(() => {
                    row.remove();
                    
                    // Atualizar contagem de resultados
                    updateResultsCount();
                }, 500);
            }
        } else {
            throw new Error(data.message || 'Erro ao excluir o toner');
        }
    })
    .catch(error => {
        console.error('Erro ao excluir toner:', error);
        showAlert('error', error.message || 'Erro ao excluir o toner. Por favor, tente novamente.');
    })
    .finally(() => {
        // Restaurar botão de exclusão
        if (deleteBtn) {
            deleteBtn.innerHTML = originalText;
            deleteBtn.removeAttribute('data-deleting');
            deleteBtn.disabled = false;
        }
    });
}

// Função para atualizar a contagem de resultados
document.updateResultsCount = function() {
    const rows = document.querySelectorAll('tbody tr:not([style*="display: none"]):not(.hidden)');
    const count = rows.length;
    const countElement = document.getElementById('resultsCount');
    
    if (countElement) {
        countElement.textContent = count === 0 ? 'Nenhum toner encontrado' : 
                                 count === 1 ? '1 toner encontrado' : 
                                 `${count} toners encontrados`;
    }
};

// Função para calcular campos durante a edição
function calcularEdicao(id) {
    const pesoCheio = parseFloat(document.querySelector(`.edit-input-peso_cheio-${id}`).value) || 0;
    const pesoVazio = parseFloat(document.querySelector(`.edit-input-peso_vazio-${id}`).value) || 0;
    const capacidadeFolhas = parseFloat(document.querySelector(`.edit-input-capacidade_folhas-${id}`).value) || 0;
    const precoToner = parseFloat(document.querySelector(`.edit-input-preco_toner-${id}`).value) || 0;
    
    // Calcular gramatura
    const gramatura = (pesoCheio - pesoVazio).toFixed(2);
    document.querySelector(`.edit-input-gramatura-${id}`).value = gramatura;
    
    // Calcular gramatura por folha
    const gramaturaPorFolha = capacidadeFolhas > 0 ? (gramatura / capacidadeFolhas).toFixed(4) : 0;
    document.querySelector(`.edit-input-gramatura_por_folha-${id}`).value = gramaturaPorFolha;
    
    // Calcular custo por folha
    const custoPorFolha = capacidadeFolhas > 0 ? (precoToner / capacidadeFolhas).toFixed(4) : 0;
    document.querySelector(`.edit-input-custo_por_folha-${id}`).value = custoPorFolha;
}

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar evento de tecla Enter nos campos de edição
    document.querySelectorAll('[class*="edit-input-"]').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const id = this.className.match(/edit-input-\w+-(\d+)/)[1];
                salvarToner(id);
            }
        });
    });
});
