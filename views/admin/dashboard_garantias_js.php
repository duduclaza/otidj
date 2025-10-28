// ===== DASHBOARD GARANTIAS =====
let garantiasCharts = {};

// Inicializar aba de Garantias
async function initGarantiasTab() {
  console.log('🛡️ Inicializando dashboard de Garantias...');
  
  // Carregar fornecedores no dropdown
  await carregarFornecedoresGarantias();
  
  // Definir datas padrão (últimos 30 dias)
  const hoje = new Date();
  const dataFinal = hoje.toISOString().split('T')[0];
  const dataInicial = new Date(hoje.setDate(hoje.getDate() - 30)).toISOString().split('T')[0];
  
  document.getElementById('dataInicialGarantias').value = dataInicial;
  document.getElementById('dataFinalGarantias').value = dataFinal;
  
  // Carregar dados
  await loadDashboardGarantias();
}

// Carregar fornecedores no filtro
async function carregarFornecedoresGarantias() {
  try {
    console.log('📡 Buscando fornecedores...');
    const response = await fetch('/garantias/fornecedores');
    const result = await response.json();
    
    console.log('✅ Fornecedores recebidos:', result);
    
    if (result.success && result.data) {
      const select = document.getElementById('filtroFornecedorGarantias');
      select.innerHTML = '<option value="">Todos os Fornecedores</option>';
      
      result.data.forEach(fornecedor => {
        const option = document.createElement('option');
        option.value = fornecedor.id;
        option.textContent = fornecedor.nome;
        select.appendChild(option);
      });
      
      console.log(`✅ ${result.data.length} fornecedores carregados no filtro`);
    } else {
      console.warn('⚠️ Nenhum fornecedor encontrado');
    }
  } catch (error) {
    console.error('❌ Erro ao carregar fornecedores:', error);
  }
}

// Carregar dados do dashboard
async function loadDashboardGarantias() {
  try {
    const fornecedor = document.getElementById('filtroFornecedorGarantias')?.value || '';
    const dataInicial = document.getElementById('dataInicialGarantias')?.value || '';
    const dataFinal = document.getElementById('dataFinalGarantias')?.value || '';
    
    console.log('📡 Buscando garantias com filtros:', { fornecedor, dataInicial, dataFinal });
    
    const response = await fetch('/garantias/list');
    const result = await response.json();
    
    console.log('✅ Resposta do servidor:', result);
    
    if (result.success && result.data) {
      // Filtrar dados
      let garantias = result.data;
      
      if (fornecedor) {
        garantias = garantias.filter(g => g.fornecedor_id == fornecedor);
      }
      
      if (dataInicial) {
        garantias = garantias.filter(g => g.created_at >= dataInicial);
      }
      
      if (dataFinal) {
        const dataFinalFim = dataFinal + ' 23:59:59';
        garantias = garantias.filter(g => g.created_at <= dataFinalFim);
      }
      
      console.log('✅ Garantias filtradas:', garantias.length);
      
      // Processar dados
      const dadosProcessados = processarDadosGarantias(garantias);
      
      // Atualizar cards
      atualizarCardsGarantias(dadosProcessados);
      
      // Atualizar gráficos
      atualizarGraficosGarantias(dadosProcessados);
      
    } else {
      console.error('❌ Erro ao carregar dados:', result.message || 'Sem dados');
    }
  } catch (error) {
    console.error('❌ Erro ao carregar dashboard de garantias:', error);
  }
}

