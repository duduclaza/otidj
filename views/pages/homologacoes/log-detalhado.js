/**
 * Sistema de Log Detalhado para Homologações
 * Integração com Kanban existente
 */

// Variáveis globais
let homologacaoAtual = null;
let etapaAtual = null;

/**
 * Abrir modal para registrar dados da etapa
 */
function abrirModalDadosEtapa(homologacaoId, etapa) {
    homologacaoAtual = homologacaoId;
    etapaAtual = etapa;
    
    // Criar modal dinamicamente
    const modal = criarModalDadosEtapa(etapa);
    document.body.appendChild(modal);
    
    // Mostrar modal
    modal.style.display = 'flex';
    
    // Carregar dados existentes da etapa (se houver)
    carregarDadosEtapa(homologacaoId, etapa);
}

/**
 * Criar HTML do modal para dados da etapa
 */
function criarModalDadosEtapa(etapa) {
    const modal = document.createElement('div');
    modal.id = 'modalDadosEtapa';
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;
    
    const campos = obterCamposEtapa(etapa);
    const tituloEtapa = obterTituloEtapa(etapa);
    
    modal.innerHTML = `
        <div class="modal-content" style="
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        ">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2 style="margin: 0; color: #1e40af; font-size: 24px;">
                    📋 ${tituloEtapa}
                </h2>
                <button onclick="fecharModalDadosEtapa()" style="
                    background: none;
                    border: none;
                    font-size: 24px;
                    cursor: pointer;
                    color: #6b7280;
                    padding: 5px;
                ">×</button>
            </div>
            
            <form id="formDadosEtapa" style="display: grid; gap: 20px;">
                ${campos.map(campo => `
                    <div>
                        <label style="
                            display: block;
                            font-weight: bold;
                            color: #374151;
                            margin-bottom: 8px;
                        ">${campo.label}:</label>
                        ${campo.tipo === 'textarea' ? 
                            `<textarea 
                                name="${campo.nome}" 
                                rows="4" 
                                placeholder="${campo.placeholder || ''}"
                                style="
                                    width: 100%;
                                    padding: 12px;
                                    border: 2px solid #e5e7eb;
                                    border-radius: 8px;
                                    font-family: inherit;
                                    resize: vertical;
                                "
                            ></textarea>` :
                            `<input 
                                type="${campo.tipo}" 
                                name="${campo.nome}" 
                                placeholder="${campo.placeholder || ''}"
                                style="
                                    width: 100%;
                                    padding: 12px;
                                    border: 2px solid #e5e7eb;
                                    border-radius: 8px;
                                    font-family: inherit;
                                "
                            />`
                        }
                    </div>
                `).join('')}
                
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" onclick="fecharModalDadosEtapa()" style="
                        padding: 12px 24px;
                        background: #6b7280;
                        color: white;
                        border: none;
                        border-radius: 8px;
                        cursor: pointer;
                        font-weight: bold;
                    ">Cancelar</button>
                    <button type="submit" style="
                        padding: 12px 24px;
                        background: #3b82f6;
                        color: white;
                        border: none;
                        border-radius: 8px;
                        cursor: pointer;
                        font-weight: bold;
                    ">💾 Salvar Dados</button>
                </div>
            </form>
        </div>
    `;
    
    // Adicionar event listener para o formulário
    modal.querySelector('#formDadosEtapa').addEventListener('submit', salvarDadosEtapa);
    
    return modal;
}

/**
 * Obter campos específicos para cada etapa
 */
