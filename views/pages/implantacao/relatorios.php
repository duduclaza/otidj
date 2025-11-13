<?php
// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>

<section class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📈 Relatórios - Gestão de Implantação</h1>
            <p class="text-gray-600 mt-2">Analytics avançado e relatórios personalizáveis para tomada de decisão</p>
        </div>
    </div>

    <!-- Banner Principal -->
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: linear-gradient(45deg, #fff 25%, transparent 25%), linear-gradient(-45deg, #fff 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #fff 75%), linear-gradient(-45deg, transparent 75%, #fff 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;"></div>
        </div>
        
        <div class="relative p-8 md:p-12">
            <div class="max-w-5xl mx-auto">
                <div class="flex items-center gap-3 mb-4 justify-center">
                    <span class="px-4 py-2 bg-emerald-400 text-emerald-900 text-sm font-bold rounded-full">
                        🎯 TRIAL EM BREVE
                    </span>
                    <span class="px-4 py-2 bg-teal-400 text-teal-900 text-sm font-bold rounded-full">
                        💎 BUSINESS INTELLIGENCE
                    </span>
                </div>
                
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-4 text-center">
                    Relatórios Inteligentes & Dashboards
                </h2>
                
                <p class="text-xl text-emerald-100 mb-8 text-center max-w-3xl mx-auto">
                    Transforme dados em insights acionáveis com nosso sistema de BI completo para gestão de implantações
                </p>
                
                <!-- Grid de Métricas -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-4xl font-bold text-white mb-1">50+</div>
                        <div class="text-emerald-100 text-sm">Relatórios Prontos</div>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-4xl font-bold text-white mb-1">∞</div>
                        <div class="text-emerald-100 text-sm">Customizações</div>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-4xl font-bold text-white mb-1">10+</div>
                        <div class="text-emerald-100 text-sm">Formatos Export</div>
                    </div>
                    <div class="bg-white bg-opacity-15 backdrop-blur-sm rounded-xl p-4 text-center border border-white border-opacity-20">
                        <div class="text-4xl font-bold text-white mb-1">⚡</div>
                        <div class="text-emerald-100 text-sm">Tempo Real</div>
                    </div>
                </div>
                
                <div class="text-center mb-6">
                    <div class="flex items-baseline justify-center gap-2 mb-2">
                        <span class="text-6xl font-bold text-white">R$ 700</span>
                        <span class="text-2xl text-emerald-200">/mês</span>
                    </div>
                    <p class="text-emerald-200 text-lg">Módulo completo de Gestão de Implantação</p>
                </div>
                
                <div class="flex gap-4 justify-center flex-wrap">
                    <button disabled class="bg-white text-emerald-700 px-8 py-4 rounded-xl font-bold text-lg shadow-2xl opacity-60 cursor-not-allowed">
                        🔒 Trial Em Breve
                    </button>
                    <button disabled class="bg-emerald-500 bg-opacity-30 text-white border-2 border-white px-8 py-4 rounded-xl font-semibold text-lg opacity-60 cursor-not-allowed">
                        Ver Demo Interativa
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tipos de Relatórios -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-blue-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-blue-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Relatórios Operacionais</h3>
            </div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li>✓ Status de Implantações</li>
                <li>✓ Ordens de Serviço Abertas</li>
                <li>✓ Performance por Técnico</li>
                <li>✓ Taxa de Conclusão (SLA)</li>
                <li>✓ Retrabalhos e Problemas</li>
            </ul>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-purple-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-purple-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Relatórios Financeiros</h3>
            </div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li>✓ Custos por Implantação</li>
                <li>✓ Rentabilidade por Cliente</li>
                <li>✓ Forecast de Receita</li>
                <li>✓ Análise de Desvios</li>
                <li>✓ ROI de Projetos</li>
            </ul>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-green-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-green-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Relatórios Estratégicos</h3>
            </div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li>✓ KPIs Executivos</li>
                <li>✓ Análise de Tendências</li>
                <li>✓ Benchmarking</li>
                <li>✓ Capacidade vs Demanda</li>
                <li>✓ Previsões Inteligentes (IA)</li>
            </ul>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-orange-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-orange-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Relatórios de Qualidade</h3>
            </div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li>✓ NPS de Implantação</li>
                <li>✓ Satisfação do Cliente</li>
                <li>✓ Tempo Médio de Conclusão</li>
                <li>✓ Taxa de Sucesso</li>
                <li>✓ Não Conformidades</li>
            </ul>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-red-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-red-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Alertas e Exceções</h3>
            </div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li>✓ Atrasos Críticos</li>
                <li>✓ Estouro de Orçamento</li>
                <li>✓ Risco de SLA</li>
                <li>✓ Gargalos Operacionais</li>
                <li>✓ Anomalias Detectadas</li>
            </ul>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-lg border-t-4 border-indigo-600 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-indigo-100 rounded-lg p-3">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Relatórios Customizados</h3>
            </div>
            <ul class="space-y-2 text-sm text-gray-700">
                <li>✓ Crie seus próprios relatórios</li>
                <li>✓ Editor de queries SQL</li>
                <li>✓ Templates salvos</li>
                <li>✓ Agendamento automático</li>
                <li>✓ Distribuição por email</li>
            </ul>
        </div>
    </div>

    <!-- Formatos de Exportação -->
    <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-8 border border-blue-200">
        <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">📥 Formatos de Exportação Disponíveis</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-lg p-4 text-center shadow-md">
                <div class="text-3xl mb-2">📊</div>
                <div class="font-bold text-gray-900">Excel</div>
                <div class="text-xs text-gray-600">XLSX</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center shadow-md">
                <div class="text-3xl mb-2">📄</div>
                <div class="font-bold text-gray-900">PDF</div>
                <div class="text-xs text-gray-600">High Quality</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center shadow-md">
                <div class="text-3xl mb-2">📑</div>
                <div class="font-bold text-gray-900">CSV</div>
                <div class="text-xs text-gray-600">Data Export</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center shadow-md">
                <div class="text-3xl mb-2">🌐</div>
                <div class="font-bold text-gray-900">HTML</div>
                <div class="text-xs text-gray-600">Interactive</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center shadow-md">
                <div class="text-3xl mb-2">📧</div>
                <div class="font-bold text-gray-900">Email</div>
                <div class="text-xs text-gray-600">Auto-Send</div>
            </div>
        </div>
    </div>

    <!-- CTA Final -->
    <div class="bg-gradient-to-br from-emerald-600 to-teal-600 rounded-2xl p-10 text-center shadow-2xl">
        <h3 class="text-3xl font-bold text-white mb-4">📊 Transforme Dados em Decisões Inteligentes</h3>
        <p class="text-xl text-emerald-100 mb-8 max-w-3xl mx-auto">
            Entre em contato com nossa equipe e descubra como o módulo de Gestão de Implantação pode revolucionar sua operação!
        </p>
        <div class="flex justify-center gap-4 flex-wrap">
            <a href="mailto:comercial@sgqoti.com" class="inline-flex items-center gap-2 bg-white text-emerald-700 px-8 py-4 rounded-xl font-bold text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all">
                📧 <span>Falar com Comercial</span>
            </a>
            <a href="tel:+5511999999999" class="inline-flex items-center gap-2 bg-emerald-500 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all">
                📱 <span>WhatsApp</span>
            </a>
        </div>
        <p class="text-emerald-200 text-sm mt-6">
            💎 Condições especiais para clientes atuais | 🎁 Desconto para pagamento anual
        </p>
    </div>
</section>