// Processar dados das garantias
function processarDadosGarantias(garantias) {
  // Total de garantias
  const totalGarantias = garantias.length;
  
  // Quantidade total (soma das colunas total_quantidade de cada garantia)
  const quantidadeTotal = garantias.reduce((sum, g) => sum + (parseInt(g.total_quantidade) || 0), 0);
  
  // Valor total
  const valorTotal = garantias.reduce((sum, g) => sum + (parseFloat(g.valor_total) || 0), 0);
  
  // Quantidade por fornecedor (soma das quantidades)
  const porFornecedor = {};
  garantias.forEach(g => {
    if (!g.fornecedor_nome) return;
    if (!porFornecedor[g.fornecedor_nome]) {
      porFornecedor[g.fornecedor_nome] = 0;
    }
    porFornecedor[g.fornecedor_nome] += (parseInt(g.total_quantidade) || 0);
  });
  
  // Quantidade por mês
  const porMes = {};
  garantias.forEach(g => {
    if (!g.created_at) {
      console.warn('Garantia sem created_at:', g);
      return;
    }
    const mes = g.created_at.substring(0, 7); // YYYY-MM
    if (!porMes[mes]) {
      porMes[mes] = { quantidade: 0, valor: 0, count: 0 };
    }
    porMes[mes].quantidade += (parseInt(g.total_quantidade) || 0);
    porMes[mes].valor += (parseFloat(g.valor_total) || 0);
    porMes[mes].count += 1; // Contar número de garantias no mês
  });
  
  console.log('📅 Garantias por mês processadas:', porMes);
  
  // Quantidade por origem
  const porOrigem = {
    'Amostragem': 0,
    'Homologação': 0,
    'Em Campo': 0
  };
  garantias.forEach(g => {
    if (g.origem_garantia && porOrigem.hasOwnProperty(g.origem_garantia)) {
      porOrigem[g.origem_garantia] += (parseInt(g.total_quantidade) || 0);
    }
  });
  
  return {
    totalGarantias,
    quantidadeTotal,
    valorTotal,
    porFornecedor,
    porMes,
    porOrigem
  };
}

// Atualizar cards
function atualizarCardsGarantias(dados) {
  document.getElementById('totalGarantiasCard').textContent = dados.totalGarantias.toLocaleString('pt-BR');
  document.getElementById('quantidadeTotalCard').textContent = dados.quantidadeTotal.toLocaleString('pt-BR');
  document.getElementById('valorTotalCard').textContent = 'R$ ' + dados.valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Atualizar gráficos
function atualizarGraficosGarantias(dados) {
  // Gráfico 1: Quantidade por Fornecedor
  const ctxFornecedor = document.getElementById('garantiasFornecedorChart');
  if (ctxFornecedor) {
    if (garantiasCharts.fornecedor) {
      garantiasCharts.fornecedor.destroy();
    }
    
    const fornecedores = Object.keys(dados.porFornecedor).sort((a, b) => 
      dados.porFornecedor[b] - dados.porFornecedor[a]
    ).slice(0, 10); // Top 10
    
    const quantidades = fornecedores.map(f => dados.porFornecedor[f]);
    
    garantiasCharts.fornecedor = new Chart(ctxFornecedor, {
      type: 'bar',
      data: {
        labels: fornecedores,
        datasets: [{
          label: 'Quantidade',
          data: quantidades,
          backgroundColor: 'rgba(59, 130, 246, 0.8)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Quantidade: ' + context.parsed.y.toLocaleString('pt-BR');
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return value.toLocaleString('pt-BR');
              }
            }
          }
        }
      }
    });
  }
  
  // Gráfico 2: Garantias por Mês (Quantidade)
  const ctxMes = document.getElementById('garantiasMesChart');
  if (ctxMes) {
    if (garantiasCharts.mes) {
      garantiasCharts.mes.destroy();
    }
    
    const meses = Object.keys(dados.porMes).sort();
    const quantidadesMes = meses.map(m => dados.porMes[m].count); // Usar count em vez de quantidade
    
    console.log('📊 Criando gráfico de mês:', { meses, quantidadesMes });
    
    garantiasCharts.mes = new Chart(ctxMes, {
      type: 'line',
      data: {
        labels: meses.map(m => {
          const [ano, mes] = m.split('-');
          return `${mes}/${ano}`;
        }),
        datasets: [{
          label: 'Quantidade',
          data: quantidadesMes,
          backgroundColor: 'rgba(34, 197, 94, 0.2)',
          borderColor: 'rgba(34, 197, 94, 1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Quantidade: ' + context.parsed.y.toLocaleString('pt-BR');
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return value.toLocaleString('pt-BR');
              }
            }
          }
        }
      }
    });
  }
  
  // Gráfico 3: Valor por Mês
  const ctxValor = document.getElementById('garantiasValorChart');
  if (ctxValor) {
    if (garantiasCharts.valor) {
      garantiasCharts.valor.destroy();
    }
    
    const meses = Object.keys(dados.porMes).sort();
    const valoresMes = meses.map(m => dados.porMes[m].valor);
    
    garantiasCharts.valor = new Chart(ctxValor, {
      type: 'bar',
      data: {
        labels: meses.map(m => {
          const [ano, mes] = m.split('-');
          return `${mes}/${ano}`;
        }),
        datasets: [{
          label: 'Valor (R$)',
          data: valoresMes,
          backgroundColor: 'rgba(16, 185, 129, 0.8)',
          borderColor: 'rgba(16, 185, 129, 1)',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Valor: R$ ' + context.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return 'R$ ' + value.toLocaleString('pt-BR');
              }
            }
          }
        }
      }
    });
  }
  
  // Gráfico 4: Garantias por Origem
  const ctxOrigem = document.getElementById('garantiasOrigemChart');
  if (ctxOrigem) {
    if (garantiasCharts.origem) {
      garantiasCharts.origem.destroy();
    }
    
    const origens = Object.keys(dados.porOrigem);
    const quantidadesOrigem = origens.map(o => dados.porOrigem[o]);
    
    garantiasCharts.origem = new Chart(ctxOrigem, {
      type: 'doughnut',
      data: {
        labels: origens,
        datasets: [{
          data: quantidadesOrigem,
          backgroundColor: [
            'rgba(147, 51, 234, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(249, 115, 22, 0.8)'
          ],
          borderColor: [
            'rgba(147, 51, 234, 1)',
            'rgba(59, 130, 246, 1)',
            'rgba(249, 115, 22, 1)'
          ],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((context.parsed / total) * 100).toFixed(1);
                return context.label + ': ' + context.parsed.toLocaleString('pt-BR') + ' (' + percentage + '%)';
              }
            }
          }
        }
      }
    });
  }
}

