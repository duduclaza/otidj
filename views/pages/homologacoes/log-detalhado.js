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

/**
 * Adicionar botões de log detalhado aos cards do Kanban
 */
function adicionarBotoesLogDetalhado() {
    // Aguardar carregamento do DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Observar mudanças no Kanban para adicionar botões dinamicamente
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && node.classList.contains('kanban-card')) {
                            adicionarBotoesAoCard(node);
                        }
                    });
                }
            });
        });
        
        // Observar mudanças no container do Kanban
        const kanbanContainer = document.querySelector('.kanban-container');
        if (kanbanContainer) {
            observer.observe(kanbanContainer, { childList: true, subtree: true });
        }
        
        // Adicionar botões aos cards existentes
        document.querySelectorAll('.kanban-card').forEach(adicionarBotoesAoCard);
    });
}

/**
 * Adicionar botões ao card individual
 */
function adicionarBotoesAoCard(card) {
    const homologacaoId = card.dataset.homologacaoId;
    const etapa = card.dataset.etapa;
    
    if (!homologacaoId || !etapa) return;
    
    // Verificar se já tem botões
    if (card.querySelector('.botoes-log-detalhado')) return;
    
    const botoesContainer = document.createElement('div');
    botoesContainer.className = 'botoes-log-detalhado';
    botoesContainer.style.cssText = `
        display: flex;
        gap: 8px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #e5e7eb;
    `;
    
    // Botão para registrar dados da etapa
    const btnDados = document.createElement('button');
    btnDados.innerHTML = '📝 Dados';
    btnDados.title = 'Registrar dados da etapa';
    btnDados.style.cssText = `
        flex: 1;
        padding: 6px 12px;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        font-weight: bold;
    `;
    btnDados.onclick = () => abrirModalDadosEtapa(homologacaoId, etapa);
    
    // Botão para relatório completo
    const btnRelatorio = document.createElement('button');
    btnRelatorio.innerHTML = '📊 Relatório';
    btnRelatorio.title = 'Ver relatório completo';
    btnRelatorio.style.cssText = `
        flex: 1;
        padding: 6px 12px;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        font-weight: bold;
    `;
    btnRelatorio.onclick = () => abrirRelatorioCompleto(homologacaoId);
    
    botoesContainer.appendChild(btnDados);
    botoesContainer.appendChild(btnRelatorio);
    card.appendChild(botoesContainer);
}

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

// Inicializar sistema de log detalhado
adicionarBotoesLogDetalhado();
