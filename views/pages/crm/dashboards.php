<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>

<section class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"> Dashboards - CRM</h1>
            <p class="text-gray-600 mt-2">Dashboards executivos e operacionais em tempo real</p>
        </div>
    </div>

    <div class="relative overflow-hidden bg-gradient-to-br from-slate-700 via-blue-800 to-indigo-900 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10"></div>
        <div class="relative p-8 md:p-12">
            <div class="max-w-5xl mx-auto">
                <div class="flex items-center gap-3 mb-4 justify-center">
                    <span class="px-4 py-2 bg-blue-400 text-blue-900 text-sm font-bold rounded-full animate-pulse"> TRIAL EM BREVE</span>
                    <span class="px-4 py-2 bg-purple-400 text-purple-900 text-sm font-bold rounded-full"> REAL-TIME ANALYTICS</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-4 text-center">Dashboards Interativos & Personalizáveis</h2>
                <p class="text-xl text-blue-100 mb-8 text-center max-w-3xl mx-auto">Visualize todos os KPIs importantes em dashboards customizáveis com atualização em tempo real. Tome decisões rápidas e assertivas.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 border border-white border-opacity-20">
                        <h3 class="text-white font-bold mb-3 flex items-center gap-2"><span class="text-2xl"></span> Tempo Real</h3>
                        <ul class="space-y-2 text-blue-100 text-sm">
                            <li> Atualização automática de dados</li>
                            <li> Notificações de mudanças críticas</li>
                            <li> Sincronização multi-dispositivo</li>
                            <li> Filtros dinâmicos interativos</li>
                            <li> Drill-down para detalhes</li>
                        </ul>
                    </div>
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 border border-white border-opacity-20">
                        <h3 class="text-white font-bold mb-3 flex items-center gap-2"><span class="text-2xl"></span> Personalização Total</h3>
                        <ul class="space-y-2 text-blue-100 text-sm">
                            <li> Drag & drop para organizar widgets</li>
                            <li> Múltiplos dashboards customizados</li>
                            <li> Compartilhamento com equipe</li>
                            <li> Temas claro/escuro</li>
                            <li> Exportação de imagens/PDF</li>
                        </ul>
                    </div>
                </div>
                <div class="text-center mb-6">
                    <div class="flex items-baseline justify-center gap-2 mb-2">
                        <span class="text-6xl font-bold text-white">R$ 900</span>
                        <span class="text-2xl text-blue-200">/mês</span>
                    </div>
                    <p class="text-blue-200 text-lg">CRM Completo com todos os módulos</p>
                </div>
                <div class="flex gap-4 justify-center">
                    <button disabled class="bg-white text-indigo-700 px-8 py-4 rounded-xl font-bold text-lg shadow-2xl opacity-60 cursor-not-allowed"> Trial Em Breve</button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-8 border border-blue-200 shadow-lg">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3"><span class="text-4xl"></span>Dashboard Executivo</h3>
            <div class="space-y-4">
                <div class="bg-white rounded-lg p-4 border border-blue-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900">KPIs Estratégicos</span>
                        <span class="text-blue-600 font-bold"></span>
                    </div>
                    <p class="text-sm text-gray-600">Receita, MRR, LTV, CAC, Churn Rate e mais</p>
                </div>
                <div class="bg-white rounded-lg p-4 border border-blue-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900">Visão 360° do Negócio</span>
                        <span class="text-blue-600 font-bold"></span>
                    </div>
                    <p class="text-sm text-gray-600">Pipeline, forecast, metas e performance</p>
                </div>
                <div class="bg-white rounded-lg p-4 border border-blue-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900">Análise de Tendências</span>
                        <span class="text-blue-600 font-bold"></span>
                    </div>
                    <p class="text-sm text-gray-600">Gráficos de evolução e comparativos</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-8 border border-indigo-200 shadow-lg">
            <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3"><span class="text-4xl"></span>Dashboard Operacional</h3>
            <div class="space-y-4">
                <div class="bg-white rounded-lg p-4 border border-indigo-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900">Atividades do Dia</span>
                        <span class="text-indigo-600 font-bold"></span>
                    </div>
                    <p class="text-sm text-gray-600">Tarefas, reuniões, follow-ups pendentes</p>
                </div>
                <div class="bg-white rounded-lg p-4 border border-indigo-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900">Performance Individual</span>
                        <span class="text-indigo-600 font-bold"></span>
                    </div>
                    <p class="text-sm text-gray-600">Metas, conversões, ranking da equipe</p>
                </div>
                <div class="bg-white rounded-lg p-4 border border-indigo-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900">Leads & Oportunidades</span>
                        <span class="text-indigo-600 font-bold"></span>
                    </div>
                    <p class="text-sm text-gray-600">Pipeline, leads quentes, próximos passos</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-8 shadow-xl">
        <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center"> Tipos de Visualizações Disponíveis</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3"><span class="text-3xl"></span></div>
                <h4 class="font-bold text-gray-900 mb-1">Gráficos</h4>
                <p class="text-xs text-gray-600">Barras, Linhas, Pizza</p>
            </div>
            <div class="text-center">
                <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3"><span class="text-3xl"></span></div>
                <h4 class="font-bold text-gray-900 mb-1">Cards KPI</h4>
                <p class="text-xs text-gray-600">Métricas Principais</p>
            </div>
            <div class="text-center">
                <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3"><span class="text-3xl"></span></div>
                <h4 class="font-bold text-gray-900 mb-1">Tabelas</h4>
                <p class="text-xs text-gray-600">Dados Detalhados</p>
            </div>
            <div class="text-center">
                <div class="bg-orange-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3"><span class="text-3xl"></span></div>
                <h4 class="font-bold text-gray-900 mb-1">Mapas</h4>
                <p class="text-xs text-gray-600">Geolocalização</p>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-slate-700 to-indigo-900 rounded-2xl p-10 text-center shadow-2xl">
        <h3 class="text-3xl font-bold text-white mb-4"> Visualize Seus Dados em Tempo Real!</h3>
        <p class="text-xl text-blue-100 mb-8 max-w-3xl mx-auto">Entre em contato e descubra como nossos dashboards podem facilitar sua gestão e tomada de decisão.</p>
        <div class="flex justify-center gap-4">
            <a href="mailto:comercial@sgqoti.com" class="inline-block bg-white text-indigo-700 px-8 py-4 rounded-xl font-bold text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all"> Solicitar Demo</a>
            <a href="tel:+5511999999999" class="inline-block bg-blue-500 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all"> WhatsApp</a>
        </div>
    </div>
</section>