function obterCamposEtapa(etapa) {
    const campos = {
        'recebido': [
            { nome: 'data_recebimento', label: 'Data de Recebimento', tipo: 'datetime-local' },
            { nome: 'condicoes_material', label: 'Condições do Material', tipo: 'textarea', placeholder: 'Descreva as condições em que o material foi recebido...' },
            { nome: 'conferencia_realizada', label: 'Conferência Realizada', tipo: 'textarea', placeholder: 'Descreva a conferência realizada...' },
            { nome: 'observacoes', label: 'Observações Gerais', tipo: 'textarea', placeholder: 'Observações adicionais sobre o recebimento...' }
        ],
        'em_analise': [
            { nome: 'data_inicio_analise', label: 'Data Início da Análise', tipo: 'datetime-local' },
            { nome: 'testes_realizados', label: 'Testes Realizados', tipo: 'textarea', placeholder: 'Descreva os testes realizados no material...' },
            { nome: 'resultados_testes', label: 'Resultados dos Testes', tipo: 'textarea', placeholder: 'Descreva os resultados obtidos...' },
            { nome: 'responsavel_analise', label: 'Responsável pela Análise', tipo: 'text', placeholder: 'Nome do responsável técnico...' },
            { nome: 'observacoes', label: 'Observações Técnicas', tipo: 'textarea', placeholder: 'Observações técnicas da análise...' }
        ],
        'em_homologacao': [
            { nome: 'data_inicio_homologacao', label: 'Data Início da Homologação', tipo: 'datetime-local' },
            { nome: 'criterios_avaliados', label: 'Critérios Avaliados', tipo: 'textarea', placeholder: 'Liste os critérios avaliados na homologação...' },
            { nome: 'aprovacao_tecnica', label: 'Aprovação Técnica', tipo: 'select', opcoes: ['Pendente', 'Aprovado', 'Reprovado', 'Aprovado com Ressalvas'] },
            { nome: 'recomendacoes', label: 'Recomendações', tipo: 'textarea', placeholder: 'Recomendações para uso do material...' },
            { nome: 'observacoes', label: 'Observações da Homologação', tipo: 'textarea', placeholder: 'Observações sobre o processo de homologação...' }
        ],
        'aprovado': [
            { nome: 'data_aprovacao', label: 'Data da Aprovação', tipo: 'datetime-local' },
            { nome: 'justificativa', label: 'Justificativa da Aprovação', tipo: 'textarea', placeholder: 'Justifique a aprovação do material...' },
            { nome: 'restricoes_uso', label: 'Restrições de Uso', tipo: 'textarea', placeholder: 'Descreva eventuais restrições...' },
            { nome: 'validade_aprovacao', label: 'Validade da Aprovação', tipo: 'date' },
            { nome: 'observacoes', label: 'Observações Finais', tipo: 'textarea', placeholder: 'Observações finais sobre a aprovação...' }
        ],
        'reprovado': [
            { nome: 'data_reprovacao', label: 'Data da Reprovação', tipo: 'datetime-local' },
            { nome: 'justificativa', label: 'Justificativa da Reprovação', tipo: 'textarea', placeholder: 'Justifique a reprovação do material...' },
            { nome: 'nao_conformidades', label: 'Não Conformidades Encontradas', tipo: 'textarea', placeholder: 'Liste as não conformidades...' },
            { nome: 'acoes_recomendadas', label: 'Ações Recomendadas', tipo: 'textarea', placeholder: 'Recomendações para correção...' },
            { nome: 'observacoes', label: 'Observações da Reprovação', tipo: 'textarea', placeholder: 'Observações sobre a reprovação...' }
        ]
    };
    
    return campos[etapa] || [];
}

/**
 * Obter título da etapa
 */
function obterTituloEtapa(etapa) {
    const titulos = {
        'recebido': 'Dados do Recebimento',
        'em_analise': 'Dados da Análise Técnica',
        'em_homologacao': 'Dados da Homologação',
        'aprovado': 'Dados da Aprovação',
        'reprovado': 'Dados da Reprovação'
    };
    
    return titulos[etapa] || 'Dados da Etapa';
}

/**
 * Carregar dados existentes da etapa
 */
async function carregarDadosEtapa(homologacaoId, etapa) {
    try {
        const response = await fetch(`/homologacoes/${homologacaoId}/dados-etapa/${etapa}`);
        if (response.ok) {
            const dados = await response.json();
            
            // Preencher formulário com dados existentes
            const form = document.getElementById('formDadosEtapa');
            Object.keys(dados).forEach(campo => {
                const input = form.querySelector(`[name="${campo}"]`);
                if (input) {
                    input.value = dados[campo];
                }
            });
        }
    } catch (error) {
        console.log('Nenhum dado anterior encontrado para esta etapa');
    }
}

