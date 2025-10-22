// ===== BUSCA INTELIGENTE NO GRID DE TONERS =====
// Arquivo separado para evitar conflitos e SyntaxErrors

// Função para limpar a busca
window.clearSearch = function() {
    console.log('🧽 Limpando busca...');
    const input = document.getElementById('searchToners');
    const select = document.getElementById('searchColumn');
    
    if (input) input.value = '';
    if (select) select.value = 'all';
    
    window.searchToners();
};

// Função de busca por coluna específica
window.searchToners = function() {
    console.log('🔍 Executando busca...');
    const input = document.getElementById('searchToners');
    const searchColumn = document.getElementById('searchColumn')?.value || 'all';
    
    if (!input) {
        console.error('❌ Campo de busca não encontrado!');
        return;
    }
    
    let tbody = document.getElementById('tonersTbody');
    if (!tbody) {
        tbody = document.querySelector('table tbody');
        console.warn('⚠️ Usando fallback para tbody');
    }
    
    if (!tbody) {
        console.error('❌ Tbody não encontrado!');
        return;
    }
    
    const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.cells.length >= 2);
    let visibleCount = 0;
    
    console.log(`📊 Total de linhas: ${rows.length}`);

    const raw = (input.value || '').trim().toLowerCase();
    const normalized = raw.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    const tokens = normalized.split(/\s+/).filter(Boolean);
    
    console.log(`🔑 Termos de busca: ${tokens.length > 0 ? tokens.join(', ') : '(vazio)'} | Coluna: ${searchColumn}`);

    // Remover possível linha de mensagem antiga
    const emptyMsg = tbody.querySelector('.no-results-row');
    if (emptyMsg) emptyMsg.remove();

    rows.forEach((row, index) => {
      // Se não há termos de busca, mostrar tudo
      if (tokens.length === 0) {
        row.style.display = '';
        visibleCount++;
        return;
      }

      let haystack = '';
      if (searchColumn === 'all') {
        haystack = Array.from(row.cells).map(td => td.textContent || '').join(' ');
      } else {
        const columnIndex = parseInt(searchColumn);
        haystack = row.cells[columnIndex]?.textContent || '';
      }

      let norm = haystack.toLowerCase();
      norm = norm.normalize('NFD').replace(/[\u0300-\u036f]/g, '');

      const match = tokens.every(tok => norm.includes(tok));
      if (match) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    // Mostrar mensagem de nenhum resultado
    if (visibleCount === 0 && rows.length > 0 && tokens.length > 0) {
      const tr = document.createElement('tr');
      tr.className = 'no-results-row';
      const td = document.createElement('td');
      td.colSpan = 12;
      td.className = 'px-4 py-6 text-center text-gray-500';
      td.innerHTML = '<div class="flex flex-col items-center gap-2"><svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><span class="font-medium">🔍 Nenhum resultado encontrado</span><span class="text-xs">Tente buscar com outros termos</span></div>';
      tr.appendChild(td);
      tbody.appendChild(tr);
    }

    console.log(`✅ Busca concluída: ${visibleCount} de ${rows.length} linhas visíveis`);
    window.updateResultsCount(visibleCount, rows.length);
};

// Função para atualizar contador de resultados
window.updateResultsCount = function(visibleCount, totalCount) {
    const resultsCount = document.getElementById('resultsCount');
    if (resultsCount) {
      const resultText = visibleCount === totalCount 
        ? `${totalCount} toner(s) cadastrado(s)` 
        : `Mostrando ${visibleCount} de ${totalCount} toner(s)`;
      resultsCount.textContent = resultText;
    }
};

// Debounce helper
function debounce(fn, delay = 200) {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(null, args), delay);
    };
}

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
  console.log('🚀 Inicializando busca de toners...');
  
  // Bind live events
  const searchInput = document.getElementById('searchToners');
  const searchSelect = document.getElementById('searchColumn');
  const runSearch = debounce(() => window.searchToners(), 150);
  
  if (searchInput) {
    searchInput.addEventListener('input', runSearch);
    searchInput.addEventListener('keyup', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        window.searchToners();
      } else {
        runSearch();
      }
    });
  }
  
  if (searchSelect) {
    searchSelect.addEventListener('change', () => {
      window.searchToners();
      if (searchInput) searchInput.focus();
    });
  }
  
  const runBtn = document.getElementById('runSearchBtn');
  if (runBtn) {
    runBtn.addEventListener('click', () => {
      window.searchToners();
    });
  }
  
  const searchActionBtn = document.getElementById('searchActionBtn');
  if (searchActionBtn) {
    searchActionBtn.addEventListener('click', () => {
      window.searchToners();
    });
  }
  
  const clearBtn = document.getElementById('clearSearchBtn');
  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      window.clearSearch();
    });
  }

  // Evitar submit de qualquer formulário ao pressionar Enter no campo de busca
  document.addEventListener('keydown', (e) => {
    const active = document.activeElement;
    if (e.key === 'Enter' && active && active.id === 'searchToners') {
      e.preventDefault();
      window.searchToners();
    }
  }, true);

  // Primeiro cálculo do contador e estado inicial
  const initialRows = document.querySelectorAll('#tonersTbody tr').length;
  window.updateResultsCount(initialRows, initialRows);
  window.searchToners();
  
  console.log('✅ Busca de toners inicializada com sucesso!');
});
