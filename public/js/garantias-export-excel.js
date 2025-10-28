// ===== EXPORTAÇÃO EXCEL DE GARANTIAS =====
// Requer biblioteca XLSX.js (SheetJS)

// Função principal de exportação
async function exportarExcel() {
    try {
        console.log('📊 Iniciando exportação para Excel...');
        
        // Verificar se estamos na página correta
        if (!window.garantias) {
            console.warn('⚠️ Dados de garantias não disponíveis');
            alert('⚠️ Carregue os dados antes de exportar!');
            return;
        }
        
        // Verificar se XLSX está disponível
        if (typeof XLSX === 'undefined') {
            alert('❌ Biblioteca XLSX não carregada. Recarregue a página e tente novamente.');
            return;
        }
        
        // Verificar se a função carregarConfigColunas existe
        if (typeof carregarConfigColunas !== 'function') {
            console.error('❌ Função carregarConfigColunas não encontrada');
            alert('❌ Erro ao carregar configuração. Recarregue a página.');
            return;
        }
        
        // Carregar configuração de colunas
        const colunasConfig = carregarConfigColunas();
        const colunasVisiveis = colunasConfig.filter(c => c.visivel);
        
        if (colunasVisiveis.length === 0) {
            alert('⚠️ Selecione pelo menos uma coluna para exportar!');
            return;
        }
        
        // Buscar dados
        const garantias = window.garantias || [];
        
        if (garantias.length === 0) {
            alert('⚠️ Nenhuma garantia encontrada para exportar!');
            return;
        }
        
        console.log(`📝 Exportando ${garantias.length} garantias com ${colunasVisiveis.length} colunas`);
        
        // Preparar dados para Excel
        const dados = prepararDadosParaExcel(garantias, colunasVisiveis);
        
        // Criar planilha
        const ws = XLSX.utils.aoa_to_sheet(dados);
        
        // Aplicar estilos (larguras de coluna)
        const wscols = colunasVisiveis.map(col => {
            switch(col.id) {
                case 'id': return { wch: 8 };
                case 'fornecedor': return { wch: 25 };
                case 'filial': return { wch: 20 };
                case 'origem': return { wch: 15 };
                case 'nfs': return { wch: 20 };
                case 'serie': return { wch: 20 };
                case 'lote': return { wch: 15 };
                case 'ticket_os': return { wch: 20 };
                case 'ticket_interno': return { wch: 20 };
                case 'produto': return { wch: 30 };
                case 'quantidade': return { wch: 10 };
                case 'status': return { wch: 25 };
                case 'defeito': return { wch: 40 };
                case 'itens': return { wch: 10 };
                case 'valor': return { wch: 15 };
                case 'anexos': return { wch: 10 };
                case 'data': return { wch: 18 };
                default: return { wch: 15 };
            }
        });
        ws['!cols'] = wscols;
        
        // Criar workbook
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Garantias');
        
        // Gerar nome do arquivo
        const dataHora = new Date().toLocaleString('pt-BR').replace(/[/:]/g, '-').replace(', ', '_');
        const nomeArquivo = `Garantias_${dataHora}.xlsx`;
        
        // Baixar arquivo
        XLSX.writeFile(wb, nomeArquivo);
        
        console.log('✅ Exportação concluída:', nomeArquivo);
        
        // Mostrar notificação se a função existir
        if (typeof mostrarNotificacao === 'function') {
            mostrarNotificacao(`✅ Excel exportado com sucesso! (${garantias.length} registros)`, 'success');
        } else {
            alert(`✅ Excel exportado com sucesso! (${garantias.length} registros)`);
        }
        
    } catch (error) {
        console.error('❌ Erro ao exportar Excel:', error);
        alert('❌ Erro ao exportar Excel: ' + error.message);
    }
}

// Preparar dados para Excel
function prepararDadosParaExcel(garantias, colunasVisiveis) {
    // Criar array com cabeçalhos
    const dados = [];
    const cabecalhos = colunasVisiveis.map(col => col.nome);
    dados.push(cabecalhos);
    
    // Adicionar dados
    garantias.forEach(garantia => {
        const linha = [];
        
        colunasVisiveis.forEach(col => {
            let valor = '';
            
            switch(col.id) {
                case 'id':
                    valor = `#${garantia.id}`;
                    break;
                    
                case 'fornecedor':
                    valor = garantia.fornecedor_nome || 'N/A';
                    break;
                    
                case 'filial':
                    valor = garantia.filial_nome || '-';
                    break;
                    
                case 'origem':
                    valor = garantia.origem_garantia || '-';
                    break;
                    
                case 'nfs':
                    const nfs = [];
                    if (garantia.numero_nf_compras) nfs.push(`C: ${garantia.numero_nf_compras}`);
                    if (garantia.numero_nf_remessa_simples) nfs.push(`RS: ${garantia.numero_nf_remessa_simples}`);
                    if (garantia.numero_nf_remessa_devolucao) nfs.push(`RD: ${garantia.numero_nf_remessa_devolucao}`);
                    valor = nfs.join(' | ') || '-';
                    break;
                    
                case 'serie':
                    valor = garantia.numero_serie || '-';
                    break;
                    
                case 'lote':
                    valor = garantia.numero_lote || '-';
                    break;
                    
                case 'ticket_os':
                    valor = garantia.numero_ticket_os || '-';
                    break;
                    
                case 'ticket_interno':
                    valor = garantia.numero_ticket_interno || '-';
                    break;
                    
                case 'produto':
                    valor = garantia.produtos_lista || '-';
                    break;
                    
                case 'quantidade':
                    valor = parseInt(garantia.total_quantidade) || 0;
                    break;
                    
                case 'status':
                    valor = garantia.status || '-';
                    break;
                    
                case 'defeito':
                    valor = garantia.descricao_defeito || '-';
                    break;
                    
                case 'itens':
                    valor = parseInt(garantia.total_itens) || 0;
                    break;
                    
                case 'valor':
                    const valorNum = parseFloat(garantia.valor_total) || 0;
                    valor = `R$ ${valorNum.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                    break;
                    
                case 'anexos':
                    valor = parseInt(garantia.total_anexos) || 0;
                    break;
                    
                case 'data':
                    if (garantia.created_at) {
                        const data = new Date(garantia.created_at);
                        valor = data.toLocaleString('pt-BR');
                    } else {
                        valor = '-';
                    }
                    break;
                    
                default:
                    valor = '-';
            }
            
            linha.push(valor);
        });
        
        dados.push(linha);
    });
    
    return dados;
}

// Carregar biblioteca XLSX se não estiver carregada
function carregarXLSXLib() {
    // Verificar se estamos na página de garantias
    const garantiasTable = document.getElementById('garantiasTable');
    if (!garantiasTable) {
        console.log('⚠️ Página de garantias não detectada, pulando carregamento XLSX');
        return;
    }
    
    if (typeof XLSX === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
        script.onload = () => console.log('✅ Biblioteca XLSX carregada');
        script.onerror = () => console.error('❌ Erro ao carregar biblioteca XLSX');
        document.head.appendChild(script);
    }
}

// Carregar XLSX ao carregar a página
document.addEventListener('DOMContentLoaded', carregarXLSXLib);