/**
 * Salvar dados da etapa
 */
async function salvarDadosEtapa(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Converter FormData para objeto
    const dados = {};
    for (let [key, value] of formData.entries()) {
        if (value.trim()) {
            dados[key] = value;
        }
    }
    
    try {
        const response = await fetch('/homologacoes/registrar-dados-etapa', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                homologacao_id: homologacaoAtual,
                etapa: etapaAtual,
                dados: JSON.stringify(dados)
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacao('Dados salvos com sucesso!', 'success');
            fecharModalDadosEtapa();
            
            // Atualizar interface do Kanban se necessário
            if (typeof atualizarKanban === 'function') {
                atualizarKanban();
            }
        } else {
            mostrarNotificacao('Erro: ' + result.message, 'error');
        }
        
    } catch (error) {
        mostrarNotificacao('Erro ao salvar dados: ' + error.message, 'error');
    }
}

/**
 * Fechar modal de dados da etapa
 */
function fecharModalDadosEtapa() {
    const modal = document.getElementById('modalDadosEtapa');
    if (modal) {
        modal.remove();
    }
    homologacaoAtual = null;
    etapaAtual = null;
}

/**
 * Abrir modal com logs da homologação
 */
async function abrirModalLogs(homologacaoId) {
    try {
        // Buscar logs da homologação
        const response = await fetch(`/homologacoes/${homologacaoId}/logs`);
        const result = await response.json();
        
        if (!result.success) {
            mostrarNotificacao('Erro ao carregar logs: ' + result.message, 'error');
            return;
        }
        
        // Criar modal de logs
        const modal = criarModalLogs(result.logs, result.homologacao);
        document.body.appendChild(modal);
        modal.style.display = 'flex';
        
    } catch (error) {
        mostrarNotificacao('Erro ao carregar logs: ' + error.message, 'error');
    }
}

/**
 * Criar modal de logs
 */
function criarModalLogs(logs, homologacao) {
    const modal = document.createElement('div');
    modal.id = 'modalLogs';
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;
    
    modal.innerHTML = `
        <div class="modal-content" style="
            background: white;
            border-radius: 12px;
            padding: 0;
            max-width: 900px;
            width: 95%;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
        ">
            <!-- Header -->
            <div style="
                background: linear-gradient(135deg, #8b5cf6, #7c3aed);
                color: white;
                padding: 20px 30px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            ">
                <div>
                    <h2 style="margin: 0; font-size: 24px;">📜 Histórico de Logs</h2>
                    <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;">
                        ${homologacao.cod_referencia} - ${homologacao.descricao.substring(0, 50)}...
                    </p>
                </div>
                <button onclick="fecharModalLogs()" style="
                    background: rgba(255,255,255,0.2);
                    border: none;
                    color: white;
                    font-size: 24px;
                    cursor: pointer;
                    padding: 8px 12px;
                    border-radius: 6px;
                    transition: background 0.2s;
                " onmouseover="this.style.background='rgba(255,255,255,0.3)'" 
                   onmouseout="this.style.background='rgba(255,255,255,0.2)'">×</button>
            </div>
            
            <!-- Conteúdo dos logs -->
            <div style="
                flex: 1;
                overflow-y: auto;
                padding: 20px 30px;
                background: #f8fafc;
            ">
                ${gerarHTMLLogs(logs)}
            </div>
            
            <!-- Footer -->
            <div style="
                padding: 15px 30px;
                background: #f1f5f9;
                border-top: 1px solid #e2e8f0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            ">
                <span style="color: #64748b; font-size: 14px;">
                    📊 Total de ${logs.length} registro(s) de log
                </span>
                <button onclick="exportarLogs(${homologacao.id})" style="
                    padding: 8px 16px;
                    background: #3b82f6;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: bold;
                ">📥 Exportar Logs</button>
            </div>
        </div>
    `;
    
    return modal;
}

/**
 * Gerar HTML dos logs
 */