// Limpar filtros
function clearFiltersGarantias() {
  document.getElementById('filtroFornecedorGarantias').value = '';
  
  const hoje = new Date();
  const dataFinal = hoje.toISOString().split('T')[0];
  const dataInicial = new Date(hoje.setDate(hoje.getDate() - 30)).toISOString().split('T')[0];
  
  document.getElementById('dataInicialGarantias').value = dataInicial;
  document.getElementById('dataFinalGarantias').value = dataFinal;
  
  loadDashboardGarantias();
}

// Funções de expansão dos gráficos
function expandirGraficoGarantiasFornecedor() {
  expandirGraficoGenerico('garantiasFornecedorChart', 'Quantidade por Fornecedor');
}

function expandirGraficoGarantiasMes() {
  expandirGraficoGenerico('garantiasMesChart', 'Garantias por Mês');
}

function expandirGraficoGarantiasValor() {
  expandirGraficoGenerico('garantiasValorChart', 'Valor por Mês (R$)');
}

function expandirGraficoGarantiasOrigem() {
  expandirGraficoGenerico('garantiasOrigemChart', 'Garantias por Origem');
}

// Função genérica para expandir gráfico
function expandirGraficoGenerico(canvasId, titulo) {
  const modal = document.getElementById('modalExpandidoRetornados');
  const canvas = document.getElementById(canvasId);
  
  if (!modal || !canvas) return;
  
  // Clonar o canvas
  const canvasClone = canvas.cloneNode(true);
  canvasClone.id = canvasId + '_expandido';
  canvasClone.style.maxHeight = '400px'; // Ajustado de 600px para 400px
  
  // Limpar conteúdo anterior
  const modalBody = modal.querySelector('#modalContentRetornados');
  const existingCanvas = modalBody.querySelector('canvas');
  if (existingCanvas) {
    existingCanvas.remove();
  }
  
  // Adicionar título
  let tituloEl = modalBody.querySelector('h2');
  if (!tituloEl) {
    tituloEl = document.createElement('h2');
    tituloEl.className = 'text-2xl font-bold text-white mb-6';
    modalBody.insertBefore(tituloEl, modalBody.firstChild.nextSibling.nextSibling);
  }
  tituloEl.textContent = '🛡️ ' + titulo;
  
  // Adicionar canvas
  modalBody.appendChild(canvasClone);
  
  // Mostrar modal
  modal.classList.remove('hidden');
  setTimeout(() => {
    modalBody.classList.remove('scale-95', 'opacity-0');
    modalBody.classList.add('scale-100', 'opacity-100');
  }, 10);
  
  // Recriar o gráfico no canvas expandido
  const chartOriginal = garantiasCharts[canvasId.replace('Chart', '').replace('garantias', '').toLowerCase()];
  if (chartOriginal) {
    const ctx = canvasClone.getContext('2d');
    new Chart(ctx, {
      type: chartOriginal.config.type,
      data: chartOriginal.config.data,
      options: {
        ...chartOriginal.config.options,
        maintainAspectRatio: false
      }
    });
  }
}
