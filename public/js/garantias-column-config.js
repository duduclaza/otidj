// ===== SISTEMA DE CONFIGURAÇÃO DE COLUNAS =====

// Definição das colunas disponíveis
const COLUNAS_DISPONIVEIS = [
    { id: 'id', nome: 'ID', visivel: true },
    { id: 'fornecedor', nome: 'Fornecedor', visivel: true },
    { id: 'filial', nome: 'Filial', visivel: true },
    { id: 'origem', nome: 'Origem', visivel: true },
    { id: 'nfs', nome: 'NFs', visivel: true },
    { id: 'serie', nome: 'Nº Série', visivel: true },
    { id: 'lote', nome: 'Lote', visivel: true },
    { id: 'ticket_os', nome: 'Ticket/OS', visivel: true },
    { id: 'ticket_interno', nome: 'Ticket Interno', visivel: true },
    { id: 'produto', nome: 'Produto', visivel: true },
    { id: 'quantidade', nome: 'Qtd', visivel: true },
    { id: 'status', nome: 'Status', visivel: true },
    { id: 'defeito', nome: 'Descrição do Defeito', visivel: true },
    { id: 'itens', nome: 'Itens', visível: true },
    { id: 'valor', nome: 'Valor Total', visivel: true },
    { id: 'anexos', nome: 'Anexos', visivel: true },
    { id: 'data', nome: 'Criado em', visivel: true }
];

// Carregar configuração salva
function carregarConfigColunas() {
    const config = localStorage.getItem('garantias_colunas_visiveis');
    if (config) {
        try {
            return JSON.parse(config);
        } catch (e) {
            console.error('Erro ao carregar configuração:', e);
        }
    }
    return COLUNAS_DISPONIVEIS;
}

// Salvar configuração
function salvarConfigColunasLocalStorage(colunas) {
    localStorage.setItem('garantias_colunas_visiveis', JSON.stringify(colunas));
}

// Abrir modal de configuração
function toggleColumnVisibility() {
    const modal = document.getElementById('columnConfigModal');
    const togglesContainer = document.getElementById('columnToggles');
    
    // Carregar configuração atual
    const colunasConfig = carregarConfigColunas();
    
    // Limpar container
    togglesContainer.innerHTML = '';
    
    // Criar checkboxes
    colunasConfig.forEach(col => {
        const div = document.createElement('div');
        div.className = 'flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = `col-${col.id}`;
        checkbox.checked = col.visivel;
        checkbox.className = 'w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2';
        
        const label = document.createElement('label');
        label.htmlFor = `col-${col.id}`;
        label.textContent = col.nome;
        label.className = 'ml-3 text-sm font-medium text-gray-900 cursor-pointer flex-1';
        
        div.appendChild(checkbox);
        div.appendChild(label);
        togglesContainer.appendChild(div);
    });
    
    // Mostrar modal
    modal.classList.remove('hidden');
}

// Fechar modal
function closeColumnConfig() {
    document.getElementById('columnConfigModal').classList.add('hidden');
}

// Selecionar todas as colunas
function selecionarTodasColunas() {
    const checkboxes = document.querySelectorAll('#columnToggles input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = true);
}

// Desselecionar todas as colunas
function deselecionarTodasColunas() {
    const checkboxes = document.querySelectorAll('#columnToggles input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = false);
}

// Salvar configuração e aplicar
function salvarConfigColunas() {
    const colunasConfig = carregarConfigColunas();
    const checkboxes = document.querySelectorAll('#columnToggles input[type="checkbox"]');
    
    if (!checkboxes || checkboxes.length === 0) {
        console.warn('⚠️ Nenhum checkbox encontrado');
        return;
    }
    
    // Atualizar configuração
    checkboxes.forEach(cb => {
        const colId = cb.id.replace('col-', '');
        const coluna = colunasConfig.find(c => c.id === colId);
        if (coluna) {
            coluna.visivel = cb.checked;
        }
    });
    
    // Salvar no localStorage
    salvarConfigColunasLocalStorage(colunasConfig);
    
    // Aplicar na tabela
    aplicarConfigColunas(colunasConfig);
    
    // Fechar modal
    closeColumnConfig();
    
    // Feedback visual
    mostrarNotificacao('✅ Configuração de colunas salva com sucesso!', 'success');
}

// Aplicar configuração na tabela
function aplicarConfigColunas(colunasConfig) {
    const table = document.getElementById('garantiasTable');
    if (!table) {
        console.warn('⚠️ Tabela de garantias não encontrada');
        return;
    }
    
    const thead = table.querySelector('thead tr');
    const tbody = table.querySelector('tbody');
    
    if (!thead || !tbody) {
        console.warn('⚠️ Estrutura da tabela incompleta');
        return;
    }
    
    // Aplicar nos cabeçalhos
    colunasConfig.forEach((col, index) => {
        const th = thead.querySelector(`th[data-column="${col.id}"]`);
        if (th) {
            th.style.display = col.visivel ? '' : 'none';
        }
    });
    
    // Aplicar nas linhas
    tbody.querySelectorAll('tr').forEach(tr => {
        const tds = tr.querySelectorAll('td');
        colunasConfig.forEach((col, index) => {
            if (tds[index]) {
                tds[index].style.display = col.visivel ? '' : 'none';
            }
        });
    });
    
    // Atualizar largura do scroll superior
    setTimeout(() => {
        const scrollTopInner = document.getElementById('scrollTopGarantiasInner');
        if (scrollTopInner && table) {
            scrollTopInner.style.width = table.scrollWidth + 'px';
        }
    }, 100);
}

// Aplicar configuração salva ao carregar
function aplicarConfigSalvaAoCarregar() {
    const table = document.getElementById('garantiasTable');
    if (!table) {
        console.log('⚠️ Tabela não encontrada, pulando aplicação de configuração');
        return;
    }
    
    const colunasConfig = carregarConfigColunas();
    aplicarConfigColunas(colunasConfig);
}

// Mostrar notificação
function mostrarNotificacao(mensagem, tipo = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-[9999] px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 ${
        tipo === 'success' ? 'bg-green-500 text-white' :
        tipo === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = mensagem;
    notification.style.opacity = '0';
    notification.style.transform = 'translateY(-20px)';
    
    document.body.appendChild(notification);
    
    // Animação de entrada
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 10);
    
    // Remover após 3 segundos
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Inicializar ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se estamos na página de garantias
    const garantiasTable = document.getElementById('garantiasTable');
    if (!garantiasTable) {
        console.log('⚠️ Página de garantias não detectada, pulando inicialização de colunas');
        return;
    }
    
    // Aplicar configuração salva
    setTimeout(() => {
        aplicarConfigSalvaAoCarregar();
    }, 500);
});