function gerarHTMLLogs(logs) {
    if (!logs || logs.length === 0) {
        return `
            <div style="
                text-align: center;
                padding: 40px;
                color: #64748b;
            ">
                <div style="font-size: 48px; margin-bottom: 16px;">📝</div>
                <h3 style="margin: 0 0 8px 0; color: #374151;">Nenhum log encontrado</h3>
                <p style="margin: 0;">Esta homologação ainda não possui registros de log.</p>
            </div>
        `;
    }
    
    return logs.map((log, index) => {
        const dataFormatada = new Date(log.data_acao).toLocaleString('pt-BR');
        const tempoEtapa = log.tempo_etapa ? formatarTempo(log.tempo_etapa) : null;
        
        return `
            <div style="
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                margin-bottom: 16px;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            ">
                <!-- Header do log -->
                <div style="
                    background: ${getCorEtapa(log.etapa_nova)};
                    color: white;
                    padding: 12px 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="
                            background: rgba(255,255,255,0.2);
                            padding: 4px 8px;
                            border-radius: 12px;
                            font-size: 12px;
                            font-weight: bold;
                        ">#${index + 1}</span>
                        <span style="font-weight: bold; font-size: 16px;">
                            ${log.acao_realizada}
                        </span>
                        ${tempoEtapa ? `<span style="
                            background: rgba(255,255,255,0.2);
                            padding: 2px 8px;
                            border-radius: 10px;
                            font-size: 11px;
                        ">⏱️ ${tempoEtapa}</span>` : ''}
                    </div>
                    <span style="font-size: 14px; opacity: 0.9;">
                        ${dataFormatada}
                    </span>
                </div>
                
                <!-- Conteúdo do log -->
                <div style="padding: 16px 20px;">
                    <div style="
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 12px;
                        margin-bottom: 12px;
                    ">
                        <div>
                            <strong style="color: #374151;">👤 Responsável:</strong><br>
                            <span style="color: #64748b;">${log.usuario_nome}</span>
                        </div>
                        <div>
                            <strong style="color: #374151;">🔄 Etapa:</strong><br>
                            <span style="color: #64748b;">${formatarNomeEtapa(log.etapa_nova)}</span>
                        </div>
                        ${log.etapa_anterior ? `
                        <div>
                            <strong style="color: #374151;">⬅️ Etapa Anterior:</strong><br>
                            <span style="color: #64748b;">${formatarNomeEtapa(log.etapa_anterior)}</span>
                        </div>
                        ` : ''}
                    </div>
                    
                    ${log.detalhes_acao ? `
                    <div style="margin-bottom: 12px;">
                        <strong style="color: #374151;">📋 Detalhes:</strong><br>
                        <span style="color: #64748b;">${log.detalhes_acao}</span>
                    </div>
                    ` : ''}
                    
                    ${log.observacoes ? `
                    <div style="
                        background: #f8fafc;
                        border: 1px solid #e2e8f0;
                        border-radius: 6px;
                        padding: 12px;
                        margin-top: 12px;
                    ">
                        <strong style="color: #374151;">💬 Observações:</strong><br>
                        <span style="color: #64748b;">${log.observacoes.replace(/\n/g, '<br>')}</span>
                    </div>
                    ` : ''}
                    
                    ${log.dados_etapa ? gerarDadosEtapaHTML(log.dados_etapa) : ''}
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Gerar HTML dos dados específicos da etapa
 */
function gerarDadosEtapaHTML(dadosJson) {
    try {
        const dados = JSON.parse(dadosJson);
        if (!dados || Object.keys(dados).length === 0) return '';
        
        const campos = Object.entries(dados)
            .filter(([key, value]) => value && key !== 'observacoes')
            .map(([key, value]) => `
                <div style="display: flex; margin-bottom: 6px;">
                    <strong style="min-width: 150px; color: #4b5563;">${formatarNomeCampoLog(key)}:</strong>
                    <span style="color: #64748b; flex: 1;">${value}</span>
                </div>
            `).join('');
            
        if (!campos) return '';
        
        return `
            <div style="
                background: #eff6ff;
                border: 1px solid #bfdbfe;
                border-radius: 6px;
                padding: 12px;
                margin-top: 12px;
            ">
                <strong style="color: #1e40af; display: block; margin-bottom: 8px;">📊 Dados Registrados:</strong>
                ${campos}
            </div>
        `;
    } catch (e) {
        return '';
    }
}

/**
 * Obter cor da etapa
 */
function getCorEtapa(etapa) {
    const cores = {
        'aguardando_recebimento': '#f59e0b',
        'recebido': '#3b82f6',
        'em_analise': '#8b5cf6',
        'em_homologacao': '#06b6d4',
        'aprovado': '#10b981',
        'reprovado': '#ef4444'
    };
    return cores[etapa] || '#6b7280';
}

/**
 * Formatar nome da etapa
 */
function formatarNomeEtapa(etapa) {
    const nomes = {
        'aguardando_recebimento': 'Aguardando Recebimento',
        'recebido': 'Recebido',
        'em_analise': 'Em Análise',
        'em_homologacao': 'Em Homologação',
        'aprovado': 'Aprovado',
        'reprovado': 'Reprovado'
    };
    return nomes[etapa] || etapa;
}

/**
 * Formatar nome do campo para log
 */
function formatarNomeCampoLog(campo) {
    const nomes = {
        'data_recebimento': 'Data Recebimento',
        'condicoes_material': 'Condições do Material',
        'conferencia_realizada': 'Conferência',
        'data_inicio_analise': 'Início Análise',
        'testes_realizados': 'Testes Realizados',
        'resultados_testes': 'Resultados',
        'responsavel_analise': 'Responsável',
        'data_inicio_homologacao': 'Início Homologação',
        'criterios_avaliados': 'Critérios',
        'aprovacao_tecnica': 'Aprovação Técnica',
        'recomendacoes': 'Recomendações',
        'data_aprovacao': 'Data Aprovação',
        'data_reprovacao': 'Data Reprovação',
        'justificativa': 'Justificativa',
        'restricoes_uso': 'Restrições',
        'validade_aprovacao': 'Validade',
        'nao_conformidades': 'Não Conformidades',
        'acoes_recomendadas': 'Ações Recomendadas'
    };
    return nomes[campo] || campo.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

/**
 * Formatar tempo em minutos
 */
function formatarTempo(minutos) {
    if (minutos < 60) {
        return `${minutos}min`;
    }
    
    const horas = Math.floor(minutos / 60);
    const mins = minutos % 60;
    
    if (horas < 24) {
        return mins > 0 ? `${horas}h ${mins}min` : `${horas}h`;
    }
    
    const dias = Math.floor(horas / 24);
    const horasRestantes = horas % 24;
    
    return `${dias}d ${horasRestantes}h`;
}

/**
 * Fechar modal de logs
 */
function fecharModalLogs() {
    const modal = document.getElementById('modalLogs');
    if (modal) {
        modal.remove();
    }
}

/**
 * Exportar logs
 */
function exportarLogs(homologacaoId) {
    const url = `/homologacoes/${homologacaoId}/logs/export`;
    window.open(url, '_blank');
}

/**
 * Abrir relatório completo
 */
function abrirRelatorioCompleto(homologacaoId) {
    const url = `/homologacoes/${homologacaoId}/relatorio`;
    window.open(url, '_blank', 'width=1200,height=800,scrollbars=yes');
}

/**
 * Mostrar notificação
 */
function mostrarNotificacao(mensagem, tipo = 'info') {
    // Remover notificação anterior
    const existente = document.getElementById('notificacao-log');
    if (existente) {
        existente.remove();
    }
    
    const notificacao = document.createElement('div');
    notificacao.id = 'notificacao-log';
    notificacao.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        background: ${tipo === 'success' ? '#10b981' : tipo === 'error' ? '#ef4444' : '#3b82f6'};
    `;
    
    notificacao.textContent = mensagem;
    document.body.appendChild(notificacao);
    
    // Remover após 3 segundos
    setTimeout(() => {
        notificacao.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notificacao.remove(), 300);
    }, 3000);
}

// Funções de log detalhado já integradas na página principal

// Adicionar estilos CSS para animações
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
